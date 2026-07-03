<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">បញ្ចូលពិន្ទុការប្រឡង</h1>
      <p class="text-sm text-slate-500 mt-1">សូមជ្រើសរើសថ្នាក់ និងការប្រឡង ដើម្បីចាប់ផ្តើម</p>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ថ្នាក់</label>
        <select v-model="selectedClass" @change="resetExam"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-[14px] outline-none focus:border-indigo-500">
          <option value="">ជ្រើសរើសថ្នាក់</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.grade_level }}{{ cls.name }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ប្រភេទការប្រឡង</label>
        <select v-model="selectedType" @change="resetExam"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm outline-none focus:border-indigo-500">
          <option value="">ជ្រើសរើសប្រភេទ</option>
          <option v-for="type in uniqueExamTypes" :key="type" :value="type">{{ type }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ឈ្មោះការប្រឡង</label>
        <select v-model="selectedExam" :disabled="!selectedType"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm outline-none focus:border-indigo-500">
          <option value="">ជ្រើសរើសការប្រឡង</option>
          <option v-for="exam in filteredExams" :key="exam.id" :value="exam.id">{{ exam.name }}</option>
        </select>
      </div>

      <button @click="fetchStudents" :disabled="isLoading"
        class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all h-[42px] disabled:opacity-50">
        {{ isLoading ? 'កំពុងផ្ទុក...' : 'បង្ហាញសិស្ស' }}
      </button>
    </div>

    <div v-if="students.length > 0" class="overflow-x-auto bg-white rounded-2xl border border-slate-200 shadow-sm">
      <table class="w-full text-left border-collapse min-w-[800px]">
        <thead class="bg-slate-200">
          <tr>
            <th class="p-3 text-xs font-bold text-slate-600">ល.រ</th>
            <th class="p-3 text-xs font-bold text-slate-600">ឈ្មោះសិស្ស</th>
            <th v-for="sub in subjects" :key="sub.id" class="p-3 text-xs font-bold text-slate-600 text-center">
              {{ sub.name }}
            </th>
            <th class="p-3 text-xs font-bold text-indigo-600 text-center">សរុប</th>
            <th class="p-3 text-xs font-bold text-indigo-600 text-center">មធ្យមភាគ</th>
            <th class="p-3 text-xs font-bold text-indigo-600 text-center">ចំណាត់ថ្នាក់</th>
            <th class="p-3 text-xs font-bold text-indigo-600 text-center">និទ្ទេសខ្មែរ</th>
            <th class="p-3 text-xs font-bold text-indigo-600 text-center">និទ្ទេស</th>
            <th class="p-3 text-xs font-bold text-indigo-600 text-center">លទ្ធផល</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-300">
          <tr v-for="(student, index) in sortedStudents" :key="student.id" class="hover:bg-slate-50">
            <td class="p-3 text-sm text-slate-600">{{ index + 1 }}</td>
            <td class="p-3 text-sm font-bold text-slate-800">{{ student.name_kh }}</td>

            <td v-for="sub in subjects" :key="sub.id" class="p-2 md:p-2">
              <input type="number" v-model.number="student.scores[sub.id]"
                class="w-14 p-1.5 md:w-16 mx-auto block border border-slate-300 rounded p-1 text-center text-xs md:text-sm outline-none focus:border-indigo-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
            </td>

            <td class="p-3 text-sm font-bold text-indigo-700 text-center bg-indigo-50/50">
              {{ calculateTotal(student) }}
            </td>
            <td class="p-3 text-sm text-center">
              {{ calculateAverage(student) }}
            </td>
            <td class="p-3 text-sm text-center font-bold">
              {{ index + 1 }}
            </td>
            <td class="p-3 text-sm text-center">
              {{ getGradeKh(calculateAverage(student)) }}
            </td>
            <td class="p-3 text-sm text-center">
              {{ getGradeEn(calculateAverage(student)) }}
            </td>
            <td class="p-3 text-sm text-center">
              <span :class="calculateAverage(student) >= 50 ? 'text-green-600' : 'text-red-600'">
                {{ calculateAverage(student) >= 50 ? 'ជាប់' : 'ធ្លាក់' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="p-4 border-t flex justify-end bg-slate-50">
        <button @click="saveScores" :disabled="isSaving"
          class="bg-emerald-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-emerald-700 disabled:opacity-50">
          {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកពិន្ទុ' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../../services/authService'

const selectedClass = ref('')
const selectedType = ref('')
const selectedExam = ref('')
const isSaving = ref(false)
const isLoading = ref(false)
const students = ref([])
const classes = ref([])
const exams = ref([])
const subjects = ref([])

const sortedStudents = computed(() => {
  return [...students.value].sort((a, b) => calculateTotal(b) - calculateTotal(a))
})

const uniqueExamTypes = computed(() => [...new Set(exams.value.map(e => e.type))].filter(Boolean))
const filteredExams = computed(() => exams.value.filter(e => e.type === selectedType.value))

const resetExam = () => { selectedExam.value = ''; students.value = [] }

const calculateTotal = (student) => {
  return Object.values(student.scores || {}).reduce((acc, curr) => acc + (Number(curr) || 0), 0)
}

const calculateAverage = (student) => {
  if (subjects.value.length === 0) return 0;
  const total = calculateTotal(student);
  return (total / subjects.value.length).toFixed(2);
}

const getGradeKh = (avg) => {
  if (avg >= 90) return 'ល្អណាស់';
  if (avg >= 80) return 'ល្អ';
  if (avg >= 70) return 'ល្អបង្គួរ';
  if (avg >= 60) return 'មធ្យម';
  if (avg >= 50) return 'ខ្សោយ';
  return 'ធ្លាក់';
}

const getGradeEn = (avg) => {
  if (avg >= 90) return 'A';
  if (avg >= 80) return 'B';
  if (avg >= 70) return 'C';
  if (avg >= 60) return 'D';
  if (avg >= 50) return 'E';
  return 'F';
}

onMounted(async () => {
  try {
    const [cRes, eRes, sRes] = await Promise.all([api.get('/classes'), api.get('/exams'), api.get('/subjects')])
    classes.value = cRes.data.data || cRes.data
    exams.value = eRes.data.data || eRes.data
    subjects.value = sRes.data.data || sRes.data
  } catch (err) { console.error(err) }
})

const fetchStudents = async () => {
  if (!selectedClass.value) {
    alert("សូមជ្រើសរើសថ្នាក់ជាមុនសិន!");
    return;
  }
  isLoading.value = true;
  try {
    const sRes = await api.get(`/classes/${selectedClass.value}/students`);
    // ត្រូវប្រាកដថាទិន្នន័យមានផ្ទុក scores ដើម្បីកុំឱ្យ Error នៅពេល Calculate
    students.value = (sRes.data.data || sRes.data).map(s => ({
      ...s,
      scores: {}
    }));
  } catch (err) {
    console.error(err);
    alert('មានបញ្ហាក្នុងការទាញយកទិន្នន័យ');
  } finally {
    isLoading.value = false;
  }
}

const saveScores = async () => {
  isSaving.value = true
  console.log(students.value)
  isSaving.value = false
}
</script>