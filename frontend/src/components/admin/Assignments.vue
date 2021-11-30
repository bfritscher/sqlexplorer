<template>
  <div>
    <div class="questions-filter padding">
      <AssignmentSelect
        :modelValue="Number.parseInt($route.params.id, 10)"
        :updateId="assignmentUpdate"
        @update:modelValue="$router.push(`/admin/assignments/${$event || ''}`)"
      />
      <button class="btn icon-left" @click="showData = !showData">
        <TableSolid /> {{ showData ? "Hide data" : "Show data" }}
      </button>

      <router-link
        :to="`/admin/assignments/${$route.params.id}/print/${showData ? 1 : 0}`"
        class="text-decoration-none"
        target="_blank"
      >
        <button
          class="btn icon-left"
          :disabled="assignment.questions.length === 0"
        >
          <PrintSolid /> Print
        </button>
      </router-link>
      <span class="separator"></span>
      <button class="btn icon-left" @click="createNewAssignment()">
        <PlusSolid /> New Assignment
      </button>
    </div>
    <div
      v-if="assignment.id"
      class="padding questions-filter assignment-edit"
      :class="{ success: saveSuccess }"
    >
      <div>
        <label
          ><span>Name</span><input type="text" v-model="assignment.name"
        /></label>
        <label
          ><span>Course</span><input type="text" v-model="assignment.course"
        /></label>
        <label
          ><span>Year</span><input type="text" v-model="assignment.year"
        /></label>
      </div>
      <div>
        <label
          ><span>Start date</span>
          <input
            type="datetime-local"
            :value="toLocal(assignment.start_date)"
            @input="assignment.start_date = toTZ($event)"
        /></label>
        <label
          ><span>End date</span>
          <input
            type="datetime-local"
            :value="toLocal(assignment.end_date)"
            @input="assignment.end_date = toTZ($event)"
        /></label>
      </div>
      <div>
        <label
          ><span>Description</span><textarea v-model="assignment.description" />
        </label>
      </div>
      <div>
        <button class="btn block" @click="updateAssignment">Update</button>
      </div>
    </div>

    <h1 v-if="isLoadingData">Loading</h1>

    <div class="padding">
      <div class="flex-row">
        <h3>Questions ({{ assignment.questions.length }})</h3>
        <router-link
          v-if="assignment"
          :to="`/assignment/${assignment.id}`"
          class="text-decoration-none"
        >
          <button class="btn icon-left outline"><EyeSolid /> View</button>
        </router-link>
        <router-link
          v-if="assignment"
          :to="`/admin/assignments/${assignment.id}/slide`"
          class="text-decoration-none"
          target="_blank"
        >
          <button class="btn icon-left outline">
            <Presentation /> Present
          </button>
        </router-link>
      </div>
      <draggable
        v-model="assignment.questions"
        handle=".question h4"
        item-key="id"
        class="questions-draggable"
        @start="drag = true"
        @end="updateOrder"
      >
        <template #item="{ element, index }">
          <question
            :question="element"
            :number="index + 1"
            :show-data="showData"
            :can-remove="true"
            @remove="removeQuestionFromAssignment(element.id)"
          />
        </template>
      </draggable>
    </div>
  </div>
</template>

<script>
import { ref, watch, watchEffect } from "vue";
import { useRoute, useRouter } from "vue-router";
import draggable from "vuedraggable";
import { DateTime } from "luxon";
import AssignmentSelect from "../AssignmentSelect.vue";
import Question from "./Question.vue";
import createAssignment from "./createAssignment";
import TableSolid from "../../assets/icons/table-solid.svg";
import PrintSolid from "../../assets/icons/print-solid.svg";
import EyeSolid from "../../assets/icons/eye-solid.svg";
import PlusSolid from "../../assets/icons/plus-solid.svg";
import Presentation from "../../assets/icons/presentation.svg";

import { API_URL } from "../../config";

export default {
  components: {
    AssignmentSelect,
    draggable,
    Question,
    TableSolid,
    PrintSolid,
    EyeSolid,
    PlusSolid,
    Presentation,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const assignment = ref({ questions: [] });
    const isLoadingData = ref(false);
    const showData = ref(false);
    const assignmentUpdate = ref(0);
    const saveSuccess = ref(false);

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
    }

    watch(
      () => route.params.id,
      () => {
        showData.value = false;
        if (!route.params.id) {
          assignment.value = { questions: [] };
          return;
        }
        fetch(`${API_URL}/api/assignment/${route.params.id}`, {
          credentials: "include",
        })
          .then((res) => res.json())
          .then((data) => {
            assignment.value = data;
          });
      },
      { immediate: true }
    );

    watchEffect(() => {
      if (showData.value) {
        loadQuestionResults();
      }
    });

    return {
      assignment,
      assignmentUpdate,
      saveSuccess,
      isLoadingData,
      showData,
      toLocal(dateString) {
        const dateParsed = DateTime.fromISO(dateString).toLocal();
        return dateParsed.toISO({ includeOffset: false });
      },
      toTZ(evt) {
        return DateTime.fromISO(evt.currentTarget.value).toUTC().toISO();
      },
      loadQuestionResults,
      removeQuestionFromAssignment(questionId) {
        fetch(
          `${API_URL}/api/assignment/${route.params.id}/question/${questionId}`,
          {
            method: "DELETE",
            credentials: "include",
            headers: {
              "Content-Type": "application/json",
            },
          }
        ).then((res) => {
          if (res.status === 200) {
            assignment.value.questions = assignment.value.questions.filter(
              (question) => question.id !== questionId
            );
          } else {
            alert(
              "Could not remove question from assignment, as it already has answers!"
            );
          }
        });
      },
      updateAssignment() {
        fetch(`${API_URL}/api/assignment/${route.params.id}`, {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            name: assignment.value.name,
            course: assignment.value.course,
            year: assignment.value.year,
            description: assignment.value.description,
            start_date: assignment.value.start_date,
            end_date: assignment.value.end_date,
          }),
        }).then((res) => {
          if (res.status === 200) {
            this.saveSuccess = true;
            setTimeout(() => {
              this.saveSuccess = false;
            }, 300);
            assignmentUpdate.value++;
          }
        });
      },
      updateOrder() {
        fetch(`${API_URL}/api/assignment/${route.params.id}/order`, {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            order: assignment.value.questions.map((question, index) => {
              return [question.id, index];
            }),
          }),
        });
      },
      createNewAssignment() {
        createAssignment().then((data) => {
          router.push(`/admin/assignments/${data.id}`);
          assignmentUpdate.value++;
        });
      },
    };
  },
};
</script>

<style>
.assignment-edit {
  display: flex;
  flex-direction: column;
}
.questions-filter.assignment-edit > div {
  display: flex;
  flex-wrap: wrap;
  margin-right: 0;
}
.assignment-edit > div:last-child {
  padding: 0 16px;
}
.assignment-edit label {
  margin: 4px 16px 4px 16px;
  display: flex;
  align-items: center;
  flex: 1;
}
.assignment-edit label span {
  width: 90px;
}
.assignment-edit input,
.assignment-edit textarea {
  padding: 4px;
  flex: 1;
  width: inherit;
}
</style>
