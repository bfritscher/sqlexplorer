import * as VueRouter from "vue-router";
import Query from "./components/Query.vue";
import { DEFAULT_DB, API_URL } from "./config";

const Databases = () => import("./components/admin/Databases.vue");
const Logs = () => import("./components/admin/Logs.vue");
const Questions = () => import("./components/admin/Questions.vue");
const Assignments = () => import("./components/admin/Assignments.vue");
const AssignmentsPrint = () =>
  import("./components/admin/AssignmentsPrint.vue");
const AssignmentsSlide = () =>
  import("./components/admin/AssignmentsSlide.vue");
const LtiSelect = () => import("./components/admin/LtiSelect.vue");

function adminLogin(to, from, next) {
  return fetch(`${API_URL}/api/login`, {
    credentials: "include",
  }).then((res) => {
    if (res.status !== 200) {
      return next("/");
    }
    next();
  });
}

const routes = [
  {
    path: "/admin",
    name: "adminDatabases",
    beforeEnter: adminLogin,
    component: Databases,
  },
  {
    path: "/admin/logs/:assignmentId?",
    name: "adminLogs",
    beforeEnter: adminLogin,
    component: Logs,
  },
  {
    path: "/admin/assignments/:id?",
    name: "adminAssignments",
    beforeEnter: adminLogin,
    component: Assignments,
  },
  {
    path: "/admin/assignments/:id/print/:showData?",
    name: "print",
    beforeEnter: adminLogin,
    component: AssignmentsPrint,
  },
  {
    path: "/admin/assignments/:id/slide",
    name: "slide",
    beforeEnter: adminLogin,
    component: AssignmentsSlide,
  },
  { path: "/admin/questions", redirect: "/admin/questions/ALL" },
  {
    path: "/admin/questions/:db",
    name: "adminQuestions",
    beforeEnter: adminLogin,
    component: Questions,
  },
  { path: "/admin/ltiselect", name: "ltiSelect", component: LtiSelect },
  {
    path: "/admin/:db/:questionId?",
    beforeEnter: adminLogin,
    name: "adminQuery",
    component: Query,
  },
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
