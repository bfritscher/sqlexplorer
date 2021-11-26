<template>
  <div style="min-height: 100vh; display: flex; flex-direction: column">
    <app-header v-if="$route.name && $route.name.includes('admin')" />
    <router-view></router-view>
    <div class="spacer"></div>
    <footer class="padding text-right print-hide text-secondary">
      <a
        href="https://github.com/bfritscher/sqlexplorer/"
        target="_blank"
        class="text-decoration-none"
        >SQL Explorer</a
      >
      {{ GIT_HASH }}
    </footer>
  </div>
</template>

<script>
import AppHeader from "./components/AppHeader.vue";
export default {
  name: "App",
  components: {
    AppHeader,
  },
  setup() {
    return {
      GIT_HASH: process.env.GIT_HASH,
    };
  },
};
</script>

<style>
:root {
  --primary: #006ac3;
  --secondary: #9e9e9e;
}
* {
  box-sizing: border-box;
}
input[type="datetime-local" i],
body {
  font-family: Roboto, sans-serif;
  margin: 0;
}

h1,
h2,
h3,
h4 {
  color: #666;
}

.CodeMirror,
.output,
code,
pre {
  font-family: "Source Code Pro", monospace !important;
}
pre {
  margin: 0;
}
pre code.hljs {
  padding: 0 !important;
}

.hidden {
  display: none;
}

label {
  cursor: pointer;
}

.text-right {
  text-align: right;
}

.text-center {
  text-align: center;
}

.text-italic {
  font-style: italic;
}

.text-decoration-none {
  text-decoration: none;
}

.text-secondary,
.text-secondary * {
  color: var(--secondary);
}

.flex-row {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.padding {
  padding: 8px;
}
.padding-left {
  padding-left: 8px;
}

.padding-top {
  padding-top: 8px;
}

.padding-bottom {
  padding-bottom: 8px;
}

.spacer {
  flex: 1;
}

.toolbar {
  border-top: 1px solid #dadce0;
  border-bottom: 1px solid #dadce0;
  padding: 1px 0;
  display: flex;
}

.separator {
  border-left: 1px solid #dadce0;
}

.btn {
  outline: none;
  border: none;
  background: none;
  color: #424242;
  padding: 3px;
  margin: 6px 4px;
  height: 24px;
  position: relative;
  text-decoration: none;
  cursor: pointer;
  user-select: none;
  display: flex;
  align-items: center;
}

.btn.block {
  width: 100%;
  display: block;
  color: #fff;
  background-color: var(--primary);
  border-radius: 4px;
  padding: 8px;
  height: auto;
  margin: 10px 0;
}

.btn.outline {
  border: 1px solid var(--secondary);
  border-radius: 5px;
}

.btn:active {
  background: #2980b9;
  top: 2px;
}

.btn:hover:not([disabled]) {
  background-color: #f0eeee;
}

.btn.block:hover:not([disabled]) {
  background-color: #666;
}

.btn[disabled] {
  color: black;
  border-color: grey;
  background-color: #ddd;
  cursor: not-allowed;
}

.btn svg,
.icon svg {
  width: 18px;
  height: 18px;
}

.btn:hover svg {
  color: var(--primary);
}

*.icon-left svg {
  margin-right: 8px;
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.2s;
}
.fade-enter,
.fade-leave-to {
  background-color: rgba(252, 250, 152, 0.445);
}

.fa-spin {
  animation: fa-spin 2s linear infinite;
}

@keyframes fa-spin {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }

  to {
    -webkit-transform: rotate(1turn);
    transform: rotate(1turn);
  }
}

textarea {
  width: 100%;
  font-family: Roboto, sans-serif;
}

.print-page .print-hide {
  display: none !important;
}

@media print {
  .print-hide,
  .print-hide * {
    display: none !important;
  }
}

footer {
  font-size: 10px;
  color: #333;
}

.questions-filter {
  background-color: #f7f7f7;
  border-bottom: 1px solid #e5e5e5;
  display: flex;
}

.questions-filter > div:first-child {
  min-width: 400px;
  margin-right: 20px;
}

@media (max-width: 600px) {
  .questions-filter {
    flex-wrap: wrap;
  }

  .questions-filter > div:first-child {
    width: 100%;
  }
}
</style>
