<template>
  <div class="db-preview-container">
    <router-link
      v-for="d in databases"
      :key="d.name"
      class="db-preview"
      :to="`/admin/questions/${d.name}`"
    >
      <div v-if="d.schemaPicMissing" class="error">
        Missing: schema_pics/{{ d.name }}.png
      </div>
      <img
        v-else
        :src="`${API_URL}/schema_pics/${d.name}.png`"
        draggable="false"
        @error="d.schemaPicMissing = true"
      />
      <div class="title">
        {{ d.name }}
        <span class="spacer"></span>
        <button class="btn">
          <label @click.stop="" title="Upload Schema Image" class="icon">
            <UploadSolid />
            <input
              class="hidden"
              type="file"
              name="file"
              @change="uploadSchemaPic($event, d.name)"
            />
          </label>
        </button>
        <button
          title="Show nb tables and fix permissions"
          class="btn icon-left"
          @click.prevent.stop="checkPermissions(d)"
        >
          <CheckDoubleSolid />
          {{ d.count === undefined ? "check" : d.count }}
        </button>
      </div>
    </router-link>
  </div>
</template>

<script>
import { API_URL } from "../../config";
import { ref } from "vue";
import UploadSolid from "../../assets/icons/upload-solid.svg";
import CheckDoubleSolid from "../../assets/icons/check-double-solid.svg";

export default {
  components: {
    UploadSolid,
    CheckDoubleSolid,
  },
  setup() {
    const databases = ref([]);
    fetch(`${API_URL}/api/db/list`)
      .then((res) => res.json())
      .then((data) => {
        databases.value = data;
      });

    return {
      databases,
      API_URL,
      uploadSchemaPic(event, dbName) {
        var data = new FormData();
        data.append("file", event.target.files[0]);
        fetch(`${API_URL}/api/db/${dbName}`, {
          method: "POST",
          credentials: "include",
          body: data,
        }).then((res) => {
          window.location.reload();
        });
      },
      checkPermissions(db) {
        fetch(`${API_URL}/api/db/${db.name}/permissions`, {
          method: "POST",
          credentials: "include",
        })
          .then((res) => res.json())
          .then((data) => {
            db.count = data;
          });
      },
    };
  },
};
</script>

<style>
.db-preview-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-around;
  overflow: hidden;
  margin-top: 1em;
}
a.db-preview {
  width: 300px;
  height: 150px;
  margin: 5px;
  text-decoration: none;
  position: relative;
  border: 1px solid #ccc;
}

a.db-preview > div {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  display: flex;
}

a.db-preview .title {
  color: white;
  background-color: rgba(0, 0, 0, 0.3);
  height: 36px;
  padding: 4px;
}
a.db-preview .title button {
  margin-top: 2px;
  color: white;
}

a.db-preview .title button:hover {
  color: var(--primary);
}

a.db-preview > img {
  object-fit: contain;
  width: 100%;
  height: 100%;
}
a.db-preview .error {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}
a.db-preview:hover {
  border-color: var(--primary);
}
</style>
