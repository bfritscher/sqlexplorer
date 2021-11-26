<template>
  <div class="logs">
    <div class="logs-filter">
      <v-select
        name="user"
        placeholder="All users"
        v-model="user"
        :options="userOptions"
        class="padding"
        :filterable="false"
        @search="fetchOptions"
      >
        <template v-slot:[`no-options`]>type to search users.. </template>
      </v-select>
      <AssignmentSelect v-model="assignment" class="padding" />
    </div>
    <div class="padding">
      <h1 class="text-center" v-if="!user && !assignment">Select a filter</h1>
      <div v-for="(row, index) in result" :key="index">
        <!-- assignment only -->
        <table class="log-table">
          <tr v-if="row.questions">
            <th>Question</th>
            <th>Nb users finished</th>
            <th>Nb users attempted</th>
            <th>Finished / Attempted %</th>
          </tr>
          <tr v-for="(q, index) in row.questions" :key="q.id">
            <td :title="q.text">
              Q{{ index + 1 }}
              {{ q.text }}
            </td>
            <td>
              {{ q.nb_users_finished }}
            </td>
            <td>
              {{ q.nb_users_attempted }}
            </td>
            <td>
              {{
                Math.round(
                  (q.nb_users_finished / q.nb_users_attempted) * 100 || 0
                )
              }}%
            </td>
          </tr>
        </table>
        <!-- user with optional assignment -->
        <div v-for="a in row.assignments" :key="a.id">
          <h3>{{ a.name }}</h3>
          <strong
            >Questions: {{ a.nb_correct }} / {{ a.nb_open }} /
            {{ a.nb_questions }}</strong
          >
          <div v-for="q in a.questions" :key="q.id" class="log-question">
            <h4 :class="{ success: q.is_correct, error: !q.is_correct }">
              Q{{ q.q_order + 1 }}
              <button
                class="btn outline"
                title="Load attempts"
                @click="loadAttempts(q, a.id)"
              >
                attempts: {{ q.attempts }}
              </button>
            </h4>
            <div class="question-text">{{ q.text }}</div>

            <div
              v-for="(r, index) in q.responses"
              :key="r.id"
              :class="{ error: !r.is_correct, success: r.is_correct }"
            >
              <div class="log-datetime">
                #{{ q.responses.length - index }} @
                {{ datetimeFormat(r.submitted_at) }}
              </div>
              <highlightjs language="sql" :code="r.sql" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { API_URL } from "../../config.js";
import { ref, watchEffect, watch } from "vue";
import { useRoute } from "vue-router";
import vSelect from "vue-select";
import AssignmentSelect from "../AssignmentSelect.vue";

export default {
  components: {
    vSelect,
    AssignmentSelect,
  },
  setup() {
    const user = ref(null);
    const assignment = ref(null);
    const userOptions = ref([]);
    const result = ref([]);
    const route = useRoute();

    function send(payload) {
      return fetch(`${API_URL}/api/logs`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      }).then((res) => res.json());
    }

    watchEffect(async () => {
      const payload = {};
      if (user.value) {
        payload.user_id = parseInt(user.value.id, 10);
      }
      if (assignment.value) {
        payload.assignment_id = parseInt(assignment.value, 10);
      }
      result.value = await send(payload);
    });

    watch(
      () => route.params.assignmentId,
      () => {
        if (
          route.params.assignmentId &&
          route.params.assignmentId !== assignment.value
        ) {
          assignment.value = parseInt(route.params.assignmentId, 10);
        }
      },
      { immediate: true }
    );

    return {
      user,
      userOptions,
      assignment,
      result,
      fetchOptions(search, loading) {
        if (search.length === 0) return;
        loading(true);
        send({
          search,
        })
          .then((data) => {
            userOptions.value = data;
          })
          .finally(() => {
            loading(false);
          });
      },
      loadAttempts(question, assignment_id) {
        send({
          user_id: parseInt(user.value.id, 10),
          question_id: question.id,
          assignment_id,
        }).then((data) => {
          question.responses = data;
        });
      },
      datetimeFormat(date) {
        return new Date(date).toLocaleString();
      },
    };
  },
};
</script>

<style>
.logs-filter {
  background-color: #f7f7f7;
  border-bottom: 1px solid #e5e5e5;
  display: flex;
}
.logs-filter > * {
  flex: 1;
}

.question-text {
  font-style: italic;
  margin-bottom: 12px;
}

.log-question {
  padding: 4px;
  margin: 8px 0;
}

.log-question .outline {
  font-size: 58%;
  padding: 0 2px;
  margin: 0;
  height: auto;
}

.logs h4 {
  display: flex;
  margin: 0 0 8px 0;
}
.logs h4 .btn {
  margin: 0 0 0 8px;
}

.log-datetime {
  font-size: 80%;
  padding: 2px 0;
}

.logs .hljs {
  background-color: inherit;
}

.log-table {
  border-collapse: collapse;
  margin: 1em auto 0;
}

.log-table th {
  text-align: left;
  padding: 4px;
  border-bottom: 1px solid #e5e5e5;
}

.log-table td {
  padding: 4px;
  border-bottom: 1px solid #e5e5e5;
  text-align: right;
}

.log-table td:first-child {
  text-align: left;
  max-width: 400px;
}

.log-table tr:hover td {
  background-color: #f7f7f7;
}

@media (max-width: 600px) {
  .logs-filter {
    display: block;
  }
}
</style>
