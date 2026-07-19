import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// Add Token to every request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('user_token');

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);


// Handle Unauthorized Error (401)
api.interceptors.response.use(
  (response) => response,

  (error) => {
    if (
      error.response?.status === 401 &&
      !error.config.url.includes('/login')
    ) {
      localStorage.clear();
      window.location.href = '/login';
    }

    return Promise.reject(error);
  }
);


// Login User
export const loginUser = async (credentials, router, states) => {

  states.isLoading.value = true;
  states.errorMessage.value = '';

  try {

    const response = await api.post('/login', credentials);

    const { user_token, user_data } = response.data;


    if (user_token) {

      localStorage.setItem(
        'user_token',
        user_token
      );

      localStorage.setItem(
        'user_role',
        user_data.role
      );

      localStorage.setItem(
        'user_data',
        JSON.stringify(user_data)
      );


      // Redirect by Role
      const role = user_data.role;


      if (role === 'admin') {

        await router.replace(
          '/admin/dashboard'
        );

      } else if (role === 'teacher') {

        await router.replace(
          '/teacher/dashboard'
        );

      } else {

        await router.replace(
          '/student/home'
        );

      }


      return response.data;
    }


  } catch (error) {

    console.error(
      "Login Error:",
      error
    );


    states.errorMessage.value =
      error.response?.data?.message ||
      'អ៊ីមែល ឬលេខសម្ងាត់មិនត្រឹមត្រូវ';


    throw error;


  } finally {

    states.isLoading.value = false;

  }

};



// Logout User
export const logoutUser = async () => {

  try {

    await api.post('/logout');

  } catch (error) {

    console.error(
      "Logout error:",
      error
    );

  } finally {

    localStorage.clear();

    window.location.replace('/login');

  }

};


export default api;