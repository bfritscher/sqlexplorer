<template>
  <div class="collapse-box">
    <div class="collapse-box__header" @click="toggle">
      <span class="collapse-box__header-icon">
        <ChevronDownSolid
          class="fa-transition"
          :class="{ 'fa-rotate-90': !isOpen }"
        />
      </span>
      <slot name="header">
        <h3 class="collapse-box__header-text">
          {{ header }}
        </h3>
      </slot>
    </div>
    <div class="collapse-box__body" v-show="isOpen">
      <slot></slot>
    </div>
  </div>
</template>

<script>
import { ref } from "vue";
import ChevronDownSolid from "../assets/icons/chevron-down-solid.svg";

export default {
  name: "CollapseBox",
  components: {
    ChevronDownSolid,
  },
  props: {
    header: {
      type: String,
      default: "",
    },
  },
  setup() {
    const isOpen = ref(true);
    return {
      isOpen,
      toggle() {
        isOpen.value = !isOpen.value;
      },
    };
  },
};
</script>

<style>
.collapse-box {
  max-width: 100%;
  margin-top: 16px;
}
.collapse-box__header {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
  color: var(--primary);
  padding-left: 8px;
  padding-bottom: 8px;
}
.collapse-box__header-text {
  margin: 0;
  font-size: 16px;
}

.collapse-box__header-icon svg {
  width: 14px;
  margin-right: 8px;
}
.fa-transition {
  transition: all 0.2s ease-in-out;
}
.fa-rotate-90 {
  transform: rotate(-90deg);
}
</style>
