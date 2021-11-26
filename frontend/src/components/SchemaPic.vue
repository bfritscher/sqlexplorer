<template>
  <div>
    <VueDraggableResizable
      :w="schemaW"
      :h="schemaH"
      :min-height="50"
      :min-width="50"
      :lockAspectRatio="lockAspectRatio"
      class-name="schema-container"
    >
      <img ref="schema" :src="src" @load="schemaLoaded" />
    </VueDraggableResizable>
  </div>
</template>

<script>
import { ref } from "vue";
import "vue-draggable-resizable/src/components/vue-draggable-resizable.css";
import VueDraggableResizable from "vue-draggable-resizable/src/components/vue-draggable-resizable.vue";

export default {
  props: ["src"],
  components: { VueDraggableResizable },
  setup() {
    const schemaW = ref(50);
    const schemaH = ref(50);
    const schema = ref(null);
    const lockAspectRatio = ref(false);
    return {
      schemaLoaded(event) {
        let ratio = 1;
        if (schema.value.naturalWidth > window.innerWidth * 0.8) {
          ratio = (window.innerWidth * 0.8) / schema.value.naturalWidth;
        }

        schemaH.value = Math.round(schema.value.naturalHeight * ratio);
        schemaW.value = Math.round(schema.value.naturalWidth * ratio);
        setTimeout(() => {
          lockAspectRatio.value = true;
        });
      },

      schemaW,
      schemaH,
      schema,
      lockAspectRatio,
    };
  },
};
</script>

<style>
.schema-container img {
  width: 100%;
  height: 100%;
}
.schema-container {
  box-shadow: 0 0 5px 2px rgba(0, 0, 0, 0.1);
  opacity: 0.8;
}
</style>
