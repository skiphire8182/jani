<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Expenses Management</h5>
            <button type="button" class="btn btn-light" id="addNewExpense">
                <i class="fas fa-plus"></i> Add New Expense
            </button>
        </div>
        <div class="card-body">
            <!-- Date Filter -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="date" class="form-control" id="startDate">
                        <input type="date" class="form-control" id="endDate">
                        <button class="btn btn-primary" id="filterBtn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Section -->
            <div id="expensesSummary" class="mb-4">
                <!-- Summary will be loaded here -->
            </div>

            <!-- Expenses List -->
            <div id="expensesList" class="table-responsive">
                <!-- Expenses table will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expenseForm">
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" id="expenseDescription" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (PKR)</label>
                        <input type="number" class="form-control" id="expenseAmount" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="expenseDate" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-control" id="expenseCategory" required>
                            <option value="">Select Category</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Ingredients">Ingredients</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Salary">Salary</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add updateDashboard function
    function updateDashboard() {
        $.ajax({
            url: 'api/dashboard.php',
            method: 'GET',
            success: function(response) {
                console.log('Dashboard Response:', response); // Add debug logging
                if (response.success) {
                    $('#todayOrders').text(response.todays_orders);
                    $('#todaySales').text('PKR ' + response.todays_sales.toLocaleString());
                    $('#pendingAmount').text('PKR ' + response.total_pending.toLocaleString());
                    $('#todayExpenses').text('PKR ' + response.todays_expenses.toLocaleString());
                    console.log('Updated expenses to:', response.todays_expenses); // Add debug logging
                } else {
                    console.error('Dashboard update failed:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Dashboard update failed:', error);
            }
        });
    }

    // Set default date range to current month
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    $('#startDate').val(firstDay.toISOString().split('T')[0]);
    $('#endDate').val(lastDay.toISOString().split('T')[0]);

    function loadExpenses() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        // Show loading state
        $('#expensesList').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading expenses...</div>');
        
        // Clear any previous errors
        $('.alert-danger').remove();
        
        $.ajax({
            url: 'api/expenses.php',
            method: 'GET',
            data: { 
                start_date: startDate, 
                end_date: endDate 
            },
            success: function(response) {
                console.log('Server response:', response); // Debug log
                if (response.success) {
                    let html = `
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>`;
                    
                    if (response.expenses && response.expenses.length > 0) {
                        let total = 0;
                        response.expenses.forEach(expense => {
                            total += parseFloat(expense.amount);
                            html += `
                                <tr>
                                    <td>${expense.expense_date}</td>
                                    <td>${expense.description}</td>
                                    <td>${expense.category}</td>
                                    <td>PKR ${parseFloat(expense.amount).toLocaleString()}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger delete-expense" data-id="${expense.expense_id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>`;
                        });
                        
                        html += `
                                <tr class="table-info">
                                    <td colspan="3"><strong>Total</strong></td>
                                    <td colspan="2"><strong>PKR ${total.toLocaleString()}</strong></td>
                                </tr>`;
                    } else {
                        html += '<tr><td colspan="5" class="text-center">No expenses found for the selected period</td></tr>';
                    }
                    
                    html += '</tbody></table>';
                    $('#expensesList').html(html);

                    // Update summary if available
                    if (response.summary) {
                        const summary = response.summary;
                        let summaryHtml = `
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h6>Total Entries</h6>
                                            <h4>${summary.total_entries}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <h6>Total Expenses</h6>
                                            <h4>PKR ${parseFloat(summary.total_expenses).toLocaleString()}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <h6>Average Daily</h6>
                                            <h4>PKR ${parseFloat(summary.average_daily).toLocaleString()}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <h6>Top Category</h6>
                                            <h4>${summary.top_category}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        $('#expensesSummary').html(summaryHtml);
                    }
                } else {
                    console.error('Server returned error:', response.message);
                    $('#expensesList').html(`
                        <div class="alert alert-danger">
                            <strong>Error:</strong> ${response.message}
                        </div>`);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                let errorMessage = 'Failed to load expenses';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Error parsing error response:', e);
                }
                
                $('#expensesList').html(`
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${errorMessage}
                    </div>`);
            }
        });
    }

    // Load initial expenses
    loadExpenses();

    // Filter button click
    $('#filterBtn').click(function(e) {
        e.preventDefault();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        
        if (startDate > endDate) {
            alert('Start date cannot be after end date');
            return;
        }
        
        loadExpenses();
    });

    // Save expense
    $('#expenseForm').off('submit').on('submit', function(event) {
        event.preventDefault();
        
        // Disable submit button to prevent double submission
        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true);
        
        const expenseData = {
            description: $('#expenseDescription').val(),
            amount: $('#expenseAmount').val(),
            date: $('#expenseDate').val(),
            category: $('#expenseCategory').val()
        };

        $.ajax({
            url: 'api/expenses.php',
            method: 'POST',
            data: JSON.stringify(expenseData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    $('#expenseModal').modal('hide');
                    $('#expenseForm')[0].reset();
                    loadExpenses();
                    updateDashboard();
                    alert('Expense added successfully!');
                } else {
                    alert(response.message || 'Failed to add expense');
                }
            },
            error: function(xhr) {
                alert('Error adding expense. Please try again.');
            },
            complete: function() {
                // Re-enable submit button
                submitButton.prop('disabled', false);
            }
        });
    });

    // Add expense button click
    $('#addNewExpense').off('click').on('click', function() {
        $('#expenseDate').val(new Date().toISOString().split('T')[0]);
        $('#expenseForm')[0].reset();
        $('#expenseModal').modal('show');
    });

    // Delete expense
    $(document).off('click', '.delete-expense').on('click', '.delete-expense', function() {
        if (confirm('Are you sure you want to delete this expense?')) {
            const expenseId = $(this).data('id');
            
            // Disable delete button
            $(this).prop('disabled', true);
            
            $.ajax({
                url: 'api/expenses.php',
                method: 'DELETE',
                data: JSON.stringify({ expense_id: expenseId }),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        loadExpenses();
                        updateDashboard();
                        alert('Expense deleted successfully!');
                    } else {
                        alert(response.message || 'Failed to delete expense');
                    }
                },
                error: function() {
                    alert('Error deleting expense. Please try again.');
                },
                complete: function() {
                    // Re-enable delete button
                    $('.delete-expense').prop('disabled', false);
                }
            });
        }
    });
});
</script> 