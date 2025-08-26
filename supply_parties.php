<!-- Add Party Modal -->
<div class="modal fade" id="addPartyModal" tabindex="-1" aria-labelledby="addPartyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header gradient-1 text-white">
                <h5 class="modal-title" id="addPartyModalLabel">Add New Supply Party</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-party-form-modal">
                    <div class="mb-3">
                        <label for="party_name_modal" class="form-label">Party Name:</label>
                        <input type="text" class="form-control" id="party_name_modal" name="party_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="supply_date_modal" class="form-label">Date:</label>
                        <input type="date" class="form-control" id="supply_date_modal" name="supply_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="details_modal" class="form-label">Details:</label>
                        <textarea class="form-control" id="details_modal" name="details"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="total_amount_modal" class="form-label">Total Amount:</label>
                        <input type="number" class="form-control" id="total_amount_modal" name="total_amount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="advance_paid_modal" class="form-label">Advance Paid:</label>
                        <input type="number" class="form-control" id="advance_paid_modal" name="advance_paid" step="0.01">
                    </div>
                    <button type="submit" class="btn btn-primary gradient-1">Add Party</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header gradient-2 text-white">
                <h5 class="modal-title" id="addPaymentModalLabel">Add Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-payment-form-modal">
                    <input type="hidden" id="party_id_modal" name="party_id">
                    <div class="mb-3">
                        <label for="amount_paid_now_modal" class="form-label">Amount Paid Now:</label>
                        <input type="number" class="form-control" id="amount_paid_now_modal" name="amount_paid_now" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date_modal" class="form-label">Payment Date:</label>
                        <input type="date" class="form-control" id="payment_date_modal" name="payment_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="note_modal" class="form-label">Note:</label>
                        <textarea class="form-control" id="note_modal" name="note"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary gradient-2">Submit Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="paymentHistoryModalContent"></div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header gradient-4 text-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Supply Parties</h5>
        <button class="btn btn-light btn-sm" id="addNewPartyBtn"><i class="fas fa-plus me-2"></i>Add New Party</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Party Name</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="parties-list-body">
                    <!-- Party data will be loaded here by JavaScript -->
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <td colspan="2" class="text-end"><strong>Total:</strong></td>
                        <td id="totalAmount">0.00</td>
                        <td id="totalPaid">0.00</td>
                        <td id="totalBalance">0.00</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>