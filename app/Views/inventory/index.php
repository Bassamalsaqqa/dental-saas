<?php include APPPATH . 'Views/layouts/partials/view_permissions.php'; ?>
<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params);
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
?>


<?php helper('asset'); ?>
<link rel="stylesheet" href="<?= base_url('assets/css/v4-operations.css') ?>?v=<?= esc(asset_version('assets/css/v4-operations.css')) ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<div class="v4-stock-workspace">
<header class="v4-page-heading"><div><h1><?= esc($tr('inventoryManagement')) ?></h1><p><?= esc(lang('V4.stockHelp')) ?></p></div><div class="v4-page-actions"><a class="v4-button" href="<?= base_url('inventory/reorder-list') ?>"><i class="fas fa-cart-shopping" aria-hidden="true"></i><?= esc($tr('reorderList')) ?> <span><?= (int)($open_reorder_count??0) ?></span></a><?php if($v4Can('inventory','create')): ?><a class="v4-button v4-button--primary" href="<?= base_url('inventory/create') ?>" data-inventory-create-modal-open><i class="fas fa-plus" aria-hidden="true"></i><?= esc($tr('addNewItem')) ?></a><?php endif; ?></div></header>
<nav class="v4-operation-nav" aria-label="<?= esc($tr('inventoryManagement')) ?>"><a href="<?= base_url('inventory') ?>" aria-current="page"><?= esc($tr('inventoryItems')) ?></a><a href="<?= base_url('inventory/movements') ?>"><?= esc($tr('movements')) ?></a><a href="<?= base_url('inventory/usage-history') ?>"><?= esc($tr('usageHistory')) ?></a><a href="<?= base_url('providers') ?>"><?= esc($tr('suppliers')) ?></a><?php if($v4Can('inventory','create')): ?><a href="<?= base_url('inventory/templates') ?>"><?= esc($tr('starterTemplates')) ?></a><?php endif; ?></nav>
<?php if(!empty($error)||session('error')): ?><div class="v4-form-error" role="alert"><?= esc($error??session('error')) ?></div><?php endif; ?>
<div class="v4-stock-grid">
<section class="v4-surface v4-ledger v4-stock-ledger"><header class="v4-ledger-header"><div><h2><?= esc($tr('inventoryItems')) ?></h2><p><b class="total-items-count"><?= (int)($total_items??0) ?></b> <?= esc($tr('items')) ?></p></div><details class="v4-action-menu"><summary class="v4-button v4-button--small"><i class="fas fa-download" aria-hidden="true"></i><?= esc(lang('V4.exportRecords')) ?></summary><div><button type="button" onclick="exportToPDF()">PDF</button><button type="button" onclick="printInventory()"><?= esc($tr('exportToPrint')) ?></button><button type="button" onclick="exportToCSV()">CSV</button></div></details></header>
<div class="v4-ledger-data"><table id="inventoryTable"><thead><tr><th><?= esc($tr('id')) ?></th><th><?= esc($tr('item')) ?></th><th><?= esc($tr('category')) ?></th><th><?= esc($tr('quantity')) ?></th><th><?= esc($tr('unitPrice')) ?></th><th><?= esc($tr('supplier')) ?></th><th><?= esc($tr('expiryDate')) ?></th><th><?= esc($tr('status')) ?></th><th><?= esc($tr('actions')) ?></th></tr></thead><tbody></tbody></table><div id="v4-inventory-cards" class="v4-mobile-records" aria-live="polite"></div></div>
</section>
<aside class="v4-stock-attention"><h2><?= esc(lang('V4.stockAttention')) ?></h2><p><?= esc(lang('V4.stockAttentionHelp')) ?></p><a class="v4-stock-alert" href="<?= base_url('inventory/low-stock') ?>"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i><span><?= esc($tr('lowStock')) ?></span><strong class="low-stock-count"><?= (int)($low_stock_items??0) ?></strong></a><a class="v4-stock-alert" href="<?= base_url('inventory/low-stock') ?>"><i class="fas fa-box" aria-hidden="true"></i><span><?= esc($tr('outOfStock')) ?></span><strong class="out-of-stock-count"><?= (int)($out_of_stock_items??0) ?></strong></a><div class="v4-stock-reference"><span><?= esc($tr('inStock')) ?></span><strong class="in-stock-count"><?= (int)($in_stock_items??0) ?></strong></div><a class="v4-stock-reorder" href="<?= base_url('inventory/reorder-list') ?>"><i class="fas fa-cart-shopping" aria-hidden="true"></i><h3><?= esc($tr('reorderList')) ?></h3><p><?= esc(lang('V4.reviewReorders')) ?></p><span><?= (int)($open_reorder_count??0) ?> <?= esc($tr('items')) ?></span></a></aside>
</div></div>
<script>
function renderV4InventoryCards(api){
    const host=document.getElementById('v4-inventory-cards');if(!host)return;host.replaceChildren();
    api.rows({page:'current'}).every(function(){
        const cells=this.node().cells;if(cells.length<8)return;
        const card=document.createElement('article');card.className='v4-mobile-record';
        const title=document.createElement('h3');const link=cells[0].querySelector('a');if(link){const copy=link.cloneNode(true);copy.className='v4-mobile-stock-title';copy.querySelectorAll('i').forEach(x=>x.remove());title.append(copy);}else title.textContent=cells[0].textContent;
        const category=document.createElement('p');category.className='v4-mobile-record-type';category.textContent=cells[1].textContent;
        const list=document.createElement('dl');list.className='v4-mobile-stock-facts';
        const labels=<?= json_encode(array_map($tr,['quantity','unitPrice','supplier','expiryDate','status']),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;
        labels.forEach((label,i)=>{const pair=document.createElement('div'),dt=document.createElement('dt'),dd=document.createElement('dd');dt.textContent=label;dd.textContent=cells[i+2].textContent;pair.append(dt,dd);list.append(pair);});
        const actions=cells[7].cloneNode(true),tools=document.createElement('div');tools.className='v4-mobile-stock-actions';while(actions.firstChild)tools.append(actions.firstChild);card.append(category,title,list,tools);host.append(card);
    });
    if(!host.children.length){const p=document.createElement('p');p.className='v4-records-empty';p.textContent=<?= json_encode($tr('noDataAvailable'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;host.append(p);}
}

// Global variables
let inventoryTable;

// Inventory Filter Management Class
class InventoryFilterManager {
    constructor() {
        this.searchValue = '';
        this.categoryFilter = '';
        this.statusFilter = '';
        this.allItems = [];
        this.filteredItems = [];
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadAllItems();
    }
    
    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('inventorySearchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchValue = e.target.value.toLowerCase();
                    this.applyFilters();
                }, 300);
            });
        }
        
        // Category filter
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                this.categoryFilter = e.target.value;
                this.applyFilters();
            });
        }
        
        // Status filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.statusFilter = e.target.value;
                this.applyFilters();
            });
        }
    }
    
    loadAllItems() {
        // Get all inventory rows from the table
        const itemRows = document.querySelectorAll('tbody tr');
        this.allItems = Array.from(itemRows).map(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 7) {
                const itemCell = cells[0];
                const categoryCell = cells[1];
                const statusCell = cells[4];
                
                const itemName = itemCell.querySelector('.text-sm.font-medium')?.textContent?.toLowerCase() || '';
                const description = itemCell.querySelector('.text-sm.text-gray-500')?.textContent?.toLowerCase() || '';
                
                const category = categoryCell.querySelector('.bg-slate-100')?.textContent?.toLowerCase() || '';
                
                // Determine status from CSS classes
                let status = 'in_stock';
                if (statusCell.querySelector('.bg-red-100')) {
                    status = 'out_of_stock';
                } else if (statusCell.querySelector('.bg-yellow-100')) {
                    status = 'low_stock';
                }
                
                return {
                    element: row,
                    name: itemName,
                    description: description,
                    category: category,
                    status: status,
                    searchText: `${itemName} ${description}`.toLowerCase()
                };
            }
            return null;
        }).filter(item => item !== null);
        
        this.filteredItems = [...this.allItems];
    }
    
    applyFilters() {
        this.filteredItems = this.allItems.filter(item => {
            // Search filter
            if (this.searchValue && !item.searchText.includes(this.searchValue)) {
                return false;
            }
            
            // Category filter
            if (this.categoryFilter && item.category !== this.categoryFilter) {
                return false;
            }
            
            // Status filter
            if (this.statusFilter && item.status !== this.statusFilter) {
                return false;
            }
            
            return true;
        });
        
        this.updateTableDisplay();
    }
    
    updateTableDisplay() {
        // Hide all rows first
        this.allItems.forEach(item => {
            item.element.style.display = 'none';
        });
        
        // Show filtered rows
        this.filteredItems.forEach(item => {
            item.element.style.display = '';
        });
        
        // Update counter
        const filteredCountElement = document.getElementById('filteredCount');
        const totalCountElement = document.getElementById('totalCount');
        if (filteredCountElement) {
            filteredCountElement.textContent = this.filteredItems.length;
        }
        if (totalCountElement) {
            totalCountElement.textContent = this.allItems.length;
        }
        
        // Show/hide "No items found" message
        const tbody = document.querySelector('tbody');
        const noItemsRow = tbody.querySelector('tr[data-no-items]');
        
        if (this.filteredItems.length === 0) {
            if (!noItemsRow) {
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-no-items', 'true');
                
                const cell = document.createElement('td');
                cell.colSpan = 7;
                cell.className = 'px-6 py-12 text-center';
                
                const container = document.createElement('div');
                container.className = 'flex flex-col items-center';
                
                const iconDiv = document.createElement('div');
                iconDiv.className = 'w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4';
                const icon = document.createElement('i');
                icon.className = 'fas fa-search text-gray-400 text-2xl';
                iconDiv.appendChild(icon);
                
                const title = document.createElement('h3');
                title.className = 'text-lg font-medium text-gray-900 mb-2';
            title.textContent = '<?= esc($tr('noInventoryItemsFound'), 'js') ?>';
                
                const message = document.createElement('p');
                message.className = 'text-gray-500 mb-4';
                message.textContent = 'Try adjusting your search criteria.';
                
                container.appendChild(iconDiv);
                container.appendChild(title);
                container.appendChild(message);
                cell.appendChild(container);
                newRow.appendChild(cell);
                
                tbody.appendChild(newRow);
            } else {
                noItemsRow.style.display = '';
            }
        } else {
            if (noItemsRow) {
                noItemsRow.style.display = 'none';
            }
        }
    }
}


function adjustStock(id) {
    const newQuantity = prompt('Enter new quantity:');
    if (newQuantity !== null && !isNaN(newQuantity)) {
        fetch(`/inventory/${id}/adjust`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            },
            body: JSON.stringify({ 
                quantity: parseInt(newQuantity),
                [window.csrfConfig.name]: window.getCsrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc($tr('error'), 'js') ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the item.');
        });
    }
}

function deleteItem(id) {
    if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
        // Show loading state
        const deleteBtn = event.target.closest('button');
        
        // Cache original children once if not already cached
        if (!deleteBtn.hasOwnProperty('_originalChildren')) {
            deleteBtn._originalChildren = Array.from(deleteBtn.childNodes).map(node => node.cloneNode(true));
        }
        
        const loadingIcon = document.createElement('i');
        loadingIcon.className = 'fas fa-spinner fa-spin';
        deleteBtn.replaceChildren(loadingIcon);
        deleteBtn.disabled = true;
        
        fetch(`<?= base_url('inventory') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                // Show success message
                showNotification('<?= esc($tr('itemDeletedSuccessfully'), 'js') ?>', 'success');
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('<?= esc($tr('error'), 'js') ?>: ' + data.message, 'error');
                // Restore button state
                deleteBtn.replaceChildren(...deleteBtn._originalChildren.map(node => node.cloneNode(true)));
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('<?= esc($tr('errorDeletingItem'), 'js') ?>', 'error');
            // Restore button state
            deleteBtn.replaceChildren(...deleteBtn._originalChildren.map(node => node.cloneNode(true)));
            deleteBtn.disabled = false;
        });
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-teal-600 text-white'
    }`;
    
    const container = document.createElement('div');
    container.className = 'flex items-center gap-2';
    
    const icon = document.createElement('i');
    icon.className = `fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'}`;
    container.appendChild(icon);
    
    const msgSpan = document.createElement('span');
    msgSpan.textContent = message;
    container.appendChild(msgSpan);
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ms-2 text-white hover:text-gray-200';
    closeBtn.onclick = function() { this.closest('.fixed').remove(); };
    
    const closeIcon = document.createElement('i');
    closeIcon.className = 'fas fa-times';
    closeBtn.appendChild(closeIcon);
    container.appendChild(closeBtn);
    
    notification.appendChild(container);
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Load inventory statistics
function loadInventoryStats() {
    $.ajax({
        url: '<?= base_url('inventory/getInventoryStats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Update the stats cards if they exist
            if (data.total_items !== undefined) {
                $('.total-items-count').text(data.total_items);
            }
            if (data.active_items !== undefined) {
                $('.active-items-count').text(data.active_items);
            }
            if (data.low_stock_items !== undefined) {
                $('.low-stock-count').text(data.low_stock_items);
            }
            if (data.out_of_stock_items !== undefined) {
                $('.out-of-stock-count').text(data.out_of_stock_items);
            }
        },
        error: function() {
            console.error('<?= esc($tr('failedToLoadInventoryStatistics'), 'js') ?>');
        }
    });
}

// Initialize DataTable with server-side processing
$(document).ready(function() {
    // Check if DataTables is loaded
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables is not loaded. Please check if the DataTables library is included.');
        return;
    }
    
    // Load statistics
    loadInventoryStats();
    
    // Custom loading indicator management
    function hideLoadingIndicators() {
        $('.dataTables_processing').hide();
        $('.loading-indicator').hide();
        $('.spinner').hide();
        $('.loading-dots').hide();
        $('#inventoryTable').closest('.table-container').find('.loading').hide();
    }
    
    function showLoadingIndicators() {
        $('.dataTables_processing').show();
    }
    
    inventoryTable = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        processingIndicator: true,
        ajax: {
            url: '<?= base_url('inventory/getInventoryData') ?>',
            type: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            data: function(d) {
                d[window.csrfConfig.name] = window.getCsrfToken();
                return d;
            },
            dataSrc: function(json) {
                // Check if new CSRF token is returned
                if (json.csrf_token) {
                    window.refreshCsrfToken(json.csrf_token);
                }
                
                if (json.error) {
                    console.error('Server error:', json.error);
                        alert('<?= esc($tr('error'), 'js') ?>: ' + json.error);
                    return [];
                }
                
                if (!json.data) {
                    console.error('No data property in response');
                    return [];
                }
                
                // Hide loading indicators after data is received
                setTimeout(function() {
                    hideLoadingIndicators();
                }, 100);
                
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax error:', {
                    xhr: xhr,
                    error: error,
                    thrown: thrown,
                    responseText: xhr.responseText,
                    status: xhr.status,
                    statusText: xhr.statusText
                });
                
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
                
            let errorMessage = '<?= esc($tr('errorLoadingData'), 'js') ?>';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || error || thrown;
                }
                
                console.error('DataTables Error: ' + errorMessage);
                
                // Show a user-friendly message in the table
                const tbody = document.querySelector('#inventoryTable tbody');
                tbody.replaceChildren(); // Clear existing content
                
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 9;
                td.className = 'py-8 text-center text-slate-500';
                
                const container = document.createElement('div');
                container.className = 'flex flex-col items-center gap-2';
                
                const icon = document.createElement('i');
                icon.className = 'fas fa-exclamation-triangle text-4xl text-yellow-500';
                
                const title = document.createElement('p');
                title.className = 'text-lg font-medium';
                title.textContent = 'Unable to load inventory data';
                
                const msg = document.createElement('p');
                msg.className = 'text-sm';
                msg.textContent = 'Please check your connection and try again.';
                
                const btn = document.createElement('button');
                btn.className = 'mt-4 rounded-xl bg-teal-700 px-4 py-2 text-sm font-bold text-white hover:bg-teal-800';
                btn.onclick = () => location.reload();
                
                const btnIcon = document.createElement('i');
                btnIcon.className = 'fas fa-refresh me-2';
                btn.appendChild(btnIcon);
                btn.appendChild(document.createTextNode('Retry'));
                
                container.appendChild(icon);
                container.appendChild(title);
                container.appendChild(msg);
                container.appendChild(btn);
                
                td.appendChild(container);
                tr.appendChild(td);
                tbody.appendChild(tr);
            }
        },
        columns: [
            { 
                data: 0, // ID (hidden)
                visible: false
            },
            { 
                data: 1, // Item Name
                render: function(data, type, row) {
                    return data; // Return text for sorting/filtering
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    td.replaceChildren(); // Clear content
                    
                    const itemId = rowData[0];
                    const link = document.createElement('a');
                    link.href = '<?= base_url('inventory') ?>/' + itemId;
                    link.className = 'group block cursor-pointer rounded-xl transition-colors duration-200 hover:bg-teal-50/70';

                    const container = document.createElement('div');
                    container.className = 'flex items-center gap-3';
                    
                    const iconBox = document.createElement('div');
                    iconBox.className = 'v4-stock-icon';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-box text-sm';
                    iconBox.appendChild(icon);
                    
                    const textBox = document.createElement('div');
                    
                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'v4-stock-name';
                    nameDiv.textContent = cellData;
                    
                    const idDiv = document.createElement('div');
                    idDiv.className = 'text-xs font-bold text-slate-500';
                    idDiv.textContent = 'ID: ' + rowData[0];
                    
                    textBox.appendChild(nameDiv);
                    textBox.appendChild(idDiv);
                    
                    container.appendChild(iconBox);
                    container.appendChild(textBox);
                    link.appendChild(container);
                    td.appendChild(link);
                }
            },
            { 
                data: 2, // Category
                render: function(data, type, row) {
                    return data;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    td.replaceChildren();
                    
                    const span = document.createElement('span');
                    span.className = 'v4-muted';
                    span.textContent = cellData;
                    
                    td.appendChild(span);
                }
            },
            { 
                data: 3 // Quantity
            },
            { 
                data: 4 // Unit Price
            },
            { 
                data: 5 // Supplier
            },
            { 
                data: 6, // Expiry Date
                render: function(data, type, row) {
                    return data || 'N/A';
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    td.replaceChildren();
                    
                    if (!cellData || cellData === 'N/A') {
                        const span = document.createElement('span');
                        span.className = 'text-sm font-bold text-slate-400';
                        span.textContent = 'N/A';
                        td.appendChild(span);
                        return;
                    }
                    
                    const container = document.createElement('div');
                    container.className = 'flex items-center gap-2';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-calendar text-orange-600 text-xs';
                    
                    const dateText = document.createElement('span');
                    dateText.className = 'text-sm font-bold text-slate-900';
                    dateText.textContent = cellData;
                    
                    container.appendChild(icon);
                    container.appendChild(dateText);
                    td.appendChild(container);
                }
            },
            { 
                data: 7 // Status
            },
            { 
                data: 8, // Actions
                orderable: false,
                searchable: false
            }
        ],
        order: [[1, 'asc']], // Sort by item name by default
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: "Loading inventory data...",
                emptyTable: "<?= esc($tr('noInventoryItemsFound'), 'js') ?>",
                zeroRecords: "<?= esc($tr('noMatchingInventoryItemsFound'), 'js') ?>",
            lengthMenu: "_MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ inventory items",
                infoEmpty: "<?= esc($tr('noInventoryItemsAvailable'), 'js') ?>",
            infoFiltered: "(filtered from _MAX_ total inventory items)",
            search: "",
                searchPlaceholder: "<?= esc($tr('searchInventoryItems'), 'js') ?>",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"v4-ledger-tools"lf>rt<"v4-ledger-footer"ip>',
        responsive: true,
        initComplete: function(settings, json) {
            // Hide all loading indicators
            hideLoadingIndicators();
        },
        drawCallback: function(settings) {
            renderV4InventoryCards(this.api());
            // Re-initialize any interactive elements after table redraw
            if (typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }
            
            // Ensure loading indicator is hidden after each draw
            hideLoadingIndicators();
        }
    });
    
    // Fallback timeout to ensure loading indicators are hidden
    setTimeout(function() {
        hideLoadingIndicators();
    }, 5000); // Hide after 5 seconds regardless
});

// Load inventory statistics
function loadInventoryStats() {
    $.ajax({
        url: '<?= base_url('inventory/getInventoryStats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Update the stats cards if they exist
            if (data.total_items !== undefined) {
                $('.total-items-count').text(data.total_items);
            }
            if (data.active_items !== undefined) {
                $('.active-items-count').text(data.active_items);
            }
            if (data.low_stock_items !== undefined) {
                $('.low-stock-count').text(data.low_stock_items);
            }
            if (data.out_of_stock_items !== undefined) {
                $('.out-of-stock-count').text(data.out_of_stock_items);
            }
        },
        error: function() {
            console.error('<?= esc($tr('failedToLoadInventoryStatistics'), 'js') ?>');
        }
    });
}

// Export Functions
function exportToPDF() {
    generatePDF();
}

function printInventory() {
    printInventoryTable();
}

function generatePDF() {
    if (!inventoryTable) {
        alert('Table not ready. Please wait for the page to load completely.');
        return;
    }
    
    try {
        // Get all data from the table
        const data = inventoryTable.data().toArray();
        
        // Create PDF content using jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'mm', 'a4');
        
        // Add title
        doc.setFontSize(20);
        doc.text('Inventory Report', 105, 20, { align: 'center' });
        
        // Add generation date
        doc.setFontSize(12);
        doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 20, 35);
        doc.text(`<?= esc($tr('totalItems'), 'js') ?>: ${inventoryTable.page.info().recordsTotal}`, 20, 45);
        
        // Prepare table data
        const headers = ['ID', 'Item Name', 'Category', 'Quantity', 'Unit Price', 'Supplier', 'Expiry Date', 'Status'];
        const tableData = data.map(row => {
            return row.map(cell => {
                if (typeof cell === 'string') {
                    // Remove HTML tags and clean text
                    return cell.replace(/<[^>]*>/g, '').trim();
                }
                return cell || '';
            });
        });
        
        // Add table using autoTable
        doc.autoTable({
            head: [headers],
            body: tableData,
            startY: 55,
            styles: {
                fontSize: 8,
                cellPadding: 3,
            },
            headStyles: {
                fillColor: [245, 245, 245],
                textColor: [0, 0, 0],
                fontStyle: 'bold',
            },
            columnStyles: {
                0: { cellWidth: 15 }, // ID
                1: { cellWidth: 35 }, // Item Name
                2: { cellWidth: 25 }, // Category
                3: { cellWidth: 20 }, // Quantity
                4: { cellWidth: 25 }, // Unit Price
                5: { cellWidth: 30 }, // Supplier
                6: { cellWidth: 25 }, // Expiry Date
                7: { cellWidth: 20 }, // Status
            },
            margin: { left: 20, right: 20 },
        });
        
        // Save the PDF
        const fileName = `inventory_report_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(fileName);
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('<?= esc($tr('errorGeneratingPDF'), 'js') ?>');
    }
}

function printInventoryTable() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    if (!printWindow) {
        alert('Please allow popups for this website');
        return;
    }

    // Get table data
    const table = document.getElementById('inventoryTable');
    const tableClone = table.cloneNode(true);
    
    // Set up the print document
    const doc = printWindow.document;
    doc.open();
    
    // Create HTML structure safely
    doc.write('<!DOCTYPE html><html><head><title>Inventory Report</title></head><body></body></html>');
    doc.close();
    
    const head = doc.head;
    const body = doc.body;
    
    // Add styles
    const style = doc.createElement('style');
    style.textContent = `
        body { font-family: "Inter", "Noto Sans Arabic", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Noto Sans", sans-serif; margin: 20px; }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .no-print { display: none; }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
    `;
    head.appendChild(style);
    
    // Add content
    const h1 = doc.createElement('h1');
    h1.textContent = 'Inventory Report';
    body.appendChild(h1);
    
    const pDate = doc.createElement('p');
    const dateStrong = doc.createElement('strong');
    dateStrong.textContent = 'Generated on: ';
    pDate.appendChild(dateStrong);
    pDate.appendChild(doc.createTextNode(new Date().toLocaleDateString()));
    body.appendChild(pDate);
    
    const pTotal = doc.createElement('p');
    const totalStrong = doc.createElement('strong');
    totalStrong.textContent = '<?= esc($tr('totalItems'), 'js') ?>: ';
    pTotal.appendChild(totalStrong);
    const totalText = inventoryTable ? inventoryTable.page.info().recordsTotal : 'N/A';
    pTotal.appendChild(doc.createTextNode(totalText));
    body.appendChild(pTotal);
    
    // Append cloned table
    body.appendChild(tableClone);
    
    // Wait for content to load (styles), then print
    // setTimeout is robust for ensuring rendering before print dialog
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 500);
}

function exportToCSV() {
    exportInventoryToCSV();
}

function exportInventoryToCSV() {
    if (!inventoryTable) {
        alert('Table not ready. Please wait for the page to load completely.');
        return;
    }
    
    try {
        // Get all data from the table
        const data = inventoryTable.data().toArray();
        
        // Create CSV headers
        const headers = ['ID', 'Item Name', 'Category', 'Quantity', 'Unit Price', 'Supplier', 'Expiry Date', 'Status'];
        
        // Create CSV content
        let csvContent = headers.join(',') + '\n';
        
        data.forEach(row => {
            // Clean the data for CSV (remove HTML tags, quotes, etc.)
            const cleanRow = row.map(cell => {
                if (typeof cell === 'string') {
                    // Remove HTML tags
                    const textContent = cell.replace(/<[^>]*>/g, '');
                    // Escape quotes and wrap in quotes if contains comma
                    return textContent.includes(',') ? `"${textContent.replace(/"/g, '""')}"` : textContent;
                }
                return cell || '';
            });
            csvContent += cleanRow.join(',') + '\n';
        });
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `inventory_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
    } catch (error) {
        console.error('Error exporting CSV:', error);
        alert('<?= esc($tr('errorExportingCSV'), 'js') ?>');
    }
}

// Load statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    // loadInventoryStats is already called in $(document).ready
});
</script>

<?= view('inventory/partials/quick_create_modal', [
    'return_to' => workflow_current_target() ?? 'inventory',
    'is_rtl' => !empty($is_rtl),
    'supplier_directory' => $supplier_directory ?? [],
    'currencies' => $currencies ?? ['ILS'],
]) ?>
<?= $this->endSection() ?>
