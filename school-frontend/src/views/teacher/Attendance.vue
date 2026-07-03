<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-slate-900">គ្រប់គ្រងវត្តមានសិស្ស</h1>
      <p class="text-slate-500 text-sm mt-1">{{ new Date().toLocaleDateString('km-KH', { dateStyle: 'full' }) }}</p>
    </div>

    <div class="mb-8">
      <select 
        v-model="selectedClassId" 
        @change="fetchStudents" 
        class="w-full bg-white border border-slate-200 text-slate-700 rounded-2xl px-5 py-3 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer"
      >
        <option value="" disabled>-- ជ្រើសរើសថ្នាក់បង្រៀន --</option>
        <option v-for="sched in todaySchedules" :key="sched.id" :value="sched.class_id">
          ថ្នាក់ {{ sched.class?.grade_level }}{{ sched.class?.name }} (ម៉ោង {{ sched.time }})
        </option>
      </select>
    </div>

    <div class="space-y-3">
      <div v-for="(student, index) in students" :key="student.student_id" 
        class="bg-white p-4 rounded-2xl border border-slate-100 flex items-center justify-between hover:border-indigo-100 transition-colors"
      >
        <div class="flex items-center gap-4">
          <span class="text-slate-400 font-medium w-6">{{ index + 1 }}</span>
          <span class="font-semibold text-slate-800">{{ student.name_kh }}</span>
        </div>

        <div class="flex gap-1.5">
          <button @click="student.status = 'present'" 
            :class="['px-4 py-1.5 rounded-full text-xs font-bold transition-all', student.status === 'present' ? 'bg-emerald-50 text-emerald-600' : 'text-slate-400 hover:bg-slate-50']">
            មានវត្តមាន
          </button>
          <button @click="student.status = 'late'" 
            :class="['px-4 py-1.5 rounded-full text-xs font-bold transition-all', student.status === 'late' ? 'bg-amber-50 text-amber-600' : 'text-slate-400 hover:bg-slate-50']">
            យឺត
          </button>
          <button @click="student.status = 'absent'" 
            :class="['px-4 py-1.5 rounded-full text-xs font-bold transition-all', student.status === 'absent' ? 'bg-rose-50 text-rose-600' : 'text-slate-400 hover:bg-slate-50']">
            អវត្តមាន
          </button>
        </div>
      </div>
    </div>

    <div v-if="students.length > 0" class="fixed bottom-6 right-6 md:right-10">
      <button 
        @click="saveAttendance" 
        :disabled="isSaving"
        class="bg-slate-900 text-white px-8 py-4 rounded-full font-bold shadow-xl hover:bg-slate-800 transition-all flex items-center gap-2"
      >
        {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកវត្តមាន' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/authService'

const students = ref([])
const todaySchedules = ref([])
const selectedClassId = ref('')
const isSaving = ref(false)

// ១. ទាញយកកាលវិភាគ
const fetchSchedules = async () => {
  try {
    const res = await api.get('/teacher/attendance/schedules')
    todaySchedules.value = res.data
  } catch (e) { console.error("Error fetching schedules:", e) }
}

// ២. ទាញសិស្សតាមថ្នាក់ (ភ្ជាប់ជាមួយ API)
const fetchStudents = async () => {
  if (!selectedClassId.value) return
  try {
    const today = new Date().toISOString().split('T')[0]
    const res = await api.get(`/teacher/attendance/students/${selectedClassId.value}?date=${today}`)
    students.value = res.data
  } catch (e) { 
    console.error("Error fetching students:", e)
    alert('មិនអាចទាញទិន្នន័យសិស្សបានទេ!')
  }
}

// ៣. រក្សាទុកវត្តមាន
const saveAttendance = async () => {
  isSaving.value = true
  try {
    const today = new Date().toISOString().split('T')[0]
    await api.post('/teacher/attendance/save', {
      class_id: selectedClassId.value,
      date: today,
      attendances: students.value.map(s => ({
        student_id: s.student_id,
        status: s.status,
        notes: null 
      }))
    })
    alert('រក្សាទុកវត្តមានដោយជោគជ័យ! 🎉')
  } catch (e) { 
    console.error("Save error:", e)
    alert('មានបញ្ហាក្នុងការរក្សាទុកទិន្នន័យ!') 
  } finally { 
    isSaving.value = false 
  }
}

onMounted(fetchSchedules)
</script>


<!-- <template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="mb-6 flex justify-between items-end">
      <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងវត្តមានសិស្ស</h1>
      <button @click="saveAttendance" :disabled="isSaving" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700">
        {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកវត្តមាន' }}
      </button>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-slate-50 border-b">
          <tr>
            <th class="px-6 py-4 text-xs font-bold text-slate-500">ឈ្មោះសិស្ស</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 text-center">ស្ថានភាព</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500">មូលហេតុ (បើអវត្តមាន)</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="student in students" :key="student.student_id">
            <td class="px-6 py-4 font-bold text-slate-700">{{ student.name_kh }}</td>
            
            <td class="px-6 py-4 flex justify-center gap-4">
              <label class="flex items-center gap-1">
                <input type="radio" :name="'status-'+student.student_id" v-model="student.is_absent" :value="false" class="accent-emerald-600"> មាន
              </label>
              
              <label class="flex items-center gap-1" v-if="!student.is_absent">
                <input type="checkbox" v-model="student.is_late" class="accent-amber-500"> យឺត
              </label>

              <label class="flex items-center gap-1">
                <input type="radio" :name="'status-'+student.student_id" v-model="student.is_absent" :value="true" class="accent-rose-500"> អវត្តមាន
              </label>
            </td>

            <td class="px-6 py-4">
              <select v-model="student.reason" :disabled="!student.is_absent" class="w-full bg-slate-50 border rounded-lg px-3 py-1.5 text-sm disabled:opacity-30">
                <option value="">-- ជ្រើសរើសមូលហេតុ --</option>
                <option value="sick">ឈឺ (មានច្បាប់)</option>
                <option value="family">ធុរៈគ្រួសារ</option>
                <option value="no_reason">គ្មានច្បាប់</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const students = ref([
  { student_id: 1, name_kh: 'សុខ គីមសៀង', is_absent: false, is_late: true, reason: '' },
  { student_id: 2, name_kh: 'លី ដាណា', is_absent: true, is_late: false, reason: 'sick' }
])

const isSaving = ref(false)

const saveAttendance = () => {
  isSaving.value = true
  const payload = students.value.map(s => ({
    student_id: s.student_id,
    status: s.is_absent ? 'absent' : (s.is_late ? 'late' : 'present'),
    is_late: !s.is_absent && s.is_late, 
    notes: s.is_absent ? s.reason : null
  }))
  
  console.log("Data to Save:", payload)
  setTimeout(() => { isSaving.value = false; alert('រក្សាទុកជោគជ័យ!') }, 500)
}
</script> -->