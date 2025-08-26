<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Database connection error: ' . $e->getMessage() . '</div>';
    exit;
}
?>

<!-- Add SweetAlert2 library in the head section -->
<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Include Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Add custom styles for Select2 in modal -->
<style>
.select2-container {
    width: 100% !important;
}
.select2-dropdown {
    z-index: 9999;
}
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #0d6efd;
}
.select2-search__field {
    padding: 8px !important;
    border-radius: 4px !important;
}
</style>

<div class="card">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show m-3">
            <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Income Management</h5>
        <div class="btn-group">
            <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="fas fa-plus"></i> Add Income
            </button>
            <button class="btn btn-light btn-sm" id="todayBtn">Today</button>
            <button class="btn btn-light btn-sm" id="weekBtn">This Week</button>
            <button class="btn btn-light btn-sm" id="monthBtn">This Month</button>
            <button class="btn btn-light btn-sm" id="customBtn">Custom Range</button>
        </div>
    </div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group date-range" style="display: none;">
                    <input type="date" class="form-control" id="startDate">
                    <input type="date" class="form-control" id="endDate">
                    <button class="btn btn-primary" id="filterBtn">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Total Income</h6>
                        <h3 id="totalIncome">PKR 0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Total Orders</h6>
                        <h3 id="totalOrders">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Average Order Value</h6>
                        <h3 id="avgOrderValue">PKR 0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6>Pending Payments</h6>
                        <h3 id="pendingPayments">PKR 0</h3>
                    </div>
                </div>
            </div>
        </div>

        <div id="incomeList" class="table-responsive">
            <!-- Income data will be loaded here -->
        </div>
    </div>
</div>

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Income</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addIncomeForm">
                    <div class="mb-3">
                        <label class="form-label">Order ID</label>
                        <select class="form-control" id="orderSelect" name="order_id" required>
                            <option value="">Search for an order...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (PKR)</label>
                        <input type="number" class="form-control" id="amount" name="amount" required min="0" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addIncomeBtn">Add Income</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Remove hash from URL if present
    if (window.location.hash) {
        history.replaceState('', document.title, window.location.pathname + window.location.search);
    }

    // Initialize date range with proper error handling
    try {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        $('#startDate').val(firstDay.toISOString().split('T')[0]);
        $('#endDate').val(today.toISOString().split('T')[0]);
    } catch (e) {
        console.error('Error initializing dates:', e);
    }

    // Load initial income data
    loadIncomeData();

    // Prevent default hash behavior on button clicks
    $('.btn-group .btn').click(function(e) {
        e.preventDefault();
    });

    // Add validation for advance payment
    $('#advancePayment').on('input', function() {
        const totalAmount = parseFloat($('#totalAmount').val()) || 0;
        let advanceAmount = parseFloat($(this).val()) || 0;
        
        if (advanceAmount > totalAmount) {
            showToast('Advance payment cannot exceed total order amount', 'error');
            $(this).val(totalAmount);
            advanceAmount = totalAmount;
        }
        
        // Update remaining amount
        const remainingAmount = totalAmount - advanceAmount;
        $('#remainingAmount').val(remainingAmount.toFixed(2));
    });

    // Add validation for income amount
    $('#incomeAmount').on('input', function() {
        const amount = parseFloat($(this).val()) || 0;
        const selectedOrder = $('#orderIdInput option:selected');
        const totalAmount = parseFloat(selectedOrder.data('total')) || 0;
        const remainingAmount = parseFloat(selectedOrder.data('remaining')) || 0;
        
        if (amount > remainingAmount) {
            showToast(`Amount cannot exceed remaining amount (PKR ${remainingAmount.toLocaleString()})`, 'error');
            $(this).val(remainingAmount);
        }
        
        if (amount > totalAmount) {
            showToast(`Amount cannot exceed total order amount (PKR ${totalAmount.toLocaleString()})`, 'error');
            $(this).val(totalAmount);
        }
    });

    // Handle form submission with enhanced validation
    $('#addIncomeForm').submit(function(e) {
        e.preventDefault();
        
        try {
            // Validate amount
            const amount = parseFloat($('#amount').val());
            const selectedOrder = $('#orderSelect option:selected');
            const totalAmount = parseFloat(selectedOrder.data('total'));
            const remainingAmount = parseFloat(selectedOrder.data('remaining'));
            
            if (!amount || amount <= 0 || isNaN(amount)) {
                showToast('Please enter a valid amount', 'error');
                return false;
            }

            if (amount > remainingAmount) {
                showToast(`Amount cannot exceed remaining amount (PKR ${remainingAmount.toLocaleString()})`, 'error');
                return false;
            }

            if (amount > totalAmount) {
                showToast(`Amount cannot exceed total order amount (PKR ${totalAmount.toLocaleString()})`, 'error');
                return false;
            }
            
            const formData = $(this).serialize();
            
            $.ajax({
                url: '../api/add_income.php',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showToast('Income added successfully');
                        $('#addIncomeForm')[0].reset();
                        $('#addIncomeModal').modal('hide');
                        updateDashboardAndIncome();
                        refreshOrderSelect();
                        
                        // Clear the form fields
                        $('#orderSelect').val(null).trigger('change');
                        $('#amount').val('');
                        $('#description').val('');
                    } else {
                        showToast(response.message || 'Error adding income', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showToast('Error adding income: ' + (error || 'Unknown error'), 'error');
                }
            });
        } catch (e) {
            console.error('Form submission error:', e);
            showToast('Error processing form: ' + e.message, 'error');
        }
    });

    // Date filter handling
    $('#filterBtn').click(function() {
        $('.date-range').show();
        loadIncomeData();
    });

    // Custom date range button
    $('#customBtn').click(function() {
        $('.date-range').show();
        $(this).addClass('active').siblings().removeClass('active');
    });

    // Quick date filters with proper week calculation
    $('#todayBtn').click(function() {
        $('.date-range').hide();
        $(this).addClass('active').siblings().removeClass('active');
        const today = new Date();
        const dateStr = today.toISOString().split('T')[0];
        $('#startDate').val(dateStr);
        $('#endDate').val(dateStr);
        loadIncomeData();
    });

    $('#weekBtn').click(function() {
        $('.date-range').hide();
        $(this).addClass('active').siblings().removeClass('active');
        const today = new Date();
        const weekStart = new Date(today);
        weekStart.setDate(today.getDate() - today.getDay());
        const weekEnd = new Date(today);
        $('#startDate').val(weekStart.toISOString().split('T')[0]);
        $('#endDate').val(weekEnd.toISOString().split('T')[0]);
        loadIncomeData();
    });

    $('#monthBtn').click(function() {
        $('.date-range').hide();
        $(this).addClass('active').siblings().removeClass('active');
        const today = new Date();
        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
        $('#startDate').val(monthStart.toISOString().split('T')[0]);
        $('#endDate').val(today.toISOString().split('T')[0]);
        loadIncomeData();
    });

    // Function to update both dashboard and income data
    function updateDashboardAndIncome() {
        // Show loading state
        $('#incomeList').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading income data...</div>');
        
        // Update dashboard with pending amount
        $.ajax({
            url: '../api/dashboard.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#todayOrders').text(response.todays_orders);
                    $('#todaySales').text('PKR ' + response.todays_sales.toLocaleString());
                    $('#todayExpenses').text('PKR ' + response.todays_expenses.toLocaleString());
                }
            }
        });

        // Get pending payments total
        $.ajax({
            url: '../api/get_pending_total.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Update both dashboard and income page pending amounts
                    const pendingAmount = parseFloat(response.pending_total) || 0;
                    $('#pendingAmount, #pendingPayments').text('PKR ' + pendingAmount.toLocaleString());
                }
            }
        });

        loadIncomeData();
    }

    function loadIncomeData() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        // Show loading indicator
        $('#incomeList').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading income data...</div>');
        
        $.ajax({
            url: '../api/income.php',
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    // Update summary cards
                    $('#totalIncome').text('PKR ' + (response.summary.total_income || 0).toLocaleString());
                    $('#totalOrders').text(response.summary.total_orders || 0);
                    $('#avgOrderValue').text('PKR ' + (response.summary.average_order || 0).toLocaleString());

                    // Update income list
                    let html = `
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total Amount</th>
                                    <th>Received Amount</th>
                                    <th>Remaining</th>
                                    <th>Payment Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>`;
                    
                    if (response.income && response.income.length > 0) {
                        response.income.forEach(function(item) {
                            const remainingAmount = parseFloat(item.total_amount) - parseFloat(item.received_amount);
                            html += `
                                <tr class="income-row">
                                    <td>${item.payment_date}</td>
                                    <td>#${item.order_id}</td>
                                    <td>${item.customer_name}</td>
                                    <td>PKR ${parseFloat(item.total_amount).toLocaleString()}</td>
                                    <td>PKR ${parseFloat(item.received_amount).toLocaleString()}</td>
                                    <td>PKR ${remainingAmount.toLocaleString()}</td>
                                    <td>${item.payment_type}</td>
                                    <td><span class="badge bg-${getStatusBadgeColor(item.status)}">${item.status}</span></td>
                                </tr>`;
                        });
                    } else {
                        html += '<tr><td colspan="8" class="text-center">No income records found</td></tr>';
                    }
                    
                    html += '</tbody></table>';
                    $('#incomeList').html(html);
                }
            },
            error: function(xhr, status, error) {
                showToast('Error loading income data: ' + (error || 'Unknown error'), 'error');
                console.error('Loading income failed:', error);
            }
        });
    }

    function getStatusBadgeColor(status) {
        switch(status) {
            case 'Fulfilled': return 'success';
            case 'Partially_Paid': return 'warning';
            case 'Pending': return 'danger';
            default: return 'secondary';
        }
    }

    function showToast(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            // Fallback to alert if SweetAlert is not available
            alert(message);
        }
    }

    // Function to refresh order select dropdown
    function refreshOrderSelect() {
        $.ajax({
            url: '../api/get_pending_orders.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const orderSelect = $('#orderSelect');
                    orderSelect.empty();
                    orderSelect.append('<option value="">Select Order</option>');
                    
                    response.orders.forEach(function(order) {
                        if (order.actual_remaining > 0) {
                            orderSelect.append(`
                                <option value="${order.order_id}" 
                                    data-total="${order.total_amount}"
                                    data-remaining="${order.actual_remaining}"
                                    data-customer="${order.customer_name}">
                                    Order #${order.order_id} - ${order.customer_name}
                                    (Remaining: PKR ${parseFloat(order.actual_remaining).toLocaleString()})
                                </option>
                            `);
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                showToast('Error refreshing orders: ' + (error || 'Unknown error'), 'error');
            }
        });
    }

    // Add some CSS for smooth animations
    $('<style>')
        .text(`
            .income-row {
                transition: all 0.3s ease;
            }
            .income-row:hover {
                background-color: #f8f9fa;
            }
            .fade-transition {
                transition: opacity 0.3s ease;
            }
        `)
        .appendTo('head');

    // Initialize Select2 when modal is shown
    $('#addIncomeModal').on('shown.bs.modal', function () {
        if (!$('#orderSelect').data('select2')) {
            $('#orderSelect').select2({
                dropdownParent: $('#addIncomeModal'),
                placeholder: 'Search for an order by ID, customer name, or phone...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '../api/get_pending_orders.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        if (!data.success) {
                            return {
                                results: []
                            };
                        }
                        return {
                            results: data.orders.map(function(order) {
                                return {
                                    id: order.order_id,
                                    text: `Order #${order.order_id} - ${order.customer_name} (Remaining: PKR ${order.remaining_amount})`,
                                    order: order
                                };
                            })
                        };
                    },
                    cache: false
                },
                templateResult: function(data) {
                    if (!data.order) return data.text;
                    
                    return $(`
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Order #${data.order.order_id}</strong> - ${data.order.customer_name}
                            </div>
                            <div class="text-end">
                                <div class="text-success">Remaining: PKR ${data.order.remaining_amount}</div>
                                <small class="text-muted">Total: PKR ${data.order.total_amount}</small>
                            </div>
                        </div>
                    `);
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;
                if (data.order) {
                    // Pre-fill the amount with the remaining amount
                    $('#amount').val(parseFloat(data.order.remaining_amount.replace(/,/g, '')));
                }
            });
        }
    });

    // Clear Select2 when modal is hidden
    $('#addIncomeModal').on('hidden.bs.modal', function () {
        $('#orderSelect').val(null).trigger('change');
        $('#addIncomeForm')[0].reset();
    });

    // Handle Add Income form submission
    $('#addIncomeBtn').click(function() {
        const orderId = $('#orderSelect').val();
        const amount = $('#amount').val();
        const description = $('#description').val();

        if (!orderId || !amount) {
            showToast('Please fill in all required fields', 'error');
            return;
        }

        $.ajax({
            url: '../api/add_income.php',
            method: 'POST',
            data: {
                order_id: orderId,
                amount: amount,
                description: description
            },
            success: function(response) {
                if (response.success) {
                    $('#addIncomeModal').modal('hide');
                    showToast('Income added successfully', 'success');
                    
                    // Refresh all tables and data
                    updateAllTables();
                    
                    // Clear form
                    $('#addIncomeForm')[0].reset();
                    $('#orderSelect').val(null).trigger('change');
                } else {
                    showToast(response.message || 'Failed to add income', 'error');
                }
            },
            error: function(xhr, status, error) {
                showToast('Error adding income: ' + (error || 'Unknown error'), 'error');
            }
        });
    });

    // Function to update all tables and data
    function updateAllTables() {
        // Update income table
        loadIncomeData();
        
        // Update dashboard data
        updateDashboardAndIncome();
        
        // If we're on the customer history page and there's a search term
        const customerSearch = $('#customerSearch');
        if (customerSearch.length > 0) {
            const searchTerm = customerSearch.val();
            if (searchTerm) {
                // Refresh customer history
                loadCustomerHistory(searchTerm);
            }
        }

        // Refresh pending payments display
        $.ajax({
            url: '../api/get_pending_total.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#pendingPayments').text('PKR ' + response.pending_total.toLocaleString());
                }
            }
        });

        // Update dashboard cards
        $.ajax({
            url: '../api/dashboard.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#todayOrders').text(response.todays_orders);
                    $('#todaySales').text('PKR ' + response.todays_sales.toLocaleString());
                    $('#todayExpenses').text('PKR ' + response.todays_expenses.toLocaleString());
                    // Update any other dashboard elements
                    if ($('#pendingAmount').length) {
                        $('#pendingAmount').text('PKR ' + response.total_pending.toLocaleString());
                    }
                }
            }
        });
    }
});
</script> 