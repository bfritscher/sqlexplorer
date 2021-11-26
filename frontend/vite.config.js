import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import svgLoader from "vite-svg-loader";
import legacy from "@vitejs/plugin-legacy";
import { execSync } from "child_process";

// https://vitejs.dev/config/
export default defineConfig(async () => {
  const hash = execSync("git rev-parse --short HEAD").toString().trim();
  return {
    define: {
      "process.env.GIT_HASH": `"${hash}"`,
    },
    server: {
      port: 4000,
    },
    plugins: [
      vue(),
      svgLoader(),
      legacy({
        targets: ["defaults", "not IE 11"],
      }),
    ],
    build: {
      rollupOptions: {
        // https://rollupjs.org/guide/en/#outputmanualchunks
        output: {
          manualChunks: {
            admin: [
              "./src/components/admin/Assignments.vue",
              "./src/components/admin/Databases.vue",
              "./src/components/admin/Logs.vue",
              "./src/components/admin/Question.vue",
              "./src/components/admin/Questions.vue",
            ],
          },
        },
      },
    },
  };
});
