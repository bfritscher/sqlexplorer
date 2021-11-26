<template>
  <div class="padding">
    <h1 class="logo-query padding-bottom"><span>SQL</span> Explorer</h1>
    <v-select
      v-model="assignmentId"
      name="assignment"
      placeholder="Assignment"
      :options="assignments"
      :reduce="(assignment) => assignment.id"
      :getOptionLabel="
        (assignment) =>
          `${assignment.name} / ${assignment.course}  ${assignment.year} [ ${assignment.nb} ]`
      "
    >
    </v-select>
    <button class="btn block" @click="select" :disabled="!assignmentId">
      Continue
    </button>
    <form ref="itemSelectedForm" :action="formData.returnUrl" method="post">
      <input
        type="hidden"
        v-for="(value, name) in formData.params"
        :key="name"
        :name="name"
        :value="value"
      />
      <button v-if="formData.returnUrl" class="btn" type="submit">
        Terminer
      </button>
    </form>
  </div>
</template>

<script>
import { ref } from "vue";
import { API_URL } from "../../config";
import vSelect from "vue-select";

export default {
  components: {
    vSelect,
  },
  setup() {
    const assignments = ref([]);
    const assignmentId = ref(null);
    const itemSelectedForm = ref(null);
    const formData = ref({});

    fetch(`${API_URL}/api/assignment/list`, {
      credentials: "include",
    })
      .then((res) => res.json())
      .then((data) => {
        assignments.value = data;
      });
    return {
      assignments,
      assignmentId,
      select() {
        fetch(`${API_URL}/lti/selected`, {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            id: assignmentId.value,
          }),
        })
          .then((res) => res.json())
          .then((data) => {
            // via form submit because of CORS
            formData.value = data;
            setTimeout(() => {
              itemSelectedForm.value.submit();
            });
          });
      },
      formData,
      itemSelectedForm,
    };
  },
};
</script>

<style></style>
