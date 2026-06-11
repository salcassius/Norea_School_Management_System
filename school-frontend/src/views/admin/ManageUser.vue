<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងអ្នកប្រើប្រាស់</h1>
        <p class="text-sm text-slate-500 mt-1">គ្រប់គ្រងសិទ្ធិ និងព័ត៌មានលម្អិតរបស់អ្នកប្រើប្រាស់ក្នុងប្រព័ន្ធ</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button @click="openModal"
          class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          បន្ថែមអ្នកប្រើប្រាស់
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in stats" :key="stat.id" @click="filterByStats(stat.id)" :class="[
        'p-5 rounded-2xl border transition-all cursor-pointer shadow-sm flex items-center gap-4 active:scale-95',
        selectedRole === stat.id ? 'bg-white border-indigo-500 ring-4 ring-indigo-50' : 'bg-white border-slate-200 hover:border-indigo-300'
      ]">
        <div :class="['p-3 rounded-xl', stat.bgClass]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconClass]" />
        </div>
        <div>
          <p class="text-[12px] text-slate-500 font-medium">{{ stat.label }}</p>
          <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row gap-4 justify-between bg-slate-50/30">
        <div class="relative max-w-sm w-full">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះ ឬអុីមែល..."
            class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" />
        </div>
        <div v-if="selectedRole !== 'all'" class="flex items-center">
          <button @click="selectedRole = 'all'" class="text-xs text-indigo-600 hover:underline">បង្ហាញទាំងអស់</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <div v-if="isLoading" class="p-10 text-center text-slate-500">កំពុងទាញទិន្នន័យ...</div>
        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">អ្នកប្រើប្រាស់</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">តួនាទី</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ថ្ងៃចូលរួម</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50/80 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    {{ user.name?.charAt(0) || 'U' }}
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700 leading-tight">{{ user.name }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600 capitalize">{{ user.role }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm border',
                  (user.status === 'Active' || user.status == 1)
                    ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                    : 'bg-rose-50 text-rose-600 border-rose-100'
                ]">
                  {{ (user.status === 'Active' || user.status == 1) ? 'សកម្ម' : 'អសកម្ម' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-500">{{ user.joinedDate }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="openEditModal(user)"
                    class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all active:scale-90"
                    title="Edit">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="deleteUser(user.id)"
                    class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all active:scale-90"
                    title="Delete">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredUsers.length === 0">
              <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-sm">មិនមានទិន្នន័យឡើយ</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <CreateUserModel 
      :isOpen="isModalOpen" 
      :loading="isLoading" 
      @close="closeModal" 
      @submit="handleCreateUser" 
    />

    <editeUser 
      :isOpen="isEditModalOpen" 
      :user="editingUser"
      :loading="isLoading"
      @close="isEditModalOpen = false"
      @submit="handleUpdateUser" 
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  Users, UserCheck, UserPlus, Search, Plus,
  Edit3, Trash2
} from 'lucide-vue-next';
import userService from '../../services/userService';
import CreateUserModel from './create/CreateUserModel.vue';
import editeUser from '../admin/edite/EditeUser.vue'

// --- State ---
const searchQuery = ref('');
const users = ref([]);
const isLoading = ref(false);
const selectedRole = ref('all');
const isModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editingUser = ref(null);

// --- Stats Definition ---
const stats = ref([
  { id: 'all', label: 'សរុបអ្នកប្រើប្រាស់', value: 0, icon: Users, bgClass: 'bg-indigo-50', iconClass: 'text-indigo-600' },
  { id: 'teacher', label: 'គ្រូបង្រៀន', value: 0, icon: UserCheck, bgClass: 'bg-emerald-50', iconClass: 'text-emerald-600' },
  { id: 'student', label: 'សិស្សានុសិស្ស', value: 0, icon: UserPlus, bgClass: 'bg-blue-50', iconClass: 'text-blue-600' },
  { id: 'active', label: 'អ្នកកំពុងសកម្ម', value: 0, icon: UserCheck, bgClass: 'bg-emerald-50', iconClass: 'text-emerald-600' },
]);

// --- Fetch Data ---
const fetchUsers = async () => {
  try {
    isLoading.value = true;
    const response = await userService.getUsers();
    const rawData = response.data || []; 

    users.value = rawData.map(u => ({
      ...u,
      joinedDate: u.created_at ? new Date(u.created_at).toLocaleDateString('km-KH') : 'N/A'
    }));

    updateStats();
  } catch (err) {
    console.error('Failed to load users:', err);
  } finally {
    isLoading.value = false;
  }
};

const updateStats = () => {
  stats.value[0].value = users.value.length;
  stats.value[1].value = users.value.filter(u => u.role?.toLowerCase() === 'teacher').length;
  stats.value[2].value = users.value.filter(u => u.role?.toLowerCase() === 'student').length;
  stats.value[3].value = users.value.filter(u => u.status == 1 || u.status === 'Active').length;
};

const filterByStats = (id) => {
  selectedRole.value = id;
};

const filteredUsers = computed(() => {
  let result = users.value;
  if (selectedRole.value !== 'all') {
    if (selectedRole.value === 'active') {
      result = result.filter(u => u.status === 'Active' || u.status == 1);
    } else if (selectedRole.value === 'student') {
      result = result.filter(u => u.role?.toLowerCase() === 'student');
    } else {
      result = result.filter(u => u.role?.toLowerCase() === selectedRole.value);
    }
  }
  const query = searchQuery.value.toLowerCase();
  if (query) {
    result = result.filter(user =>
      user.name?.toLowerCase().includes(query) ||
      user.email?.toLowerCase().includes(query)
    );
  }
  return result;
});

// --- Modal Actions ---
const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const handleCreateUser = async (formData) => {
  try {
    isLoading.value = true;
    await userService.createUser(formData);
    alert('បង្កើតអ្នកប្រើប្រាស់ជោគជ័យ!');
    closeModal();
    await fetchUsers();
  } catch (err) {
    alert(err.response?.data?.message || 'មិនអាចបង្កើតអ្នកប្រើប្រាស់បានទេ');
  } finally {
    isLoading.value = false;
  }
};

// --- Edit Actions ---
const openEditModal = (user) => {
  editingUser.value = { ...user }; // Clone data to avoid direct mutation
  isEditModalOpen.value = true;
};

const handleUpdateUser = async (formData) => {
  try {
    isLoading.value = true;
    await userService.updateUser(editingUser.value.id, formData);
    alert('ការកែសម្រួលជោគជ័យ!');
    isEditModalOpen.value = false;
    await fetchUsers(); 
  } catch (err) {
    alert(err.response?.data?.message || 'មានបញ្ហាក្នុងការកែសម្រួល');
  } finally {
    isLoading.value = false;
  }
};

// --- Delete Action ---
const deleteUser = async (id) => {
  if (!confirm('តើអ្នកប្រាកដថាចង់លុបអ្នកប្រើប្រាស់នេះមែនទេ?')) return;
  try {
    await userService.deleteUser(id);
    await fetchUsers();
  } catch (err) {
    alert('មិនអាចលុបបានទេ: ' + (err.response?.data?.message || 'Error'));
  }
};

onMounted(fetchUsers);
</script>