<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800 font-[Khmer_OS_Muol_Light]">គ្រប់គ្រងអវត្តមានសិស្ស (Attendance)</h1>
      <p class="text-sm text-slate-500 mt-1">កត់ត្រាវត្តមាន អវត្តមាន និងការមកយឺតរបស់សិស្សប្រចាំថ្ងៃ</p>
    </div>

    <div
      class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-400 uppercase">១. ជ្រើសរើសថ្នាក់រៀន</label>
        <select v-model="selectedClassId" @change="fetchAttendanceSheet"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm font-semibold outline-none focus:border-indigo-500 cursor-pointer">
          <option value="" disabled>--- ជ្រើសរើសថ្នាក់ ---</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">
            ថ្នាក់ទី៖ {{ cls.grade_level }} {{ cls.name }}
          </option>
        </select>
      </div>

      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-400 uppercase">២. កាលបរិច្ឆេទ</label>
        <input v-model="selectedDate" type="date" @change="fetchAttendanceSheet"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm font-semibold outline-none focus:border-indigo-500 text-slate-800" />
      </div>
    </div>

    <div v-if="selectedClassId" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

      <div v-if="isLoading" class="p-12 text-center text-slate-400 font-medium">
        កំពុងទាញយកបញ្ជីឈ្មោះសិស្ស...
      </div>

      <div v-else>
        <div
          class="p-4 bg-slate-50/50 border-b border-slate-100 flex flex-wrap gap-2 items-center justify-between text-xs font-bold text-slate-500">
          <span>បញ្ជីឈ្មោះសិស្សសរុប៖ {{ attendanceRows.length }} នាក់</span>
          <div class="flex gap-2">
            <button @click="markAllStatus('present')"
              class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors">✓
              វត្តមានទាំងអស់</button>
            <button @click="markAllStatus('absent')"
              class="px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg transition-colors">✗
              អវត្តមានទាំងអស់</button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50/30 border-b border-slate-100">
                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase w-20 text-center">ល.រ</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase">ឈ្មោះសិស្ស (ភាសាខ្មែរ)</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase w-[320px] text-center">ស្ថានភាពវត្តមាន
                </th>
                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase">មូលហេតុ / ចំណាំ</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(row, idx) in attendanceRows" :key="row.student_id"
                class="hover:bg-slate-50/30 transition-colors">
                <td class="px-6 py-4 text-center text-sm font-bold text-slate-400">
                  {{ idx + 1 }}
                </td>
                <td class="px-6 py-4 text-sm font-bold text-slate-700">
                  {{ row.student_name }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-center items-center gap-2">

                    <label :class="[
                      'flex-1 flex items-center justify-center gap-1 py-1.5 px-3 rounded-xl border text-xs font-bold cursor-pointer select-none transition-all duration-150 active:scale-95 text-center',
                      row.status === 'present'
                        ? 'bg-emerald-600 border-emerald-600 text-white shadow-md shadow-emerald-600/10'
                        : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-600'
                    ]">
                      <input type="radio" :name="'status_' + row.student_id" value="present" v-model="row.status"
                        class="hidden" />
                      វត្តមាន
                    </label>

                    <label :class="[
                      'flex-1 flex items-center justify-center gap-1 py-1.5 px-3 rounded-xl border text-xs font-bold cursor-pointer select-none transition-all duration-150 active:scale-95 text-center',
                      row.status === 'late'
                        ? 'bg-amber-500 border-amber-500 text-white shadow-md shadow-amber-500/10'
                        : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-600'
                    ]">
                      <input type="radio" :name="'status_' + row.student_id" value="late" v-model="row.status"
                        class="hidden" />
                      មកយឺត
                    </label>

                    <label :class="[
                      'flex-1 flex items-center justify-center gap-1 py-1.5 px-3 rounded-xl border text-xs font-bold cursor-pointer select-none transition-all duration-150 active:scale-95 text-center',
                      row.status === 'absent'
                        ? 'bg-rose-600 border-rose-600 text-white shadow-md shadow-rose-600/10'
                        : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-600'
                    ]">
                      <input type="radio" :name="'status_' + row.student_id" value="absent" v-model="row.status"
                        class="hidden" />
                      អវត្តមាន
                    </label>

                  </div>
                </td>
                <td class="px-6 py-4">
                  <input v-model="row.notes" type="text" placeholder="មានច្បាប់, ឈឺ ឬ គ្មានការអនុញ្ញាត..."
                    :disabled="row.status === 'present'" :class="[
                      'w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm outline-none transition-all focus:border-indigo-500 focus:bg-white',
                      row.status === 'present' ? 'opacity-40 cursor-not-allowed' : ''
                    ]" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-5 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center">
          <span class="text-xs text-slate-400 font-medium">* សូមពិនិត្យបញ្ជីឈ្មោះវត្តមានឱ្យបានច្បាស់លាស់
            មុនពេលចុចរក្សាទុក</span>
          <button @click="submitAttendance" :disabled="isSaving"
            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-indigo-200 active:scale-95 disabled:opacity-50">
            <Save class="w-4 h-4" /> {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកវត្តមាន' }}
          </button>
        </div>
      </div>

    </div>

    <div v-else
      class="p-12 text-center bg-white rounded-2xl border border-slate-200/80 shadow-sm text-slate-400 font-medium">
      <p>⚠️ សូមជ្រើសរើសថ្នាក់រៀន ដើម្បីបើកផ្ទាំងគ្រប់គ្រងអវត្តមាន។</p>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Save } from 'lucide-vue-next'
import api from '../../services/authService'

const classes = ref([])
const selectedClassId = ref('')
const selectedDate = ref(new Date().toISOString().split('T')[0])

const attendanceRows = ref([])
const isLoading = ref(false)
const isSaving = ref(false)

// ១. ទាញយកបញ្ជីថ្នាក់រៀនដែលគ្រូនេះមានសិទ្ធិបង្រៀន (ផ្អែកលើ API ក្នុង Role Teacher)
// កែសម្រួលនៅក្នុង ManageAttendance.vue ត្រង់ជួរទី ១៥៩
onMounted(async () => {
  try {
    // 🎯 ប្តូរពី '/teacher/classes' មកប្រើ '/classes' វិញ
    const response = await api.get('/classes')
    classes.value = response.data?.data || response.data
  } catch (error) {
    console.error('💥 Error fetching teacher classes:', error)
  }
})

// ២. ទាញយកបញ្ជីឈ្មោះសិស្ស និងស្ថានភាពវត្តមានចាស់ (ផ្អែកលើច្បាប់ Route ថ្មី)
const fetchAttendanceSheet = async () => {
  if (!selectedClassId.value || !selectedDate.value) return

  isLoading.value = true
  try {
    // 🔗 ហៅទៅកាន់ Route ថ្មីដែលបានបង្កើត
    const response = await api.get('/attendance/sheet', {
      params: {
        class_id: selectedClassId.value,
        date: selectedDate.value
      }
    })
    attendanceRows.value = response.data?.data || []
  } catch (error) {
    console.error('💥 Error fetching attendance sheet:', error)
    attendanceRows.value = []
  } { isLoading.value = false }
}

// ៣. ប៊ូតុងជំនួយ៖ ចុចវត្តមានទាំងអស់ ឬអវត្តមានទាំងអស់ក្នុងមួយឃ្លីក
const markAllStatus = (statusValue) => {
  attendanceRows.value.forEach(row => {
    row.status = statusValue
    if (statusValue === 'present') {
      row.notes = ''
    }
  })
}

// ៤. ផ្ញើទិន្នន័យវត្តមានដុំទៅរក្សាទុក (Bulk UpdateOrCreate)
const submitAttendance = async () => {
  isSaving.value = true
  try {
    // 🔗 ហៅទៅកាន់ Route រក្សាទុកទិន្នន័យដុំថ្មី
    await api.post('/attendance/save', {
      class_id: selectedClassId.value,
      date: selectedDate.value,
      attendance: attendanceRows.value
    })
    alert('បានរក្សាទុកបញ្ជីវត្តមានដោយជោគជ័យ! 📋')
    fetchAttendanceSheet()
  } catch (error) {
    alert('មានបញ្ហាក្នុងការរក្សាទុកវត្តមាន!')
    console.error(error)
  } finally { isSaving.value = false }
}
</script>