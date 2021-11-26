exports.up = function (knex) {
  return knex.schema.createView("question_schemas", (view) => {
    view.columns(["id", "db_schema", "schema"]);
    view.as(
      knex.raw(`SELECT questions.id,
      questions.db_schema,
      questions.text,
      regexp_replace(regexp_replace("substring"(questions.sql, '^SELECT *(?:DISTINCT)? *(.*?) *FROM.*?'::text), ', .*? AS '::text, ', '::text, 'gs'::text), '^.*? AS '::text, ''::text, 'gs'::text)
      FROM questions;`)
    );
  });
};

exports.down = function (knex) {
  return knex.schema.dropViewIfExists("question_schemas");
};
