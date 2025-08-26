$(document).ready(function() {
    // --- Helper Functions ---
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        if (typeof text !== 'string') return text;
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function showToast(message, type) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        } else {
            alert(message);
        }
    }

    // --- Data Loading and Dashboard ---
    function updateDashboard() {
        $.ajax({
            url: AppRouter.getBaseUrl() + '/api/dashboard.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#todayOrders').text(escapeHtml(response.todays_orders));
                    $('#todaySales').text('PKR ' + escapeHtml(response.todays_sales.toLocaleString()));
                    $('#pendingAmount').text('PKR ' + escapeHtml(response.total_pending.toLocaleString()));
                    $('#todayExpenses').text('PKR ' + escapeHtml(response.todays_expenses.toLocaleString()));
                }
            },
            error: function() {
                showToast('Could not update dashboard.', 'error');
            }
        });
    }

    // --- New Order Form Logic ---
    function initializeNewOrderForm() {
        const deliveryTimeDropdown = $('#deliveryTime');
        if (deliveryTimeDropdown.children().length === 0) {
            const now = new Date();
            for (let hour = 0; hour < 24; hour++) {
                for (let minute = 0; minute < 60; minute += 30) {
                    const time = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hour, minute);
                    const timeString = time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                    const option = new Option(timeString, time.toTimeString().substring(0, 5));
                    deliveryTimeDropdown.append(option);
                }
            }
        }

        let menuItems = [];
        $.get(AppRouter.getBaseUrl() + '/api/get_menu_items.php', function(response) {
            if (response.success) {
                menuItems = response.items;
            }
        });

        $('#mainContent').off('click', '#addItemBtn').on('click', '#addItemBtn', function() {
            const itemHtml = `
                <div class="row mb-3 item-row">
                    <div class="col-md-4">
                        <select class="form-control item-select" required>
                            <option value="">Select Item</option>
                            <option value="custom">+ Add Custom Item</option>
                            ${menuItems.map(item => `<option value="${item.item_id}" data-price="${item.price}">${item.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-2"><input type="number" class="form-control quantity" required min="1" value="1"></div>
                    <div class="col-md-2"><input type="number" class="form-control unit-price" required min="0" value="0"></div>
                    <div class="col-md-2"><input type="number" class="form-control item-total" readonly value="0"></div>
                    <div class="col-md-2"><button type="button" class="btn btn-danger remove-item"><i class="fas fa-trash"></i></button></div>
                </div>
            `;
            $('#itemsContainer').append(itemHtml);
        });

        $('#mainContent').off('click', '.remove-item').on('click', '.remove-item', function() {
            $(this).closest('.item-row').remove();
            updateTotalAmount();
        });

        $('#mainContent').off('change', '.item-select').on('change', '.item-select', function() {
            const row = $(this).closest('.item-row');
            const selectElement = $(this);
            const unitPriceInput = row.find('.unit-price');
            if ($(this).val() === 'custom') {
                Swal.fire({ title: 'Add Custom Item', input: 'text', inputLabel: 'Enter custom item name', showCancelButton: true })
                .then((result) => {
                    if (result.isConfirmed && result.value) {
                        const newOption = new Option(result.value, 'custom', true, true);
                        $(newOption).attr('data-custom-name', result.value);
                        selectElement.append(newOption);
                        unitPriceInput.val(0).prop('readonly', false).focus();
                        row.attr('data-custom-name', result.value);
                    } else {
                        selectElement.val('');
                    }
                    updateItemTotal(row);
                });
            } else {
                const price = $(this).find(':selected').data('price');
                unitPriceInput.val(price || 0);
                updateItemTotal(row);
            }
        });

        function updateItemTotal(row) {
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            row.find('.item-total').val((quantity * unitPrice).toFixed(2));
            updateTotalAmount();
        }

        $('#mainContent').off('input', '.quantity, .unit-price').on('input', '.quantity, .unit-price', function() {
            updateItemTotal($(this).closest('.item-row'));
        });

        function updateTotalAmount() {
            let total = 0;
            $('#itemsContainer .item-total').each(function() { total += parseFloat($(this).val()) || 0; });
            $('#totalAmount').val(total.toFixed(2));
            updateRemainingAmount();
        }

        function updateRemainingAmount() {
            const total = parseFloat($('#totalAmount').val()) || 0;
            const advance = parseFloat($('#advancePayment').val()) || 0;
            $('#remainingAmount').val((total - advance).toFixed(2));
        }

        $('#mainContent').off('input', '#advancePayment').on('input', '#advancePayment', updateRemainingAmount);

        $('#mainContent').off('submit', '#orderForm').on('submit', '#orderForm', function(e) {
            e.preventDefault();
            const items = [];
            $('.item-row').each(function() {
                const itemSelect = $(this).find('.item-select');
                const quantity = parseFloat($(this).find('.quantity').val()) || 0;
                const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
                if (itemSelect.val() && quantity > 0 && unitPrice >= 0) {
                    items.push({
                        item_id: itemSelect.val(),
                        custom_item_name: itemSelect.val() === 'custom' ? $(this).find('option:selected').text() : null,
                        quantity: quantity,
                        unit_price: unitPrice
                    });
                }
            });

            if (items.length === 0) { showToast('Please add at least one item.', 'error'); return; }

            const orderData = {
                customer_name: $('#customerName').val(),
                phone: $('#phone').val(),
                order_type: $('#orderType').val(),
                delivery_date: $('#deliveryDate').val(),
                delivery_time: $('#deliveryTime').val(),
                address: $('#address').val(),
                total_amount: parseFloat($('#totalAmount').val()),
                advance_payment: parseFloat($('#advancePayment').val()),
                items: items
            };

            $.ajax({
                url: AppRouter.getBaseUrl() + '/api/create_order.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(orderData),
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', 'Order created successfully!', 'success');
                        const orderId = response.order_id;
                        // Load print content into the modal
                        $('#printContent').load(AppRouter.getBaseUrl() + '/forms/print_order.php?order_id=' + orderId, function() {
                            // Show the modal after content is loaded
                            $('#printPreviewModal').modal('show');
                        });
                    } else {
                        showToast(response.message || 'Error creating order', 'error');
                    }
                },
                error: function() { showToast('An unknown error occurred.', 'error'); }
            });
        });
    }

    function loadSupplyPartiesData() {
        console.log('Loading supply parties data...');
        $.ajax({
            url: AppRouter.getBaseUrl() + '/api/get_parties.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Response from get_parties.php:', response);
                if (response.success) {
                    let rows = '';
                    let totalAmount = 0;
                    let totalPaid = 0;
                    console.log('Number of parties:', response.parties.length);
                    response.parties.forEach(party => {
                        const balance = party.total_amount - party.paid_amount;
                        const status = balance <= 0 ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-danger">Unpaid</span>';
                        rows += `<tr>
                            <td>${escapeHtml(party.party_name)}</td>
                            <td>${escapeHtml(party.supply_date)}</td>
                            <td>${escapeHtml(party.total_amount)}</td>
                            <td>${escapeHtml(party.paid_amount)}</td>
                            <td>${escapeHtml(balance.toFixed(2))}</td>
                            <td>${status}</td>
                            <td>
                                <button class="btn btn-sm btn-info add-payment-btn" data-party-id="${party.id}"><i class="fas fa-plus"></i> Add Payment</button>
                                <button class="btn btn-sm btn-primary view-history-btn" data-party-id="${party.id}"><i class="fas fa-history"></i> View History</button>
                            </td>
                        </tr>`;
                        totalAmount += parseFloat(party.total_amount);
                        totalPaid += parseFloat(party.paid_amount);
                    });
                    console.log('Updating table with rows:', rows);
                    $('#parties-list-body').html(rows);
                    $('#totalAmount').text(totalAmount.toFixed(2));
                    $('#totalPaid').text(totalPaid.toFixed(2));
                    $('#totalBalance').text((totalAmount - totalPaid).toFixed(2));
                } else {
                    showToast(response.error || 'Failed to load parties.', 'error');
                }
            },
            error: function() {
                showToast('An error occurred while fetching parties.', 'error');
            }
        });
    }

    // --- Router ---
    const AppRouter = {
        // Function to get the base URL of the application
        getBaseUrl: function() {
            const path = window.location.pathname;
            // Assuming index.php is at the root or directly in a subdirectory
            // e.g., / or /subdirectory/
            const parts = path.split('/');
            if (parts.length > 2 && parts[parts.length - 1] === 'index.php') {
                // If path is like /subdirectory/index.php, base is /subdirectory
                return parts.slice(0, -1).join('/');
            } else if (parts.length > 1 && parts[parts.length - 1] === '') {
                // If path is like /subdirectory/, base is /subdirectory
                return parts.slice(0, -1).join('/');
            } else if (parts.length === 2 && parts[1] === '') {
                // If path is just /, base is empty string
                return '';
            }
            // Default to empty string if at root or other complex cases
            return '';
        },

        routes: {
            '/': 'loadDashboard',
            '/new-order': 'loadNewOrderForm',
            '/customer-history': 'loadCustomerHistory',
            '/khata': 'loadNewKhata',
            '/khata/add': 'loadNewKhataAdd',
            '/expenses': 'loadExpenses',
            '/income': 'loadIncome',
            '/supply-parties': 'loadSupplyParties',
            '/manage-users': 'loadManageUsers',
            '/logout': 'logoutUser'
        },

        navigate: function(path, pushState = true) {
            const baseUrl = this.getBaseUrl();
            const fullPath = baseUrl + path;

            if (pushState) {
                history.pushState({path: fullPath}, '', fullPath);
            }
            const handlerName = this.routes[path] || 'loadDashboard';
            if (typeof this.handlers[handlerName] === 'function') {
                this.handlers[handlerName]();
                this.updateNav(path);
            }
        },

        updateNav: function(path) {
            $('.nav-link').removeClass('active');
            // Use the original path for matching hrefs, as they are defined in index.php
            $(`a.nav-link[href='${path}']`).addClass('active');
        },

        handlers: {
            loadDashboard: function() {
                $('#mainContent').empty();
                $('.dashboard-cards').show();
                updateDashboard();
            },
            loadNewOrderForm: function() {
                $('.dashboard-cards').hide();
                $('#mainContent').load(AppRouter.getBaseUrl() + '/forms/new_order.php', initializeNewOrderForm);
            },
            loadCustomerHistory: function() {
                $('.dashboard-cards').hide();
                $('#mainContent').load(AppRouter.getBaseUrl() + '/forms/customer_history.php');
            },
            loadNewKhata: function() {
                $('.dashboard-cards').hide();
                $('#mainContent').load(AppRouter.getBaseUrl() + '/forms/new_khata.php');
            },
            loadNewKhataAdd: function() {
                $('.dashboard-cards').hide();
                $('#mainContent').load(AppRouter.getBaseUrl() + '/forms/new_khata_add.php');
            },
            loadExpenses: function() {
                $('.dashboard-cards').hide();
                $('#mainContent').load(AppRouter.getBaseUrl() + '/forms/expenses.php');
            },
            loadIncome: function() {
                $('.dashboard-cards').hide();
                $('#mainContent').load(AppRouter.getBaseUrl() + '/forms/income.php');
            },
            loadSupplyParties: function() {
                $('.dashboard-cards').hide();
                $('#mainContent').load(AppRouter.getBaseUrl() + '/forms/supply_parties.php', function() {
                    loadSupplyPartiesData();
                });
            },
            loadManageUsers: function() {
                $('.dashboard-cards').hide();
                $('#mainContent').load(AppRouter.getBaseUrl() + '/manage_users.php');
            },
            logoutUser: function() {
                console.log('Logging out...');
                $.ajax({
                    url: AppRouter.getBaseUrl() + '/auth/logout.php',
                    method: 'POST',
                    dataType: 'json'
                }).done(function(response) {
                    console.log('Logout successful:', response);
                    if (response.success) {
                        window.location.href = AppRouter.getBaseUrl() + '/login.html';
                    } else {
                        showToast(response.message || 'Logout failed.', 'error');
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Logout failed:', textStatus, errorThrown);
                    showToast('An error occurred during logout.', 'error');
                });
            }
        }
    };

    // --- Event Listeners ---
    $(document).on('click', 'a.nav-link, a.dropdown-item', function(e) {
        const path = $(this).attr('href');
        if (path && path !== '#') {
            e.preventDefault();
            // Pass the original path to navigate, which will then prepend the base URL
            AppRouter.navigate(path);
        }
    });

    // Handle Add New Party button click
    $(document).on('click', '#addNewPartyBtn', function() {
        var addPartyModal = new bootstrap.Modal(document.getElementById('addPartyModal'));
        addPartyModal.show();
    });

    $('#mainContent').on('submit', '#add-party-form-modal', function(e) {
        e.preventDefault();
        $.ajax({
            url: AppRouter.getBaseUrl() + '/api/add_party.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.party_id) {
                    showToast('Party added successfully!', 'success');
                    var addPartyModal = bootstrap.Modal.getInstance(document.getElementById('addPartyModal'));
                    addPartyModal.hide();
                    // Refresh the supply parties list
                    AppRouter.handlers.loadSupplyParties();
                } else {
                    showToast(response.error || 'Failed to add party.', 'error');
                }
            },
            error: function() {
                showToast('An error occurred.', 'error');
            }
        });
    });

    // Handle "Add Payment" button click
    $('#mainContent').on('click', '.add-payment-btn', function() {
        const partyId = $(this).data('party-id');
        $('#party_id_modal').val(partyId);
        var addPaymentModal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
        addPaymentModal.show();
    });

    // Handle "Add Payment" form submission
    $('#mainContent').on('submit', '#add-payment-form-modal', function(e) {
        e.preventDefault();
        $.ajax({
            url: AppRouter.getBaseUrl() + '/api/add_payment.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.message) {
                    showToast(response.message, 'success');
                    var addPaymentModal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
                    addPaymentModal.hide();
                    // Refresh the supply parties list
                    AppRouter.handlers.loadSupplyParties();
                } else {
                    showToast(response.error || 'Failed to add payment.', 'error');
                }
            },
            error: function() {
                showToast('An error occurred while adding payment.', 'error');
            }
        });
    });

    // Handle "View History" button click
    $('#mainContent').on('click', '.view-history-btn', function() {
        console.log('View History button clicked');
        const partyId = $(this).data('party-id');
        console.log('Party ID:', partyId);
        $.ajax({
            url: AppRouter.getBaseUrl() + '/api/get_payment_history.php',
            type: 'GET',
            data: { party_id: partyId },
            dataType: 'json',
            success: function(response) {
                console.log('Response from get_payment_history.php:', response);
                if (response.success) {
                    let historyHtml = `<div class="modal-header">
                                          <h5 class="modal-title">Payment History for ${escapeHtml(response.party_name)}</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                       </div>
                                       <div class="modal-body">
                                          <table class="table table-striped">
                                              <thead>
                                                  <tr>
                                                      <th>Date</th>
                                                      <th>Amount Paid</th>
                                                      <th>Note</th>
                                                  </tr>
                                              </thead>
                                              <tbody>`;
                    response.payments.forEach(payment => {
                        historyHtml += `<tr>
                                            <td>${escapeHtml(payment.payment_date)}</td>
                                            <td>${escapeHtml(payment.amount_paid)}</td>
                                            <td>${escapeHtml(payment.note)}</td>
                                        </tr>`;
                    });
                    historyHtml += `</tbody></table></div>`;
                    console.log('Updating payment history modal with content:', historyHtml);
                    $('#paymentHistoryModalContent').html(historyHtml);
                    var paymentHistoryModal = new bootstrap.Modal(document.getElementById('paymentHistoryModal'));
                    paymentHistoryModal.show();
                } else {
                    showToast(response.error || 'Failed to load payment history.', 'error');
                }
            },
            error: function() {
                showToast('An error occurred while fetching payment history.', 'error');
            }
        });
    });

    $('#mainContent').on('submit', '#newKhataAddForm', function(e) {
        e.preventDefault();
        $.ajax({
            url: AppRouter.getBaseUrl() + '/api/new_khata_save.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast('Entry added successfully!', 'success');
                    AppRouter.navigate('/khata');
                } else {
                    showToast(response.error || 'Failed to add entry.', 'error');
                }
            },
            error: function() {
                showToast('An error occurred.', 'error');
            }
        });
    });

    $('.navbar-brand').on('click', function(e) {
        e.preventDefault();
        AppRouter.navigate('/');
    });

    $(window).on('popstate', function(e) {
        if (e.originalEvent.state && e.originalEvent.state.path) {
            // When popstate, the path is already the full path, so we need to extract the route path
            const baseUrl = AppRouter.getBaseUrl();
            let routePath = e.originalEvent.state.path;
            if (baseUrl && routePath.startsWith(baseUrl)) {
                routePath = routePath.substring(baseUrl.length);
            }
            AppRouter.navigate(routePath, false);
        }
    });

    // --- Initial Load ---
    // Determine the initial route path based on the current URL
    const initialPathname = window.location.pathname;
    const baseUrl = AppRouter.getBaseUrl();
    let initialRoute = initialPathname;
    if (baseUrl && initialPathname.startsWith(baseUrl)) {
        initialRoute = initialPathname.substring(baseUrl.length);
    }
    AppRouter.navigate(initialRoute || '/', false);
});