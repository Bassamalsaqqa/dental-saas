// Card component for shadcn/ui
class Card {
  constructor(element, options = {}) {
    this.element = element
    this.options = {
      variant: 'default',
      interactive: false,
      ...options
    }
    this.init()
  }

  init() {
    this.addClasses()
    this.bindEvents()
  }

  addClasses() {
    const { variant, interactive } = this.options
    
    // Base classes
    this.element.className = this.cn(
      'rounded-lg border bg-card text-card-foreground shadow-sm',
      this.getVariantClasses(variant),
      interactive ? 'cursor-pointer hover:shadow-md transition-shadow duration-200' : '',
      this.element.className
    )
  }

  getVariantClasses(variant) {
    const variants = {
      default: 'border-border',
      elevated: 'shadow-md border-border',
      flat: 'shadow-none border-2 border-border',
      interactive: 'hover:shadow-md transition-shadow duration-200 cursor-pointer',
      success: 'border-success-200 bg-success-50',
      warning: 'border-warning-200 bg-warning-50',
      error: 'border-error-200 bg-error-50',
      info: 'border-info-200 bg-info-50'
    }
    return variants[variant] || variants.default
  }

  bindEvents() {
    if (this.options.interactive) {
      this.element.addEventListener('click', (e) => {
        if (e.target.closest('button, a, input, select, textarea')) {
          return // Don't trigger card click for form elements
        }
        this.element.dispatchEvent(new CustomEvent('card:click', {
          detail: { card: this.element }
        }))
      })
    }
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }

  // Public methods
  setInteractive(interactive) {
    this.options.interactive = interactive
    this.addClasses()
    this.bindEvents()
  }

  destroy() {
    // Cleanup if needed
  }
}

// Card sub-components
class CardHeader {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      'flex flex-col space-y-1.5 p-6',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

class CardTitle {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      'text-2xl font-semibold leading-none tracking-tight',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

class CardDescription {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      'text-sm text-muted-foreground',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

class CardContent {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      'p-6 pt-0',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

class CardFooter {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      'flex items-center p-6 pt-0',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

// Auto-initialize cards with data attributes
document.addEventListener('DOMContentLoaded', () => {
  // Initialize main cards
  const cards = document.querySelectorAll('[data-card]')
  cards.forEach(card => {
    const variant = card.getAttribute('data-variant') || 'default'
    const interactive = card.hasAttribute('data-interactive')
    
    new Card(card, { variant, interactive })
  })

  // Initialize card sub-components
  const cardHeaders = document.querySelectorAll('[data-card-header]')
  cardHeaders.forEach(header => new CardHeader(header))

  const cardTitles = document.querySelectorAll('[data-card-title]')
  cardTitles.forEach(title => new CardTitle(title))

  const cardDescriptions = document.querySelectorAll('[data-card-description]')
  cardDescriptions.forEach(description => new CardDescription(description))

  const cardContents = document.querySelectorAll('[data-card-content]')
  cardContents.forEach(content => new CardContent(content))

  const cardFooters = document.querySelectorAll('[data-card-footer]')
  cardFooters.forEach(footer => new CardFooter(footer))
})

// Export for manual initialization
window.Card = Card
window.CardHeader = CardHeader
window.CardTitle = CardTitle
window.CardDescription = CardDescription
window.CardContent = CardContent
window.CardFooter = CardFooter
