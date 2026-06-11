<template>
  <div class="font-[Battambang] bg-white rounded-[2.5rem] overflow-hidden">
    <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
      <div>
        <h3 class="text-xl font-black text-slate-800 tracking-tight">បង្កើតថ្នាក់រៀនថ្មី</h3>
        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 tracking-[0.2em]">
          សូមបំពេញព័ត៌មានឱ្យបានត្រឹមត្រូវ
        </p>
      </div>
      <button @click="$emit('close')"
        class="p-2.5 hover:bg-rose-50 rounded-2xl transition-all text-slate-400 hover:text-rose-500 group">
        <X class="w-5 h-5 group-active:scale-90" />
      </button>
    </div>

    <form @submit.prevent="submitForm" class="p-8 space-y-5">
      <div class="space-y-2">
        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">
          ឆ្នាំសិក្សា <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
          <CalendarDays class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <select v-model="form.year_id"
            class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pl-11 pr-10 text-sm font-bold text-slate-600 outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all appearance-none cursor-pointer shadow-sm"
            :class="{ 'border-rose-400 bg-rose-50/30': errors.year_id }" required>
            <option value="">--- ជ្រើសរើសឆ្នាំសិក្សា ---</option>
            <option v-for="year in activeYears" :key="year.id" :value="year.id">
              {{ year.year_name }}
            </option>
          </select>
          <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
            <ChevronDown class="w-4 h-4" />
          </div>
        </div>
        <p v-if="errors.year_id" class="text-[10px] text-rose-500 font-bold ml-2">{{ errors.year_id[0] }}</p>
      </div>

      <div class="space-y-2">
        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">
          ឈ្មោះថ្នាក់ <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
          <Layout class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input v-model="form.name" type="text"
            class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pl-11 pr-4 text-sm font-bold text-slate-600 outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all shadow-sm"
            :class="{ 'border-rose-400 bg-rose-50/30': errors.name }" placeholder="ឧទាហរណ៍៖ A, B, C..." required>
        </div>
        <p v-if="errors.name" class="text-[10px] text-rose-500 font-bold ml-2">{{ errors.name[0] }}</p>
      </div>

      <div class="grid grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">
            កម្រិតថ្នាក់ <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <Layers class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <select v-model="form.grade_level"
              class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pl-11 pr-10 text-sm font-bold text-slate-600 outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all shadow-sm cursor-pointer appearance-none"
              :class="{ 'border-rose-400 bg-rose-50/30': errors.grade_level }" required>
              <option value="">ជ្រើសរើស...</option>
              <option v-for="n in [7, 8, 9,]" :key="n" :value="n.toString()">ថ្នាក់ទី {{ n }}</option>
            </select>
            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
              <ChevronDown class="w-4 h-4" />
            </div>
          </div>
          <p v-if="errors.grade_level" class="text-[10px] text-rose-500 font-bold ml-2">{{ errors.grade_level[0] }}</p>
        </div>

        <div class="space-y-2">
          <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1 text-center block">ស្ថានភាព</label>
          <div class="flex items-center justify-center pt-2">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.is_active" class="sr-only peer">
              <div
                class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
              </div>
              <span class="ml-3 text-xs font-bold text-slate-500">{{ form.is_active ? 'សកម្ម' : 'មិនសកម្ម' }}</span>
            </label>
          </div>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">
          គ្រូបន្ទប់ថ្នាក់ (ស្រេចចិត្ត)
        </label>
        <div class="relative">
          <UserRound class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <select v-model="form.teacher_id"
            class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pl-11 pr-10 text-sm font-bold text-slate-600 outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all appearance-none cursor-pointer shadow-sm">
            <option :value="null">--- មិនទាន់មានគ្រូបន្ទប់ថ្នាក់ ---</option>
            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
              {{ teacher.name_kh || teacher.name }} {{ teacher.name_en ? `(${teacher.name_en})` : '' }}
            </option>
          </select>
          <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
            <ChevronDown class="w-4 h-4" />
          </div>
        </div>
      </div>

      <div class="flex gap-4 pt-4">
        <button type="button"
          class="flex-1 px-6 py-4 border border-slate-200 text-slate-500 rounded-2xl hover:bg-slate-50 transition-all text-sm font-black active:scale-95"
          @click="$emit('close')">
          បោះបង់
        </button>
        <button type="submit"
          class="flex-[2] px-6 py-4 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all text-sm font-black active:scale-95 disabled:opacity-50"
          :disabled="loading">
          <span v-if="loading" class="flex items-center justify-center gap-2">
            <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
            រក្សាទុក...
          </span>
          <span v-else>រក្សាទុកទិន្នន័យ</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { X, ChevronDown, Layout, UserRound, CalendarDays, Layers } from 'lucide-vue-next';
import api from '../../../services/authService';

const emit = defineEmits(['close', 'refresh']);

const form = ref({
  year_id: '',
  name: '',
  grade_level: '',
  is_active: true,
  teacher_id: null
});

const activeYears = ref([]);
const teachers = ref([]);
const errors = ref({});
const loading = ref(false);

const fetchData = async () => {
  try {
    const [yearsRes, teachersRes] = await Promise.all([
      api.get('/years'),
      api.get('/teachers')
    ]);
    activeYears.value = yearsRes.data.data || yearsRes.data;
    teachers.value = teachersRes.data.data || teachersRes.data;
  } catch (error) {
    console.error("Error fetching data:", error);
  }
};

const submitForm = async () => {
  loading.value = true;
  errors.value = {};

  try {
    await api.post('/classes', form.value);
    emit('refresh');
    emit('close');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      alert(error.response?.data?.message || 'មានបញ្ហាបច្ចេកទេស!');
    }
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);
</script>