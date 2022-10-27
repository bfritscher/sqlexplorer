const knex = require("../../config").knexDb;

function getAssignment(id) {
  return knex.first().from("assignments").where("id", id);
}

async function getAssignmentWithQuestionList(req, res, next) {
  try {
    const [assignment, questions] = await Promise.all([
      getAssignment(req.params.id),
      getQuestionsWithState(req.params.id, req.session.lti.user.id),
    ]);
    if (assignment === undefined) {
      throw new Error(
        `Unable to find an assignment with this id : ${req.params.id}.`
      );
    }
    if (questions.length === 0) {
      throw new Error(
        `The assignment with id ${req.params.id} does not have any related questions.`
      );
    }
    assignment.questions = questions;
    res.assignmentData = assignment;
    next();
  } catch (reason) {
    next(reason);
  }
}

function getQuestionsWithState(assignmentId, ltiUserId) {
  const filteredState = knex("question_state")
    .select("is_correct", "question_id")
    .where("lti_user_id", ltiUserId)
    .andWhere("assignment_id", assignmentId)
    .as("qst");
  return knex("question_schemas AS qs")
    .select("qs.*", "qst.*")
    .innerJoin("assignment_questions AS aq", "aq.question_id", "qs.id")
    .leftJoin(filteredState, "qst.question_id", "qs.id")
    .where("aq.assignment_id", assignmentId)
    .orderBy("aq.aq_order");
}

module.exports = {
  getAssignment,
  getAssignmentWithQuestionList,
  getQuestionsWithState,
};
