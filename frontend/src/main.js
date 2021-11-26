import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import "vue-select/dist/vue-select.css";

import "highlight.js/styles/vs.css";
import hljs from "highlight.js/lib/core";
import sql from "highlight.js/lib/languages/sql";
import hljsVuePlugin from "@highlightjs/vue-plugin";
hljs.registerLanguage("sql", sql);

createApp(App).use(router).use(hljsVuePlugin).mount("#app");
