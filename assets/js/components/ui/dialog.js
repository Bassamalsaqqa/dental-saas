// Dialog component for shadcn/ui
class Dialog {
  constructor(element, options = {}) {
    this.element = element
    this.options = {
      open: false,
      closable: true,
      backdrop: true,
      size: 'default',
      ...options
    }
    this.init()
  }

  init() {
    this.createDialog()
    this.bindEvents()
    this.setupAccessibility()
  }

  createDialog() {
    // Create dialog structure if it doesn't exist
    if (!this.element.querySelector('.dialog-overlay')) {
      this.element.innerHTML = `
        <div class="dialog-overlay fixed inset-0 z-50 bg-black/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0">
          <div class="dialog-content fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] sm:rounded-lg">
            <div class="dialog-header flex flex-col space-y-1.5 text-center sm:text-left">
              <h2 class="dialog-title text-lg font-semibold leading-none tracking-tight"></h2>
              <p class="dialog-description text-sm text-muted-foreground"></p>
            </div>
            <div class="dialog-body">
              ${this.element.innerHTML}
            </div>
            <div class="dialog-footer flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
              <button class="dialog-cancel btn btn-outline" type="button">Cancel</button>
              <button class="dialog-confirm btn btn-primary" type="button">Confirm</button>
            </div>
            <button class="dialog-close absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground">
              <i class="fas fa-times"></i>
              <span class="sr-only">Close</span>
            </button>
          </div>
        </div>
      `
    }

    this.overlay = this.element.querySelector('.dialog-overlay')
    this.content = this.element.querySelector('.dialog-content')
    this.title = this.element.querySelector('.dialog-title')
    this.description = this.element.querySelector('.dialog-description')
    this.body = this.element.querySelector('.dialog-body')
    this.footer = this.element.querySelector('.dialog-footer')
    this.cancelBtn = this.element.querySelector('.dialog-cancel')
    this.confirmBtn = this.element.querySelector('.dialog-confirm')
    this.closeBtn = this.element.querySelector('.dialog-close')

    this.addClasses()
  }

  addClasses() {
    const { size } = this.options
    
    // Size classes
    const sizeClasses = {
      sm: 'max-w-sm',
      default: 'max-w-lg',
      lg: 'max-w-2xl',
      xl: 'max-w-4xl',
      full: 'max-w-full mx-4'
    }
    
    this.content.className = this.cn(
      'fixed left-[50%] top-[50%] z-50 grid w-full translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 sm:rounded-lg',
      sizeClasses[size] || sizeClasses.default,
      this.content.className
    )
  }

  bindEvents() {
    // Close button
    if (this.closeBtn) {
      this.closeBtn.addEventListener('click', () => this.close())
    }

    // Cancel button
    if (this.cancelBtn) {
      this.cancelBtn.addEventListener('click', () => this.close())
    }

    // Confirm button
    if (this.confirmBtn) {
      this.confirmBtn.addEventListener('click', () => this.confirm())
    }

    // Overlay click
    if (this.overlay && this.options.closable) {
      this.overlay.addEventListener('click', (e) => {
        if (e.target === this.overlay) {
          this.close()
        }
      })
    }

    // Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.options.open) {
        this.close()
      }
    })
  }

  setupAccessibility() {
    // ARIA attributes
    this.element.setAttribute('role', 'dialog')
    this.element.setAttribute('aria-modal', 'true')
    this.element.setAttribute('aria-hidden', 'true')
    
    if (this.title) {
      this.element.setAttribute('aria-labelledby', 'dialog-title')
      this.title.id = 'dialog-title'
    }
    
    if (this.description) {
      this.element.setAttribute('aria-describedby', 'dialog-description')
      this.description.id = 'dialog-description'
    }
  }

  open() {
    this.options.open = true
    this.element.style.display = 'block'
    this.element.setAttribute('aria-hidden', 'false')
    this.element.setAttribute('data-state', 'open')
    
    // Focus management
    this.focusFirstElement()
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden'
    
    // Dispatch event
    this.element.dispatchEvent(new CustomEvent('dialog:open', {
      detail: { dialog: this.element }
    }))
  }

  close() {
    this.options.open = false
    this.element.setAttribute('aria-hidden', 'true')
    this.element.setAttribute('data-state', 'closed')
    
    // Restore body scroll
    document.body.style.overflow = ''
    
    // Hide dialog after animation
    setTimeout(() => {
      this.element.style.display = 'none'
    }, 200)
    
    // Dispatch event
    this.element.dispatchEvent(new CustomEvent('dialog:close', {
      detail: { dialog: this.element }
    }))
  }

  confirm() {
    this.element.dispatchEvent(new CustomEvent('dialog:confirm', {
      detail: { dialog: this.element }
    }))
    this.close()
  }

  focusFirstElement() {
    const focusableElements = this.element.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    )
    if (focusableElements.length > 0) {
      focusableElements[0].focus()
    }
  }

  // Public methods
  setTitle(title) {
    if (this.title) {
      this.title.textContent = title
    }
  }

  setDescription(description) {
    if (this.description) {
      this.description.textContent = description
    }
  }

  setBody(content) {
    if (this.body) {
      this.body.innerHTML = content
    }
  }

  setCancelText(text) {
    if (this.cancelBtn) {
      this.cancelBtn.textContent = text
    }
  }

  setConfirmText(text) {
    if (this.confirmBtn) {
      this.confirmBtn.textContent = text
    }
  }

  setSize(size) {
    this.options.size = size
    this.addClasses()
  }

  setClosable(closable) {
    this.options.closable = closable
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }

  destroy() {
    // Cleanup
    document.body.style.overflow = ''
    this.element.remove()
  }
}

// Dialog utility functions
class DialogManager {
  static dialogs = new Map()

  static create(options = {}) {
    const dialogElement = document.createElement('div')
    dialogElement.className = 'dialog'
    document.body.appendChild(dialogElement)
    
    const dialog = new Dialog(dialogElement, options)
    this.dialogs.set(dialogElement, dialog)
    
    return dialog
  }

  static alert(message, title = 'Alert') {
    const dialog = this.create({
      size: 'sm',
      closable: true
    })
    
    dialog.setTitle(title)
    dialog.setBody(`<p>${message}</p>`)
    dialog.setCancelText('OK')
    dialog.confirmBtn.style.display = 'none'
    
    dialog.open()
    return dialog
  }

  static confirm(message, title = 'Confirm') {
    return new Promise((resolve) => {
      const dialog = this.create({
        size: 'sm',
        closable: true
      })
      
      dialog.setTitle(title)
      dialog.setBody(`<p>${message}</p>`)
      dialog.setCancelText('Cancel')
      dialog.setConfirmText('Confirm')
      
      dialog.element.addEventListener('dialog:confirm', () => {
        resolve(true)
      })
      
      dialog.element.addEventListener('dialog:close', () => {
        resolve(false)
      })
      
      dialog.open()
    })
  }

  static prompt(message, defaultValue = '', title = 'Prompt') {
    return new Promise((resolve) => {
      const dialog = this.create({
        size: 'sm',
        closable: true
      })
      
      dialog.setTitle(title)
      dialog.setBody(`
        <p class="mb-4">${message}</p>
        <input type="text" class="form-input w-full" value="${defaultValue}" id="dialog-prompt-input">
      `)
      dialog.setCancelText('Cancel')
      dialog.setConfirmText('OK')
      
      dialog.element.addEventListener('dialog:confirm', () => {
        const input = dialog.element.querySelector('#dialog-prompt-input')
        resolve(input ? input.value : defaultValue)
      })
      
      dialog.element.addEventListener('dialog:close', () => {
        resolve(null)
      })
      
      dialog.open()
      
      // Focus input
      setTimeout(() => {
        const input = dialog.element.querySelector('#dialog-prompt-input')
        if (input) input.focus()
      }, 100)
    })
  }

  static destroy(dialog) {
    if (this.dialogs.has(dialog.element)) {
      this.dialogs.delete(dialog.element)
      dialog.destroy()
    }
  }
}

// Auto-initialize dialogs with data attributes
document.addEventListener('DOMContentLoaded', () => {
  const dialogs = document.querySelectorAll('[data-dialog]')
  dialogs.forEach(dialog => {
    const open = dialog.hasAttribute('data-open')
    const closable = !dialog.hasAttribute('data-no-close')
    const size = dialog.getAttribute('data-size') || 'default'
    
    new Dialog(dialog, { open, closable, size })
  })
})

// Export for manual initialization
window.Dialog = Dialog
window.DialogManager = DialogManager
