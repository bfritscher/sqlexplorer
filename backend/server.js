const express = require("express");
const fileUpload = require("express-fileupload");
const Pool = require("pg").Pool;
const Client = require("pg").Client;
const app = express();
const cors = require("cors");
const Raven = require("raven");
const passport = require("passport");
const BasicStrategy = require("passport-http").BasicStrategy;
const ltiRouter = require("./lti/router");
const { isInstructor } = require("./lti/services/lti-user");
const path = require("path");
const logger = require("morgan");
const Papa = require("papaparse");
const config = require("./config");

// to run user sent queries
const pgUserConfig = {
  user: config.pgsql.user.user,
  password: config.pgsql.user.password,
  host: config.pgsql.user.host || "localhost",
  port: config.pgsql.user.port || 5432,
  database: config.pgsql.user.database,
};

// to run frontend view and admin queries
const pgAdminConfig = {
  user: config.pgsql.admin.user,
  password: config.pgsql.admin.password,
  host: config.pgsql.admin.host || "localhost",
  port: config.pgsql.admin.port || 5432,
  database: config.pgsql.admin.database,
};

const pgAdminPool = new Pool(pgAdminConfig);

if (process.env.NODE_ENV === "production") {
  Raven.config(config.sentry.dsn).install();
  app.use(Raven.requestHandler());
  app.use(Raven.errorHandler());
}

// Express settings

// Mandatory so that Express can access the initial request data instead of only the proxy request data.
app.enable("trust proxy");

app.use(logger("dev"));
app.use(
  fileUpload({
    createParentPath: true,
  })
);

pgAdminPool.on("error", (err) => Raven.captureException(err));

passport.use(
  new BasicStrategy(function (username, password, done) {
    if (username === "admin" && password === config.app.adminPassword) {
      done(null, true);
    } else {
      done(null, false);
    }
  })
);

app.use(
  cors({
    origin: true, //  reflect the request origin, as defined by req.header('Origin')
    credentials: true,
  })
);
app.use(express.static(path.join(__dirname, "/public")));
app.use("/schema_pics", express.static(path.join(__dirname, "/schema_pics")));
app.use("/assets", express.static(path.join(__dirname, "/views/assets")));
app.use(passport.initialize());

const jsonParser = express.json();

app.use("/lti", ltiRouter);

app.use((err, req, res, next) => {
  res.status(500);
  res.json({ error: err.toString() });
});

async function executeQuery(res, sql, args) {
  let client;
  try {
    client = await pgAdminPool.connect();
  } catch (err) {
    console.error("error fetching client from pool", err);
    Raven.captureException(err);
    res.sendStatus(500);
    return;
  }
  let result;
  try {
    result = await client.query(sql, args);
  } catch (err) {
    console.error("error running query", err);
    Raven.captureException(err);
    res.sendStatus(500);
  } finally {
    client.release();
  }
  return result;
}

function isAdminAuthBasic(req, res, next) {
  return new Promise((resolve, reject) => {
    passport.authenticate("basic", { session: false }, (err, isAdmin) => {
      if (err) {
        resolve(false);
      } else {
        resolve(isAdmin);
      }
    })(req, res, next);
  });
}

app.get("/api/db/list", async (req, res) => {
  const result = await executeQuery(
    res,
    `SELECT substring(datname from 5) as name
        FROM pg_database
        WHERE datname LIKE 'mpe_%'
        ORDER BY datname`
  );
  result && res.json(result.rows);
});

app.get("/api/assignment/list", async (req, res) => {
  const result = await executeQuery(
    res,
    `SELECT a.id, a.name, a.year, a.course, a.description, a.start_date, a.end_date, COUNT(aq.assignment_id) AS nb
        FROM assignments a
        LEFT JOIN assignment_questions aq ON a.id = aq.assignment_id
        GROUP BY a.id, a.name, a.year, a.course, a.description, a.start_date, a.end_date
        ORDER BY a.year DESC, a.course ASC, a.name ASC`
  );
  result && res.json(result.rows);
});
/*
app.get("/api/questions/:dbname", async (req, res) => {
  // TODO: by dbname or public assignments?
  let assignment = await executeQuery(
    res,
    `SELECT * FROM assignments WHERE name = $1`,
    [req.params.dbname]
  );
  if (assignment && assignment.rows.length === 0) {
    return res.sendStatus(404);
  }
  assignment = assignment.rows[0];
  const questions = await executeQuery(
    res,
    `SELECT * FROM assignment_questions aq
      JOIN questions q ON q.id = question_id
      WHERE aq.assignment_id = $1
      ORDER BY aq.aq_order`,
    [assignment.id]
  );
  if (assignment && questions) {
    assignment.questions = questions.rows;
    res.json(assignment);
  }
});
*/

app.get("/api/assignment/:id", async (req, res, next) => {
  if (isNaN(req.params.id)) {
    return res.sendStatus(404);
  }
  const isAdmin = await isAdminAuthBasic(req, res, next);

  const assignment = await executeQuery(
    res,
    `SELECT * FROM assignments WHERE id = $1`,
    [req.params.id]
  );

  const questionSource = isAdmin ? "questions" : "question_schemas";
  const questions = await executeQuery(
    res,
    `SELECT * FROM assignment_questions aq
      JOIN ${questionSource} q ON q.id = question_id
      WHERE aq.assignment_id = $1
      ORDER BY aq.aq_order`,
    [req.params.id]
  );
  if (assignment && assignment.rows.length === 1 && questions) {
    const result = assignment.rows[0];
    result.questions = questions.rows;
    res.json(result);
  }
});

app.get("/api/db/:name", async (req, res) => {
  const client = new Client(
    Object.assign({}, pgUserConfig, { database: `mpe_${req.params.name}` })
  );
  try {
    await client.connect();
    const result =
      await client.query(`SELECT table_name, json_agg(column_name) AS columns
    FROM information_schema.columns
    WHERE table_schema = 'public'
    GROUP BY table_name
    ORDER BY table_name`);
    result &&
      res.json(
        result.rows.reduce((acc, cur) => {
          acc[cur.table_name] = cur.columns;
          return acc;
        }, {})
      );

    client.end();
  } catch (err) {}
});

app.get("/api/questiontext/:id", async (req, res) => {
  const result = await executeQuery(
    res,
    "SELECT * FROM question_schemas WHERE id = $1",
    [req.params.id]
  );
  if (result.rows.length > 0) {
    res.json(result.rows[0]);
  } else {
    res.sendStatus(404);
  }
});

app.post("/api/evaluate", ltiRouter.ltiSession, jsonParser, (req, res) => {
  if (!req.body.db || !req.body.sql) return res.sendStatus(400);
  evaluateQuery(req, res);
});

app.post("/api/evaluate/csv", jsonParser, (req, res) => {
  if (!req.body.db || !req.body.sql) return res.sendStatus(400);
  evaluateQuery(req, res, true);
});

// ADMIN
app.post(
  "/api/db/:name",
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    if (!req.files || Object.keys(req.files).length === 0) {
      return res.status(400).send("No files were uploaded.");
    }

    const uploadPath = path.join(
      __dirname,
      "/schema_pics",
      `${req.params.name}.png`
    );

    req.files.file.mv(uploadPath, (err) => {
      if (err) return res.status(500).send(err);

      res.sendStatus(200);
    });
  }
);

app.post(
  "/api/db/:name/permissions",
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const client = new Client(
      Object.assign({}, pgAdminConfig, { database: `mpe_${req.params.name}` })
    );
    try {
      await client.connect();

      const user = config.pgsql.user.user;

      const result = await client.query(
        `SELECT t.table_name, COUNT(p.privilege_type) = 1 AS enabled
    FROM information_schema.tables t
    LEFT JOIN information_schema.table_privileges p
        ON t.table_catalog = p.table_catalog
               AND t.table_schema = p.table_schema
               AND t.table_name = p.table_name
               AND privilege_type = 'SELECT'
               AND grantee=$1
    WHERE t.table_type='BASE TABLE' AND t.table_schema = 'public'
    GROUP BY t.table_name;`,
        [user]
      );
      for (const row of result.rows) {
        if (!row.enabled) {
          await client.query(`GRANT SELECT ON ${row.table_name} TO ${user}`);
        }
      }
      res.json(result.rows.length);
      client.end();
    } catch (err) {
      Raven.captureException(err);
      res.sendStatus(500);
      client.end();
    }
  }
);

app.get(
  "/api/question/:id",
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const result = await executeQuery(
      res,
      `SELECT q.*, qv.schema
       FROM questions q
       JOIN question_schemas qv ON qv.id = q.id
       WHERE q.id = $1`,
      [req.params.id]
    );
    if (result && result.rows.length > 0) {
      res.json(result.rows[0]);
    } else {
      res.sendStatus(404);
    }
  }
);

app.post(
  "/api/question",
  jsonParser,
  passport.authenticate("basic", { session: false }),
  function (req, res) {
    if (!req.body) return res.sendStatus(400);
    upsertQuestion(req, res);
  }
);

app.get(
  "/api/tags",
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const result = await executeQuery(
      res,
      `SELECT name, Count(q.id) AS nb
        FROM keywords k
        LEFT JOIN questions q ON lower(q.sql) LIKE  '%' || lower(k.name) || '%'
        GROUP BY name
        ORDER BY name`
    );
    result && res.json(result.rows);
  }
);

app.post(
  "/api/assignment",
  jsonParser,
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const result = await executeQuery(
      res,
      "INSERT INTO assignments (name, year, course) VALUES ($1, $2, $3) RETURNING id",
      [req.body.name, req.body.year, req.body.course]
    );

    if (result.rows.length > 0) {
      res.json(result.rows[0]);
    } else {
      res.sendStatus(500);
    }
  }
);

app.post(
  "/api/assignment/:assignmentId",
  jsonParser,
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const result = await executeQuery(
      res,
      `UPDATE assignments SET
       name = $2,
       course = $3,
       year = $4,
       description = $5,
       start_date = $6,
       end_date = $7
      WHERE id = $1 RETURNING id`,
      [
        req.params.assignmentId,
        req.body.name,
        req.body.course,
        req.body.year,
        req.body.description,
        req.body.start_date,
        req.body.end_date,
      ]
    );

    if (result && result.rows.length > 0) {
      res.json(result.rows[0]);
    } else {
      res.sendStatus(500);
    }
  }
);

app.post(
  "/api/assignment/:assignmentId/question",
  jsonParser,
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const result = await executeQuery(
      res,
      `INSERT INTO assignment_questions (assignment_id, question_id, aq_order)
        VALUES ($1, $2, (SELECT COALESCE(MAX(aq_order), 0) + 1 FROM assignment_questions WHERE assignment_id = $1))`,
      [req.params.assignmentId, req.body.questionId]
    );
    result && res.sendStatus(200);
  }
);

app.delete(
  "/api/assignment/:assignmentId/question/:questionId",
  jsonParser,
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const result = await executeQuery(
      res,
      `DELETE FROM assignment_questions WHERE assignment_id = $1 AND question_id = $2`,
      [req.params.assignmentId, req.params.questionId]
    );
    result && res.sendStatus(200);
  }
);

app.post(
  "/api/assignment/:assignmentId/order",
  jsonParser,
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    if (!req.body || !req.body.order) return res.sendStatus(400);
    let sql = `UPDATE assignment_questions AS aq SET
    aq_order = u.aq_order
FROM (VALUES `;
    sql += req.body.order
      .map((row) => {
        return `(${parseInt(row[0], 10)} , ${parseInt(row[1], 10)})`;
      })
      .join(", ");
    sql += `) AS u(question_id, aq_order)
    WHERE aq.question_id = u.question_id
    AND aq.assignment_id = $1`;
    try {
      await executeQuery(res, sql, [parseInt(req.params.assignmentId, 10)]);
      res.sendStatus(200);
    } catch (e) {}
  }
);

app.post(
  "/api/questions",
  jsonParser,
  passport.authenticate("basic", { session: false }),
  (req, res) => {
    if (!req.body) return res.sendStatus(400);
    getQuestionsByKeywords(req, res);
  }
);

// LTI LOGS

app.post(
  "/api/logs",
  ltiRouter.ltiSession,
  jsonParser,
  async (req, res, next) => {
    // admin via basic auth or lti session instructor
    const isAdmin = await isAdminAuthBasic(req, res, next);
    const isVaidInstructor = !!(
      req.session &&
      req.session.lti &&
      isInstructor(req.session.lti)
    );
    if (!(isAdmin || isVaidInstructor)) {
      return res.sendStatus(403);
    }

    let result = { rows: [] };

    // using same api endpoint for all queries
    // TODO: SECURITY if via lti session should only show logs for that lti provider and same assignment
    if (req.body.search) {
      result = await executeQuery(
        res,
        `SELECT lti_user.*, lti_consumers.name AS lti_source,
        lti_user.firstname || ' ' || lti_user.lastname || ' (' || lti_consumers.name || ')' AS label
        FROM lti_user
        JOIN lti_consumers ON lti_user.lti_tc_id = lti_consumers.id
        WHERE firstname ILIKE $1 OR lastname ILIKE $1 LIMIT 50`,
        [`%${req.body.search}%`]
      );
    } else if (
      req.body.question_id &&
      req.body.assignment_id &&
      req.body.user_id
    ) {
      result = await executeQuery(
        res,
        `SELECT r.* FROM response r
      JOIN question_state qs on r.question_state_id = qs.id
      WHERE qs.question_id = $1 AND qs.assignment_id = $2 AND qs.lti_user_id = $3
      ORDER BY r.submitted_at DESC`,
        [req.body.question_id, req.body.assignment_id, req.body.user_id]
      );
    } else if (req.body.user_id) {
      const params = [req.body.user_id];
      let filter = "";
      if (req.body.assignment_id) {
        params.push(req.body.assignment_id);
        filter = "AND a.id = $2";
      }
      result = await executeQuery(
        res,
        `SELECT user_id,
      firstname,
      lastname,
      json_agg(json_build_object('id', assignment_id, 'name', assignment_name, 'questions', questions, 'nb_open',
                                 assignment_nb_open
          , 'nb_correct', assignment_nb_correct, 'nb_questions', (SELECT COUNT(*)
                                                                  FROM assignment_questions aa
                                                                  WHERE aa.assignment_id = a.assignment_id))) AS assignments
FROM (
        SELECT user_id,
               firstname,
               lastname,
               assignment_id,
               assignment_name,
               COUNT(*)                                                                   AS assignment_nb_open,
               COUNT(CASE WHEN question_correct THEN 1 END)                               AS assignment_nb_correct,
               json_agg(json_build_object('id', question_id, 'text', question_text, 'is_correct', question_correct,
                                          'q_order',
                                          question_order, 'attempts', question_attempts)) AS questions
        FROM (SELECT user_id,
                     firstname,
                     lastname,
                     assignment_id,
                     assignment_name,
                     question_id,
                     question_text,
                     question_correct,
                     question_order,
                     COUNT(attempt_submitted_at) AS question_attempts
              FROM (SELECT u.id           AS user_id,
                           u.firstname,
                           u.lastname,
                           a.id           AS assignment_id,
                           a.name         AS assignment_name,
                           q.id           AS question_id,
                           q.text         AS question_text,
                           qs.is_correct  AS question_correct,
                           qa.aq_order    AS question_order,
                           r.submitted_at AS attempt_submitted_at
                    FROM assignments a
                             JOIN assignment_questions qa ON a.id = qa.assignment_id
                             JOIN questions q ON q.id = qa.question_id
                             JOIN question_state qs ON qa.question_id = qs.question_id AND qa.assignment_id = a.id
                             JOIN lti_user u ON qs.lti_user_id = u.id
                             JOIN response r ON r.question_state_id = qs.id
                       WHERE u.id = $1 ${filter}
                   ) l
              GROUP BY user_id, firstname, lastname,
                       assignment_id, assignment_name,
                       question_id, question_text, question_correct, question_order
              ORDER BY lastname, firstname, assignment_name, question_order) q
        GROUP BY user_id, firstname, lastname,
                 assignment_id, assignment_name
        ORDER BY lastname, firstname, assignment_name) a
GROUP BY user_id, firstname, lastname`,
        params
      );
    } else if (req.body.assignment_id) {
      result = await executeQuery(
        res,
        ` SELECT json_agg(row_to_json(a)) AS questions FROM (SELECT qa.question_id, q.text, COUNT(qs.question_id) nb_users_attempted, COUNT(CASE WHEN qs.is_correct THEN 1 END) nb_users_finished
      FROM assignment_questions qa
      JOIN questions q ON qa.question_id = q.id
      LEFT JOIN question_state qs ON qa.question_id = qs.question_id AND qa.assignment_id = qs.assignment_id
      WHERE qa.assignment_id = $1
      GROUP BY qa.question_id, q.text, qa.aq_order
      ORDER BY qa.aq_order) a`,
        [req.body.assignment_id]
      );
    }
    result && res.json(result.rows);
  }
);

// legacy logs
/*
app.get(
  "/api/logs",
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const result = await executeQuery(
      res,
      `SELECT json_agg(row_to_json(t)) AS json
        FROM (
          SELECT user_name, user_id, json_agg((SELECT row_to_json( _ ) FROM (SELECT activity as name, questions) _ )) as activities
          FROM (
            SELECT user_name, user_id, activity, json_agg((SELECT row_to_json( _ ) FROM (SELECT question_id as id, count) _ )) AS questions
            FROM (
              SELECT user_id, user_name, activity, question_id, COUNT(*) AS count
              FROM logs
              GROUP BY user_id, user_name, activity, question_id
              ORDER BY user_name, user_id, activity, question_id
            ) a
            GROUP BY  user_name, user_id, activity
          ) b
          GROUP BY user_name, user_id
        ) t`
    );
    if (result.rows.length > 0) {
      res.json(result.rows[0].json);
    } else {
      res.sendStatus(404);
    }
  }
);

app.get(
  "/api/logs/:user_id",
  passport.authenticate("basic", { session: false }),
  async (req, res) => {
    const result = await executeQuery(
      res,
      `SELECT activity, question_id, COUNT(*), json_agg((SELECT row_to_json(_) FROM (SELECT query, error, created, ip) _ )) AS attempts
        FROM logs
        WHERE user_id LIKE $1 || \'%\'
        GROUP BY activity, question_id
        ORDER BY activity, question_id`,
      [req.params.user_id]
    );
    result && res.json(result.rows);
  }
);
*/

// user functions

async function evaluateQuery(req, res, csv) {
  const schema = req.body.db.replace(/[^a-z_0-9]/gi, "");
  // since we cannot change database for existing connections, we need to create a new connection
  const data = {
    headers: [],
    content: [],
    numrows: 0,
  };

  async function sendAnswer(correct, msg) {
    const log = {
      activity: schema,
      question_id: undefined,
      query: req.body.sql,
      error: undefined,
      user_id: undefined,
      user_name: undefined,
      ip: req.headers["x-forwarded-for"] || req.connection.remoteAddress,
    };
    if (typeof correct !== "undefined") {
      data.is_correct = correct;
      log.is_correct = correct;
    }
    if (data.is_correct) {
      data.answer = msg;
    } else {
      if (typeof msg !== "undefined") {
        data.error = msg;
        log.error = msg;
      }
    }
    if (req.body.id) {
      log.question_id = req.body.id;
    }
    if (req.body.user_id) {
      log.user_id = req.body.user_id;
    }
    if (req.body.user_name) {
      log.user_name = req.body.user_name;
    }

    if (req.session && req.session.lti) {
      try {
        await ltiRouter.saveResponse({
          lti: req.session.lti,
          sql: req.body.sql,
          is_correct: correct,
          questionId: req.body.id,
        });
      } catch (e) {
        data.error = e.message;
      }
    }
    // log for assignments done via LTI setup
    // logAnswer(log);

    res.json(data);
    client.end();
  } // answer function

  const client = new Client(
    Object.assign({}, pgUserConfig, { database: `mpe_${schema}` })
  );

  try {
    await client.connect();
  } catch (err) {
    console.log("Error acquiring connection", err);
    Raven.captureException(err);
    res.status(500);
    res.send(`Error connecting to database ${schema}`);
    client.end();
    return;
  }

  // execute user request
  const sqlToTest = req.body.sql.replace(/;/g, "");
  let resultUser;
  try {
    resultUser = await client.query(sqlToTest);
    if (resultUser.rowCount > 0) {
      data.headers = resultUser.fields.map((h) => h.name);
      // limit output to 1000 rows and replace null with string 'NULL'
      data.content = resultUser.rows.slice(0, 1000);
      data.numrows = resultUser.rows.length;
    }
  } catch (err) {
    console.log("Error executing user query:", err);
    sendAnswer(false, err.toString());
    client.end();
    return;
  }

  if (csv) {
    const csvData = Papa.unparse(resultUser.rows);
    res.header("Content-Type", "text/csv");
    res.attachment("query.csv");
    res.send(csvData);
    return;
  }

  if (!req.body.id) {
    sendAnswer();
    return;
  }

  // If the request is an answer to a question validate it
  let sqlAnswer;
  try {
    const question = await getQuestionByID(req.body.id);
    sqlAnswer = question.sql.replace(/;/g, "");
  } catch (err) {
    console.log("Error fetching the question: no question for this id");
    client.end();
    return res.sendStatus(404);
  }
  let resultAnswer;
  try {
    resultAnswer = await client.query(sqlAnswer);
  } catch (err) {
    console.log("Error executing query sqlAnswer:", err);
    Raven.captureException(err);
    client.end();
    return res.sendStatus(500);
  }
  // Check that both result sets have the same row length
  if (resultAnswer.rows.length !== resultUser.rows.length) {
    sendAnswer(false, "Vérifier conditions et/ou schéma.");
    return;
  } else if (resultAnswer.fields.length !== resultUser.fields.length) {
    sendAnswer(false, "Vérifier SELECT.");

    return;
  } else {
    // Check that the union of both requests does not yield any result
    const sanitizedSqlToTest = sqlToTest
      .replace(/;/g, "")
      .toUpperCase()
      .replace(/LIMIT\s+(.|\n)*/g, "")
      .replace(/ORDER\s+BY(.|\n)*/g, "");
    const sanitizedSqlAnswer = sqlAnswer
      .replace(/;/g, "")
      .toUpperCase()
      .replace(/LIMIT\s+(.|\n)*/g, "")
      .replace(/ORDER\s+BY(.|\n)*/g, "");
    const sqlSets = `(${sanitizedSqlAnswer} EXCEPT ${sanitizedSqlToTest}) UNION (${sanitizedSqlToTest} EXCEPT ${sanitizedSqlAnswer})`;
    try {
      resultSets = await client.query(sqlSets);
      if (resultSets.rows.length > 0) {
        sendAnswer(false, "Pas la bonne réponse...");
        return;
      }
    } catch (err) {
      sendAnswer(false, `Erreur de vérification : ${err.message}`);
      return;
    }
    //if the returned result is empty the user's query was certainly correct
    //if there is an order by we have to compare lines
    if (sqlAnswer.toUpperCase().match(/ORDER\s+BY/)) {
      var a, b;
      for (let r = 0; r < resultAnswer.rows.length; r++) {
        const fields = Object.keys(resultAnswer.rows[r]);
        for (let field of fields) {
          a = resultAnswer.rows[r][field];
          b = resultUser.rows[r][field];
          if (a.constructor === Date) {
            if (a.getTime() !== b.getTime()) {
              sendAnswer(false, "Vérifier ordre");
              return;
            }
          } else {
            if (a !== b) {
              sendAnswer(false, "Vérifier ordre");
              return;
            }
          }
        }
      }
    }
    sendAnswer(true, sqlAnswer);
  }
}

function getQuestionByID(id, callback) {
  return new Promise((resolve, reject) => {
    pgAdminPool.connect(function (err, client, done) {
      if (err) {
        console.error("error fetching client from pool", err);
        Raven.captureException(err);
        return reject(err);
      }
      client.query(
        "SELECT * FROM questions WHERE id = $1",
        [id],
        function (err, result) {
          if (err) {
            console.error("error running query", err);
            Raven.captureException(err);
            reject(err);
          }
          if (result.rows.length > 0) {
            if (callback) {
              callback(result.rows[0]);
            } else {
              resolve(result.rows[0]);
            }
          } else {
            if (callback) {
              callback();
            } else {
              resolve();
            }
          }
          done();
        }
      );
    });
  });
}

function logAnswer(log) {
  pgAdminPool.connect(function (err, client, done) {
    if (err) {
      console.error("error fetching client from pool", err);
      Raven.captureException(err);
      return;
    }
    client.query(
      "INSERT INTO logs (activity, question_id, query, error, user_id, user_name, ip, created) VALUES($1,$2,$3,$4,$5,$6,$7, NOW())",
      [
        log.activity,
        log.question_id,
        log.query,
        log.error,
        log.user_id,
        log.user_name,
        log.ip,
      ],
      function (err, result) {
        if (err) {
          console.error("error running query", err);
          Raven.captureException(err);
        }
        done();
      }
    );
  });
}

// admin

async function getQuestionsByKeywords(req, res) {
  const keywords = req.body.keywords || [];
  const dbname = (req.body.dbname || "ALL").toUpperCase();
  const andOr = req.body.inclusive === "1" ? "OR" : "AND";

  let sql = `SELECT t.id, t.text, t.sql, t.db_schema, json_agg(keyword) AS keywords
    FROM (
      SELECT q.id, q.text, q.sql, q.db_schema, k.name AS keyword
      FROM questions q
      JOIN keywords k ON lower(q.sql) LIKE  '%' || lower(k.name) || '%'`;

  if (dbname !== "ALL") {
    sql += "WHERE UPPER(q.db_schema) = $" + (keywords.length + 1);
  }

  sql += ` ORDER BY keyword, sql
    ) t
    GROUP BY t.id, t.text, t.sql, t.db_schema`;

  if (keywords.length > 0) {
    sql += " HAVING ";
    keywords.forEach(function (keywords, i) {
      if (i > 0) {
        sql += andOr;
      }
      sql += " $" + (i + 1) + " = ANY(array_agg(keyword)) ";
    });
  }
  sql += " ORDER BY db_schema LIMIT 100";
  if (dbname !== "ALL") {
    keywords.push(dbname);
  }

  const result = await executeQuery(res, sql, keywords);
  if (result) {
    res.json(result.rows);
  }
}

function upsertQuestion(req, res) {
  const question = req.body;
  pgAdminPool.connect(function (err, client, done) {
    if (err) {
      console.error("error fetching client from pool", err);
      Raven.captureException(err);
      res.sendStatus(500);
      return;
    }

    //if id update
    if (question.id) {
      client.query(
        "UPDATE questions SET text=$2, sql=$3, modified = now() WHERE id = $1 RETURNING id",
        [question.id, question.text, question.sql],
        function (err, result) {
          if (err) {
            console.error("error running query", err);
            Raven.captureException(err);
            res.sendStatus(500);
            return;
          }
          if (result.rows.length > 0) {
            res.send(JSON.stringify(result.rows[0]));
          }
          done();
        }
      );
    } else {
      //else insert
      client.query(
        "INSERT INTO questions (db_schema, text, sql, modified) VALUES ($1, $2, $3, now()) RETURNING id",
        [question.db_schema, question.text, question.sql],
        function (err, result) {
          if (err) {
            console.error("error running query", err);
            Raven.captureException(err);
            res.sendStatus(500);
            return;
          }
          if (result.rows.length > 0) {
            res.json(result.rows[0]);
          }
          done();
        }
      );
    }
  });
}
// for SPA routing
app.get("*", (req, res) => {
  res.sendFile(path.resolve(__dirname, "public", "index.html"));
});

app.listen(config.app.port, () => {
  console.log(
    `[${
      new Date().toTimeString().split(" ")[0]
    }] SQLExplorer-backend app listening on port ${config.app.port}!`
  );
});
