/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}", 
  ],
  theme: {
    extend: {
       fontFamily: {
        khmer: ['Battambang', 'sans-serif'],
        moul: ['KhmerMoul', 'sans-serif'],
      },
    },
  },

  plugins: [],
}
