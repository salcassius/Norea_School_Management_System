<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-in zoom-in duration-150 font-[Battambang]">
      
      <div class="p-6 pb-4 flex items-center justify-between border-b border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center shadow-sm">
            <BookOpen class="w-6 h-6 text-indigo-600" />
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-800">បន្ថែមមុខវិជ្ជាថ្មី</h2>
            <!-- <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Add New Subject</p> -->
          </div>
        </div>
        <button @click="$emit('update:modelValue', false)" class="p-2 hover:bg-slate-100 rounded-xl transition-colors text-slate-400 hover:text-slate-600">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-6 space-y-5">
        
        <div class="space-y-2">
          <label class="text-[14px] font-bold text-slate-600 uppercase tracking-wider ml-1">ឈ្មោះមុខវិជ្ជា</label>
          <div class="relative">
            <Type class="absolute left-4 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <input 
              v-model="form.name"
              type="text" 
              placeholder="ឧទាហរណ៍៖ គណិតវិទ្យា"
              required
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
            />
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-8">
          <button type="button" @click="$emit('update:modelValue', false)" 
           class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
            បោះបង់
          </button>
          <button type="submit" :disabled="isSubmitting"
            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all font-bold text-sm flex items-center gap-2 disabled:opacity-50">
            <span v-if="isSubmitting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full font-bold animate-spin"></span>
            {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { X, BookOpen, Type } from 'lucide-vue-next'
import api from '../../../services/authService'

const emit = defineEmits(['update:modelValue', 'refresh'])
const isSubmitting = ref(false)

const form = ref({
  name: ''
})

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    await api.post('/subjects', { name: form.value.name }) 
    
    emit('refresh')
    emit('update:modelValue', false)
    form.value.name = '' // Reset form
  } catch (error) {
    alert(error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក!')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}
</style>