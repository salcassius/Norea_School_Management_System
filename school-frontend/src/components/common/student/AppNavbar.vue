<template>
  <div class="relative w-full">
    <header
      class="font-[Battambang] h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-30">

      <div class="flex items-center gap-4 flex-1">
        <button @click="emit('toggle')" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
          <Menu class="w-5 h-5 text-slate-600" />
        </button>
        <div class="hidden md:flex items-center relative max-w-md w-full">
          <Search class="absolute left-3 w-4 h-4 text-slate-400" />
          <input v-model="searchQuery" type="text" placeholder="ស្វែងរកអ្វីមួយ..."
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-10 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
        </div>
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
            <ChevronDown :class="['w-4 h-4 text-slate-400 transition-transform', isDropdownOpen ? 'rotate-180' : '']" />
          </button>

          <div v-if="isDropdownOpen"
            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-[100]">
            
            <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
              <User class="w-4 h-4" /> ប្រវត្តិរូប
            </button>
            
            <hr class="my-1 border-slate-50" />

            <button 
              @mousedown.prevent.stop="handleLogout"
              type="button"
              class="w-full flex items-center gap-3 px-4 py-2 text-sm text-rose-500 hover:bg-rose-50 font-medium cursor-pointer transition-colors"
            >
              <LogOut class="w-4 h-4" />
              <span>ចាកចេញ</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <div v-if="isDropdownOpen" @click="isDropdownOpen = false" class="fixed inset-0 z-[90] bg-transparent"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { logoutUser } from '../../../services/authService'; // ពិនិត្យ Path ឱ្យត្រឹមត្រូវ
import { Search, ChevronDown, User, LogOut, Menu } from 'lucide-vue-next';

const emit = defineEmits(['toggle']);
const isDropdownOpen = ref(false);
const searchQuery = ref('');
const user = ref({ name: '', email: '' });

// ទាញទិន្នន័យ User ពេល Component បើកដំបូង
onMounted(() => {
  const data = sessionStorage.getItem('user_data');
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
  console.log("Logout triggered!"); // ឆែកក្នុង Console (F12)

  // ១. បិទ Dropdown ជាមុន
  isDropdownOpen.value = false;

  try {
    // ២. សម្អាត Storage ទាំងអស់ភ្លាមៗ
    sessionStorage.clear();
    localStorage.clear();

    // ៣. បង្ខំទៅទំព័រ Login ដោយការ Refresh (ដោះស្រាយរឿង UI មិនព្រមទៅ)
    window.location.replace('/login');

    // ៤. ហៅ API តាមក្រោយ (Optional)
    await logoutUser().catch(e => console.warn("Server already logged out"));
    
  } catch (error) {
    console.error("Logout Error:", error);
    // ទោះ Error ក៏ត្រូវតែទៅ Login
    window.location.replace('/login');
  }
};
</script>