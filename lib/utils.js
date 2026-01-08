import { clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs) {
  return twMerge(clsx(inputs))
}

// Utility functions for the dental management system
export const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

export const formatDate = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(new Date(date))
}

export const formatDateTime = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

export const formatPhoneNumber = (phone) => {
  const cleaned = phone.replace(/\D/g, '')
  const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/)
  if (match) {
    return `(${match[1]}) ${match[2]}-${match[3]}`
  }
  return phone
}

export const generateId = () => {
  return Math.random().toString(36).substr(2, 9)
}

export const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

export const throttle = (func, limit) => {
  let inThrottle
  return function() {
    const args = arguments
    const context = this
    if (!inThrottle) {
      func.apply(context, args)
      inThrottle = true
      setTimeout(() => inThrottle = false, limit)
    }
  }
}

// Dental-specific utilities
export const getToothStatus = (tooth) => {
  const statuses = {
    'healthy': { label: 'Healthy', color: 'success' },
    'cavity': { label: 'Cavity', color: 'error' },
    'filled': { label: 'Filled', color: 'warning' },
    'crown': { label: 'Crown', color: 'info' },
    'missing': { label: 'Missing', color: 'neutral' },
    'implant': { label: 'Implant', color: 'primary' }
  }
  return statuses[tooth.status] || statuses['healthy']
}

export const getAppointmentStatus = (status) => {
  const statuses = {
    'scheduled': { label: 'Scheduled', color: 'info' },
    'confirmed': { label: 'Confirmed', color: 'success' },
    'in_progress': { label: 'In Progress', color: 'warning' },
    'completed': { label: 'Completed', color: 'success' },
    'cancelled': { label: 'Cancelled', color: 'error' },
    'no_show': { label: 'No Show', color: 'error' }
  }
  return statuses[status] || statuses['scheduled']
}

export const getTreatmentType = (type) => {
  const types = {
    'preventive': { label: 'Preventive', color: 'success' },
    'restorative': { label: 'Restorative', color: 'warning' },
    'cosmetic': { label: 'Cosmetic', color: 'info' },
    'surgical': { label: 'Surgical', color: 'error' },
    'orthodontic': { label: 'Orthodontic', color: 'primary' }
  }
  return types[type] || types['preventive']
}