<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">New Order</h5>
                </div>
                <div class="card-body">
                    <form id="orderForm">
                        <!-- Customer Information -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customerName" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Order Type</label>
                                <select class="form-control" id="orderType" required>
                                    <option value="">Select Type</option>
                                    <option value="Local">Local</option>
                                    <option value="Online">Online</option>
                                </select>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Delivery Date</label>
                                <input type="date" class="form-control" id="deliveryDate" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Delivery Time</label>
                                <select class="form-control" id="deliveryTime" required>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Delivery Address</label>
                                <input type="text" class="form-control" id="address">
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Order Items</h6>
                                <button type="button" class="btn btn-primary" id="addItemBtn">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="itemsContainer">
                                    <!-- Items will be added here dynamically -->
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Total Amount (PKR)</label>
                                <input type="number" class="form-control" id="totalAmount" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Advance Payment (PKR)</label>
                                <input type="number" class="form-control" id="advancePayment" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Remaining Amount (PKR)</label>
                                <input type="number" class="form-control" id="remainingAmount" readonly>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Preview Modal -->
<div class="modal fade" id="printPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="printContent">
                <!-- Print content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>