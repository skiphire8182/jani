<?php
// Add any required includes
require_once '../includes/db.php';
require_once '../includes/functions.php';
?>

<style>
.order-status .badge {
    margin-right: 8px;
}
.text-muted {
    color: #6c757d !important;
}
</style>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Customer History</h5>
    </div>
    <div class="card-body">
        <!-- Search Form -->
        <form id="searchForm" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="customerSearch" name="search" placeholder="Enter customer name or phone number">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Results Area -->
        <div id="customerSummary" class="mb-4" style="display: none;"></div>
        <div id="searchResults"></div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle form submission
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        const searchTerm = $('#customerSearch').val().trim();
        if (searchTerm) {
            searchCustomers(searchTerm);
        }
    });

    // Function to perform search
    function searchCustomers(searchTerm) {
        // Show loading state
        $('#searchResults').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#customerSummary').hide();

        // Make API call
        $.ajax({
            url: '../api/customer_history.php',
            type: 'GET',
            data: { search: searchTerm },
            success: function(response) {
                if (!response.success) {
                    $('#searchResults').html('<div class="alert alert-danger">Error: ' + (response.message || 'Failed to load results') + '</div>');
                    return;
                }

                if (!response.orders || response.orders.length === 0) {
                    $('#searchResults').html('<div class="alert alert-info">No orders found for this customer</div>');
                    return;
                }

                // Build the results table
                let html = '<div class="table-responsive"><table class="table table-striped">';
                html += `<thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>`;

                response.orders.forEach(function(order) {
                    html += `
                        <tr>
                            <td>${order.order_id}</td>
                            <td>
                                ${order.customer_name}<br>
                                <small class="text-muted">${order.phone}</small>
                            </td>
                            <td>${order.order_date}</td>
                            <td>${order.order_type}</td>
                            <td>PKR ${parseFloat(order.total_amount).toLocaleString()}</td>
                            <td>PKR ${parseFloat(order.paid_amount).toLocaleString()}</td>
                            <td>PKR ${parseFloat(order.remaining_amount).toLocaleString()}</td>
                            <td><span class="badge bg-${getStatusColor(order.status)}">${order.status}</span></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-sm view-order" data-id="${order.order_id}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm add-payment" data-id="${order.order_id}" data-remaining="${order.remaining_amount}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm print-order" data-id="${order.order_id}">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr id="details-${order.order_id}" style="display: none;">
                            <td colspan="9">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Order Items:</h6>
                                        <ul>
                                            ${order.items.map(item => `<li>${item}</li>`).join('')}
                                        </ul>
                                        ${order.payments.length > 0 ? `
                                            <h6>Payment History:</h6>
                                            <ul>
                                                ${order.payments.map(payment => `<li>${payment}</li>`).join('')}
                                            </ul>
                                        ` : ''}
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                });

                // Add summary row if we have summary data
                if (response.summary && response.summary.length > 0) {
                    const summary = response.summary[0];
                    html += `
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="4"><strong>Total Summary:</strong></td>
                                <td><strong>PKR ${parseFloat(summary.total_amount).toLocaleString()}</strong></td>
                                <td><strong>PKR ${parseFloat(summary.total_paid).toLocaleString()}</strong></td>
                                <td><strong>PKR ${parseFloat(summary.total_remaining).toLocaleString()}</strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>`;

                    // Show customer summary card
                    $('#customerSummary').html(`
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">${summary.customer_name}</h5>
                                <p class="card-text">
                                    <strong>Phone:</strong> ${summary.phone}<br>
                                    <strong>Total Orders:</strong> ${summary.total_orders}<br>
                                    <strong>Total Amount:</strong> PKR ${parseFloat(summary.total_amount).toLocaleString()}<br>
                                    <strong>Total Paid:</strong> PKR ${parseFloat(summary.total_paid).toLocaleString()}<br>
                                    <strong>Balance:</strong> PKR ${parseFloat(summary.total_remaining).toLocaleString()}<br>
                                    <strong>Last Order:</strong> ${summary.last_order_date}
                                </p>
                                <div class="order-status mt-3">
                                    <span class="badge bg-success">${summary.fulfilled_orders} Fulfilled</span>
                                    <span class="badge bg-warning">${summary.partial_orders} Partially Paid</span>
                                    <span class="badge bg-danger">${summary.pending_orders} Pending</span>
                                </div>
                            </div>
                        </div>
                    `).show();
                }

                html += '</table></div>';
                $('#searchResults').html(html);

                // Initialize event handlers
                initializeEventHandlers();
            },
            error: function(xhr, status, error) {
                $('#searchResults').html('<div class="alert alert-danger">Error loading results. Please try again.</div>');
                console.error('Search error:', error);
            }
        });
    }

    // Initialize event handlers
    function initializeEventHandlers() {
        // View order details
        $('.view-order').click(function() {
            const orderId = $(this).data('id');
            $(`#details-${orderId}`).toggle();
        });

        // Add payment
        $('.add-payment').click(function() {
            const orderId = $(this).data('id');
            const remainingAmount = $(this).data('remaining');
            $('#addIncomeModal').modal('show');
            $('#orderSelect').val(orderId).trigger('change');
            $('#amount').val(remainingAmount);
        });

        // Print order
        $('.print-order').click(function() {
            const orderId = $(this).data('id');
            window.location.href = `print.php?order_id=${orderId}`;
        });
    }

    // Helper function for status colors
    function getStatusColor(status) {
        switch(status) {
            case 'Fulfilled': return 'success';
            case 'Partially_Paid': return 'warning';
            case 'Pending': return 'danger';
            default: return 'secondary';
        }
    }
});
</script> 