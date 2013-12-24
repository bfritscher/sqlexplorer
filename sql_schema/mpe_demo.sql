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
-- Name: mpe_demo; Type: DATABASE; Schema: -; Owner: -
--

CREATE DATABASE mpe_demo WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8';


\connect mpe_demo

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

SET default_with_oids = false;

--
-- Name: demo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE demo (
    id integer NOT NULL,
    name character varying(50),
    birthdate numeric
);


--
-- Name: demo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE demo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: demo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE demo_id_seq OWNED BY demo.id;


--
-- Name: demomore; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE demomore (
    id integer NOT NULL,
    name character varying(50),
    ref_id integer
);


--
-- Name: demomore_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE demomore_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: demomore_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE demomore_id_seq OWNED BY demomore.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY demo ALTER COLUMN id SET DEFAULT nextval('demo_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY demomore ALTER COLUMN id SET DEFAULT nextval('demomore_id_seq'::regclass);


--
-- Data for Name: demo; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO demo VALUES (1, 'Alice', 1980);
INSERT INTO demo VALUES (2, 'Bob', 1999);
INSERT INTO demo VALUES (3, 'Charles', 1950);


--
-- Name: demo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('demo_id_seq', 3, true);


--
-- Data for Name: demomore; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO demomore VALUES (1, 'addonA', 1);
INSERT INTO demomore VALUES (2, 'addonB', 1);
INSERT INTO demomore VALUES (3, 'addonA', 2);


--
-- Name: demomore_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('demomore_id_seq', 3, true);


--
-- Name: demo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY demo
    ADD CONSTRAINT demo_pkey PRIMARY KEY (id);


--
-- Name: demomore_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY demomore
    ADD CONSTRAINT demomore_pkey PRIMARY KEY (id);


--
-- Name: fki_refid; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_refid ON demomore USING btree (ref_id);


--
-- Name: refid; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY demomore
    ADD CONSTRAINT refid FOREIGN KEY (ref_id) REFERENCES demo(id);


--
-- PostgreSQL database dump complete
--

