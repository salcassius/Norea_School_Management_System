<template>
  <div class="p-3 sm:p-6 bg-slate-50 min-h-screen">
    <div class="mb-4 sm:mb-6">
      <div class="text-xl sm:text-2xl font-bold text-slate-800 font-[Battambang]">គ្រប់គ្រងវត្តមានសិស្ស</div>
      <p class="text-xs sm:text-sm text-slate-500 mt-1 font-[Battambang]">កត់ត្រាវត្តមាន អវត្តមាន ប្រចាំថ្ងៃ</p>
    </div>

    <!-- Tabs: scrollable on small screens so labels never wrap/overflow -->
    <div class="flex gap-4 sm:gap-8 border-b border-slate-200 mb-4 sm:mb-6 bg-white px-2 sm:px-4 pt-2 rounded-t-xl font-[Battambang] overflow-x-auto whitespace-nowrap">
      <button
        @click="currentTab = 'input'"
        :class="['pb-3 font-bold text-sm sm:text-base transition-all duration-200 border-b-2 shrink-0',
          currentTab === 'input' ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-indigo-500']"
      >
        ១. ស្រង់វត្តមានប្រចាំថ្ងៃ
      </button>
      <button
        @click="currentTab = 'report'"
        :class="['pb-3 font-bold text-sm sm:text-base transition-all duration-200 border-b-2 shrink-0',
          currentTab === 'report' ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-indigo-500']"
      >
        ២. លទ្ធផលវត្តមានប្រចាំឆ្នាំ
      </button>
    </div>

    <div class="mt-2 sm:mt-4">
      <keep-alive>
        <component :is="activeComponent" />
      </keep-alive>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import InputAttendance from '../admin/attendance/InputAttendance.vue'
import ViewAttendance from '../admin/report/ReportAttendance.vue'

const currentTab = ref('input')

// ជ្រើសរើស Component តាម Tab
const activeComponent = computed(() => {
  return currentTab.value === 'input' ? InputAttendance : ViewAttendance
})
</script>