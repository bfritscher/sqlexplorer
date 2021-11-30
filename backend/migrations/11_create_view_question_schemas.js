exports.up = function (knex) {
  return knex.schema.createView("question_schemas", (view) => {
    view.columns(["id", "db_schema", "text", "schema"]);
    view.as(
      knex.raw(`SELECT questions.id,
      questions.db_schema,
      questions.text,
      regexp_replace(regexp_replace(substring(sql from '^SELECT *(?:DISTINCT)? *(.*?) *FROM.*?'), ', .*? AS ',', ','gs'), '^.*? AS ', '', 'gs')
      FROM questions;`)
    );
  });
};

exports.down = function (knex) {
  return knex.schema.dropViewIfExists("question_schemas");
};
