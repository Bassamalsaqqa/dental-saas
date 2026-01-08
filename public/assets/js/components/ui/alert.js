// Alert component for shadcn/ui
class Alert {
  constructor(element, options = {}) {
    this.element = element
    this.options = {
      variant: 'default',
      dismissible: true,
      autoHide: false,
      duration: 5000,
      ...options
    }
    this.init()
  }

  init() {
    this.addClasses()
    this.setupDismissible()
    this.setupAutoHide()
  }

  addClasses() {
    const { variant } = this.options
    
    // Base classes
    this.element.className = this.cn(
      'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
      this.getVariantClasses(variant),
      this.element.className
    )
  }

  getVariantClasses(variant) {
    const variants = {
      default: 'bg-background text-foreground',
      destructive: 'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
      success: 'border-success/50 text-success-700 bg-success-50 [&>svg]:text-success-600',
      warning: 'border-warning/50 text-warning-700 bg-warning-50 [&>svg]:text-warning-600',
      info: 'border-info/50 text-info-700 bg-info-50 [&>svg]:text-info-600'
    }
    return variants[variant] || variants.default
  }

  setupDismissible() {
    if (!this.options.dismissible) return

    // Add close button if not exists
    if (!this.element.querySelector('.alert-close')) {
      const closeButton = document.createElement('button')
      closeButton.className = 'alert-close absolute right-2 top-2 p-1 rounded-md hover:bg-muted transition-colors'
      closeButton.innerHTML = '<i class="fas fa-times text-sm"></i>'
      closeButton.addEventListener('click', () => this.dismiss())
      
      this.element.appendChild(closeButton)
    }
  }

  setupAutoHide() {
    if (!this.options.autoHide) return

    setTimeout(() => {
      this.dismiss()
    }, this.options.duration)
  }

  dismiss() {
    this.element.style.transition = 'all 0.3s ease-out'
    this.element.style.transform = 'translateX(100%)'
    this.element.style.opacity = '0'
    
    setTimeout(() => {
      this.element.remove()
    }, 300)
  }

  show() {
    this.element.style.display = 'block'
    this.element.style.transition = 'all 0.3s ease-out'
    this.element.style.transform = 'translateX(0)'
    this.element.style.opacity = '1'
  }

  hide() {
    this.element.style.transition = 'all 0.3s ease-out'
    this.element.style.transform = 'translateX(-100%)'
    this.element.style.opacity = '0'
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }

  // Public methods
  setVariant(variant) {
    this.options.variant = variant
    this.addClasses()
  }

  setDismissible(dismissible) {
    this.options.dismissible = dismissible
    this.setupDismissible()
  }

  destroy() {
    this.element.remove()
  }
}

// Alert utility functions
class AlertManager {
  static alerts = new Map()

  static create(message, options = {}) {
    const alertElement = document.createElement('div')
    alertElement.className = 'alert'
    alertElement.innerHTML = `
      <div class="alert-content">
        <div class="alert-message">${message}</div>
      </div>
    `
    
    // Add to page
    const container = document.querySelector('.alert-container') || this.createContainer()
    container.appendChild(alertElement)
    
    const alert = new Alert(alertElement, options)
    this.alerts.set(alertElement, alert)
    
    return alert
  }

  static createContainer() {
    const container = document.createElement('div')
    container.className = 'alert-container fixed top-4 right-4 z-50 space-y-2'
    document.body.appendChild(container)
    return container
  }

  static success(message, options = {}) {
    return this.create(message, { ...options, variant: 'success' })
  }

  static error(message, options = {}) {
    return this.create(message, { ...options, variant: 'destructive' })
  }

  static warning(message, options = {}) {
    return this.create(message, { ...options, variant: 'warning' })
  }

  static info(message, options = {}) {
    return this.create(message, { ...options, variant: 'info' })
  }

  static dismissAll() {
    this.alerts.forEach(alert => alert.dismiss())
    this.alerts.clear()
  }
}

// Auto-initialize alerts with data attributes
document.addEventListener('DOMContentLoaded', () => {
  const alerts = document.querySelectorAll('[data-alert]')
  alerts.forEach(alert => {
    const variant = alert.getAttribute('data-variant') || 'default'
    const dismissible = !alert.hasAttribute('data-no-dismiss')
    const autoHide = alert.hasAttribute('data-auto-hide')
    const duration = parseInt(alert.getAttribute('data-duration')) || 5000
    
    new Alert(alert, { variant, dismissible, autoHide, duration })
  })
})

// Export for manual initialization
window.Alert = Alert
window.AlertManager = AlertManager
