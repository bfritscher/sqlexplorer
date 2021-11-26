import * as VueRouter from "vue-router";
import Query from "./components/Query.vue";
import { DEFAULT_DB } from "./config";

const Databases = () => import("./components/admin/Databases.vue");
const Logs = () => import("./components/admin/Logs.vue");
const Questions = () => import("./components/admin/Questions.vue");
const Assignments = () => import("./components/admin/Assignments.vue");
const AssignmentsPrint = () =>
  import("./components/admin/AssignmentsPrint.vue");
const LtiSelect = () => import("./components/admin/LtiSelect.vue");

const routes = [
  { path: "/admin", name: "adminDatabases", component: Databases },
  { path: "/admin/logs/:assignmentId?", name: "adminLogs", component: Logs },
  {
    path: "/admin/assignments/:id?",
    name: "adminAssignments",
    component: Assignments,
  },
  {
    path: "/admin/assignments/:id/print/:showData?",
    name: "print",
    component: AssignmentsPrint,
  },
  { path: "/admin/questions", redirect: "/admin/questions/ALL" },
  {
    path: "/admin/questions/:db",
    name: "adminQuestions",
    component: Questions,
  },
  { path: "/admin/ltiselect", name: "ltiSelect", component: LtiSelect },
  { path: "/admin/:db/:questionId?", name: "adminQuery", component: Query },
  { path: "/assignmentlti/:assignmentId", name: "ltiQuery", component: Query },
  {
    path: "/assignment/:assignmentId",
    name: "assignmentQuery",
    component: Query,
  },
  { path: "/:db", name: "Query", component: Query },
  {
    path: "/:catchAll(.*)",
    redirect: `/${DEFAULT_DB}`,
  },
];

const router = VueRouter.createRouter({
  history: VueRouter.createWebHistory(),
  routes,
});

const DEFAULT_TITLE = "SQL Explorer";
router.afterEach((to, from) => {
  document.title = to.params.db
    ? `${DEFAULT_TITLE} - ${to.params.db}`
    : DEFAULT_TITLE;
});

export default router;
