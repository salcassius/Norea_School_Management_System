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
        sans: ['"Battambang"', ' "Roboto" ' , 'sans-serif'],
        khmer: ['Battambang', 'sans-serif'],
        muol: ['Kantumruy Pro', 'sans-serif'],
      }
    },
  },
  plugins: [],
}