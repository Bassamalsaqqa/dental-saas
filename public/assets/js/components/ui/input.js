// Input component for shadcn/ui
class Input {
  constructor(element, options = {}) {
    this.element = element
    this.options = {
      variant: 'default',
      size: 'default',
      disabled: false,
      error: false,
      success: false,
      ...options
    }
    this.init()
  }

  init() {
    this.addClasses()
    this.bindEvents()
    this.setupValidation()
  }

  addClasses() {
    const { variant, size, disabled, error, success } = this.options
    
    // Base classes
    this.element.className = this.cn(
      'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      this.getVariantClasses(variant),
      this.getSizeClasses(size),
      this.getStateClasses(error, success),
      this.element.className
    )

    if (disabled) {
      this.element.disabled = true
    }
  }

  getVariantClasses(variant) {
    const variants = {
      default: 'border-input',
      filled: 'bg-muted border-muted',
      ghost: 'border-transparent bg-transparent',
      error: 'border-error-300 focus:border-error-500 focus:ring-error-500',
      success: 'border-success-300 focus:border-success-500 focus:ring-success-500'
    }
    return variants[variant] || variants.default
  }

  getSizeClasses(size) {
    const sizes = {
      default: 'h-10 px-3 py-2',
      sm: 'h-9 px-2 py-1 text-xs',
      lg: 'h-11 px-4 py-3 text-base'
    }
    return sizes[size] || sizes.default
  }

  getStateClasses(error, success) {
    if (error) {
      return 'border-error-300 focus:border-error-500 focus:ring-error-500'
    }
    if (success) {
      return 'border-success-300 focus:border-success-500 focus:ring-success-500'
    }
    return ''
  }

  bindEvents() {
    // Focus events
    this.element.addEventListener('focus', () => {
      this.element.classList.add('ring-2', 'ring-primary-500', 'ring-offset-2')
    })

    this.element.addEventListener('blur', () => {
      this.element.classList.remove('ring-2', 'ring-primary-500', 'ring-offset-2')
    })

    // Input events
    this.element.addEventListener('input', (e) => {
      this.validate()
      this.element.dispatchEvent(new CustomEvent('input:change', {
        detail: { value: e.target.value, input: this.element }
      }))
    })

    // Clear button functionality
    if (this.element.hasAttribute('data-clearable')) {
      this.addClearButton()
    }
  }

  setupValidation() {
    // Real-time validation
    this.element.addEventListener('blur', () => {
      this.validate()
    })
  }

  validate() {
    const value = this.element.value
    const type = this.element.type
    let isValid = true
    let message = ''

    // Required validation
    if (this.element.hasAttribute('required') && !value.trim()) {
      isValid = false
      message = 'This field is required'
    }

    // Email validation
    if (type === 'email' && value && !this.isValidEmail(value)) {
      isValid = false
      message = 'Please enter a valid email address'
    }

    // Phone validation
    if (type === 'tel' && value && !this.isValidPhone(value)) {
      isValid = false
      message = 'Please enter a valid phone number'
    }

    // Min length validation
    const minLength = this.element.getAttribute('minlength')
    if (minLength && value.length < parseInt(minLength)) {
      isValid = false
      message = `Minimum length is ${minLength} characters`
    }

    // Max length validation
    const maxLength = this.element.getAttribute('maxlength')
    if (maxLength && value.length > parseInt(maxLength)) {
      isValid = false
      message = `Maximum length is ${maxLength} characters`
    }

    this.setValidationState(isValid, message)
    return isValid
  }

  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  isValidPhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/
    return phoneRegex.test(phone.replace(/\D/g, ''))
  }

  setValidationState(isValid, message = '') {
    this.options.error = !isValid
    this.options.success = isValid && this.element.value.length > 0

    // Update classes
    this.addClasses()

    // Show/hide error message
    this.updateErrorMessage(message)

    // Dispatch validation event
    this.element.dispatchEvent(new CustomEvent('input:validate', {
      detail: { isValid, message, input: this.element }
    }))
  }

  updateErrorMessage(message) {
    let errorElement = this.element.parentNode.querySelector('.input-error')
    
    if (message && !isValid) {
      if (!errorElement) {
        errorElement = document.createElement('div')
        errorElement.className = 'input-error text-sm text-error-600 mt-1'
        this.element.parentNode.appendChild(errorElement)
      }
      errorElement.textContent = message
      errorElement.style.display = 'block'
    } else if (errorElement) {
      errorElement.style.display = 'none'
    }
  }

  addClearButton() {
    if (this.element.parentNode.querySelector('.input-clear')) return

    const clearButton = document.createElement('button')
    clearButton.type = 'button'
    clearButton.className = 'input-clear absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors'
    clearButton.innerHTML = '<i class="fas fa-times"></i>'
    
    // Position the input relatively and add clear button
    this.element.parentNode.style.position = 'relative'
    this.element.parentNode.appendChild(clearButton)

    clearButton.addEventListener('click', () => {
      this.element.value = ''
      this.element.focus()
      this.validate()
      this.element.dispatchEvent(new Event('input'))
    })

    // Show/hide clear button based on input value
    this.element.addEventListener('input', () => {
      clearButton.style.display = this.element.value ? 'block' : 'none'
    })

    clearButton.style.display = this.element.value ? 'block' : 'none'
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }

  // Public methods
  setValue(value) {
    this.element.value = value
    this.validate()
  }

  getValue() {
    return this.element.value
  }

  setDisabled(disabled) {
    this.options.disabled = disabled
    this.element.disabled = disabled
  }

  setError(error) {
    this.options.error = error
    this.addClasses()
  }

  setSuccess(success) {
    this.options.success = success
    this.addClasses()
  }

  focus() {
    this.element.focus()
  }

  blur() {
    this.element.blur()
  }

  destroy() {
    // Cleanup if needed
  }
}

// Auto-initialize inputs with data attributes
document.addEventListener('DOMContentLoaded', () => {
  const inputs = document.querySelectorAll('input[data-input], textarea[data-input], select[data-input]')
  inputs.forEach(input => {
    const variant = input.getAttribute('data-variant') || 'default'
    const size = input.getAttribute('data-size') || 'default'
    const disabled = input.hasAttribute('data-disabled')
    const error = input.hasAttribute('data-error')
    const success = input.hasAttribute('data-success')
    
    new Input(input, { variant, size, disabled, error, success })
  })
})

// Export for manual initialization
window.Input = Input
