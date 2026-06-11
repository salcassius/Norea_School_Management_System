<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">បញ្ចូលពិន្ទុការប្រឡង</h1>
      <p class="text-sm text-slate-500 mt-1">សូមជ្រើសរើសថ្នាក់ និងមុខវិជ្ជាដើម្បីចាប់ផ្តើមដាក់ពិន្ទុ</p>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-wrap gap-4">
      <div class="flex-1 min-w-[200px]">
        <label class="text-xs font-bold text-slate-600 ml-1">ថ្នាក់</label>
        <select v-model="selectedClass" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm outline-none focus:border-indigo-500">
          <option value="">ជ្រើសរើសថ្នាក់</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
        </select>
      </div>
      <div class="flex-1 min-w-[200px]">
        <label class="text-xs font-bold text-slate-600 ml-1">ការប្រឡង/មុខវិជ្ជា</label>
        <select v-model="selectedExam" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm outline-none focus:border-indigo-500">
          <option value="">ជ្រើសរើសការប្រឡង</option>
          <option v-for="exam in exams" :key="exam.id" :value="exam.id">{{ exam.name }} - {{ exam.subject.name }}</option>
        </select>
      </div>
      <div class="flex items-end">
        <button @click="fetchStudents" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all">
          បង្ហាញសិស្ស
        </button>
      </div>
    </div>

    <div v-if="students.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-slate-50 border-b border-slate-100">
          <tr>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ល.រ</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ឈ្មោះសិស្ស</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ពិន្ទុ (០-១០០)</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">មតិយោបល់</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-slate-50">
            <td class="px-6 py-4 text-sm">{{ index + 1 }}</td>
            <td class="px-6 py-4 font-bold text-slate-700">{{ student.name }}</td>
            <td class="px-6 py-3">
              <input 
                type="number" 
                v-model="student.score" 
                min="0" max="100"
                class="w-20 bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-sm outline-none focus:border-indigo-500"
              />
            </td>
            <td class="px-6 py-3">
              <input 
                type="text" 
                v-model="student.comment" 
                placeholder="ចំណាំ..."
                class="w-full bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-3 text-sm outline-none"
              />
            </td>
          </tr>
        </tbody>
      </table>
      
      <div class="p-4 border-t border-slate-100 flex justify-end">
        <button @click="saveScores" class="bg-emerald-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200">
          រក្សាទុកពិន្ទុ
        </button>
      </div>
    </div>
    
    <div v-else-if="selectedClass && selectedExam" class="text-center py-20 text-slate-400">
      កំពុងផ្ទុកទិន្នន័យ ឬមិនមានសិស្សក្នុងថ្នាក់នេះ...
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/services/authService'

const selectedClass = ref('')
const selectedExam = ref('')
const students = ref([])
const classes = ref([]) // គួរទាញមកពី API
const exams = ref([])   // គួរទាញមកពី API

const fetchStudents = async () => {
  try {
    // ហៅ API ដើម្បីយកបញ្ជីសិស្សតាមថ្នាក់
    const res = await api.get(`/students?class_id=${selectedClass.value}`)
    students.value = res.data.map(s => ({ ...s, score: 0, comment: '' }))
  } catch (err) {
    alert('មានកំហុសក្នុងការទាញយកទិន្នន័យ')
  }
}

const saveScores = async () => {
  try {
    await api.post('/scores/bulk', {
      exam_id: selectedExam.value,
      scores: students.value
    })
    alert('រក្សាទុកពិន្ទុបានជោគជ័យ!')
  } catch (err) {
    alert('មិនអាចរក្សាទុកពិន្ទុបានទេ')
  }
}
</script>