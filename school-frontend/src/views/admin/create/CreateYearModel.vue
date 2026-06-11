<template>
  <Transition name="fade">
    <div v-if="isOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden">
        
        <div class="px-8 pt-8 pb-4 flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">បង្កើតឆ្នាំសិក្សាថ្មី</h2>
            <p class="text-sm text-slate-500 mt-1">សូមបញ្ចូលព័ត៌មានឆ្នាំសិក្សាថ្មី</p>
          </div>
          <button @click="closeModal" class="p-2 hover:bg-slate-100 rounded-full transition-colors text-slate-400">
            <X class="w-6 h-6" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="p-8 space-y-6">
          <div class="space-y-2">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">ឈ្មោះឆ្នាំសិក្សា</label>
            <div class="relative">
              <CalendarDays class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <input 
                v-model="form.year_name"
                type="text" 
                required
                placeholder="ឧទាហរណ៍៖ ២០២៤-២០២៥" 
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pl-12 pr-4 text-sm font-bold outline-none focus:border-indigo-500 transition-all"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">ថ្ងៃចាប់ផ្តើម</label>
              <input v-model="form.start_date" type="date" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 px-4 text-sm font-bold outline-none focus:border-indigo-500" />
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">ថ្ងៃបញ្ចប់</label>
              <input v-model="form.end_date" type="date" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 px-4 text-sm font-bold outline-none focus:border-indigo-500" />
            </div>
          </div>

          <label class="flex items-center gap-3 cursor-pointer group p-1">
            <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm font-bold text-slate-600 group-hover:text-indigo-600 transition-colors">កំណត់ជាឆ្នាំសិក្សាបច្ចុប្បន្ន</span>
          </label>

          <div class="flex gap-3 pt-4">
            <button type="button" @click="closeModal" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 transition-all text-sm font-bold">បោះបង់</button>
            <button 
              type="submit" 
              :disabled="loading"
              class="flex-1 px-6 py-4 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all text-sm font-bold flex items-center justify-center gap-2"
            >
              <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
              {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { X, CalendarDays, Loader2 } from 'lucide-vue-next'
import api from '../../../services/authService' // កែមកជា service (គ្មាន s)

const props = defineProps({ isOpen: Boolean })
const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const form = reactive({
  year_name: '',
  start_date: '',
  end_date: '',
  is_active: false
})

const closeModal = () => {
  form.year_name = ''; form.start_date = ''; form.end_date = ''; form.is_active = false;
  emit('close')
}

const handleSubmit = async () => {
  loading.value = true
  try {
    // ប្តូរមកប្រើ /years ឱ្យត្រូវជាមួយ routes/api.php របស់អ្នក
    const response = await api.post('/years', form)
    console.log("Success:", response.data)
    emit('success')
    closeModal()
  } catch (error) {
    console.error("Error detail:", error.response) // បើកមើលក្នុង Console ដើម្បីដឹងថា Error អ្វីពិតប្រាកដ
    alert("មានបញ្ហាក្នុងការរក្សាទុក!")
  } finally {
    loading.value = false
  }
}

</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>