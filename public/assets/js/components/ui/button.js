// Button component for shadcn/ui
class Button {
  constructor(element, options = {}) {
    this.element = element
    this.options = {
      variant: 'default',
      size: 'default',
      disabled: false,
      loading: false,
      ...options
    }
    this.init()
  }

  init() {
    this.addClasses()
    this.bindEvents()
  }

  addClasses() {
    const { variant, size, disabled, loading } = this.options
    
    // Base classes
    this.element.className = this.cn(
      'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
      this.getVariantClasses(variant),
      this.getSizeClasses(size),
      this.element.className
    )

    if (disabled) {
      this.element.disabled = true
    }

    if (loading) {
      this.showLoading()
    }
  }

  getVariantClasses(variant) {
    const variants = {
      default: 'bg-primary text-primary-foreground hover:bg-primary/90',
      destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
      outline: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
      secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
      ghost: 'hover:bg-accent hover:text-accent-foreground',
      link: 'text-primary underline-offset-4 hover:underline',
      dental: 'bg-dental-600 text-white hover:bg-dental-700 focus:ring-dental-500',
      success: 'bg-success-600 text-white hover:bg-success-700 focus:ring-success-500',
      warning: 'bg-warning-600 text-white hover:bg-warning-700 focus:ring-warning-500',
      error: 'bg-error-600 text-white hover:bg-error-700 focus:ring-error-500'
    }
    return variants[variant] || variants.default
  }

  getSizeClasses(size) {
    const sizes = {
      default: 'h-10 px-4 py-2',
      sm: 'h-9 rounded-md px-3',
      lg: 'h-11 rounded-md px-8',
      icon: 'h-10 w-10'
    }
    return sizes[size] || sizes.default
  }

  showLoading() {
    const originalContent = this.element.innerHTML
    this.element.setAttribute('data-original-content', originalContent)
    this.element.innerHTML = `
      <svg class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Loading...
    `
    this.element.disabled = true
  }

  hideLoading() {
    const originalContent = this.element.getAttribute('data-original-content')
    if (originalContent) {
      this.element.innerHTML = originalContent
      this.element.removeAttribute('data-original-content')
      this.element.disabled = false
    }
  }

  bindEvents() {
    this.element.addEventListener('click', (e) => {
      if (this.options.loading || this.element.disabled) {
        e.preventDefault()
        return false
      }
    })
  }

  cn(...classes) {
    // Simple class name utility - in a real implementation, you'd use clsx and tailwind-merge
    return classes.filter(Boolean).join(' ')
  }

  // Public methods
  setLoading(loading) {
    this.options.loading = loading
    if (loading) {
      this.showLoading()
    } else {
      this.hideLoading()
    }
  }

  setDisabled(disabled) {
    this.options.disabled = disabled
    this.element.disabled = disabled
  }

  destroy() {
    // Cleanup if needed
  }
}

// Auto-initialize buttons with data attributes
document.addEventListener('DOMContentLoaded', () => {
  const buttons = document.querySelectorAll('[data-button]')
  buttons.forEach(button => {
    const variant = button.getAttribute('data-variant') || 'default'
    const size = button.getAttribute('data-size') || 'default'
    const disabled = button.hasAttribute('data-disabled')
    const loading = button.hasAttribute('data-loading')
    
    new Button(button, { variant, size, disabled, loading })
  })
})

// Export for manual initialization
window.Button = Button
