<template>
  <div>
    <header class="query-header padding" v-if="!isAdmin">
      <h1 class="logo-query"><span>SQL</span> Explorer</h1>
      <span class="spacer"></span>

      <span v-if="user"
        >Connecté en tant que
        <strong>{{ user.firstname }} {{ user.lastname }}</strong></span
      >
      <span v-if="isLti && user.isInstructor">
        <router-link :to="`/admin/logs/${assignment.id}`" class="lti-logs-link"
          >logs</router-link
        ></span
      >
    </header>
    <div
      class="query-db-selector"
      style="min-width: 300px"
      v-if="$route.params.db"
    >
      <DatabaseSelect
        :modelValue="$route.params.db"
        @update:modelValue="
          $router.push(`/${isAdmin ? 'admin/' : ''}${$event}`)
        "
      />
    </div>
    <div v-if="assignment" class="assignment-header padding">
      <h1>
        {{ assignment.name }}
      </h1>
      <h2>Résultat : {{ score }}/{{ assignment.questions.length }}</h2>
    </div>

    <div v-if="assignment" class="assignment-details padding-top">
      <ul>
        <li
          v-for="(q, $index) in assignment.questions"
          :key="$index"
          class="question-list-item"
          :class="question === q ? 'active' : ''"
          @click="setCurrentQuestion($index)"
        >
          <div>
            <span>Q{{ $index + 1 }}</span>
            <span class="state" :class="`state-${q.is_correct}`"></span>
          </div>
        </li>
      </ul>
    </div>
    <transition name="fade" mode="out-in">
      <div :key="question.id">
        <div
          v-if="question.text || isAdmin || (result && result.error)"
          class="padding"
        >
          <template v-if="question.text || isAdmin">
            <div v-if="isNotStarted" class="error message">
              Le devoir n'est pas encore ouvert!
            </div>
            <div v-if="isOver" class="error message">
              Le délai de rendu du devoir est passé!
            </div>
            <div
              v-if="!isAdmin && question.text"
              class="query-question-text padding-top"
            >
              {{ question.text }}
            </div>
            <textarea
              v-if="isAdmin"
              v-model="question.text"
              rows="3"
              placeholder="Question text"
              class="query-question-text padding"
              :class="{ success: saveSuccess }"
            ></textarea>
            <div v-if="question.text" class="padding-top">
              Schéma:
              <span class="schema text-italic">{{ question.schema }}</span>
            </div>
          </template>
          <div
            v-if="result.error"
            class="error message"
            :class="{ 'padding-top': question.text || isAdmin }"
          >
            {{ result.error }}
          </div>
        </div>
        <div
          v-if="isAdmin || !(question.is_correct || isOver || isNotStarted)"
          class="toolbar"
        >
          <button
            title="Exécuter la requête SQL"
            :disabled="evaluating"
            class="icon-left btn"
            @click="evaluate()"
          >
            <CogSolid v-if="evaluating" class="fa-spin" />
            <BoltSolid v-else />
            Exécuter
          </button>
          <button title="Formater le code (shift + alt + F)" class="btn" @click="format()">
            <AlignLeftSolid />
          </button>
          <button
            title="Annuler"
            class="btn"
            @click="codeMirrorInstance.undo()"
          >
            <UndoSolid />
          </button>
          <button
            title="Refaire"
            class="btn"
            @click="codeMirrorInstance.redo()"
          >
            <RedoSolid />
          </button>
          <button
            title="Télécharger CSV"
            :disabled="evaluating"
            class="btn"
            @click="evaluateCsv()"
          >
            <FileCsvSolid />
          </button>
          <template v-if="user && user.isInstructor">
            <span class="separator"></span>
            <button
              @click="getSolutionForLtiInstructor()"
              class="btn icon-left"
            >
              <CheckSolid />
              Solution
            </button>
          </template>
          <template v-if="isAdmin">
            <span class="separator"></span>
            <button class="btn icon-left" @click="upsert()">
              <PenSolid />
              {{ question.id ? "Enregistrer" : "Créer" }}
            </button>
            <button
              v-if="question.id"
              class="btn icon-left"
              @click="$router.push(`/admin/${question.db_schema}`)"
            >
              <PlusSolid />
              Nouveau
            </button>
          </template>
        </div>
      </div>
    </transition>
    <div
      v-if="isAdmin || !(question.is_correct || isOver || isNotStarted)"
      class="sql-container"
    >
      <codemirror
        :value="question.sql"
        :options="editorOptions"
        height="auto"
        @update:value="question.sql = $event"
        @ready="setCodeMirrorInstance"
      />
    </div>
    <div v-if="question.is_correct && !isAdmin" class="padding">
      <div class="message success">
        <h3>Question terminée</h3>
        <div v-if="history && history[0]">
          <h4>Votre réponse</h4>
          <pre>{{ history[0].sql }}</pre>
        </div>
        <div v-if="result.answer">
          <h4>La réponse</h4>
          <pre class="padding-bottom">{{ result.answer }}</pre>
          <a v-if="ERROR_REPORT_URL" :href="ERROR_REPORT_URL"
            >Signaler une erreur.</a
          >
        </div>
      </div>
    </div>

    <collapse-box v-show="result" header="Résultat">
      <ResultTable
        v-if="result && result.content"
        class="padding-left"
        :result="result"
        :fixed="true"
      />
    </collapse-box>

    <collapse-box header="Historique" class="history">
      <table>
        <tr
          v-for="(r, $index) in historyFiltered"
          :key="$index"
          :class="{
            error:
              r.numrows <= 0 || (r.is_correct !== undefined && !r.is_correct),
            success: r.is_correct !== undefined && r.is_correct,
          }"
          @click="question.sql = r.sql"
        >
          <td :title="`rows: ${r.numrows}` || r.error">
            {{ history.length - $index }}
          </td>
          <td>
            <pre title="Copier la requête vers l'éditeur">{{ r.sql }}</pre>
          </td>
        </tr>
        <tr v-if="history.length > 0">
          <td>
            <button
              v-if="!isLti"
              class="btn"
              title="Effacer historique"
              @click="clearHistory()"
            >
              <TrashSolid />
            </button>
          </td>
          <td>
            <button class="btn" @click="historyLimit = !historyLimit">
              {{ historyLimit ? "Afficher plus" : "Afficher moins" }}
            </button>
          </td>
        </tr>
      </table>
    </collapse-box>

    <collapse-box header="Schema">
      <SchemaPic
        v-if="question.db_schema"
        class="padding"
        :src="`${API_URL}/schema_pics/${question.db_schema}.png`"
      />
    </collapse-box>
  </div>
</template>

<script>
import { ref, computed, watch } from "vue";
import CodeMirror from "codemirror";
import Codemirror from "codemirror-editor-vue3";
import "codemirror/mode/sql/sql";
import "codemirror/addon/edit/matchbrackets";
import "codemirror/theme/neat.css";

import "codemirror/addon/hint/show-hint";
import "codemirror/addon/hint/show-hint.css";
import "codemirror/addon/hint/sql-hint";

import { useRoute, useRouter } from "vue-router";

import { DateTime } from "luxon";

import { API_URL, ERROR_REPORT_URL } from "../config";
import SchemaPic from "./SchemaPic.vue";
import ResultTable from "./ResultTable.vue";
import DatabaseSelect from "./DatabaseSelect.vue";
import CollapseBox from "./CollapseBox.vue";

import BoltSolid from "../assets/icons/bolt-solid.svg";
import AlignLeftSolid from "../assets/icons/align-left-solid.svg";
import UndoSolid from "../assets/icons/undo-solid.svg";
import RedoSolid from "../assets/icons/redo-solid.svg";
import FileCsvSolid from "../assets/icons/file-csv-solid.svg";
import CogSolid from "../assets/icons/cog-solid.svg";
import TrashSolid from "../assets/icons/trash-solid.svg";
import PenSolid from "../assets/icons/pen-solid.svg";
import PlusSolid from "../assets/icons/plus-solid.svg";
import CheckSolid from "../assets/icons/check-solid.svg";
/*
If we want to trigger as we type?

    onChange(editor, change) {
      const { text, origin } = change[0];
      // trigger when origin is input and text is not empty
      if (origin === "+input" && text[0].trim()) {
        editor.execCommand("autocomplete");
      }
    },

*/

export default {
  components: {
    Codemirror,
    SchemaPic,
    ResultTable,
    DatabaseSelect,
    CollapseBox,
    BoltSolid,
    AlignLeftSolid,
    UndoSolid,
    RedoSolid,
    FileCsvSolid,
    CogSolid,
    TrashSolid,
    PenSolid,
    PlusSolid,
    CheckSolid,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    // lti
    const user = ref(null);
    const assignment = ref(null);
    const isLti = computed(() => {
      return !!user.value && !!assignment.value;
    });
    const isOver = computed(() => {
      if (isLti.value && assignment.value && assignment.value.end_date) {
        const dateParsed = DateTime.fromISO(
          assignment.value.end_date
        ).toLocal();
        return dateParsed > new Date();
      }
      return false;
    });
    const isNotStarted = computed(() => {
      if (isLti.value && assignment.value && assignment.value.start_date) {
        const dateParsed = DateTime.fromISO(
          assignment.value.start_date
        ).toLocal();
        return dateParsed > new Date();
      }
      return false;
    });
    const score = computed(() => {
      return (
        (assignment.value &&
          assignment.value.questions.reduce(
            (total, q) => (total += q.is_correct ? 1 : 0),
            0
          )) ||
        0
      );
    });

    function setCurrentQuestion(index) {
      question.value = assignment.value.questions[index];
    }

    function loadLtiHistory() {
      fetch(
        `${API_URL}/lti/assignment/${assignment.value.id}/question/${question.value.id}/history`,
        {
          credentials: "include",
        }
      )
        .then((res) => res.json())
        .then((data) => {
          history.value = data;
        });
    }

    function getSolutionForLtiInstructor() {
      fetch(
        `${API_URL}/lti/assignment/${assignment.value.id}/question/${question.value.id}/solution`,
        {
          credentials: "include",
        }
      )
        .then((res) => res.text())
        .then((data) => {
          question.value.sql = data;
        });
    }

    // history management
    const history = ref([]);
    const historyLimit = ref(true);
    const maxHistoryLength = 5;

    const historyFiltered = computed(() => {
      return history.value.slice(
        0,
        historyLimit.value ? maxHistoryLength : history.value.length
      );
    });

    const historyKey = computed(() => {
      let key = question.value.db_schema;
      if (question.value.id) {
        key += `-${question.value.id}`;
      }
      return key;
    });

    function saveHistory() {
      localStorage.setItem(historyKey.value, JSON.stringify(history.value));
    }

    function loadHistory() {
      const data = localStorage.getItem(historyKey.value);
      if (data) {
        try {
          history.value = JSON.parse(data);
        } catch (e) {
          history.value = [];
        }
      }
    }

    function clearHistory() {
      localStorage.removeItem(historyKey.value);
      history.value = [];
    }

    // sql part
    const question = ref({
      text: "",
      db_schema: "",
      sql: "",
    });

    const result = ref({});
    const evaluating = ref(false);

    const editorOptions = ref({
      lineNumbers: true,
      mode: "text/x-sql",
      theme: "neat",
      matchBrackets: true,
      extraKeys: {
        "Ctrl-Space": "autocomplete",
        "Ctrl-Enter": () => {
          evaluate();
        },
        "Shift-Alt-F": () => {
          format();
        },
      },
    });

    function clearResult() {
      result.value = {};
    }

    // evaluate sql
    async function evaluate() {
      clearResult();
      if (!Boolean(question.value.sql)) {
        result.value.error = "Pas de requête à exécuter.";
        return;
      }

      evaluating.value = true;

      const controller = new AbortController();
      const timeout = 10000;
      const id = setTimeout(() => controller.abort(), timeout);

      const payload = { sql: question.value.sql, db: question.value.db_schema };
      // if assignment
      if (question.value.id && !isAdmin.value) {
        payload.id = question.value.id;
      }
      try {
        const data = await fetch(`${API_URL}/api/evaluate`, {
          method: "POST",
          credentials: isLti.value ? "include" : "omit",
          headers: {
            "Content-Type": "application/json",
          },
          signal: controller.signal,
          body: JSON.stringify(payload),
        }).then((res) => (res.status === 200 ? res.json() : res.text()));
        if (!data || !data.content) {
          result.value.error = data || "Erreur serveur";
          return;
        }
        result.value = data;
        /*
          headers: [],
          content: [],
          numrows: 0,
          correct: true
          answer: "",
          error: "",
        */

        const historyEntry = {
          sql: question.value.sql,
          is_correct: data.is_correct,
        };
        question.value.is_correct = data.is_correct;
        if (data.error) {
          historyEntry.error = data.error;
        } else {
          historyEntry.numrows = data.numrows;
        }
        history.value.unshift(historyEntry);
        saveHistory();
      } catch (err) {
        if (err.name === "AbortError") {
          result.value.error = "Execution timed out";
        } else {
          result.value.error = err.message;
        }
      } finally {
        evaluating.value = false;
        clearTimeout(id);
      }
    }

    function evaluateCsv() {
      fetch(`${API_URL}/api/evaluate/csv`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          db: question.value.db_schema,
          sql: question.value.sql,
        }),
      })
        .then(async (res) => {
          if (res.status === 200) {
            return res.blob();
          }
          throw new Error(await res.text());
        })
        .then((blob) => {
          const file = window.URL.createObjectURL(blob);
          window.location.assign(file);
        })
        .catch((err) => {
          result.value.error = err.message;
        });
    }

    function format() {
      if (!Boolean(question.value.sql)) {
        result.value.error = "Pas de requête à formater.";
        return;
      }
      const payload = {
        sql: question.value.sql,
        reindent: 1,
        keyword_case: "upper",
      };
      const formBody = [];
      for (const property in payload) {
        const encodedKey = encodeURIComponent(property);
        const encodedValue = encodeURIComponent(payload[property]);
        formBody.push(`${encodedKey}=${encodedValue}`);
      }

      fetch("https://sqlformat.org/api/v1/format", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
        },
        body: formBody.join("&"),
      })
        .then((res) => (res.status === 200 ? res.json() : res.text()))
        .then((data) => {
          if (data && data.result) {
            question.value.sql = data.result;
          } else {
            result.value.error = data;
          }
        });
    }

    function upsert() {
      const payload = {
        sql: question.value.sql,
        text: question.value.text,
        db_schema: question.value.db_schema || route.params.db,
      };
      if (question.value.id) {
        payload.id = question.value.id;
      }

      fetch(`${API_URL}/api/question`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.id !== payload.id) {
            router.push(`/admin/${payload.db_schema}/${data.id}`);
          } else {
            loadAdminQuestion();
          }
          this.saveSuccess = true;
          setTimeout(() => {
            this.saveSuccess = false;
          }, 300);
        });
    }

    function loadAutoComplete() {
      fetch(`${API_URL}/api/db/${question.value.db_schema}`)
        .then((res) => res.json())
        .then((tables) => {
          CodeMirror.commands.autocomplete = (cm) => {
            cm.showHint({
              hint: CodeMirror.hint.sql,
              tables,
              completeSingle: false,
            });
          };
        });
    }

    const codeMirrorInstance = ref(null);
    function setCodeMirrorInstance(cm) {
      codeMirrorInstance.value = cm;
    }

    // router watch

    watch(
      () => route.params.db,
      () => {
        if (route.params.db && route.params.db !== question.value.db_schema) {
          question.value.db_schema = route.params.db;
          // TODO check if we want to load example questions /api/questions/:dbname
        }
      },
      { immediate: true }
    );

    watch(
      () => question.value.db_schema,
      () => {
        if (question.value.db_schema) {
          loadAutoComplete();
          loadHistory();
          clearResult();
        }
      },
      { immediate: true }
    );

    watch(
      () => question.value.id,
      () => {
        if (question.value.id && isLti.value) {
          loadLtiHistory();
        } else {
          loadHistory();
        }
        clearResult();
      },
      { immediate: true }
    );

    function loadAdminQuestion() {
     fetch(`${API_URL}/api/question/${route.params.questionId}`, {
            credentials: "include",
          })
            .then((res) => res.json())
            .then((data) => {
              question.value = data;
            });
    }

    watch(
      () => route.params.questionId,
      () => {
        if (
          route.params.questionId &&
          route.params.questionId !== question.value.id
        ) {
          loadAdminQuestion();
        } else if (!route.params.questionId && question.value.id) {
          delete question.value.id;
          question.value.text = "";
          question.value.sql = "";
        }
      },
      { immediate: true }
    );

    watch(
      () => route.params.assignmentId,
      () => {
        if (
          route.params.assignmentId &&
          route.name === "ltiQuery" &&
          (!assignment.value ||
            route.params.assignmentId !== assignment.value.id)
        ) {
          Promise.all([
            fetch(`${API_URL}/lti/assignment/${route.params.assignmentId}`, {
              credentials: "include",
            })
              .then((res) => res.json())
              .then((data) => {
                if (data.error) {
                  throw new Error(data.error);
                }
                data.questions.forEach((q) => {
                  if (!q.sql) {
                    q.sql = "";
                  }
                });
                assignment.value = data;
              }),
            fetch(`${API_URL}/lti/me`, {
              credentials: "include",
            })
              .then((res) => res.json())
              .then((data) => {
                if (data.error) {
                  throw new Error(data.error);
                }
                user.value = data;
              }),
          ])
            .then(() => {
              setCurrentQuestion(0);
            })
            .catch((err) => {
              result.value.error = err.message;
            });
        } else if (
          route.params.assignmentId &&
          route.name === "assignmentQuery" &&
          (!assignment.value ||
            route.params.assignmentId !== assignment.value.id)
        ) {
          fetch(`${API_URL}/api/assignment/${route.params.assignmentId}`)
            .then((res) => res.json())
            .then((data) => {
              if (data.error) {
                throw new Error(data.error);
              }
              data.questions.forEach((q) => {
                if (!q.sql) {
                  q.sql = "";
                }
              });
              assignment.value = data;
              setCurrentQuestion(0);
            });
        }
      },
      { immediate: true }
    );

    const isAdmin = computed(() => route.name === "adminQuery");
    const saveSuccess = ref(false);

    return {
      ERROR_REPORT_URL,
      API_URL,
      // lti
      user,
      assignment,
      score,
      isLti,
      isOver,
      isNotStarted,
      setCurrentQuestion,
      getSolutionForLtiInstructor,

      // history
      history,
      historyLimit,
      clearHistory,
      historyFiltered,

      // sql
      editorOptions,
      evaluate,
      evaluateCsv,
      question,
      result,
      evaluating,
      format,
      upsert,
      isAdmin,
      saveSuccess,
      codeMirrorInstance,
      setCodeMirrorInstance,
    };
  },
};
</script>

<style>
.CodeMirror {
  border: 1px solid #eee;
}

.error {
  background-color: rgba(255, 0, 0, 0.05);
  color: crimson;
}
.message {
  padding: 8px;
}
.message.error {
  border: 1px solid crimson;
  border-radius: 3px;
}

.message.success {
  border: 1px solid rgb(144, 238, 144);
  border-radius: 8px;
}

.message.success h3 {
  margin: 0;
}

.message.success h4 {
  margin: 8px 0 0 0;
}
.success {
  background-color: rgb(144, 238, 144, 0.2);
  color: green;
}

.query-db-selector {
  position: absolute;
  top: 8px;
  right: 8px;
}

.assignment-header {
  display: flex;
  background-color: #f9f9f9;
  align-items: center;
  justify-content: space-between;
}

.assignment-header h1 {
  margin: 0;
  font-size: 1em;
}

.assignment-header h2 {
  margin: 0;
  font-size: 1em;
}

.assignment-details ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.assignment-details ul li {
  display: inline-block;
  padding: 2px 8px;
}

.question-list-item.active {
  border-bottom: 3px solid var(--primary);
  background-color: rgba(0, 0, 255, 0.1);
}

a.lti-logs-link {
  color: var(--secondary);
  border-left: 1px solid var(--secondary);
  margin-left: 8px;
  padding-left: 8px;
  text-decoration: none;
}

a.lti-logs-link:hover {
  text-decoration: underline;
}

.question-list-item {
  cursor: pointer;
}

.question-list-item:not(.active):hover {
  border-bottom: 3px solid rgba(0, 106, 195, 0.5);
}

.question-list-item > div {
  display: flex;
  align-items: center;
}

.question-list-item > div > span:first-child {
  margin-right: 6px;
}

.state {
  width: 15px;
  height: 15px;
  background-color: white;
  border-radius: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
  border: thin solid #006ac3;
  font-size: 12px;
  line-height: 12px;
}

.state-true {
  border-color: green;
}

.state-false {
  border-color: red;
}

.state-true::before {
  content: "✔";
  color: green;
}

.state-false::before {
  content: "✘";
  color: red;
}

.history table {
  cursor: pointer;
  font-size: 80%;
  border-collapse: collapse;
}

.history td:first-child {
  vertical-align: top;
  padding: 1px;
  text-align: right;
  width: 2%;
  padding-left: 8px;
}

.history tr {
  border-top: 1px solid #ddd;
  margin: 0;
}

.history tr:hover td {
  background-color: #ffffb2;
}

.history pre {
  border-left: 1px solid #ddd;
  margin: 0;
  padding: 1px;
}

.logo-query {
  margin: 0;
  font-size: 24px;
  color: var(--secondary);
  white-space: nowrap;
  font-weight: 700;
}

.logo-query span {
  color: var(--primary);
}
.query-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 80%;
}

.sql-container .CodeMirror-scroll {
  min-height: 150px;
}
</style>
