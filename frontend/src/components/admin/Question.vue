<template>
  <div class="question padding">
    <h4 class="icon-left">
      <ArrowAltSolid class="print-hide show-draggable" />Question {{ number }}
      <router-link
        :to="`/admin/${question.db_schema}/${question.id}`"
        class="print-hide"
        >#{{ question.id }}</router-link
      >
      <router-link
        :to="`/admin/questions/${question.db_schema}`"
        class="print-hide"
        >{{ question.db_schema }}</router-link
      >
      <button
        v-if="canRemove"
        class="btn icon-left print-hide outline"
        @click="confirmDelete"
      >
        <MinusSolid />
        remove
      </button>
      <button
        v-if="canAdd"
        class="btn icon-left print-hide outline"
        @click="$emit('add')"
      >
        <PlusSolid />
        add
      </button>
    </h4>
    <div class="question-text">{{ question.text }}</div>

    <highlightjs language="sql" :code="question.sql" />

    <result-table
      style="margin-top: 1em"
      v-if="showData && question.result"
      :result="question.result"
    />
  </div>
</template>

<script>
import ResultTable from "../ResultTable.vue";
import ArrowAltSolid from "../../assets/icons/arrows-alt-solid.svg";
import MinusSolid from "../../assets/icons/minus-solid.svg";
import PlusSolid from "../../assets/icons/plus-solid.svg";

export default {
  components: { ResultTable, ArrowAltSolid, MinusSolid, PlusSolid },
  props: ["question", "showData", "number", "canAdd", "canRemove"],
  emits: ["remove", "add"],
  setup(props, { emit }) {
    return {
      confirmDelete() {
        if (confirm(`Remove question ${props.question.id}?`)) {
          emit("remove");
        }
      },
    };
  },
};
</script>

<style>
.question:hover,
.question:hover .hljs {
  background-color: #f5f5f5;
}

.question h4 {
  cursor: move;
  margin: 0;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
}
.question h4 svg {
  width: 16px;
  height: 16px;
}

.question h4 a {
  text-decoration: none;
  color: var(--primary);
  padding: 0 4px;
}

.question h4 a:hover {
  color: var(--secondary);
}
.show-draggable {
  display: none;
}
.questions-draggable .show-draggable {
  display: initial;
}
.question-text {
  font-style: italic;
  margin-bottom: 12px;
}
</style>
