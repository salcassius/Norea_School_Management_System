import axios from 'axios';

const api = axios.create({
  baseURL: (import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '') + '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// រាល់ពេលបាញ់ Request គឺទៅយក Token ពី localStorage មកដាក់ក្នុង Header
api.interceptors.request.use(config => {
  const token = localStorage.getItem('user_token'); 
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, error => {
  return Promise.reject(error);
});

// បើ Backend ប្រាប់ថា Token អស់សុពលភាព (401) វានឹង Clear ចោលហើយដេញទៅ Login វិញ
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401 && !error.config.url.includes('/login')) {
      localStorage.clear();
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);



export const loginUser = async (credentials, router, states) => {
  states.isLoading.value = true;
  states.errorMessage.value = '';

  try {
    const response = await api.post('/login', credentials);
    
    // ចាប់យកទិន្នន័យពី Response (ពិនិត្យមើលឱ្យច្បាស់ថា Backend ផ្ញើមកឈ្មោះ Key ហ្នឹងមែនអត់)
    const { user_token, user_data } = response.data;

    if (user_token) {
      // រក្សាទុកក្នុង localStorage
      localStorage.setItem('user_token', user_token);
      localStorage.setItem('user_role', user_data.role);
      localStorage.setItem('user_data', JSON.stringify(user_data));

      // ប្តូរទំព័រទៅតាម Role
      const role = user_data.role;
      if (role === 'admin') {
        await router.replace('/admin/dashboard');
      } else if (role === 'teacher') {
        await router.push('/teacher/dashboard');
      } else {
        await router.push('/student/home');
      }

      return response.data;
    }
  } catch (error) {
    console.error("Login Error:", error);
    states.errorMessage.value = error.response?.data?.message || 'អ៊ីមែល ឬលេខសម្ងាត់មិនត្រឹមត្រូវ';
    throw error;
  } finally {
    states.isLoading.value = false;
  }
};

export const logoutUser = async () => {
  try {
    // បាញ់ទៅប្រាប់ Backend ថា Logout (បើមាន)
    await api.post('/logout');
  } catch (error) {
    console.error("Logout error:", error);
  } finally {
    // សម្អាតទិន្នន័យក្នុងម៉ាស៊ីន ហើយទៅកាន់ទំព័រ Login
    localStorage.clear();
    window.location.replace('/login');
  }
};

export default api;