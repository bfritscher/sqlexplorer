<template>
  <div>
    <div class="output" :class="{ fixed, norows: result.content.length === 0 }">
      <table>
        <thead>
          <tr>
            <th v-for="(title, $index) in result.headers" :key="$index">
              {{ title }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, rindex) in result.content" :key="rindex">
            <td
              v-for="(column, cindex) in row"
              :key="cindex"
              :class="{ 'text-right': isNum(column) || isNull(column) }"
            >
              {{ column === null ? "(NULL)" : column }}
            </td>
          </tr>
          <tr v-if="result.numrows > result.content.length">
            <td
              v-for="(column, cindex) in result.content[0]"
              :key="cindex"
              :class="{ 'text-right': isNum(column) || isNull(column) }"
            >
              ...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="print-hide result-table-footer">
      {{ result.headers.length }} colonnes et {{ result.numrows }} lignes
    </div>
  </div>
</template>

<script>
export default {
  props: {
    result: {
      type: Object,
      default: () => ({
        numrows: 0,
        headers: [],
        content: [],
      }),
    },
    fixed: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    return {
      isNum(a) {
        return !isNaN(a);
      },
      isNull(a) {
        return a === null;
      },
    };
  },
};
</script>

<style>
.output table {
  border-collapse: collapse;
}

.output.fixed {
  overflow: auto;
  max-height: 300px;
  width: max-content;
  border: 1px solid #ddd;
  max-width: 100%;
}

.output.fixed thead th {
  position: sticky;
  top: 0;
  z-index: 1;
}

.output th {
  border: 1px solid #ddd;
  text-align: left;
  background: #eee;
}

.output td {
  border: 1px solid #ddd;
}

.output.fixed td:last-child,
.output.fixed th:last-child {
  border-right: none;
}

.output.fixed td:first-child,
.output.fixed th:first-child {
  border-right: none;
}

.output.fixed tr:last-child td {
  border-bottom: none;
}

.output.fixed th {
  border-top: none;
}

.output td,
.output th {
  white-space: nowrap;
  padding: 4px;
  text-overflow: ellipsis;
}

.output tr:nth-child(even) {
  background-color: #f6f6f6;
}

.output tr:hover td {
  background-color: #ffffb2;
}
.result-table-footer {
  font-size: 0.8em;
  margin-top: 2px;
  padding: 2px;
}
</style>
