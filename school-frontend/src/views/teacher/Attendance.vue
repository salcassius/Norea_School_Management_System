<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="mb-6 flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងវត្តមានសិស្ស</h1>
        <p class="text-sm text-slate-500 mt-1">{{ new Date().toLocaleDateString('km-KH') }}</p>
      </div>
      <button @click="saveAttendance" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
        រក្សាទុកវត្តមាន
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-slate-50 border-b border-slate-100">
          <tr>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ល.រ</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ឈ្មោះសិស្ស</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">វត្តមាន</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-slate-50">
            <td class="px-6 py-4 text-sm text-slate-600">{{ index + 1 }}</td>
            <td class="px-6 py-4 font-bold text-slate-700">{{ student.name }}</td>
            <td class="px-6 py-3 flex justify-center gap-2">
              <button 
                @click="student.status = 'present'" 
                :class="['px-3 py-1 rounded-lg text-xs font-bold transition-all', student.status === 'present' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500']"
              >មានវត្តមាន</button>
              <button 
                @click="student.status = 'absent'" 
                :class="['px-3 py-1 rounded-lg text-xs font-bold transition-all', student.status === 'absent' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-500']"
              >អវត្តមាន</button>
              <button 
                @click="student.status = 'late'" 
                :class="['px-3 py-1 rounded-lg text-xs font-bold transition-all', student.status === 'late' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-500']"
              >មកយឺត</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// ទិន្នន័យតេស្ត (ជំនួសដោយការហៅ API តាមថ្នាក់)
const students = ref([
  { id: 1, name: 'សោម កក្កដា', status: 'present' },
  { id: 2, name: 'ម៉េង លីណា', status: 'present' },
  { id: 3, name: 'សុខ សារ៉ា', status: 'present' },
])

const saveAttendance = async () => {
  try {
    // ហៅ API ដើម្បីរក្សាទុក
    console.log("Saving Attendance:", students.value)
    alert('បានរក្សាទុកវត្តមានដោយជោគជ័យ!')
  } catch (err) {
    alert('មានបញ្ហាពេលរក្សាទុក!')
  }
}
</script>