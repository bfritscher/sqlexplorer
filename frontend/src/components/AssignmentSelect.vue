<template>
  <div>
    <v-select
      name="assignment"
      placeholder="Assignment"
      :options="assignments"
      :modelValue="modelValue"
      :reduce="(assignment) => assignment.id"
      :getOptionLabel="
        (assignment) =>
          `${assignment.name} / ${assignment.course}  ${assignment.year} [ ${assignment.nb} ]`
      "
      @update:modelValue="$emit('update:modelValue', $event)"
    >
    </v-select>
  </div>
</template>

<script>
import { ref, watchEffect } from "vue";
import { API_URL } from "../config";
import vSelect from "vue-select";

export default {
  components: {
    vSelect,
  },
  props: ["modelValue", "updateId"],
  emits: ["update:modelValue"],
  setup(props) {
    const assignments = ref([]);

    watchEffect(() => {
      props.updateId;
      fetch(`${API_URL}/api/assignment/list`, {
        credentials: "include",
      })
        .then((res) => res.json())
        .then((data) => {
          assignments.value = data;
        });
    });
    return {
      assignments,
    };
  },
};
</script>

<style></style>
