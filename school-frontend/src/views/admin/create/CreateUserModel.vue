<template>
  <Transition name="fade">
    <div v-if="isOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md shadow-xl overflow-hidden animate-in zoom-in duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-xl font-bold text-slate-800 font-[Battambang]">បន្ថែមអ្នកប្រើប្រាស់ថ្មី</h3>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
            <X class="w-6 h-6" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="p-6 space-y-4 font-[Battambang]">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះពេញ</label>
            <input 
              v-model="form.name" 
              type="text" 
              required 
              placeholder="បញ្ចូលឈ្មោះអ្នកប្រើប្រាស់..."
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all" 
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">អ៊ីមែល</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              placeholder="example@gmail.com"
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all" 
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">តួនាទី</label>
              <select v-model="form.role" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 outline-none cursor-pointer focus:border-indigo-500">
                <option value="student">សិស្ស</option>
                <option value="teacher">គ្រូបង្រៀន</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <!-- <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ស្ថានភាព</label>
              <select v-model="form.status" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 outline-none cursor-pointer focus:border-indigo-500">
                <option value="Active">សកម្ម</option>
                <option value="Inactive">អសកម្ម</option>
              </select>
            </div> -->
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ពាក្យសម្ងាត់</label>
            <input
              v-model="form.password" 
              type="password" 
              required 
              placeholder="••••••••"
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all" 
            />
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button 
              type="button" 
              @click="$emit('close')" 
              class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-xl font-medium transition-all"
            >
              បោះបង់
            </button>
            <button 
              type="submit" 
              :disabled="loading"
              class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 disabled:opacity-70 flex items-center gap-2"
            >
              <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { reactive } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
  isOpen: Boolean,
  loading: Boolean
});

const emit = defineEmits(['close', 'submit']);

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'student',
  status: 1 // កែពី 'Active' មកជាលេខ 1 វិញ
});

const handleSubmit = () => {
  emit('submit', { ...form });
  // Reset form
  Object.assign(form, {
    name: '',
    email: '',
    password: '',
    role: 'student',
    status: 1
  });
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>