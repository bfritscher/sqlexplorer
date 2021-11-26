<template>
  <v-select
    name="database"
    label="name"
    :clearable="false"
    :options="databases"
    :reduce="(db) => db.name"
    :getOptionKey="(db) => db.name"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
  >
  </v-select>
</template>

<script>
import { ref, computed } from "vue";
import { API_URL } from "../config";
import vSelect from "vue-select";

export default {
  components: {
    vSelect,
  },
  props: ["modelValue", "addAll"],
  emits: ["update:modelValue"],
  setup(props) {
    const _databases = ref([]);
    fetch(`${API_URL}/api/db/list`)
      .then((res) => res.json())
      .then((data) => {
        _databases.value = data;
      });

    const databases = computed(() => {
      if (props.addAll) {
        return [{ name: "ALL" }, ..._databases.value];
      }
      return _databases.value;
    });

    return {
      databases,
    };
  },
};
</script>

<style></style>
