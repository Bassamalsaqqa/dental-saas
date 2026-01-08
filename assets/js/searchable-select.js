/**
 * SearchableSelect Component
 * A reusable component for creating searchable dropdowns with server-side search
 */
class SearchableSelect {
    constructor(selectElement, options = {}) {
        this.originalSelect = selectElement;
        this.options = {
            placeholder: 'Search and select...',
            noResultsText: 'No results found',
            loadingText: 'Loading...',
            minimumInputLength: 1,
            delay: 300,
            searchUrl: '',
            searchParam: 'q',
            valueField: 'id',
            textField: 'text',
            ...options
        };
        
        this.isOpen = false;
        this.searchResults = [];
        this.selectedValue = this.originalSelect.value;
        this.selectedText = this.getSelectedText();
        this.searchTimeout = null;
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.createWrapper();
        this.bindEvents();
        this.loadInitialOptions();
    }
    
    createWrapper() {
        // Create wrapper container
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'searchable-select-wrapper';
        this.wrapper.style.position = 'relative';
        this.wrapper.style.display = 'inline-block';
        this.wrapper.style.width = '100%';
        
        // Create the search input
        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'searchable-select-input';
        this.searchInput.placeholder = this.options.placeholder;
        this.searchInput.setAttribute('autocomplete', 'off');
        this.searchInput.setAttribute('role', 'combobox');
        this.searchInput.setAttribute('aria-expanded', 'false');
        this.searchInput.setAttribute('aria-haspopup', 'listbox');
        
        // Create dropdown container
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'searchable-select-dropdown';
        this.dropdown.setAttribute('role', 'listbox');
        this.dropdown.setAttribute('aria-label', 'Search results');
        
        // Create arrow indicator
        this.arrow = document.createElement('div');
        this.arrow.className = 'searchable-select-arrow';
        this.arrow.innerHTML = '▼';
        this.arrow.setAttribute('aria-hidden', 'true');
        
        // Insert wrapper after original select
        this.originalSelect.parentNode.insertBefore(this.wrapper, this.originalSelect.nextSibling);
        
        // Add elements to wrapper
        this.wrapper.appendChild(this.searchInput);
        this.wrapper.appendChild(this.arrow);
        this.wrapper.appendChild(this.dropdown);
        
        // Hide original select
        this.originalSelect.style.display = 'none';
        
        // Set initial value
        this.searchInput.value = this.selectedText;
        
        console.log('SearchableSelect wrapper created for:', this.originalSelect.id || 'unnamed select');
    }
    
    bindEvents() {
        // Search input events
        this.searchInput.addEventListener('focus', () => this.openDropdown());
        this.searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) {
                this.closeDropdown();
            }
        });
        
        // Prevent form submission when pressing enter in search
        this.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.selectFirstResult();
            }
        });
    }
    
    handleKeydown(e) {
        const results = this.dropdown.querySelectorAll('.searchable-select-option');
        const activeResult = this.dropdown.querySelector('.searchable-select-option.active');
        let activeIndex = -1;
        
        if (activeResult) {
            activeIndex = Array.from(results).indexOf(activeResult);
        }
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.highlightOption(activeIndex + 1);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.highlightOption(activeIndex - 1);
                break;
            case 'Enter':
                e.preventDefault();
                if (activeResult) {
                    this.selectOption(activeResult);
                } else {
                    this.selectFirstResult();
                }
                break;
            case 'Escape':
                this.closeDropdown();
                break;
        }
    }
    
    highlightOption(index) {
        const results = this.dropdown.querySelectorAll('.searchable-select-option');
        
        // Remove active class from all options
        results.forEach(option => option.classList.remove('active'));
        
        // Add active class to selected option
        if (index >= 0 && index < results.length) {
            results[index].classList.add('active');
            results[index].scrollIntoView({ block: 'nearest' });
        }
    }
    
    selectFirstResult() {
        const firstResult = this.dropdown.querySelector('.searchable-select-option');
        if (firstResult) {
            this.selectOption(firstResult);
        }
    }
    
    handleSearch(query) {
        clearTimeout(this.searchTimeout);
        
        if (query.length < this.options.minimumInputLength) {
            this.clearDropdown();
            return;
        }
        
        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, this.options.delay);
    }
    
    async performSearch(query) {
        if (!this.options.searchUrl) {
            this.filterLocalOptions(query);
            return;
        }
        
        this.setLoading(true);
        console.log('Performing search for query:', query, 'URL:', this.options.searchUrl);
        
        try {
            const url = new URL(this.options.searchUrl, window.location.origin);
            url.searchParams.set(this.options.searchParam, query);
            
            console.log('Fetching URL:', url.toString());
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Search response:', data);
            
            this.searchResults = data.results || data;
            this.renderOptions();
            this.setLoading(false);
        } catch (error) {
            console.error('Search error:', error);
            this.setLoading(false);
            this.showError(`Search failed: ${error.message}`);
        }
    }
    
    filterLocalOptions(query) {
        const options = Array.from(this.originalSelect.options);
        const filteredOptions = options.filter(option => 
            option.text.toLowerCase().includes(query.toLowerCase())
        );
        
        this.searchResults = filteredOptions.map(option => ({
            [this.options.valueField]: option.value,
            [this.options.textField]: option.text
        }));
        
        this.renderOptions();
    }
    
    renderOptions() {
        this.clearDropdown();
        
        if (this.isLoading) {
            this.addLoadingOption();
            return;
        }
        
        if (this.searchResults.length === 0) {
            this.addNoResultsOption();
            return;
        }
        
        this.searchResults.forEach((item, index) => {
            const option = document.createElement('div');
            option.className = 'searchable-select-option';
            option.style.padding = '8px 12px';
            option.style.cursor = 'pointer';
            option.style.fontSize = '14px';
            option.style.borderBottom = '1px solid #f3f4f6';
            option.dataset.value = item[this.options.valueField];
            option.dataset.text = item[this.options.textField];
            option.textContent = item[this.options.textField];
            
            // Hover effects
            option.addEventListener('mouseenter', () => {
                option.style.backgroundColor = '#f3f4f6';
            });
            
            option.addEventListener('mouseleave', () => {
                option.style.backgroundColor = 'transparent';
            });
            
            // Click to select
            option.addEventListener('click', () => this.selectOption(option));
            
            this.dropdown.appendChild(option);
        });
    }
    
    addLoadingOption() {
        const option = document.createElement('div');
        option.className = 'searchable-select-option loading';
        option.style.padding = '8px 12px';
        option.style.fontSize = '14px';
        option.style.color = '#6b7280';
        option.style.fontStyle = 'italic';
        option.textContent = this.options.loadingText;
        this.dropdown.appendChild(option);
    }
    
    addNoResultsOption() {
        const option = document.createElement('div');
        option.className = 'searchable-select-option no-results';
        option.style.padding = '8px 12px';
        option.style.fontSize = '14px';
        option.style.color = '#6b7280';
        option.style.fontStyle = 'italic';
        option.textContent = this.options.noResultsText;
        this.dropdown.appendChild(option);
    }
    
    selectOption(optionElement) {
        const value = optionElement.dataset.value;
        const text = optionElement.dataset.text;
        
        this.selectedValue = value;
        this.selectedText = text;
        
        // Update original select
        this.originalSelect.value = value;
        
        // Update search input
        this.searchInput.value = text;
        
        // Trigger change event on original select
        this.originalSelect.dispatchEvent(new Event('change', { bubbles: true }));
        
        this.closeDropdown();
    }
    
    openDropdown() {
        if (this.isOpen) return;
        
        this.isOpen = true;
        this.wrapper.classList.add('open');
        this.dropdown.style.display = 'block';
        this.searchInput.setAttribute('aria-expanded', 'true');
        
        // If no search has been performed, load initial options
        if (this.searchResults.length === 0 && !this.isLoading) {
            this.loadInitialOptions();
        }
        
        console.log('Dropdown opened for:', this.originalSelect.id || 'unnamed select');
    }
    
    closeDropdown() {
        if (!this.isOpen) return;
        
        this.isOpen = false;
        this.wrapper.classList.remove('open');
        this.dropdown.style.display = 'none';
        this.searchInput.setAttribute('aria-expanded', 'false');
        
        // Reset search input to selected value
        this.searchInput.value = this.selectedText;
        
        console.log('Dropdown closed for:', this.originalSelect.id || 'unnamed select');
    }
    
    loadInitialOptions() {
        if (!this.options.searchUrl) {
            this.filterLocalOptions('');
            return;
        }
        
        // Load initial options from server
        this.performSearch('');
    }
    
    clearDropdown() {
        this.dropdown.innerHTML = '';
    }
    
    setLoading(loading) {
        this.isLoading = loading;
    }
    
    showError(message) {
        this.clearDropdown();
        const errorOption = document.createElement('div');
        errorOption.className = 'searchable-select-option error';
        errorOption.style.padding = '8px 12px';
        errorOption.style.fontSize = '14px';
        errorOption.style.color = '#dc2626';
        errorOption.textContent = message;
        this.dropdown.appendChild(errorOption);
    }
    
    getSelectedText() {
        const selectedOption = this.originalSelect.querySelector('option:checked');
        return selectedOption ? selectedOption.textContent : '';
    }
    
    // Public methods
    getValue() {
        return this.selectedValue;
    }
    
    setValue(value) {
        const option = this.originalSelect.querySelector(`option[value="${value}"]`);
        if (option) {
            this.selectedValue = value;
            this.selectedText = option.textContent;
            this.originalSelect.value = value;
            this.searchInput.value = option.textContent;
        }
    }
    
    destroy() {
        this.originalSelect.style.display = 'block';
        this.wrapper.remove();
    }
}

// Auto-initialize searchable selects
document.addEventListener('DOMContentLoaded', function() {
    console.log('SearchableSelect: DOM loaded, initializing...');
    const searchableSelects = document.querySelectorAll('[data-searchable-select]');
    console.log('SearchableSelect: Found', searchableSelects.length, 'elements');
    
    searchableSelects.forEach((select, index) => {
        try {
            const searchUrl = select.dataset.searchUrl;
            const searchParam = select.dataset.searchParam || 'q';
            const valueField = select.dataset.valueField || 'id';
            const textField = select.dataset.textField || 'text';
            const placeholder = select.dataset.placeholder || 'Search and select...';
            
            console.log(`SearchableSelect: Initializing element ${index + 1}`, {
                id: select.id || 'unnamed',
                searchUrl: searchUrl,
                placeholder: placeholder
            });
            
            new SearchableSelect(select, {
                searchUrl: searchUrl,
                searchParam: searchParam,
                valueField: valueField,
                textField: textField,
                placeholder: placeholder
            });
            
            console.log(`SearchableSelect: Successfully initialized element ${index + 1}`);
        } catch (error) {
            console.error(`SearchableSelect: Error initializing element ${index + 1}:`, error);
        }
    });
    
    console.log('SearchableSelect: Initialization complete');
});

// Export for manual initialization
window.SearchableSelect = SearchableSelect;
