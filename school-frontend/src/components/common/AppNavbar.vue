<template>
  <div class="relative w-full">
    <header class="font-[Battambang] h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-[50]">
      
      <div class="flex items-center gap-4 flex-1">
        <button @click="emit('toggle')" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
          <Menu class="w-5 h-5 text-slate-600" />
        </button>
      </div>

      <div class="flex items-center gap-4">
        <div class="relative">
          <button @click.stop="toggleDropdown"
            class="flex items-center gap-3 p-1 rounded-xl hover:bg-slate-50 transition-all cursor-pointer">
            <div
              class="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-violet-500 text-white rounded-xl flex items-center justify-center font-bold uppercase shadow-sm">
              {{ userInitial }}
            </div>
            <div class="text-left hidden lg:block leading-none">
              <p class="text-sm font-bold text-slate-700">{{ user.name || 'អ្នកប្រើប្រាស់' }}</p>
              <p class="text-[11px] text-slate-400 mt-1">{{ user.email }}</p>
            </div>
            <!-- <ChevronDown :class="['w-4 h-4 text-slate-400 transition-transform', isDropdownOpen ? 'rotate-180' : '']" /> -->
          </button>

          <!-- <div v-if="isDropdownOpen"
            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-[100]">
            
            <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
              <User class="w-4 h-4" /> ប្រវត្តិរូប
            </button>
            
            <hr class="my-1 border-slate-50" />

            <button 
              @click="handleLogout"
              type="button"
              class="w-full flex items-center gap-3 px-4 py-2 text-sm text-rose-500 hover:bg-rose-50 font-medium cursor-pointer transition-colors relative z-[110]"
            >
              <LogOut class="w-4 h-4" />
              <span>ចាកចេញ</span>
            </button>
          </div> -->
        </div>
      </div>
    </header>

    <div v-if="isDropdownOpen" 
      @click="isDropdownOpen = false" 
      class="fixed inset-0 z-[80] bg-black/5">
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { logoutUser } from '../../services/authService';
import { Search, ChevronDown, User, LogOut, Menu } from 'lucide-vue-next';

const emit = defineEmits(['toggle']);
const isDropdownOpen = ref(false);
const searchQuery = ref('');
const user = ref({ name: '', email: '' });

// ប្តូរមកប្រើ localStorage ឱ្យស៊ីគ្នាជាមួយ Router និង Login
onMounted(() => {
  const data = localStorage.getItem('user_data');
  if (data) {
    try {
      user.value = JSON.parse(data);
    } catch (error) {
      console.error("Error parsing user data", error);
    }
  }
});

const userInitial = computed(() =>
  user.value.name ? user.value.name.charAt(0).toUpperCase() : '?'
);

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

// មុខងារចាកចេញ
const handleLogout = async () => {
  // ១. បង្ហាញសញ្ញាថាប៊ូតុងត្រូវបានចុច
  console.log("Logout is running...");

  try {
    // ២. បិទ UI Dropdown ភ្លាម
    isDropdownOpen.value = false;

    // ៣. សម្អាតទិន្នន័យទាំងអស់ចេញពីម៉ាស៊ីន
    localStorage.clear();
    sessionStorage.clear();

    // ៤. បង្ខំឱ្យ Browser ទៅទំព័រ Login ដោយមិនខ្វល់ពី Router
    window.location.href = '/login';

    // ៥. Call API តាមក្រោយ (Optional)
    await logoutUser().catch(() => {});
    
  } catch (err) {
    // បើមាន Error ក៏នៅតែបង្ខំទៅ Login
    window.location.replace('/login');
  }
};
</script>