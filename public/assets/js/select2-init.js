/**
 * Select2 Initialization Script
 * Replaces custom searchable select with Select2 for better UX and functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Select2: Initializing...');
    
    // Initialize Select2 for elements with data-searchable-select attribute
    const searchableSelects = document.querySelectorAll('[data-searchable-select]');
    console.log('Select2: Found', searchableSelects.length, 'searchable select elements');
    
    searchableSelects.forEach((select, index) => {
        try {
            const searchUrl = select.dataset.searchUrl;
            const searchParam = select.dataset.searchParam || 'q';
            const valueField = select.dataset.valueField || 'id';
            const textField = select.dataset.textField || 'text';
            const placeholder = select.dataset.placeholder || 'Search and select...';
            const allowClear = select.dataset.allowClear !== 'false';
            const minimumInputLength = parseInt(select.dataset.minimumInputLength) || 1; 
            const delay = parseInt(select.dataset.delay) || 300;
            
            console.log(`Select2: Initializing element ${index + 1}`, {
                id: select.id || 'unnamed',
                searchUrl: searchUrl,
                placeholder: placeholder,
                allowClear: allowClear
            });
            
            // Configure Select2 options
            const select2Options = {
                placeholder: placeholder,
                allowClear: allowClear,
                minimumInputLength: minimumInputLength,
                delay: delay,
                width: '100%',
                theme: 'default',
                language: {
                    noResults: function() {
                        return "No results found";
                    },
                    searching: function() {
                        return "Searching...";
                    },
                    inputTooShort: function() {
                        return `Please enter ${minimumInputLength} or more characters`;
                    }
                }
            };
            
            // Add AJAX configuration if search URL is provided
            if (searchUrl) {
                select2Options.ajax = {
                    url: searchUrl,
                    dataType: 'json',
                    delay: delay,
                    data: function (params) {
                        return {
                            [searchParam]: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        
                        // Handle different response formats
                        let results = [];
                        if (data.results) {
                            results = data.results;
                        } else if (Array.isArray(data)) {
                            results = data;
                        } else if (data.data) {
                            results = data.data;
                        }
                        
                        return {
                            results: results.map(item => ({
                                id: item[valueField],
                                text: item[textField]
                            })),
                            pagination: {
                                more: (params.page * 10) < (data.total || results.length)
                            }
                        };
                    },
                    cache: true
                };
            }
            
            // Initialize Select2
            $(select).select2(select2Options);
            
            // Add custom styling classes
            $(select).on('select2:open', function() {
                $('.select2-container--default .select2-search--dropdown .select2-search__field').addClass('form-input');
            });
            
            // Handle form validation styling
            $(select).on('change', function() {
                const $select2 = $(this).next('.select2-container');
                if ($(this).hasClass('is-invalid')) {
                    $select2.addClass('is-invalid');
                } else {
                    $select2.removeClass('is-invalid');
                }
            });
            
            console.log(`Select2: Successfully initialized element ${index + 1}`);
        } catch (error) {
            console.error(`Select2: Error initializing element ${index + 1}:`, error);
        }
    });
    
    // Initialize Select2 for regular select elements that should be searchable
    const regularSelects = document.querySelectorAll('select:not([data-searchable-select]):not([data-no-select2])');
    console.log('Select2: Found', regularSelects.length, 'regular select elements');
    
    regularSelects.forEach((select, index) => {
        try {
            // Skip if it's a simple select with few options
            if (select.options.length <= 5) {
                return;
            }
            
            console.log(`Select2: Initializing regular select ${index + 1}`, {
                id: select.id || 'unnamed',
                options: select.options.length
            });
            
            $(select).select2({
                placeholder: 'Select an option...',
                allowClear: true,
                width: '100%',
                theme: 'default',
                language: {
                    noResults: function() {
                        return "No results found";
                    }
                }
            });
            
            console.log(`Select2: Successfully initialized regular select ${index + 1}`);
        } catch (error) {
            console.error(`Select2: Error initializing regular select ${index + 1}:`, error);
        }
    });
    
    console.log('Select2: Initialization complete');
});

// Custom CSS for Select2 to match application theme
const select2CustomCSS = `
<style>
/* Select2 Custom Styling */
.select2-container--default .select2-selection--single {
    height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background-color: #ffffff;
    transition: all 0.2s ease-in-out;
}

.select2-container--default .select2-selection--single:hover {
    border-color: #0284c7;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    outline: none;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #374151;
    line-height: 40px;
    padding-left: 12px;
    padding-right: 20px;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #9ca3af;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
    right: 8px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6b7280 transparent transparent transparent;
    border-style: solid;
    border-width: 5px 4px 0 4px;
    height: 0;
    left: 50%;
    margin-left: -4px;
    margin-top: -2px;
    position: absolute;
    top: 50%;
    width: 0;
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #6b7280 transparent;
    border-width: 0 4px 5px 4px;
}

.select2-dropdown {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    background-color: #ffffff;
    z-index: 9999;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 8px 12px;
    font-size: 14px;
    margin: 4px;
    width: calc(100% - 8px);
}

.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    outline: none;
}

.select2-container--default .select2-results__option {
    padding: 8px 12px;
    font-size: 14px;
    color: #374151;
    transition: background-color 0.2s ease-in-out;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #0284c7;
    color: #ffffff;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #f3f4f6;
    color: #374151;
}

.select2-container--default .select2-results__option[aria-selected=true]:hover {
    background-color: #e5e7eb;
}

.select2-container--default .select2-results__option--loading {
    color: #6b7280;
    font-style: italic;
}

.select2-container--default .select2-results__message {
    color: #6b7280;
    font-style: italic;
    padding: 8px 12px;
}

/* Error state styling */
.select2-container--default.is-invalid .select2-selection--single {
    border-color: #dc2626;
}

.select2-container--default.is-invalid.select2-container--focus .select2-selection--single {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

/* Loading state */
.select2-container--default .select2-selection--single .select2-selection__rendered {
    position: relative;
}

.select2-container--default .select2-selection--single .select2-selection__rendered::after {
    content: '';
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    border: 2px solid #e5e7eb;
    border-top: 2px solid #0284c7;
    border-radius: 50%;
    animation: select2-spin 1s linear infinite;
    display: none;
}

.select2-container--default.select2-container--loading .select2-selection--single .select2-selection__rendered::after {
    display: block;
}

@keyframes select2-spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .select2-container--default .select2-selection--single {
        height: 40px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 10px;
        padding-right: 18px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
        right: 6px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .select2-container--default .select2-selection--single {
        background-color: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f9fafb;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }
    
    .select2-dropdown {
        background-color: #1f2937;
        border-color: #374151;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .select2-container--default .select2-results__option {
        color: #f9fafb;
    }
    
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #374151;
        color: #f9fafb;
    }
}
</style>
`;

// Inject custom CSS
document.head.insertAdjacentHTML('beforeend', select2CustomCSS);
