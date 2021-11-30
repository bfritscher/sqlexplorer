<template>
  <div v-if="question" class="slide">
    <header class="padding">
      {{ assignment.name }} / {{ assignment.course }} ({{ assignment.year }})
    </header>

    <div class="padding">{{ question.text }}</div>

    <div class="padding">
      <codemirror
        :value="question.sql"
        :options="editorOptions"
        height="auto"
        @update:value="question.sql = $event"
      />
    </div>
    <div v-if="question.error" class="padding">
      <div class="error message">
        {{ question.error }}
      </div>
    </div>

    <VueDraggableResizable
      v-if="question && question.result"
      :w="resultWidth"
      :h="300"
      class-name="result-container"
    >
      <result-table class="slide-result" :result="question.result" />
    </VueDraggableResizable>
    <SchemaPic
      v-if="question.db_schema"
      class="padding"
      :src="`${API_URL}/schema_pics/${question.db_schema}.png`"
    />

<footer class="padding">
      <button class="btn" @click="switchSlide(-1)">&lt;</button>
      <button class="btn" @click="switchSlide(+1)">&gt;</button>
      {{ assignment.questions.indexOf(question) + 1 }} /
      {{ assignment.questions.length }}

    </footer>


  </div>
</template>

<script>
import { useRoute } from "vue-router";
import { onMounted, onUnmounted, ref, computed } from "vue";
import { API_URL } from "../../config";

import Codemirror from "codemirror-editor-vue3";
import SchemaPic from "../SchemaPic.vue";
import ResultTable from "../ResultTable.vue";
import VueDraggableResizable from "vue-draggable-resizable/src/components/vue-draggable-resizable.vue";

export default {
  components: {
    Codemirror,
    ResultTable,
    SchemaPic,
    VueDraggableResizable,
  },
  setup() {
    const route = useRoute();
    const assignment = ref({ questions: [] });
    const isLoadingData = ref(false);
    const question = ref();

    const editorOptions = ref({
      lineNumbers: false,
      mode: "text/x-sql",
      theme: "neat",
      matchBrackets: true,
      extraKeys: {
        "Ctrl-Enter": () => {
          evaluate();
        },
      },
    });

    async function evaluate() {
      isLoadingData.value = true;
      question.value.error = null
      question.value.result = null;
      await fetch(`${API_URL}/api/evaluate`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          db: question.value.db_schema,
          sql: question.value.sql,
        }),
      })
        .then((res) => {
          if (res.status === 200) {
            return res.json();
          }
          return { error: res.statusText };
        })
        .then((data) => {
          if (data.error) {
            question.value.error = data.error;
          } else {
            question.value.result = data;
          }
        });
      isLoadingData.value = false;
    }

    function setActiveQuestion(q) {
      question.value = q;
      evaluate();
    }

    function switchSlide(delta) {
      const index = assignment.value.questions.indexOf(question.value);
      let newIndex = index + delta;
      if (newIndex >= assignment.value.questions.length) {
        newIndex = 0;
      } else if (newIndex < 0) {
        newIndex = assignment.value.questions.length - 1;
      }
      setActiveQuestion(assignment.value.questions[newIndex]);
    }

    let keyListener;
    onMounted(() => {
      keyListener = window.addEventListener("keydown", (e) => {
        if (e.target.nodeName === "TEXTAREA") {
          return;
        }
        if (e.key === "ArrowLeft") {
          switchSlide(-1);
        } else if (e.key === "ArrowRight") {
          switchSlide(+1);
        }
      });
    });

    onUnmounted(() => {
      if (keyListener) {
        window.removeEventListener("keydown", keyListener);
      }
    });

    fetch(`${API_URL}/api/assignment/${route.params.id}`, {
      credentials: "include",
    })
      .then((res) => res.json())
      .then((data) => {
        assignment.value = data;
        if (data.questions.length > 0) {
          setActiveQuestion(data.questions[0]);
        }
      });

    return {
      API_URL,
      question,
      assignment,
      isLoadingData,
      editorOptions,
      setActiveQuestion,
      switchSlide,
      onKeyUp(event) {
        console.log(event);
      },
      resultWidth: computed(() => {
        return (window.innerWidth / 2) - 8;
      }),
    };
  },
};
</script>

<style>
.slide {
  font-size: 150%;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.slide header {
  background-color: var(--secondary);
}

.slide footer {
  display: flex;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  align-items: center;
  justify-content: flex-end;
}

.result-container {
  position: absolute;
  margin-left: 50%;
  z-index: 999 !important;
}
.slide-result {
  overflow: auto;
  max-height: 100%;
  background-color: white;
  opacity: 1;
}
</style>
