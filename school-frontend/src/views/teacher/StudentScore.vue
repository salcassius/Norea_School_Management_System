<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">លទ្ធផលសិក្សារបស់សិស្ស (ID: {{ studentId }})</h1>
    <button @click="$router.back()" class="mt-4 text-indigo-600">ត្រឡប់ក្រោយ</button>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
const studentId = route.params.id // ទាញយក id ដែលផ្ញើមកពី URL

// នៅទីនេះ អ្នកអាចប្រើ studentId ដើម្បីហៅ API យកពិន្ទុ៖
// api.get(`/scores/${studentId}`)
</script>