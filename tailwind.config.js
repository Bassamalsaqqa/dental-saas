/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./public/**/*.{js,html}",
    "./app/Controllers/**/*.php",
    "./app/Models/**/*.php",
    "./public/assets/js/components/**/*.js",
    "./lib/**/*.js",
  ],
  darkMode: 'class', // Enable dark mode with class strategy
  theme: {
    container: {
      center: true,
      padding: "2rem",
      screens: {
        "2xl": "1400px",
      },
    },
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
        // Enhanced color system with semantic naming
        // Brand colors
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
        // Dental/Medical theme colors
        dental: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
          950: '#082f49',
        },
        // Semantic colors for better UX
        success: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
          950: '#052e16',
        },
        warning: {
          50: '#fffbeb',
          100: '#fef3c7',
          200: '#fde68a',
          300: '#fcd34d',
          400: '#fbbf24',
          500: '#f59e0b',
          600: '#d97706',
          700: '#b45309',
          800: '#92400e',
          900: '#78350f',
          950: '#451a03',
        },
        error: {
          50: '#fef2f2',
          100: '#fee2e2',
          200: '#fecaca',
          300: '#fca5a5',
          400: '#f87171',
          500: '#ef4444',
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
          900: '#7f1d1d',
          950: '#450a0a',
        },
        info: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
          950: '#082f49',
        },
        // Neutral grays with better contrast
        neutral: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
          950: '#0a0a0a',
        },
      },
      
      // Enhanced typography system
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
        mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', 'monospace'],
        display: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      
      // Enhanced spacing scale
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
        '144': '36rem',
      },
      
      // Enhanced sizing scale
      width: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
        '144': '36rem',
      },
      height: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
        '144': '36rem',
      },
      
      // Enhanced border radius
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
        '4xl': '2rem',
        '5xl': '2.5rem',
      },
      
      // Enhanced shadows
      boxShadow: {
        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
        'medium': '0 4px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
        'hard': '0 10px 40px -10px rgba(0, 0, 0, 0.2), 0 2px 10px -5px rgba(0, 0, 0, 0.1)',
        'glow': '0 0 20px rgba(59, 130, 246, 0.3)',
        'glow-dental': '0 0 20px rgba(14, 165, 233, 0.3)',
      },
      
      // Enhanced animations
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'fade-in-up': 'fadeInUp 0.5s ease-out',
        'fade-in-down': 'fadeInDown 0.5s ease-out',
        'slide-in-right': 'slideInRight 0.3s ease-out',
        'slide-in-left': 'slideInLeft 0.3s ease-out',
        'bounce-gentle': 'bounceGentle 2s infinite',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'spin-slow': 'spin 3s linear infinite',
        'wiggle': 'wiggle 1s ease-in-out infinite',
      },
      
      // Custom keyframes
      keyframes: {
        "accordion-down": {
          from: { height: "0" },
          to: { height: "var(--radix-accordion-content-height)" },
        },
        "accordion-up": {
          from: { height: "var(--radix-accordion-content-height)" },
          to: { height: "0" },
        },
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        fadeInUp: {
          '0%': { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        fadeInDown: {
          '0%': { opacity: '0', transform: 'translateY(-20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideInRight: {
          '0%': { transform: 'translateX(100%)' },
          '100%': { transform: 'translateX(0)' },
        },
        slideInLeft: {
          '0%': { transform: 'translateX(-100%)' },
          '100%': { transform: 'translateX(0)' },
        },
        bounceGentle: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-5px)' },
        },
        wiggle: {
          '0%, 100%': { transform: 'rotate(-3deg)' },
          '50%': { transform: 'rotate(3deg)' },
        },
      },
      
      // Enhanced backdrop blur
      backdropBlur: {
        xs: '2px',
      },
      
      // Enhanced z-index scale
      zIndex: {
        '60': '60',
        '70': '70',
        '80': '80',
        '90': '90',
        '100': '100',
      },
      
      // Enhanced max width
      maxWidth: {
        '8xl': '88rem',
        '9xl': '96rem',
      },
      
      // Enhanced screens for better responsive design
      screens: {
        'xs': '475px',
        '3xl': '1600px',
        '4xl': '1920px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class', // Use class strategy for better control
    }),
    require('@tailwindcss/typography')({
      target: 'modern', // Use modern typography styles
    }),
    // Custom plugin for additional utilities
    function({ addUtilities, addComponents, theme }) {
      // Add custom utilities
      addUtilities({
        '.text-shadow': {
          textShadow: '0 2px 4px rgba(0,0,0,0.10)',
        },
        '.text-shadow-md': {
          textShadow: '0 4px 8px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.08)',
        },
        '.text-shadow-lg': {
          textShadow: '0 15px 35px rgba(0,0,0,0.12), 0 5px 15px rgba(0,0,0,0.07)',
        },
        '.text-shadow-none': {
          textShadow: 'none',
        },
        '.scrollbar-hide': {
          '-ms-overflow-style': 'none',
          'scrollbar-width': 'none',
          '&::-webkit-scrollbar': {
            display: 'none',
          },
        },
        '.scrollbar-default': {
          '-ms-overflow-style': 'auto',
          'scrollbar-width': 'auto',
          '&::-webkit-scrollbar': {
            display: 'block',
          },
        },
      });
      
      // Add custom components
      addComponents({
        '.btn': {
          '@apply inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed': {},
        },
        '.btn-primary': {
          '@apply btn bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500': {},
        },
        '.btn-secondary': {
          '@apply btn bg-neutral-200 text-neutral-900 hover:bg-neutral-300 focus:ring-neutral-500': {},
        },
        '.btn-dental': {
          '@apply btn bg-dental-600 text-white hover:bg-dental-700 focus:ring-dental-500': {},
        },
        '.btn-success': {
          '@apply btn bg-success-600 text-white hover:bg-success-700 focus:ring-success-500': {},
        },
        '.btn-warning': {
          '@apply btn bg-warning-600 text-white hover:bg-warning-700 focus:ring-warning-500': {},
        },
        '.btn-error': {
          '@apply btn bg-error-600 text-white hover:bg-error-700 focus:ring-error-500': {},
        },
        '.btn-outline': {
          '@apply btn bg-transparent border-2 text-primary-600 border-primary-600 hover:bg-primary-600 hover:text-white focus:ring-primary-500': {},
        },
        '.btn-ghost': {
          '@apply btn bg-transparent text-neutral-600 hover:bg-neutral-100 focus:ring-neutral-500': {},
        },
        '.card': {
          '@apply bg-white rounded-lg shadow-soft border border-neutral-200 overflow-hidden': {},
        },
        '.card-header': {
          '@apply px-6 py-4 border-b border-neutral-200 bg-neutral-50': {},
        },
        '.card-body': {
          '@apply px-6 py-4': {},
        },
        '.card-footer': {
          '@apply px-6 py-4 border-t border-neutral-200 bg-neutral-50': {},
        },
        '.form-group': {
          '@apply space-y-1': {},
        },
        '.form-label': {
          '@apply block text-sm font-medium text-neutral-700': {},
        },
        '.form-input': {
          '@apply block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm transition-colors duration-200': {},
        },
        '.form-textarea': {
          '@apply form-input resize-none': {},
        },
        '.form-select': {
          '@apply form-input pr-10': {},
        },
        '.form-checkbox': {
          '@apply h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 rounded': {},
        },
        '.form-radio': {
          '@apply h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300': {},
        },
        '.form-error': {
          '@apply text-sm text-error-600': {},
        },
        '.form-help': {
          '@apply text-sm text-neutral-500': {},
        },
        '.badge': {
          '@apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium': {},
        },
        '.badge-primary': {
          '@apply badge bg-primary-100 text-primary-800': {},
        },
        '.badge-success': {
          '@apply badge bg-success-100 text-success-800': {},
        },
        '.badge-warning': {
          '@apply badge bg-warning-100 text-warning-800': {},
        },
        '.badge-error': {
          '@apply badge bg-error-100 text-error-800': {},
        },
        '.badge-neutral': {
          '@apply badge bg-neutral-100 text-neutral-800': {},
        },
        '.alert': {
          '@apply p-4 rounded-md border': {},
        },
        '.alert-success': {
          '@apply alert bg-success-50 border-success-200 text-success-800': {},
        },
        '.alert-warning': {
          '@apply alert bg-warning-50 border-warning-200 text-warning-800': {},
        },
        '.alert-error': {
          '@apply alert bg-error-50 border-error-200 text-error-800': {},
        },
        '.alert-info': {
          '@apply alert bg-info-50 border-info-200 text-info-800': {},
        },
        '.loading': {
          '@apply animate-spin rounded-full border-2 border-neutral-300 border-t-primary-600': {},
        },
        '.skeleton': {
          '@apply animate-pulse bg-neutral-200 rounded': {},
        },
      });
    },
  ],
}
