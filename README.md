# SQL Explorer

SQL tutoring system, which allows to run query against a real database via a web interface.
Assignments can be created and used in external systems such as Moodle via LTI integration.

The frontend in a Vue.js 3 application which communicates with the backend via REST API.
The backend is a Node.js Express application, that connects to a PostgreSQL database (that contains the application data), along with several databases on an SQL Server (each of which represents an assignment subject).

## Concepts

### Database
The application data is stored in a PostgreSQL database with name sqlexplorer by default.

Data for user queries are stored in separate databases with the name `mpe_<name_to_display>`.
This part could also be done with another database Oracle, SQL Server, etc with some minior modification of the `evaluateQuery` function.

### Question
A question can be created for any user database. A question has a text field which is the questions and an answer field which is a SQL query
When the question is displayed the select schema part is extracted and displayed automatically


### Assignment
An assignmentis a collection of questions from one or more databases.
An assignment can be accessed directly without saving data or via LTI integration to support user state persistence.


### LTI

This application is compatible with the [LTI standard][lti], under its v1.1 version.
All LTI related code is found in the `lti` folder.


## Deployment

Backend configuration is done by copying the `backend/config/env.sample.js` file to `backend/config/env.production.js`

Frontend has some configuration in `frontend/src/config.js`

Some additonal variable can be configured in the `docker-compose.production.yml` file

(docker-compose file assumes that a traefik container is already deployed on an exisitng web netowork)

After running `docker-compose -f docker-compose.production.yml up --build ` the application will start and initialize the database.

After that you can add your first database via PgAdmmin or any other PostgreSQL client.
After creating a database named `mpe_<name_to_display>` it will show up in the /admin/ part of the webclient and select permissions to the user can be automatically granted via the checkmark.

### LTI configuration example for moodle

Create a new Consumer key / Shared Secret in the `lti_consumers` table in the database.

Create an External tool activity in moodle with the following configuration:
```
Tool name: SQL Explorer
LTI version: LTI 1.0/1.1
Consumer key:   <your consumer key>
Shared secret: <your shared secret>
Check: Supports Deep Linking (Content-Item Message)
Content Selection URL: https://<hostname>/lti/select
Show more...
Secure icon URL: https://<hostname>/sqlexplorer.svg or https://<hostname>/sqlexplorer.png
```
You can then use the `Select content` button to choose the assignment to be used.

## Contributors
- Boris Fritscher
- Mathias Oberson (first version of LTI integration)

[lti]: https://www.imsglobal.org/activity/learning-tools-interoperability
