const express = require("express");
const session = require("express-session");
const RedisStore = require("connect-redis")(session);
const { merge } = require("lodash");

const config = require("../config");
const {
  redisClient,
  ltiRequestValidator,
  ltiSessionValidator,
  ltiXAssignmentValidator,
  ltiInstructorValidator,
} = require("./route-validators");
const LtiContentItemLink = require("./models/lti-content-item-link.class");
const ResponseFormData = require("./models/response-form-data.class");
const assignmentService = require("../lti/services/assignments");
const ltiUserService = require("./services/lti-user");
const responseService = require("./services/responses");
const questionService = require("./services/questions");
const { LtiSessionError } = require("./utils/custom-errors");

const router = express.Router();

const ltiSession = session(
  merge(
    { store: new RedisStore({ client: redisClient }) },
    {
      resave: false,
      saveUninitialized: false,
    },
    config.session
  )
);

router.use(ltiSession);
router.use(express.urlencoded({ extended: true }));
router.use(express.json());

/**
 * Route that should be accessed by the LTI Tool Coonsumer when a user wants to select a Tool activity
 * It renders the `item-selection` templates containing the list of all the available assignments.
 */
router.post("/select", ltiRequestValidator, async (req, res) => {
  res.redirect(`${config.app.frontUrl}/admin/ltiselect`);
});

/**
 * Route that should be accessed by the LTI Tool Consumer when a user has selected an assignment.
 * It sends an LTI Response to the TC that contains the properties of the selected assignment.
 */
router.post("/selected", ltiSessionValidator, async (req, res) => {
  try {
    const returnUrl = req.session.lti.rawData.content_item_return_url;
    const consumer = req.session.lti.consumer;
    const assignment = await assignmentService.getAssignment(req.body.id);
    const itemCustomValues = {
      assignment_id: assignment.id,
    };
    const item = new LtiContentItemLink(
      assignment.name,
      assignment.description,
      itemCustomValues
    );
    const formData = new ResponseFormData(returnUrl, consumer, item);
    formData.signedWith(consumer);
    res.json(formData);
  } catch (err) {
    console.log(err);
    res.status(500);
    res.send(err);
  }
});

/**
 * This is the entry point of any LTI assignment.
 * It check the access rights and then redirect to the requested assignment.
 */
router.post(
  "/launch",
  ltiRequestValidator,
  ltiUserService.upsert,
  (req, res, next) => {
    req.session.save((err) => {
      if (err) next(err);
      res.redirect(
        `${config.app.frontUrl}/assignmentlti/${req.session.lti.rawData.custom_assignment_id}`
      );
    });
  }
);

/**
 * **This route can only be accessed if the user accessed the assignment through an LTI Launch Request.**
 *
 * Loads an assignment based on its `:id`.
 * If one tries to access this route directly, an error will be shown instead.
 */
router.get(
  "/assignment/:id",
  ltiSessionValidator,
  ltiXAssignmentValidator,
  assignmentService.getAssignmentWithQuestionList,
  (req, res) => {
    res.json(res.assignmentData);
  }
);

/**
 * **This route can only be accessed if the user accessed the assignment through an LTI Launch Request.**
 *
 * Route called by the front-end when a user submit their response to a specific SQL question.
 * It saves the response in the DB and, if neceserry, notifiy the TC that the user's note should be updated.
 */
async function saveResponse(data) {
  const userId = data.lti.user.id;
  const responseData = {
    assignment_id: data.lti.rawData.custom_assignment_id,
    question_id: data.questionId,
    lti_user_id: userId,
    sql: data.sql,
    is_correct: data.is_correct,
  };
  if (!ltiUserService.isInstructor(data.lti)) {
    const questionStateId = await responseService.upsertQuestionState(
      responseData
    );
    await responseService.addToQuestionState(questionStateId, responseData);
    await ltiUserService.updateScore(data);
  }
}

/**
 * **This route can only be accessed if the user accessed the assignment through an LTI Launch Request.**
 *
 * Route that allows to retrieve a user's question history, that is all the responses they submitted for this question of this assignment.
 */
router.get(
  "/assignment/:id/question/:qId/history",
  ltiSessionValidator,
  ltiXAssignmentValidator,
  async (req, res, next) => {
    try {
      const history = await responseService.getResponseHistory(
        req.params.id,
        req.params.qId,
        req.session.lti.user.id
      );
      res.json(history);
    } catch (error) {
      res.status(500).json([]);
    }
  }
);

router.get(
  "/assignment/:id/question/:qId/solution",
  ltiSessionValidator,
  ltiXAssignmentValidator,
  ltiInstructorValidator,
  async (req, res, next) => {
    try {
      const questionSolution = await questionService.getSolution(
        req.params.qId
      );
      res.send(questionSolution);
    } catch (err) {
      res.status(500).json(err);
    }
  }
);

/**
 * **This route can only be accessed if the user accessed the assignment through an LTI Launch Request.**
 *
 * Send all the current user's data that have been provided by the TC by the original LTI Launch Request.
 */
router.get("/me", ltiSessionValidator, (req, res) => {
  const me = req.session.lti.user;
  // Checks if the current user has the `Instructor` LTI Roles and add this to the returned object.
  me.isInstructor = ltiUserService.isInstructor(req.session.lti);
  res.json(me);
});

const routerModule = (module.exports = router);
routerModule.ltiSession = ltiSession;
routerModule.saveResponse = saveResponse;
