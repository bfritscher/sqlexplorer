exports.up = function (knex) {
  return (
    knex.schema
      .createTable("assignments", (t) => {
        t.increments("id").primary();
        t.text("name").notNullable();
        t.string("year", 9).notNullable().defaultTo("");
        t.string("course", 50).notNullable().defaultTo("");
        t.boolean("archived").notNullable().defaultTo(false);
        t.boolean("public").notNullable().defaultTo(false);
        t.timestamp("start_date");
        t.timestamp("end_date");
        t.text("description");
      })
      .createTable("keywords", (t) => {
        t.string("name", 20).primary();
      })
      .createTable("questions", (t) => {
        t.increments("id").primary();
        t.string("db_schema").notNullable();
        t.text("text").notNullable();
        t.text("sql").notNullable();
        t.timestamp("modified").defaultTo(knex.fn.now());
      })
      .createTable("assignment_questions", (t) => {
        t.integer("assignment_id")
          .notNullable()
          .references("id")
          .inTable("assignments");
        t.integer("question_id")
          .notNullable()
          .references("id")
          .inTable("questions");
        t.integer("aq_order").notNullable();
        t.primary(["assignment_id", "question_id"]);
      })
      // used for logAnswer in evaluate if enabled
      .createTable("logs", (t) => {
        t.bigIncrements("id").primary();
        t.string("activity", 50).notNullable();
        t.integer("question_id");
        t.text("query");
        t.text("error");
        t.string("user_id");
        t.timestamp("created").defaultTo(knex.fn.now());
        t.string("ip", 50);
        t.string("user_name", 50);
      })
      .then(() => {
        return knex("keywords").insert([
          { name: "Count" },
          { name: "To_Char" },
          { name: "Max" },
          { name: "Min" },
          { name: "Avg" },
          { name: "IS NULL" },
          { name: "NOT" },
          { name: "Decode" },
          { name: "Trunc" },
          { name: "sysdate" },
          { name: "Instr" },
          { name: "Upper" },
          { name: "To_Date" },
          { name: "Substr" },
          { name: ">" },
          { name: "<" },
          { name: "=" },
          { name: ">=" },
          { name: "<=" },
          { name: "<>" },
          { name: "||" },
          { name: "Mod" },
          { name: "ASC" },
          { name: "Lower" },
          { name: "+" },
          { name: "*" },
          { name: "/" },
          { name: "-" },
          { name: "InitCap" },
          { name: "Length" },
          { name: "Round" },
          { name: "Add_Months" },
          { name: "Months_Between" },
          { name: "Last_Day" },
          { name: "To_Number" },
          { name: "ALL" },
          { name: "ANY" },
          { name: "EXISTS" },
          { name: "INTERSECT" },
          { name: "UNION" },
          { name: "UNION ALL" },
          { name: "MINUS" },
          { name: "CASE" },
          { name: "Sum" },
          { name: "OUTER" },
          { name: "RIGHT" },
          { name: "LEFT" },
          { name: "FULL" },
          { name: "AND " },
          { name: "AS " },
          { name: "BETWEEN " },
          { name: " DESC" },
          { name: "DISTINCT " },
          { name: "FROM " },
          { name: "GROUP BY " },
          { name: "HAVING " },
          { name: "INNER JOIN " },
          { name: "JOIN " },
          { name: "LEFT JOIN " },
          { name: "NOT IN " },
          { name: "OR " },
          { name: "ORDER BY " },
          { name: "RIGHT JOIN " },
          { name: "SELECT " },
          { name: "WHERE " },
          { name: "IS NOT NULL " },
          { name: "LIKE " },
          { name: " DUAL" },
          { name: "INNER " },
          { name: " IN " },
        ]);
      })
  );
};

exports.down = function (knex) {
  return knex.schema
    .dropTable("assignments")
    .dropTable("keywords")
    .dropTable("questions")
    .dropTable("assignment_questions")
    .dropTable("logs");
};
