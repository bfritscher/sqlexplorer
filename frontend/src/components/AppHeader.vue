<template>
  <header class="padding app-header">
    <h1 class="logo">
      <router-link to="/"><span>SQL</span> Explorer</router-link>
    </h1>
    <router-link to="/admin/"> Databases </router-link>
    <router-link
      to="/admin/questions"
      :class="{ 'router-link-active': $route.name === 'adminQuestions' }"
    >
      Questions
    </router-link>
    <router-link to="/admin/assignments/"> Assignments </router-link>
    <router-link to="/admin/logs"> Logs </router-link>
    <router-link v-if="PGADMIN_URL" :to="PGADMIN_URL" target="_blank">
      PG Admin
    </router-link>
  </header>
</template>

<script>
import { PGADMIN_URL } from "../config";
export default {
  setup() {
    return {
      PGADMIN_URL,
    };
  },
};
</script>

<style>
.app-header {
  display: flex;
  align-items: baseline;
  border-bottom: 1px solid #e5e5e5;
  flex-wrap: wrap;
}

.logo {
  margin: 0;
  margin-right: 1em;
}

.app-header .logo a {
  font-size: 24px;
  color: var(--secondary);
  white-space: nowrap;
}
.logo span {
  color: var(--primary);
}

.app-header a {
  text-decoration: none;
  display: block;
  color: #666;
  margin: 0 8px;
  padding: 2px 0px;
  border-bottom: 2px solid transparent;
  font-size: 14px;
}

.app-header a:hover {
  border-bottom-color: var(--secondary);
}

.app-header a.router-link-active {
  color: var(--primary);
  border-bottom-color: var(--primary);
}
</style>
