--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- Data for Name: chapters; Type: TABLE DATA; Schema: public; Owner: root
--

INSERT INTO chapters VALUES (1, 'SQL101', 1, '', NULL);
INSERT INTO chapters VALUES (3, 'Hidden from menu', 999, '', NULL);
INSERT INTO chapters VALUES (2, 'Additional Assignements', 998, '', NULL);


--
-- Data for Name: assignments; Type: TABLE DATA; Schema: public; Owner: root
--

INSERT INTO assignments VALUES ('SQL101', 'Examples of the chapter', 1, 0, '2013-12-24 14:13:48');
INSERT INTO assignments VALUES ('DemoMore', 'additional', 2, 2, '2013-12-24 14:13:48');
INSERT INTO assignments VALUES ('DemoHidden', 'This assignment is not shown in the menu', 3, 3, '2013-12-24 14:13:48');
INSERT INTO assignments VALUES ('Demo', '', 1, 1, '2013-12-24 15:51:39');


--
-- Name: chapters_id_seq1; Type: SEQUENCE SET; Schema: public; Owner: root
--

SELECT pg_catalog.setval('chapters_id_seq1', 3, true);


-- Data for Name: question_tests; Type: TABLE DATA; Schema: public; Owner: root
--

INSERT INTO question_tests VALUES (1, 'SELECT * FROM
Demo JOIN DemoMore ON Demo.id = DemoMore.ref_id', true, '2013-12-24 15:54:14', 'Demo', 1, 2, 'Without where to test that it is needed');


--
-- Name: question_tests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: root
--

SELECT pg_catalog.setval('question_tests_id_seq', 1, true);


--
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: root
--

INSERT INTO questions VALUES (2, 1, 'SELECT Everything', 'SELECT * FROM Demo', 1, '2013-12-24 13:55:41', '', 'SQL101');
INSERT INTO questions VALUES (1, 1, 'All Demo entries', 'SELECT * FROM Demo', 1, '2013-12-24 13:20:56', '', 'Demo');
INSERT INTO questions VALUES (3, 1, 'Demo who have addonA', 'SELECT * FROM
Demo JOIN DemoMore ON Demo.id = DemoMore.ref_id
WHERE DemoMore.name = ''addonA''', 2, '2013-12-24 15:53:26', '', 'Demo');


--
-- Name: questions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: root
--

SELECT pg_catalog.setval('questions_id_seq', 3, true);


--
-- Data for Name: tps; Type: TABLE DATA; Schema: public; Owner: root
--

INSERT INTO tps VALUES (1, '', 'TP01', '', '');


--
-- Data for Name: questions_tps; Type: TABLE DATA; Schema: public; Owner: root
--

INSERT INTO questions_tps VALUES (1, 1, 1);


--
-- Name: questions_usages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: root
--

SELECT pg_catalog.setval('questions_usages_id_seq', 1, true);


--
-- Name: usages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: root
--

SELECT pg_catalog.setval('usages_id_seq', 1, true);


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: root
--
-- superadmin password: demo
INSERT INTO users VALUES ('1       ', 'superadmin', 'c258d9a0675c7537c9281a057fd754fe22ed4374', NULL, NULL, NULL, NULL, '2013-12-24 12:44:57');


--
-- PostgreSQL database dump complete
--

