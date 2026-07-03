<template>
  <Transition name="fade">
    <div v-if="isOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md shadow-xl overflow-hidden animate-in zoom-in duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-xl font-bold text-slate-800 font-[Battambang]">កែសម្រួលអ្នកប្រើប្រាស់</h3>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
            <X class="w-6 h-6" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="p-6 space-y-4 font-[Battambang]">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះពេញ</label>
            <input v-model="form.name" type="text" required placeholder="បញ្ចូលឈ្មោះអ្នកប្រើប្រាស់..."
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">អ៊ីមែល</label>
            <input v-model="form.email" type="email" required placeholder="example@gmail.com"
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all" />
          </div>

          <div class="relative">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              លេខសម្ងាត់ថ្មី <span class="text-xs font-normal text-slate-400">(ទុកទំនេរ បើមិនចង់ប្តូរ)</span>
            </label>
            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••"
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 pr-12 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all" />

            <button type="button" @click="showPassword = !showPassword"
              class="absolute right-3 top-[38px] text-gray-400 hover:text-slate-600 focus:outline-none">
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
              </svg>
            </button>
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
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ស្ថានភាព</label>
              <select v-model="form.status" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 outline-none cursor-pointer focus:border-indigo-500">
                <option :value="1">សកម្ម</option>
                <option :value="0">អសកម្ម</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button type="button" @click="$emit('close')"
              class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
              បោះបង់
            </button>
            <button type="submit" :disabled="loading"
              class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 disabled:opacity-70 flex items-center gap-2">
              <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការកែប្រែ' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { reactive, watch, ref } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
  isOpen: Boolean,
  loading: Boolean,
  user: Object
});

const emit = defineEmits(['close', 'submit', 'error']);
const showPassword = ref(false);

const form = reactive({
  name: '',
  email: '',
  role: '',
  status: 1,
  password: ''
});

// Sync form with user prop
watch(() => props.user, (newUser) => {
  if (newUser) {
    Object.assign(form, {
      name: newUser.name || '',
      email: newUser.email || '',
      role: newUser.role || '',
      status: (newUser.status === 'Active' || newUser.status == 1) ? 1 : 0,
      password: ''
    });
  }
  showPassword.value = false;
}, { immediate: true });

const handleSubmit = () => {
  const submitData = { ...form };
  // Only send password if the user actually typed something
  if (!submitData.password || submitData.password.trim() === '') {
    delete submitData.password;
  }
  emit('submit', submitData);
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>