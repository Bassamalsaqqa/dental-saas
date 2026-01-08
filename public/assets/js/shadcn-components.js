// shadcn/ui Components Loader
// This file loads all shadcn/ui components for the dental management system

// Import utility functions
import { cn, formatCurrency, formatDate, formatDateTime, formatPhoneNumber, generateId, debounce, throttle, getToothStatus, getAppointmentStatus, getTreatmentType } from '../../lib/utils.js'

// Load all UI components
import './components/ui/button.js'
import './components/ui/card.js'
import './components/ui/input.js'
import './components/ui/dialog.js'
import './components/ui/table.js'
import './components/ui/alert.js'

// Additional components will be loaded here
// import './components/ui/select.js'
// import './components/ui/checkbox.js'
// import './components/ui/radio.js'
// import './components/ui/switch.js'
// import './components/ui/slider.js'
// import './components/ui/progress.js'
// import './components/ui/toast.js'
// import './components/ui/tooltip.js'
// import './components/ui/popover.js'
// import './components/ui/dropdown.js'
// import './components/ui/accordion.js'
// import './components/ui/tabs.js'
// import './components/ui/collapsible.js'
// import './components/ui/separator.js'
// import './components/ui/avatar.js'
// import './components/ui/badge.js'
// import './components/ui/skeleton.js'

// Export utility functions globally
window.cn = cn
window.formatCurrency = formatCurrency
window.formatDate = formatDate
window.formatDateTime = formatDateTime
window.formatPhoneNumber = formatPhoneNumber
window.generateId = generateId
window.debounce = debounce
window.throttle = throttle
window.getToothStatus = getToothStatus
window.getAppointmentStatus = getAppointmentStatus
window.getTreatmentType = getTreatmentType

// Initialize components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('shadcn/ui components loaded successfully')
  
  // Initialize any global components or behaviors
  initializeGlobalComponents()
})

function initializeGlobalComponents() {
  // Initialize tooltips
  initializeTooltips()
  
  // Initialize dropdowns
  initializeDropdowns()
  
  // Initialize modals
  initializeModals()
  
  // Initialize form validation
  initializeFormValidation()
  
  // Initialize data tables
  initializeDataTables()
  
  // Initialize charts
  initializeCharts()
}

function initializeTooltips() {
  const tooltipElements = document.querySelectorAll('[data-tooltip]')
  tooltipElements.forEach(element => {
    element.addEventListener('mouseenter', showTooltip)
    element.addEventListener('mouseleave', hideTooltip)
  })
}

function showTooltip(event) {
  const element = event.target
  const text = element.getAttribute('data-tooltip')
  
  if (!text) return
  
  const tooltip = document.createElement('div')
  tooltip.className = 'tooltip absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg'
  tooltip.textContent = text
  tooltip.id = 'tooltip-' + Date.now()
  
  document.body.appendChild(tooltip)
  
  const rect = element.getBoundingClientRect()
  const tooltipRect = tooltip.getBoundingClientRect()
  
  tooltip.style.left = rect.left + (rect.width / 2) - (tooltipRect.width / 2) + 'px'
  tooltip.style.top = rect.top - tooltipRect.height - 5 + 'px'
  
  element.setAttribute('data-tooltip-id', tooltip.id)
}

function hideTooltip(event) {
  const element = event.target
  const tooltipId = element.getAttribute('data-tooltip-id')
  
  if (tooltipId) {
    const tooltip = document.getElementById(tooltipId)
    if (tooltip) {
      tooltip.remove()
    }
    element.removeAttribute('data-tooltip-id')
  }
}

function initializeDropdowns() {
  const dropdowns = document.querySelectorAll('[data-dropdown]')
  dropdowns.forEach(dropdown => {
    const trigger = dropdown.querySelector('[data-dropdown-trigger]')
    const content = dropdown.querySelector('[data-dropdown-content]')
    
    if (trigger && content) {
      trigger.addEventListener('click', (e) => {
        e.stopPropagation()
        toggleDropdown(dropdown)
      })
    }
  })
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-dropdown]')) {
      closeAllDropdowns()
    }
  })
}

function toggleDropdown(dropdown) {
  const isOpen = dropdown.classList.contains('open')
  
  closeAllDropdowns()
  
  if (!isOpen) {
    dropdown.classList.add('open')
    const content = dropdown.querySelector('[data-dropdown-content]')
    if (content) {
      content.style.display = 'block'
    }
  }
}

function closeAllDropdowns() {
  const openDropdowns = document.querySelectorAll('[data-dropdown].open')
  openDropdowns.forEach(dropdown => {
    dropdown.classList.remove('open')
    const content = dropdown.querySelector('[data-dropdown-content]')
    if (content) {
      content.style.display = 'none'
    }
  })
}

function initializeModals() {
  // Modal triggers
  const modalTriggers = document.querySelectorAll('[data-modal-trigger]')
  modalTriggers.forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault()
      const modalId = trigger.getAttribute('data-modal-trigger')
      const modal = document.getElementById(modalId)
      
      if (modal) {
        const dialog = new Dialog(modal, { open: true })
        dialog.open()
      }
    })
  })
}

function initializeFormValidation() {
  const forms = document.querySelectorAll('form[data-validate]')
  forms.forEach(form => {
    form.addEventListener('submit', (e) => {
      if (!validateForm(form)) {
        e.preventDefault()
      }
    })
  })
}

function validateForm(form) {
  let isValid = true
  const inputs = form.querySelectorAll('input[required], textarea[required], select[required]')
  
  inputs.forEach(input => {
    if (!input.value.trim()) {
      isValid = false
      showFieldError(input, 'This field is required')
    } else {
      clearFieldError(input)
    }
  })
  
  return isValid
}

function showFieldError(input, message) {
  clearFieldError(input)
  
  const error = document.createElement('div')
  error.className = 'field-error text-sm text-error-600 mt-1'
  error.textContent = message
  
  input.parentNode.appendChild(error)
  input.classList.add('border-error-300')
}

function clearFieldError(input) {
  const existingError = input.parentNode.querySelector('.field-error')
  if (existingError) {
    existingError.remove()
  }
  input.classList.remove('border-error-300')
}

function initializeDataTables() {
  const tables = document.querySelectorAll('[data-table]')
  tables.forEach(table => {
    // Add sorting functionality
    const headers = table.querySelectorAll('th[data-sortable]')
    headers.forEach(header => {
      header.style.cursor = 'pointer'
      header.addEventListener('click', () => sortTable(table, header))
    })
  })
}

function sortTable(table, header) {
  const column = header.getAttribute('data-sortable')
  const tbody = table.querySelector('tbody')
  const rows = Array.from(tbody.querySelectorAll('tr'))
  
  const isAscending = header.classList.contains('sort-asc')
  
  // Clear all sort classes
  table.querySelectorAll('th').forEach(th => {
    th.classList.remove('sort-asc', 'sort-desc')
  })
  
  // Add appropriate sort class
  header.classList.add(isAscending ? 'sort-desc' : 'sort-asc')
  
  // Sort rows
  rows.sort((a, b) => {
    const aValue = a.querySelector(`[data-sort="${column}"]`)?.textContent || ''
    const bValue = b.querySelector(`[data-sort="${column}"]`)?.textContent || ''
    
    if (isAscending) {
      return bValue.localeCompare(aValue)
    } else {
      return aValue.localeCompare(bValue)
    }
  })
  
  // Reorder rows in DOM
  rows.forEach(row => tbody.appendChild(row))
}

function initializeCharts() {
  // Chart.js initialization will be handled by individual chart components
  console.log('Chart initialization ready')
}

// Export for global access
window.initializeGlobalComponents = initializeGlobalComponents