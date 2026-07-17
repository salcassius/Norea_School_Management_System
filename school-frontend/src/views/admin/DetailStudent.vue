<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4"
        style="background: rgba(15,15,20,0.5);" @click.self="close">
        <div v-if="modelValue"
          class="bg-white sm:rounded-2xl w-full h-full sm:h-auto max-w-4xl max-h-full sm:max-h-[90vh] overflow-hidden font-[Battambang] flex flex-col border border-slate-200">

          <!-- Loading -->
          <div v-if="isLoading" class="p-12 sm:p-20 flex flex-col items-center gap-3">
            <div class="w-8 h-8 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
            <span class="text-slate-400 text-sm font-medium">កំពុងទាញទិន្នន័យ...</span>
          </div>

          <template v-else-if="data">
            <!-- ===== Header ===== -->
            <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 border-b border-slate-100 shrink-0">
              <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                  <img v-if="photoUrl && !photoError" :src="photoUrl" @error="photoError = true"
                    class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                    <UserRound class="w-5 h-5 sm:w-6 sm:h-6" />
                  </div>
                </div>

                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="font-bold text-slate-800 text-[14px] sm:text-[15px] truncate">{{ data.student.name_kh }}</p>
                    <span :class="[
                      'px-2 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wide border shrink-0',
                      data.student.status == 1 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                    ]">
                      {{ data.student.status == 1 ? 'កំពុងរៀន' : 'ឈប់រៀន' }}
                    </span>
                  </div>
                  <p class="text-[12px] sm:text-[13px] text-slate-500 mt-0.5 truncate">{{ data.student.name_en || '-' }}</p>
                  <p class="text-[11px] sm:text-[12px] text-slate-400 mt-0.5 truncate">{{ data.student.student_id_card || 'N/A' }} • {{ data.student.phone || 'N/A' }}</p>
                </div>
              </div>

              <button @click="close" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-all shrink-0">
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- ===== Tabs ===== -->
            <div class="flex border-b border-slate-100 px-4 sm:px-6 overflow-x-auto shrink-0">
              <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                :class="[
                  'flex items-center gap-2 px-3 sm:px-4 py-3 text-sm font-bold border-b-2 -mb-px transition-all whitespace-nowrap shrink-0',
                  activeTab === tab.key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'
                ]">
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.label }}
              </button>
            </div>

            <!-- ===== Content ===== -->
            <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-5 custom-scrollbar">

              <!-- ១. ប្រវត្តិការសិក្សា -->
              <div v-if="activeTab === 'enrollments'">
                <div v-if="!data.enrollments.length" class="empty-state">
                  <History class="w-9 h-9 text-slate-200" />
                  <p>មិនទាន់មានប្រវត្តិចូលរៀនទេ</p>
                </div>

                <div v-else class="overflow-x-auto">
                  <table class="w-full text-sm min-w-[420px]">
                    <thead>
                      <tr class="text-left text-slate-500 border-b border-slate-100">
                        <th class="py-2.5 font-bold text-[13px]">ឆ្នាំសិក្សា</th>
                        <th class="py-2.5 font-bold text-[13px]">ថ្នាក់</th>
                        <th class="py-2.5 font-bold text-[13px]">ស្ថានភាព</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(e, i) in data.enrollments" :key="i" class="border-b border-slate-50 hover:bg-slate-50/50">
                        <td class="py-3 text-slate-600">{{ e.year_name || '-' }}</td>
                        <td class="py-3 text-slate-700 font-medium">ថ្នាក់ទី{{ e.grade_level }}{{ e.class_name }}</td>
                        <td class="py-3">
                          <span :class="[
                            'px-2.5 py-1 rounded-full text-[11px] font-bold uppercase',
                            e.status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500'
                          ]">
                            {{ e.status == 1 ? 'កំពុងសិក្សា' : 'បញ្ចប់ / ឈប់' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- ២. លទ្ធផលសិក្សា -->
              <div v-if="activeTab === 'scores'">
                <div v-if="!Object.keys(data.scores.by_exam).length" class="empty-state">
                  <ClipboardList class="w-9 h-9 text-slate-200" />
                  <p>មិនទាន់មានពិន្ទុទេ</p>
                </div>

                <div v-else class="space-y-5">
                  <div v-for="(subjects, examName) in data.scores.by_exam" :key="examName"
                    class="pb-4 border-b border-slate-50 last:border-0 last:pb-0">
                    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center justify-between gap-2 mb-2">
                      <div class="min-w-0">
                        <p class="font-bold text-slate-700 text-sm">{{ examName }}</p>
                        <p class="text-[12px] text-slate-400 mt-0.5">
                          {{ data.scores.summary[examName]?.grade_level }}{{ data.scores.summary[examName]?.class_name || '-' }}
                          <span v-if="data.scores.summary[examName]?.year_name"> • ឆ្នាំសិក្សា {{ data.scores.summary[examName]?.year_name }}</span>
                        </p>
                      </div>

                      <div class="flex items-center gap-3 flex-wrap">
                        <div v-if="data.scores.summary[examName]?.rank" class="text-left sm:text-right">
                          <span class="text-[12px] text-slate-400">ចំណាត់ថ្នាក់</span>
                          <span class="font-bold text-indigo-600 text-sm ml-1">
                            {{ data.scores.summary[examName]?.rank }}<span v-if="data.scores.summary[examName]?.total_students"></span>
                          </span>
                        </div>

                        <div v-if="data.scores.summary[examName]?.grade_kh" class="text-left sm:text-right">
                          <span class="text-[12px] text-slate-400">និទ្ទេស</span>
                          <span :class="['font-bold text-sm ml-1', gradeStyle(data.scores.summary[examName]?.average).text]">
                            {{ data.scores.summary[examName]?.grade_kh }}
                          </span>
                        </div>

                        <div class="text-left sm:text-right">
                          <span class="text-[12px] text-slate-400">មធ្យមភាគ</span>
                          <span :class="['font-bold text-sm ml-1', gradeStyle(data.scores.summary[examName]?.average).text]">
                            {{ data.scores.summary[examName]?.average ?? '-' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ៣. វត្តមាន -->
              <div v-if="activeTab === 'attendance'">
                <!-- Stats -->
                <div class="grid grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3 mb-5">
                  <div class="p-2.5 sm:p-3 rounded-xl border border-slate-200 text-center">
                    <p class="text-base sm:text-lg font-bold text-slate-800">{{ data.attendance.summary.present }}</p>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">វត្តមាន</p>
                  </div>
                  <div class="p-2.5 sm:p-3 rounded-xl bg-rose-50 border border-rose-100 text-center">
                    <p class="text-base sm:text-lg font-bold text-rose-600">{{ data.attendance.summary.absent }}</p>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">អវត្តមាន</p>
                  </div>

                  <div class="p-2.5 sm:p-3 rounded-xl bg-blue-50 border border-blue-100 text-center">
                    <p class="text-base sm:text-lg font-bold text-blue-600">{{ data.attendance.summary.excused }}</p>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">សុំច្បាប់</p>
                  </div>
                </div>

                <!-- Records -->
                <div v-if="!data.attendance.records.length" class="empty-state">
                  <CalendarX2 class="w-9 h-9 text-slate-200" />
                  <p>មិនទាន់មានទិន្នន័យវត្តមានទេ</p>
                </div>

              </div>
            </div>
          </template>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import studentService from '../../services/studentService'
import {
  X, UserRound, History, ClipboardList, CheckCircle2, CalendarX2
} from 'lucide-vue-next'

const props = defineProps({ modelValue: Boolean, studentId: [String, Number] })
const emit = defineEmits(['update:modelValue'])

const data = ref(null)
const isLoading = ref(false)
const activeTab = ref('enrollments')
const photoError = ref(false)

const tabs = [
  { key: 'enrollments', label: 'ប្រវត្តិការសិក្សា', icon: History },
  { key: 'scores', label: 'លទ្ធផលសិក្សា', icon: ClipboardList },
  { key: 'attendance', label: 'វត្តមាន', icon: CheckCircle2 },
]

const close = () => emit('update:modelValue', false)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('km-KH', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-'

const statusLabel = (s) => ({
  present: 'មកទាន់ម៉ោង', absent: 'អវត្តមាន', late: 'មកយឺត', excused: 'សុំច្បាប់'
}[s] || s)

const attendanceBadge = (s) => ({
  present: 'bg-emerald-50 text-emerald-600', absent: 'bg-rose-50 text-rose-600',
  late: 'bg-amber-50 text-amber-600', excused: 'bg-blue-50 text-blue-600'
}[s] || 'bg-slate-100 text-slate-500')

// ✅ កែឲ្យត្រូវនឹងខ្នាត /50 (ត្រូវនឹងរូបមន្តមេគុណក្នុង ScoreController::recalculateTotals)
// ≥40 = ល្អណាស់ឡើងទៅ, ≥30 = ល្អបង្គួរ/មធ្យម, <30 = ធ្លាក់
const gradeStyle = (score) => {
  const s = Number(score) || 0
  if (s >= 40) return { text: 'text-emerald-600' }
  if (s >= 30) return { text: 'text-amber-600' }
  return { text: 'text-rose-600' }
}

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/+$/, '')
const photoUrl = computed(() => {
  const p = data.value?.student?.photo
  if (!p) return null
  if (/^https?:\/\//i.test(p)) return p
  return `${API_BASE_URL}/storage/${p.replace(/^\/?storage\/?/, '')}`
})

watch(() => props.studentId, async (id) => {
  if (!id) return
  isLoading.value = true
  data.value = null
  photoError.value = false
  activeTab.value = 'enrollments'
  try {
    data.value = await studentService.getStudentHistory(id)
  } catch (e) {
    console.error('Fetch history error:', e)
  } finally {
    isLoading.value = false
  }
}, { immediate: true })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap');

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 50px 0;
  color: #94a3b8;
  font-size: 14px;
}

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>