<template>
  <Transition name="fade">
    <div v-if="isOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-in zoom-in duration-150">
        
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
          <div>
            <h2 class="text-lg font-bold text-slate-800">បង្កើតឆ្នាំសិក្សាថ្មី</h2>
            <p class="text-xs text-slate-500 mt-0.5">សូមបំពេញព័ត៌មានឆ្នាំសិក្សាថ្មីខាងក្រោម</p>
          </div>
          <button @click="closeModal" class="p-1.5 hover:bg-slate-200 rounded-lg transition-colors text-slate-400">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="p-5 space-y-4 text-sm">
          <div class="space-y-1">
            <label class="block font-semibold text-slate-600">ឈ្មោះឆ្នាំសិក្សា <span class="text-rose-500"></span></label>
            <div class="relative">
              <CalendarDays class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input 
                v-model="form.year_name"
                type="text" 
                required
                placeholder="ឧទាហរណ៍៖ ២០២៤-២០២៥" 
                class="w-full border border-slate-300 rounded-xl py-2 pl-10 pr-4 outline-none focus:border-indigo-500 transition-all font-medium"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="block font-semibold text-slate-600">ថ្ងៃចាប់ផ្តើម <span class="text-rose-500"></span></label>
              <input 
                v-model="form.start_date" 
                type="date" 
                required 
                class="w-full border border-slate-300 rounded-xl py-2 px-3 outline-none focus:border-indigo-500 font-medium cursor-pointer" 
              />
            </div>
            <div class="space-y-1">
              <label class="block font-semibold text-slate-600">ថ្ងៃបញ្ចប់ <span class="text-rose-500"></span></label>
              <input 
                v-model="form.end_date" 
                type="date" 
                required 
                class="w-full border border-slate-300 rounded-xl py-2 px-3 outline-none focus:border-indigo-500 font-medium cursor-pointer" 
              />
            </div>
          </div>

          <label class="flex items-center gap-2.5 cursor-pointer group py-1">
            <input 
              v-model="form.is_active" 
              type="checkbox" 
              class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
            >
            <span class="font-medium text-slate-600 group-hover:text-indigo-600 transition-colors">
              កំណត់ជាឆ្នាំសិក្សាបច្ចុប្បន្ន
            </span>
          </label>

          <div class="flex gap-2 pt-3 border-t border-slate-100 justify-end">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
              បោះបង់
            </button>
            <button 
              type="submit" 
              :disabled="loading"
              class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-sm transition-all font-semibold flex items-center justify-center gap-2"
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
import { ref, watch } from 'vue'
import { X, CalendarDays, Loader2 } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps({ 
  isOpen: Boolean
})
const emit = defineEmits(['close', 'success', 'error'])

const loading = ref(false)

const initialForm = () => ({
  year_name: '',
  start_date: '',
  end_date: '',
  is_active: false
})

const form = ref(initialForm())
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    form.value = initialForm()
  }
})

const closeModal = () => {
  emit('close')
}

const handleSubmit = async () => {
  loading.value = true
  try {
    await api.post('/years', form.value)
    emit('success')
    closeModal()
  } catch (error) {
    console.error("Error detail:", error.response)
    alert("មានបញ្ហាក្នុងការបង្កើតទិន្នន័យ!")
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>