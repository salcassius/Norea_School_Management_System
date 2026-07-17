<template>
  <div class="p-1 sm:p-2 md:p-2 bg-slate-50 min-h-screen font-[Battambang]">
    <div class="mb-6">
      <div class="text-[22px] font-bold text-slate-800">គ្រប់គ្រងវត្តមានសិស្ស</div>
      <p class="text-sm text-slate-500 mt-1">
        ស្រង់វត្តមានប្រចាំថ្ងៃសម្រាប់ថ្នាក់ដែលអ្នកមានកាលវិភាគបង្រៀន និងមើលរបាយការណ៍ប្រចាំឆ្នាំសម្រាប់ថ្នាក់ដែលអ្នកជាគ្រូបន្ទប់
      </p>
    </div>

    <div class="flex gap-8 border-b border-slate-200 mb-6 bg-white px-4 pt-2 rounded-t-xl">
      <button
        @click="currentTab = 'input'"
        :class="['pb-3 font-bold text-base transition-all duration-200 border-b-2',
          currentTab === 'input' ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-indigo-500']"
      >
        ១. ស្រង់វត្តមានប្រចាំថ្ងៃ
      </button>

      <button
        @click="goToReportTab"
        :disabled="!checkingRole && !isHomeroomTeacher"
        :title="!isHomeroomTeacher ? 'របាយការណ៍នេះសម្រាប់តែគ្រូបន្ទប់ថ្នាក់ប៉ុណ្ណោះ' : ''"
        :class="['pb-3 font-bold text-base transition-all duration-200 border-b-2 flex items-center gap-1.5',
          currentTab === 'report' ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent',
          isHomeroomTeacher ? 'hover:text-indigo-500 cursor-pointer' : 'opacity-40 cursor-not-allowed']"
      >
        ២. លទ្ធផលវត្តមានប្រចាំឆ្នាំ
        <span v-if="!checkingRole && !isHomeroomTeacher" class="text-[11px] font-normal">(សម្រាប់តែគ្រូបន្ទប់ថ្នាក់)</span>
      </button>
    </div>

    <div v-if="checkingRole" class="p-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200/80">
      កំពុងផ្ទុកទិន្នន័យ...
    </div>

    <div v-else class="mt-4">
      <keep-alive>
        <InputAttendance v-if="currentTab === 'input'" />
        <ReportAttendance v-else-if="currentTab === 'report' && isHomeroomTeacher" :homeroom-classes="homeroomClasses" />
      </keep-alive>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/authService'
import InputAttendance from '../../views/admin/attendance/InputAttendance.vue'
import ReportAttendance from '../../views/admin/attendance/ViewAttendance.vue'

const currentTab = ref('input')
const checkingRole = ref(true)
const homeroomClasses = ref([])
const isHomeroomTeacher = ref(false)

// ត្រួតពិនិត្យថាតើគ្រូនេះជាគ្រូបន្ទប់ថ្នាក់ (មានថ្នាក់ណាមួយចាត់តាំង teacher_id ជារបស់ខ្លួន) ដែរឬទេ
const checkHomeroomStatus = async () => {
  checkingRole.value = true
  try {
    const res = await api.get('/teacher/classes')
    homeroomClasses.value = res.data?.data || res.data || []
    isHomeroomTeacher.value = homeroomClasses.value.length > 0
  } catch (e) {
    console.error('Error checking homeroom status:', e)
    homeroomClasses.value = []
    isHomeroomTeacher.value = false
  } finally {
    checkingRole.value = false
  }
}

const goToReportTab = () => {
  if (isHomeroomTeacher.value) currentTab.value = 'report'
}

onMounted(checkHomeroomStatus)
</script>