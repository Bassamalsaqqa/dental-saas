(function () {
    'use strict';

    var configNode = document.getElementById('finance-page-config');
    if (!configNode) {
        return;
    }

    var config = {};
    try {
        config = JSON.parse(configNode.textContent || '{}');
    } catch (e) {
        console.error('Finance config parse failed:', e);
        return;
    }

    var financeUrls = config.urls || {};
    var financeI18n = config.i18n || {};
    var currency = config.currency || {};
    var permissions = config.permissions || { finance: {} };
    var transactionsTable = null;

    function labelFromMap(map, key, fallback) {
        if (key && map && map[key]) {
            return map[key];
        }

        return fallback;
    }

    function actionLabel(key, fallback) {
        if (financeI18n.actions && financeI18n.actions[key]) {
            return financeI18n.actions[key];
        }

        return fallback;
    }

    function formatCurrency(amount) {
        var currencySymbol = currency.symbol || '';
        var currencyPosition = currency.position || 'before';
        var formattedAmount = parseFloat(amount || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        return currencyPosition === 'after'
            ? formattedAmount + ' ' + currencySymbol
            : currencySymbol + formattedAmount;
    }

    function formatCurrencyAbbreviated(amount) {
        var numericAmount = typeof amount === 'number' ? amount : parseFloat(amount || 0);
        var currencySymbol = currency.symbol || '';
        var currencyPosition = currency.position || 'before';
        var formattedAmount = '';

        if (numericAmount >= 1000000) {
            formattedAmount = (numericAmount / 1000000).toFixed(1) + 'M';
        } else if (numericAmount >= 1000) {
            formattedAmount = (numericAmount / 1000).toFixed(1) + 'k';
        } else {
            formattedAmount = Math.round(numericAmount).toString();
        }

        return currencyPosition === 'after'
            ? formattedAmount + ' ' + currencySymbol
            : currencySymbol + formattedAmount;
    }

    function applyDataTablesStyling() {
        $('.dataTables_length select').addClass('focus:ring-green-500 focus:border-green-500');
        $('.dataTables_filter input').addClass('focus:ring-green-500 focus:border-green-500');
        $('.dataTables_info').addClass('text-gray-600');
        $('.dataTables_paginate .paginate_button').each(function () {
            if ($(this).hasClass('current')) {
                $(this).addClass('bg-green-600 border-green-600 text-white');
            } else {
                $(this).addClass('hover:bg-green-50 hover:border-green-300 hover:text-green-700');
            }
        });
        $('.dataTables_processing').addClass('border-green-200 text-green-700');
        $('.dataTables_length label, .dataTables_filter label').addClass('text-gray-700 font-semibold');
        $('.dataTables_length i, .dataTables_filter i').addClass('text-green-600');
    }

    function loadFinanceStatistics() {
        $.ajax({
            url: financeUrls.stats,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#totalRevenue').text(data.gross_collections_formatted ? formatCurrencyAbbreviated(data.gross_collections) : formatCurrencyAbbreviated(0));
                $('#providerPaidCash').text(data.provider_paid_cash_total_formatted ? formatCurrencyAbbreviated(data.provider_paid_cash_total) : formatCurrencyAbbreviated(0));
                $('#netClinicCash').text(data.net_clinic_cash_formatted ? formatCurrencyAbbreviated(data.net_clinic_cash) : formatCurrencyAbbreviated(0));
                $('#outstandingInvoices').text(data.outstanding_invoices || 0);
                $('#outstandingInvoicesBalance').text(data.outstanding_invoices_balance_formatted || '');
            },
            error: function () {
                $('#totalRevenue').text(formatCurrencyAbbreviated(0));
                $('#providerPaidCash').text(formatCurrencyAbbreviated(0));
                $('#netClinicCash').text(formatCurrencyAbbreviated(0));
                $('#outstandingInvoices').text('0');
                $('#outstandingInvoicesBalance').text('');
            }
        });
    }

    window.exportToPDF = function () {
        if (transactionsTable) {
            transactionsTable.button('.btn-pdf').trigger();
        }
    };

    window.printTransactions = function () {
        if (transactionsTable) {
            transactionsTable.button('.btn-print').trigger();
        }
    };

    window.exportToCSV = function () {
        if (transactionsTable) {
            transactionsTable.button('.btn-csv').trigger();
        }
    };

    window.deleteTransaction = function (transactionId) {
        confirmDelete(financeUrls.base + '/' + transactionId, financeI18n.confirmDeleteTransaction || 'Are you sure?');
    };

    $(document).ready(function () {
        transactionsTable = $('#transactionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: financeUrls.data,
                type: 'POST',
                data: function (d) {
                    d[window.csrfConfig.name] = window.getCsrfToken();
                    return d;
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
                },
                complete: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                        window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                    }
                },
                error: function (xhr, error, thrown) {
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

                    var errorMessage = 'Error loading data';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            errorMessage = response.error;
                        }
                    } catch (e) {
                        errorMessage = xhr.responseText || 'Unknown error';
                    }

                    var tbody = document.getElementById('transactionsTableBody');
                    if (tbody) {
                        var tr = document.createElement('tr');
                        var td = document.createElement('td');
                        var flexDiv = document.createElement('div');
                        var icon = document.createElement('i');
                        var span = document.createElement('span');

                        td.setAttribute('colspan', '6');
                        td.className = 'px-6 py-4 text-center text-red-500';
                        flexDiv.className = 'flex items-center justify-center space-x-2';
                        icon.className = 'fas fa-exclamation-triangle';
                        span.textContent = errorMessage;

                        flexDiv.appendChild(icon);
                        flexDiv.appendChild(span);
                        td.appendChild(flexDiv);
                        tr.appendChild(td);
                        tbody.replaceChildren(tr);
                    }
                }
            },
            columns: [
                {
                    data: 'transaction_id',
                    name: 'transaction_id',
                    render: function (data, type, row) {
                        var returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                        var identityHtml =
                            '<div class="flex items-center space-x-3">' +
                            '<div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">' +
                            '<i class="fas fa-receipt text-green-600 text-sm"></i>' +
                            '</div>' +
                            '<div>' +
                            '<div class="text-sm font-medium text-gray-900">' + data + '</div>' +
                            '<div class="text-xs text-gray-500">' + (financeI18n.transactionId || 'ID') + '</div>' +
                            '</div>' +
                            '</div>';
                        if (permissions.finance && permissions.finance.view) {
                            return '<a href="' + financeUrls.base + '/' + row.id + '?return_to=' + returnTo + '" class="block rounded-md hover:bg-green-50/60 transition-colors duration-200 cursor-pointer">' + identityHtml + '</a>';
                        }
                        return identityHtml;
                    }
                },
                {
                    data: 'patient_name',
                    name: 'patient_name',
                    render: function (data, type, row) {
                        var isExpense = row.transaction_type === 'expense';
                        var iconWrap = isExpense ? 'bg-rose-100' : 'bg-green-100';
                        var icon = isExpense ? 'fas fa-building' : 'fas fa-user';
                        var iconColor = isExpense ? 'text-rose-600' : 'text-green-600';
                        var subtitle = isExpense
                            ? (row.expense_category ? row.expense_category.replace(/_/g, ' ') : (financeI18n.expensePayee || 'Counterparty'))
                            : (row.patient_id || financeI18n.noId || '-');
                        return '<div class="flex items-center space-x-3">' +
                            '<div class="w-10 h-10 ' + iconWrap + ' rounded-lg flex items-center justify-center">' +
                            '<i class="' + icon + ' ' + iconColor + ' text-sm"></i>' +
                            '</div>' +
                            '<div>' +
                            '<div class="text-sm font-medium text-gray-900">' + data + '</div>' +
                            '<div class="text-xs text-gray-500">' + subtitle + '</div>' +
                            '</div>' +
                            '</div>';
                    }
                },
                {
                    data: 'transaction_type',
                    name: 'transaction_type',
                    render: function (data) {
                        var typeColors = {
                            payment: 'bg-green-100 text-green-800',
                            invoice: 'bg-blue-100 text-blue-800',
                            refund: 'bg-red-100 text-red-800',
                            adjustment: 'bg-yellow-100 text-yellow-800',
                            expense: 'bg-rose-100 text-rose-800'
                        };
                        var typeIcons = {
                            payment: 'fas fa-credit-card',
                            invoice: 'fas fa-file-invoice',
                            refund: 'fas fa-undo',
                            adjustment: 'fas fa-adjust',
                            expense: 'fas fa-receipt'
                        };
                        var colorClass = typeColors[data] || 'bg-gray-100 text-gray-800';
                        var iconClass = typeIcons[data] || 'fas fa-tag';
                        var typeLabel = labelFromMap(
                            financeI18n.transactionTypes,
                            data,
                            data ? data.charAt(0).toUpperCase() + data.slice(1).replace(/_/g, ' ') : '-'
                        );
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + colorClass + '">' +
                            '<i class="' + iconClass + ' mr-1"></i>' +
                            typeLabel +
                            '</span>';
                    }
                },
                {
                    data: 'amount',
                    name: 'amount',
                    render: function (data, type, row) {
                        var isPositive = data >= 0;
                        var colorClass = isPositive ? 'text-green-600' : 'text-red-600';
                        var iconClass = isPositive ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
                        var originalLine = row.has_original_currency_delta
                            ? '<div class="text-xs text-gray-500">' + row.amount_original_formatted + ' @ ' + parseFloat(row.exchange_rate || 1).toFixed(4) + '</div>'
                            : '';
                        return '<div>' +
                            '<div class="flex items-center space-x-2">' +
                            '<i class="' + iconClass + ' ' + colorClass + ' text-xs"></i>' +
                            '<span class="text-sm font-medium ' + colorClass + '">' + (row.amount_formatted || formatCurrency(Math.abs(data))) + '</span>' +
                            '</div>' +
                            originalLine +
                            '</div>';
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    render: function (data) {
                        var statusColors = {
                            paid: 'bg-green-100 text-green-800',
                            pending: 'bg-yellow-100 text-yellow-800',
                            partial: 'bg-blue-100 text-blue-800',
                            overdue: 'bg-red-100 text-red-800',
                            cancelled: 'bg-gray-100 text-gray-800'
                        };
                        var statusIcons = {
                            paid: 'fas fa-check-circle',
                            pending: 'fas fa-clock',
                            partial: 'fas fa-adjust',
                            overdue: 'fas fa-exclamation-triangle',
                            cancelled: 'fas fa-times-circle'
                        };
                        var colorClass = statusColors[data] || 'bg-gray-100 text-gray-800';
                        var iconClass = statusIcons[data] || 'fas fa-question-circle';
                        var statusLabel = labelFromMap(
                            financeI18n.paymentStatuses,
                            data,
                            data ? data.charAt(0).toUpperCase() + data.slice(1).replace(/_/g, ' ') : '-'
                        );
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + colorClass + '">' +
                            '<i class="' + iconClass + ' mr-1"></i>' +
                            statusLabel +
                            '</span>';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function (data, type, row) {
                        return '<div class="flex items-center space-x-2">' +
                            '<i class="fas fa-calendar text-green-600 text-xs"></i>' +
                            '<span class="text-sm text-gray-900">' + (row.created_at_formatted || data) + '</span>' +
                            '</div>';
                    }
                },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        var returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                        var actions = '<div class="flex items-center space-x-2">';

                        if (permissions.finance && permissions.finance.view) {
                            actions += '<a href="' + financeUrls.base + '/' + data + '?return_to=' + returnTo + '" class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200" title="' + actionLabel('viewTransaction', 'View Transaction') + '"><i class="fas fa-eye"></i></a>';
                        }
                        if (permissions.finance && permissions.finance.view && row.transaction_type === 'invoice') {
                            actions += '<a href="' + financeUrls.base + '/' + data + '/invoice?return_to=' + returnTo + '" class="p-2 text-green-600 hover:text-green-700 rounded-md hover:bg-green-50 transition-colors duration-200" title="' + actionLabel('viewInvoice', 'View Invoice') + '"><i class="fas fa-file-invoice"></i></a>';
                        }
                        if (permissions.finance && permissions.finance.edit && row.transaction_type === 'invoice') {
                            actions += '<a href="' + financeUrls.base + '/' + data + '?return_to=' + returnTo + '" class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="' + actionLabel('addPayment', 'Add Payment') + '"><i class="fas fa-money-bill-wave"></i></a>';
                        }
                        if (permissions.finance && permissions.finance.edit) {
                            actions += '<a href="' + financeUrls.base + '/' + data + '/edit?return_to=' + returnTo + '" class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="' + actionLabel('editTransaction', 'Edit Transaction') + '"><i class="fas fa-edit"></i></a>';
                        }
                        if (permissions.finance && permissions.finance.delete) {
                            actions += '<button onclick="deleteTransaction(' + data + ')" class="p-2 text-red-600 hover:text-red-700 rounded-md hover:bg-red-50 transition-colors duration-200" title="' + actionLabel('deleteTransaction', 'Delete Transaction') + '"><i class="fas fa-trash"></i></button>';
                        }
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            dom: 'B<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'desc']],
            buttons: [
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                    className: 'btn-pdf',
                    title: financeI18n.financialTransactionsReport || 'Financial Transactions Report',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-2"></i>Print',
                    className: 'btn-print',
                    title: financeI18n.financialTransactionsReport || 'Financial Transactions Report',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                },
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv mr-2"></i>CSV',
                    className: 'btn-csv',
                    title: financeI18n.financialTransactionsReport || 'Financial Transactions Report',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                }
            ],
            language: $.extend(true, {
                processing: financeI18n.loadingTransactions || 'Loading transactions...',
                infoFiltered: '',
                search: '',
                searchPlaceholder: ''
            }, financeI18n.dataTable || {}),
            drawCallback: function () {
                if (typeof $.fn.tooltip === 'function') {
                    $('[data-toggle="tooltip"]').tooltip();
                }
                applyDataTablesStyling();
            }
        });

        loadFinanceStatistics();
        $('#searchInput').on('keyup', function () {
            transactionsTable.search(this.value).draw();
        });
        $('#statusFilter').on('change', function () {
            transactionsTable.draw();
        });
    });
})();
