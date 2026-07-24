<template>
  <div class="font-[Battambang] p-3 sm:p-6 min-h-screen bg-slate-50/50 text-slate-700">

    <!-- <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800 font-[Khmer_OS_Muol_Light]">គ្រប់គ្រងអវត្តមានសិស្ស (Attendance)</h1>
      <p class="text-sm text-slate-500 mt-1">កត់ត្រាវត្តមាន អវត្តមាន និងការមកយឺតរបស់សិស្សប្រចាំថ្ងៃ</p>
    </div> -->

    <div
      class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 font-[Battambang]">
      <div class="space-y-1">
        <label class="text-[13px] sm:text-[14px] font-bold text-slate-600 uppercase font-[Battambang]">១. ជ្រើសរើសថ្នាក់រៀន</label>
        <select v-model="selectedClassId" @change="fetchAttendanceSheet"
          class=" text-[14px] w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 font-semibold outline-none focus:border-indigo-500 cursor-pointer">
          <option value="" disabled>--- ជ្រើសរើសថ្នាក់ ---</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">
            ថ្នាក់ទី៖ {{ cls.grade_level }} {{ cls.name }}
          </option>
        </select>
      </div>

      <div class="space-y-1">
        <label class="text-[13px] sm:text-[14px] font-bold text-slate-600 uppercase font-[Battambang]">២. កាលបរិច្ឆេទ</label>
        <input v-model="selectedDate" type="date" @change="fetchAttendanceSheet"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm font-semibold outline-none focus:border-indigo-500 text-slate-800" />
      </div>
    </div>

    <div v-if="selectedClassId" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

      <div v-if="isLoading" class="p-12 text-center text-slate-600 font-medium">
        កំពុងទាញយកបញ្ជីឈ្មោះសិស្ស...
      </div>

      <div v-else>
        <div
          class="p-4 bg-slate-50/50 border-b border-slate-100 flex flex-wrap gap-2 items-center justify-between text-[14px] sm:text-[15px] font-bold text-slate-700">
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
          <table class="w-full text-left border-collapse min-w-[560px]">
            <thead>
              <tr class="bg-slate-50/30 border-b border-slate-100">
                <th class="px-4 py-4 text-sm font-bold text-slate-600 text-center">ល.រ</th>
                <th class="px-4 py-4 text-sm font-bold text-slate-600">ឈ្មោះសិស្ស</th>
                <th class="px-4 py-4 text-sm font-bold text-slate-600 text-center">វត្តមាន</th>
                <th class="px-4 py-4 text-sm font-bold text-slate-600 text-center">អវត្តមាន</th>
                <th class="px-4 py-4 text-sm font-bold text-slate-600 text-center">មានច្បាប់</th>
                <th class="px-4 py-4 text-sm font-bold text-slate-600">កំណត់សម្គាល់</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(row, idx) in attendanceRows" :key="row.student_id" class="hover:bg-slate-50/50">
                <td class="px-4 py-4 text-center text-sm font-medium text-slate-700">{{ idx + 1 }}</td>
                <td class="px-4 py-4 text-sm font-medium text-slate-700">{{ row.student_name }}</td>

                <!-- Checkbox Columns (ដូចក្នុង image_af0ca7.png) -->
                <td class="px-4 py-4 text-center">
                  <input type="checkbox" v-model="row.status" true-value="present" false-value="null"
                    class="w-5 h-5 accent-emerald-500 cursor-pointer">
                </td>
                <td class="px-4 py-4 text-center">
                  <input type="checkbox" v-model="row.status" true-value="absent" false-value="null"
                    class="w-5 h-5 accent-rose-500 cursor-pointer">
                </td>
                <td class="px-4 py-4 text-center">
                  <input type="checkbox" v-model="row.status" true-value="excused" false-value="null"
                    class="w-5 h-5 accent-amber-500 cursor-pointer">
                </td>

                <td class="px-4 py-4">
                  <input v-model="row.notes" type="text" placeholder="ចំណាំ..."
                    class=" border-slate-200 rounded-xl py-2.5 px-3 w-full border-none bg-transparent text-[15px] text-slate-700 focus:ring-0">
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 flex justify-end items-center">
          <button @click="submitAttendance" :disabled="isSaving"
            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 sm:px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-indigo-200 active:scale-95 disabled:opacity-50 w-full sm:w-auto justify-center">
            {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក' }}
          </button>
        </div>
      </div>

    </div>

    <div v-else
      class="p-12 text-center bg-white rounded-2xl border border-slate-200/80 shadow-sm text-slate-600 font-medium">
      <p>⚠️ សូមជ្រើសរើសថ្នាក់រៀន ដើម្បីបើកផ្ទាំងគ្រប់គ្រងអវត្តមាន។</p>
    </div>


    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.show" :class="[
          'fixed top-5 right-5 z-[100] flex items-center gap-3 px-4 py-3 rounded-2xl border shadow-lg min-w-[260px] max-w-xs',
          toast.type === 'success'
            ? 'bg-white border-emerald-200 text-emerald-700'
            : 'bg-white border-rose-200 text-rose-600'
        ]">
          <div :class="[
            'w-8 h-8 rounded-full flex items-center justify-center shrink-0',
            toast.type === 'success' ? 'bg-emerald-50' : 'bg-rose-50'
          ]">
            <CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4 text-emerald-500" />
            <XCircle v-else class="w-4 h-4 text-rose-500" />
          </div>
          <p class="text-sm font-medium flex-1">{{ toast.message }}</p>
          <button @click="toast.show = false" class="text-slate-300 hover:text-slate-500 transition-colors">
            <X class="w-4 h-4" />
          </button>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Save } from 'lucide-vue-next'
import api from '../../../services/authService'

const classes = ref([])
const selectedClassId = ref('')
const selectedDate = ref(new Date().toISOString().split('T')[0])

const attendanceRows = ref([])
const isLoading = ref(false)
const isSaving = ref(false)

onMounted(async () => {
  try {
    const response = await api.get('/classes')
    classes.value = response.data?.data || response.data
  } catch (error) {
    console.error('💥 Error fetching teacher classes:', error)
  }
})

const fetchAttendanceSheet = async () => {
  if (!selectedClassId.value || !selectedDate.value) return

  isLoading.value = true
  try {
    const response = await api.get('/attendance/sheet', {
      params: {
        class_id: selectedClassId.value,
        date: selectedDate.value
      }
    })
    const result = response.data
    attendanceRows.value = Array.isArray(result) ? result : (result.data || [])

    if (attendanceRows.value.length === 0) {
      console.warn("មិនមានសិស្សក្នុងថ្នាក់នេះ ឬទិន្នន័យទទេ")
    }
  } catch (error) {
    console.error('💥 Error fetching attendance sheet:', error)
    showToast('មិនអាចទាញទិន្នន័យបានទេ សូមពិនិត្យមើល API!')
    attendanceRows.value = []
  } finally {
    isLoading.value = false
  }
}


const markAllStatus = (statusValue) => {
  attendanceRows.value.forEach(row => {
    row.status = statusValue
    if (statusValue === 'present') {
      row.notes = ''
    }
  })
}

const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}


const submitAttendance = async () => {
  isSaving.value = true
  try {
    const payload = {
      class_id: selectedClassId.value,
      date: selectedDate.value,
      attendance: attendanceRows.value.map(row => ({
        student_id: row.student_id,
        status: row.status, 
        notes: row.notes || ''
      }))
    }

    await api.post('/attendance/save', payload)
    showToast('បានរក្សាទុកបញ្ជីវត្តមានដោយជោគជ័យ! 📋')
    fetchAttendanceSheet()
  } catch (error) {
    if (error.response && error.response.status === 422) {
      console.error('Validation Errors:', error.response.data.errors)
      showToast('ទិន្នន័យមិនត្រឹមត្រូវ៖ ' + JSON.stringify(error.response.data.errors))
    } else {
      showToast('មានបញ្ហាក្នុងការរក្សាទុកវត្តមាន!')
    }
  } finally { 
    isSaving.value = false 
  }
}

</script>