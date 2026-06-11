<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { loginUser } from '../../services/authService';
import logo from '../../assets/logo.png';

const router = useRouter();
const showPassword = ref(false);
const isLoading = ref(false);
const errorMessage = ref('');

const credentials = reactive({
  email: '',
  password: ''
});

const handleLogin = async () => {
  const cleanCredentials = {
    email: credentials.email.trim(),
    password: credentials.password.trim()
  };

  try {
    await loginUser(cleanCredentials, router, { 
      isLoading: isLoading, 
      errorMessage: errorMessage 
    });
  } catch (err) {
    console.error("Login component error:", err);
    
  }
};
</script>

<template>
  <div class="font-[Battambang] min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-700 via-blue-800 to-blue-900 p-6">
    <div class="w-full max-w-md space-y-8 bg-white p-10 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.2)]">
      
      <div class="text-center">
        <img class="mx-auto h-24 w-auto" :src="logo" alt="Logo" />
        <h2 class="mt-4 text-xl font-extrabold tracking-tight text-gray-900">
          ស្វាគមន៍មកកាន់សាលាអនុវិទ្យាល័យនរា
        </h2>
        <p class="text-gray-500 mt-2">
          ចូលគណនីរបស់អ្នកដើម្បីប្រើប្រាស់
        </p>
      </div>

      <div v-if="errorMessage"
        class="bg-red-50 text-red-600 p-3 rounded-xl text-sm text-center font-medium border border-red-100 animate-pulse">
        {{ errorMessage }}
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
              Email address
            </label>
            <input v-model="credentials.email" id="email" type="email" required placeholder="name@gmail.com"
              class="block w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none border" />
          </div>

          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
              Password
            </label>
            <div class="relative">
              <input v-model="credentials.password" id="password" :type="showPassword ? 'text' : 'password'" required
                placeholder="••••••••"
                class="block w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-3 pr-12 text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none border" />
              
              <button type="button" @click="showPassword = !showPassword" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 focus:outline-none">
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div>
          <button type="submit" :disabled="isLoading"
            class="w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-lg disabled:opacity-70 disabled:cursor-not-allowed">
            <svg v-if="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isLoading ? 'កំពុងផ្ទៀងផ្ទាត់...' : 'ចូលប្រើប្រាស់' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>