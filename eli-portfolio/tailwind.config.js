/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        accent: 'hsl(0, 82%, 49%)', // Nebraska Red
        'light-accent': 'hsl(45, 11.76%, 86.67%)',
        'dark-accent': 'hsl(111.43, 11.11%, 12.35%)',
      },
      scale: {
        '102': '1.02',
      },
      animation: {
        'in': 'in 0.2s ease-out',
        'out': 'out 0.2s ease-in',
      },
      keyframes: {
        in: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
        out: {
          '0%': { opacity: '1', transform: 'scale(1)' },
          '100%': { opacity: '0', transform: 'scale(0.95)' },
        },
      },
    },
  },
  plugins: [],
} 