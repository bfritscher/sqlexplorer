exports.up = function (knex) {
  return knex.schema.createTable("lti_consumers", (t) => {
    t.bigIncrements("id").primary();
    t.string("name").notNullable();
    t.string("key").notNullable().unique();
    t.string("secret").notNullable();
  });
};

exports.down = function (knex) {
  return knex.schema.dropTable("lti_consumers");
};
