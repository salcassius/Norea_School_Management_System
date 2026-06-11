<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-slate-800">កាលវិភាគបង្រៀន</h1>
      <p class="text-sm text-slate-500 mt-1">ពិនិត្យមើលកាលវិភាគបង្រៀនរបស់អ្នកសម្រាប់សប្តាហ៍នេះ</p>
    </div>

    <div v-if="isLoading" class="text-center py-20 text-slate-500">
      កំពុងផ្ទុកកាលវិភាគ...
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div 
        v-for="(day, index) in scheduleDays" 
        :key="index" 
        class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm"
      >
        <h2 class="font-bold text-indigo-600 mb-4 pb-2 border-b border-indigo-50">{{ day.name }}</h2>
        
        <div v-if="day.lessons.length > 0" class="space-y-3">
          <div 
            v-for="lesson in day.lessons" 
            :key="lesson.id" 
            class="p-3 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-200 transition-all"
          >
            <div class="flex justify-between items-start">
              <span class="text-xs font-bold text-slate-500">{{ lesson.time }}</span>
              <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-bold">
                បន្ទប់៖ {{ lesson.room }}
              </span>
            </div>
            <p class="text-sm font-bold text-slate-800 mt-1">{{ lesson.subject }}</p>
            <p class="text-xs text-slate-400">ថ្នាក់៖ {{ lesson.className }}</p>
          </div>
        </div>
        
        <div v-else class="text-center py-6 text-slate-400 text-sm">
          សម្រាក
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/authService' 

const isLoading = ref(true)

const scheduleDays = ref([
  { name: 'ថ្ងៃចន្ទ', lessons: [] },
  { name: 'ថ្ងៃអង្គារ', lessons: [] },
  { name: 'ថ្ងៃពុធ', lessons: [] },
  { name: 'ថ្ងៃព្រហស្បតិ៍', lessons: [] },
  { name: 'ថ្ងៃសុក្រ', lessons: [] },
  { name: 'ថ្ងៃសៅរ៍', lessons: [] }
])

const fetchTeacherSchedule = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/teacher/my-schedules')
    const data = response.data 

    // បង្កើត Object Mapping សម្រាប់ប្រៀបធៀបឈ្មោះថ្ងៃ
    const dayMapping = {
      'ច័ន្ទ': 'ថ្ងៃចន្ទ',
      'អង្គារ': 'ថ្ងៃអង្គារ',
      'ពុធ': 'ថ្ងៃពុធ',
      'ព្រហស្បតិ៍': 'ថ្ងៃព្រហស្បតិ៍',
      'សុក្រ': 'ថ្ងៃសុក្រ',
      'សៅរ៍': 'ថ្ងៃសៅរ៍'
    }

    // សម្អាតទិន្នន័យចាស់
    scheduleDays.value.forEach(d => d.lessons = [])

    data.forEach(item => {
      // ប្រើ .trim() ដើម្បីកម្ចាត់ space ដែលលើស
      const dbDay = item.day ? item.day.trim() : '';
      const mappedDayName = dayMapping[dbDay] || dbDay;
      
      const day = scheduleDays.value.find(d => d.name === mappedDayName)
      
      if (day) {
        day.lessons.push({
          id: item.id,
          time: item.time,
          subject: item.subject?.name || 'មិនមានមុខវិជ្ជា',
          className: item.class?.name || 'មិនមានថ្នាក់', 
          room: item.room
        })
      } else {
        console.warn("រកមិនឃើញថ្ងៃដែលត្រូវគ្នានឹង៖", dbDay);
      }
    })
  } catch (error) {
    console.error('កំហុសក្នុងការទាញទិន្នន័យកាលវិភាគគ្រូ:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchTeacherSchedule()
})
</script>