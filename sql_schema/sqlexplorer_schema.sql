--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: sqlexplorer; Type: DATABASE; Schema: -; Owner: -
--

CREATE DATABASE "sqlexplorer" WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8';


\connect "sqlexplorer"

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: array_agg(anyelement); Type: AGGREGATE; Schema: public; Owner: -
--

CREATE AGGREGATE array_agg(anyelement) (
    SFUNC = array_append,
    STYPE = anyarray,
    INITCOND = '{}'
);


--
-- Name: textcat_all(text); Type: AGGREGATE; Schema: public; Owner: -
--

CREATE AGGREGATE textcat_all(text) (
    SFUNC = textcat,
    STYPE = text,
    INITCOND = ''
);


SET default_with_oids = false;

--
-- Name: assignments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE assignments (
    name character varying(255) NOT NULL,
    description text,
    chapter_id integer NOT NULL,
    order_no integer DEFAULT 0 NOT NULL,
    modified timestamp without time zone
);


--
-- Name: chapters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE chapters (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    order_no integer DEFAULT 0 NOT NULL,
    category character varying(255),
    number numeric
);


--
-- Name: chapters_id_seq1; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE chapters_id_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: chapters_id_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE chapters_id_seq1 OWNED BY chapters.id;


--
-- Name: logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE logs (
    id bigint NOT NULL,
    activity character varying(255) NOT NULL,
    question_id integer,
    query text,
    error text,
    "user" character varying(255),
    created timestamp without time zone,
    result boolean DEFAULT false,
    ip character varying(15)
);


--
-- Name: log_activity_group; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW log_activity_group AS
SELECT logs.activity, logs."user", logs.result, count(*) AS count FROM logs GROUP BY logs."user", logs.activity, logs.result ORDER BY logs."user";


--
-- Name: log_activity_result; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW log_activity_result AS
SELECT a1.activity, a1."user", max(a1.count) AS "false", max(a2.count) AS "true" FROM (log_activity_group a1 LEFT JOIN log_activity_group a2 ON ((((((a1.activity)::text = (a2.activity)::text) AND ((a1."user")::text = (a2."user")::text)) AND (a1.result <> true)) AND (a2.result <> false)))) GROUP BY a1.activity, a1."user" ORDER BY a1.activity, a1."user";


--
-- Name: logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE logs_id_seq OWNED BY logs.id;


--
-- Name: questions_tps; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE questions_tps (
    id integer NOT NULL,
    question_id integer NOT NULL,
    tp_id integer NOT NULL
);


--
-- Name: logs_jointype_by_tp; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW logs_jointype_by_tp AS
SELECT t.tp_id, t.type, count(*) AS count FROM (SELECT questions_tps.tp_id, CASE WHEN (logs.query ~~* '%join%'::text) THEN 'join'::text ELSE 'where'::text END AS type FROM (logs JOIN questions_tps USING (question_id)) WHERE (logs.created > date_trunc('year'::text, now()))) t GROUP BY t.tp_id, t.type;


--
-- Name: question_tests; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE question_tests (
    id integer NOT NULL,
    sql text NOT NULL,
    last_result boolean DEFAULT false NOT NULL,
    modified timestamp without time zone,
    assignment_name character varying(255) NOT NULL,
    chapter_id integer NOT NULL,
    question_order_no integer NOT NULL,
    comment text
);


--
-- Name: question_tests_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE question_tests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: question_tests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE question_tests_id_seq OWNED BY question_tests.id;


--
-- Name: questions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE questions (
    id integer NOT NULL,
    chapter_id integer NOT NULL,
    text text NOT NULL,
    sql text NOT NULL,
    order_no integer DEFAULT 0 NOT NULL,
    modified timestamp without time zone,
    variant character varying(50),
    assignment_name character varying(255) NOT NULL
);


--
-- Name: questions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE questions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: questions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE questions_id_seq OWNED BY questions.id;


--
-- Name: questions_usages_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE questions_usages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: questions_usages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE questions_usages_id_seq OWNED BY questions_tps.id;


--
-- Name: tps; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tps (
    id integer NOT NULL,
    "when" character varying(255) NOT NULL,
    name character varying(255) DEFAULT 'hec'::character varying NOT NULL,
    comment text,
    type character varying(50) DEFAULT 'sql'::character varying NOT NULL
);


--
-- Name: usages_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE usages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE usages_id_seq OWNED BY tps.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE users (
    matricule character(8) NOT NULL,
    username character varying(100),
    password character(40),
    last_name character varying(255),
    first_name character varying(255),
    email character varying(255),
    gender character varying(1),
    modified timestamp without time zone
);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY chapters ALTER COLUMN id SET DEFAULT nextval('chapters_id_seq1'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY logs ALTER COLUMN id SET DEFAULT nextval('logs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY question_tests ALTER COLUMN id SET DEFAULT nextval('question_tests_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions ALTER COLUMN id SET DEFAULT nextval('questions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions_tps ALTER COLUMN id SET DEFAULT nextval('questions_usages_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY tps ALTER COLUMN id SET DEFAULT nextval('usages_id_seq'::regclass);


--
-- Name: assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY assignments
    ADD CONSTRAINT assignments_pkey PRIMARY KEY (name);


--
-- Name: logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY logs
    ADD CONSTRAINT logs_pkey PRIMARY KEY (id);


--
-- Name: pkey_chapter; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY chapters
    ADD CONSTRAINT pkey_chapter PRIMARY KEY (id);


--
-- Name: pkey_question; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions
    ADD CONSTRAINT pkey_question PRIMARY KEY (id);


--
-- Name: pkey_usage; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tps
    ADD CONSTRAINT pkey_usage PRIMARY KEY (id);


--
-- Name: question_tests_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY question_tests
    ADD CONSTRAINT question_tests_pkey PRIMARY KEY (id);


--
-- Name: questions_tps_question_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions_tps
    ADD CONSTRAINT questions_tps_question_id_key UNIQUE (question_id, tp_id);


--
-- Name: questions_usages_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions_tps
    ADD CONSTRAINT questions_usages_pkey PRIMARY KEY (id);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (matricule);


--
-- Name: users_username_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: fki_chapter; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_chapter ON assignments USING btree (chapter_id);


--
-- Name: fki_chapter2; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_chapter2 ON questions USING btree (chapter_id);


--
-- Name: logs_created_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logs_created_idx ON logs USING btree (created);


--
-- Name: logs_user_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logs_user_idx ON logs USING btree ("user");


--
-- Name: chapter; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY assignments
    ADD CONSTRAINT chapter FOREIGN KEY (chapter_id) REFERENCES chapters(id);


--
-- Name: chapter2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions
    ADD CONSTRAINT chapter2 FOREIGN KEY (chapter_id) REFERENCES chapters(id);


--
-- Name: question_usage; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions_tps
    ADD CONSTRAINT question_usage FOREIGN KEY (question_id) REFERENCES questions(id);


--
-- Name: questions_assignment_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions
    ADD CONSTRAINT questions_assignment_name_fkey FOREIGN KEY (assignment_name) REFERENCES assignments(name);


--
-- Name: usage_question; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY questions_tps
    ADD CONSTRAINT usage_question FOREIGN KEY (tp_id) REFERENCES tps(id);


--
-- PostgreSQL database dump complete
--

