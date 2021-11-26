<template>
  <table class="report-container print-page">
    <thead class="report-header">
      <tr>
        <th class="report-header-cell">
          <div class="header-info">
            <h3>
              {{ assignment.name }}
              {{ assignment.course }}
              ({{ assignment.year }})
            </h3>
          </div>
        </th>
      </tr>
    </thead>
    <tfoot class="report-footer">
      <tr>
        <td class="report-footer-cell">
          <div class="footer-info"></div>
        </td>
      </tr>
    </tfoot>
    <tbody class="report-content">
      <tr>
        <td class="report-content-cell">
          <div class="main">
            <question
              v-for="(element, index) in assignment.questions"
              :key="element.id"
              :question="element"
              :number="index + 1"
              :show-data="true"
            />
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
import { useRoute } from "vue-router";
import { ref } from "vue";

import draggable from "vuedraggable";
import AssignmentSelect from "../AssignmentSelect.vue";
import Question from "./Question.vue";
import TableSolid from "../../assets/icons/table-solid.svg";

import { API_URL } from "../../config";

export default {
  components: { AssignmentSelect, draggable, Question, TableSolid },
  setup() {
    const route = useRoute();
    const assignment = ref({ questions: [] });
    const isLoadingData = ref(false);

    async function loadQuestionResults() {
      isLoadingData.value = true;
      for (const question of assignment.value.questions) {
        try {
          await fetch(`${API_URL}/api/evaluate`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              db: question.db_schema,
              sql: question.sql,
            }),
          })
            .then((res) => {
              if (res.status === 200) {
                return res.json();
              }
              return { error: res.statusText };
            })
            .then((data) => {
              question.result = data;
            });
        } catch (err) {
          question.result = { error: err.message };
        }
      }
      isLoadingData.value = false;
      window.print();
    }

    fetch(`${API_URL}/api/assignment/${route.params.id}`, {
      credentials: "include",
    })
      .then((res) => res.json())
      .then((data) => {
        assignment.value = data;
        if (route.params.showData === "1") {
          loadQuestionResults();
        }
      });

    return {
      assignment,
      isLoadingData,
    };
  },
};
</script>

<style>
table.report-container {
  page-break-after: always;
  width: 100%;
}
thead.report-header {
  display: table-header-group;
}
tfoot.report-footer {
  display: table-footer-group;
}

.header-info {
  border-bottom: 1px solid black;
}
</style>
