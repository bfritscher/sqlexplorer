<template>
  <div>
    <div class="questions-filter padding">
      <div>
        <label
          ><strong>Database</strong>
          <DatabaseSelect
            :add-all="true"
            :modelValue="$route.params.db"
            @update:modelValue="
              $router.push(`/admin/questions/${$event || ''}`)
            "
          />
        </label>

        <button
          class="btn block"
          :disabled="$route.params.db === 'ALL'"
          @click="$router.push(`/admin/${$route.params.db}`)"
        >
          Add a rew question
        </button>

        <AssignmentSelect v-model="assignmentId" :updateId="assignmentUpdate" />
        <button class="btn block" @click="createNewAssignment()">
          New Assignment
        </button>
      </div>
      <div>
        <div style="margin-left: 8px">
          <strong>Matching: </strong>
          <label
            ><input
              type="radio"
              name="andor"
              value="0"
              v-model="keywordInclusive"
            />
            AND
          </label>
          <label>
            <input
              type="radio"
              name="andor"
              value="1"
              v-model="keywordInclusive"
            />
            OR
          </label>
        </div>
        <div class="keywords">
          <div
            v-for="tag in tags"
            :key="tag.name"
            class="keyword"
            :class="{ active: isKeywordSelected(tag.name) }"
            @click="toggleKeyword(tag.name)"
          >
            <div>{{ tag.name }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="padding">
      <h3>Questions ({{ questions.length }})</h3>
      <div>
        <question
          v-for="question in questions"
          :key="question.id"
          :question="question"
          number=""
          :can-add="
            !!assignmentId && !assignmentQuestions.includes(question.id)
          "
          @add="addQuestionToAssignment(question.id)"
          :can-remove="
            !!assignmentId && assignmentQuestions.includes(question.id)
          "
          @remove="removeQuestionFromAssignment(question.id)"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watchEffect, watch } from "vue";
import Question from "./Question.vue";
import createAssignment from "./createAssignment";

import { API_URL } from "../../config";
import { useRoute } from "vue-router";
import DatabaseSelect from "../DatabaseSelect.vue";
import AssignmentSelect from "../AssignmentSelect.vue";

export default {
  components: { Question, DatabaseSelect, AssignmentSelect },
  setup() {
    const route = useRoute();
    const keywordInclusive = ref(0);
    const selectedKeywords = ref([]);
    const tags = ref([]);
    const questions = ref([]);
    const assignmentId = ref(null);
    const assignmentUpdate = ref(0);
    const assignmentQuestions = ref([]);

    fetch(`${API_URL}/api/tags`, {
      credentials: "include",
    })
      .then((res) => res.json())
      .then((data) => {
        tags.value = data;
      });

    function isKeywordSelected(keyword) {
      return selectedKeywords.value.includes(keyword);
    }

    watchEffect(() => {
      fetch(`${API_URL}/api/questions`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          keywords: selectedKeywords.value,
          inclusive: keywordInclusive.value,
          dbname: route.params.db,
        }),
      })
        .then((res) => {
          if (res.status === 200) {
            return res.json();
          }
          return [];
        })
        .then((data) => {
          questions.value = data;
        });
    });

    watch(
      () => assignmentId.value,
      () => {
        if (assignmentId.value) {
          fetch(`${API_URL}/api/assignment/${assignmentId.value}`, {
            credentials: "include",
          })
            .then((res) => res.json())
            .then((data) => {
              assignmentQuestions.value = data.questions.map((q) => q.id);
            });
        }
      }
    );

    return {
      keywordInclusive,
      tags,
      assignmentId,
      assignmentUpdate,
      questions,
      assignmentQuestions,
      isKeywordSelected,
      toggleKeyword(keyword) {
        if (isKeywordSelected(keyword)) {
          selectedKeywords.value.splice(
            selectedKeywords.value.indexOf(keyword),
            1
          );
        } else {
          selectedKeywords.value.push(keyword);
        }
      },
      createNewAssignment() {
        createAssignment().then((data) => {
          assignmentId.value = data.id;
          assignmentUpdate.value++;
        });
      },
      addQuestionToAssignment(questionId) {
        fetch(`${API_URL}/api/assignment/${assignmentId.value}/question`, {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            questionId,
          }),
        }).then((res) => {
          if (res.status === 200) {
            assignmentUpdate.value++;
            assignmentQuestions.value.push(questionId);
          }
        });
      },
      removeQuestionFromAssignment(questionId) {
        fetch(
          `${API_URL}/api/assignment/${assignmentId.value}/question/${questionId}`,
          {
            method: "DELETE",
            credentials: "include",
            headers: {
              "Content-Type": "application/json",
            },
          }
        ).then((res) => {
          if (res.status === 200) {
            assignmentUpdate.value++;
            assignmentQuestions.value = assignmentQuestions.value.filter(
              (id) => id !== questionId
            );
          } else {
            alert(
              "Could not remove question from assignment, as it already has answers!"
            );
          }
        });
      },
    };
  },
};
</script>

<style>
.keywords {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}
.keywords .keyword {
  padding: 4px 4px;
}
.keywords .keyword > div {
  border-bottom: 2px solid transparent;
  min-width: 30px;
  text-align: center;
  padding: 2px 4px;
  cursor: pointer;
}

.keywords .keyword > div:hover {
  border-bottom-color: var(--secondary);
}

.keywords .keyword.active > div {
  color: var(--primary);
  border-bottom-color: var(--primary);
}
</style>
