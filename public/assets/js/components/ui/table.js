// Table component for shadcn/ui
class Table {
  constructor(element, options = {}) {
    this.element = element
    this.options = {
      sortable: true,
      searchable: false,
      pagination: false,
      striped: true,
      hover: true,
      ...options
    }
    this.init()
  }

  init() {
    this.addClasses()
    this.setupSorting()
    this.setupSearch()
    this.setupPagination()
  }

  addClasses() {
    // Table wrapper
    this.element.className = this.cn(
      'relative overflow-auto',
      this.element.className
    )

    // Table element
    const table = this.element.querySelector('table')
    if (table) {
      table.className = this.cn(
        'w-full caption-bottom text-sm',
        table.className
      )
    }

    // Table header
    const thead = this.element.querySelector('thead')
    if (thead) {
      thead.className = this.cn(
        '[&_tr]:border-b',
        thead.className
      )
    }

    // Table body
    const tbody = this.element.querySelector('tbody')
    if (tbody) {
      tbody.className = this.cn(
        '[&_tr:last-child]:border-0',
        this.options.striped ? '[&_tr:nth-child(even)]:bg-muted/50' : '',
        this.options.hover ? '[&_tr:hover]:bg-muted/50' : '',
        tbody.className
      )
    }

    // Table rows
    const rows = this.element.querySelectorAll('tr')
    rows.forEach(row => {
      row.className = this.cn(
        'border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted',
        row.className
      )
    })

    // Table cells
    const cells = this.element.querySelectorAll('td, th')
    cells.forEach(cell => {
      cell.className = this.cn(
        'p-4 align-middle [&:has([role=checkbox])]:pr-0',
        cell.className
      )
    })

    // Table headers
    const headers = this.element.querySelectorAll('th')
    headers.forEach(header => {
      header.className = this.cn(
        'h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0',
        this.options.sortable ? 'cursor-pointer hover:text-foreground' : '',
        header.className
      )
    })
  }

  setupSorting() {
    if (!this.options.sortable) return

    const headers = this.element.querySelectorAll('th[data-sortable]')
    headers.forEach(header => {
      header.addEventListener('click', () => this.sortTable(header))
    })
  }

  sortTable(header) {
    const column = header.getAttribute('data-sortable')
    const tbody = this.element.querySelector('tbody')
    const rows = Array.from(tbody.querySelectorAll('tr'))
    
    const isAscending = header.classList.contains('sort-asc')
    
    // Clear all sort classes
    this.element.querySelectorAll('th').forEach(th => {
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

  setupSearch() {
    if (!this.options.searchable) return

    // Create search input
    const searchContainer = document.createElement('div')
    searchContainer.className = 'mb-4'
    searchContainer.innerHTML = `
      <div class="relative">
        <input type="text" placeholder="Search..." class="w-full max-w-sm px-3 py-2 pl-10 text-sm border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground"></i>
      </div>
    `
    
    this.element.parentNode.insertBefore(searchContainer, this.element)
    
    const searchInput = searchContainer.querySelector('input')
    searchInput.addEventListener('input', (e) => this.filterTable(e.target.value))
  }

  filterTable(searchTerm) {
    const tbody = this.element.querySelector('tbody')
    const rows = tbody.querySelectorAll('tr')
    
    rows.forEach(row => {
      const text = row.textContent.toLowerCase()
      const matches = text.includes(searchTerm.toLowerCase())
      row.style.display = matches ? '' : 'none'
    })
  }

  setupPagination() {
    if (!this.options.pagination) return

    // This would be implemented based on specific pagination requirements
    console.log('Pagination setup would go here')
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }

  // Public methods
  refresh() {
    this.addClasses()
  }

  destroy() {
    // Cleanup if needed
  }
}

// Table sub-components
class TableHeader {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      '[&_tr]:border-b',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

class TableBody {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      '[&_tr:last-child]:border-0',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

class TableRow {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      'border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

class TableHead {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      'h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

class TableCell {
  constructor(element) {
    this.element = element
    this.init()
  }

  init() {
    this.element.className = this.cn(
      'p-4 align-middle [&:has([role=checkbox])]:pr-0',
      this.element.className
    )
  }

  cn(...classes) {
    return classes.filter(Boolean).join(' ')
  }
}

// Auto-initialize tables with data attributes
document.addEventListener('DOMContentLoaded', () => {
  const tables = document.querySelectorAll('[data-table]')
  tables.forEach(table => {
    const sortable = table.hasAttribute('data-sortable')
    const searchable = table.hasAttribute('data-searchable')
    const pagination = table.hasAttribute('data-pagination')
    const striped = !table.hasAttribute('data-no-striped')
    const hover = !table.hasAttribute('data-no-hover')
    
    new Table(table, { sortable, searchable, pagination, striped, hover })
  })

  // Initialize table sub-components
  const tableHeaders = document.querySelectorAll('[data-table-header]')
  tableHeaders.forEach(header => new TableHeader(header))

  const tableBodies = document.querySelectorAll('[data-table-body]')
  tableBodies.forEach(body => new TableBody(body))

  const tableRows = document.querySelectorAll('[data-table-row]')
  tableRows.forEach(row => new TableRow(row))

  const tableHeads = document.querySelectorAll('[data-table-head]')
  tableHeads.forEach(head => new TableHead(head))

  const tableCells = document.querySelectorAll('[data-table-cell]')
  tableCells.forEach(cell => new TableCell(cell))
})

// Export for manual initialization
window.Table = Table
window.TableHeader = TableHeader
window.TableBody = TableBody
window.TableRow = TableRow
window.TableHead = TableHead
window.TableCell = TableCell
