<style>
    #newKhataAddForm select.form-control {
        color: #000;
    }
</style>
<div class="card shadow-sm">
    <div class="card-header gradient-4 text-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Add Khata Entry</h5>
        <a href="/khata" class="btn btn-light btn-sm nav-link">Back to Khata</a>
    </div>
    <div class="card-body">
        <form id="newKhataAddForm">
            <div class="mb-3">
                <label class="form-label">Party</label>
                <select name="party_id" class="form-control" required>
                    <?php
                    require_once '../includes/db.php';
                    $db = getDbConnection();
                    $stmt = $db->query("SELECT * FROM parties");
                    $parties = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($parties as $p){
                        echo "<option value='{$p['id']}'>{$p['party_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">New Amount</label>
                <input type="number" name="new_amount" class="form-control" step="0.01" value="0">
            </div>
            <div class="mb-3">
                <label class="form-label">Paid Amount</label>
                <input type="number" name="paid_amount" class="form-control" step="0.01" value="0">
            </div>
            <button type="submit" class="btn btn-primary gradient-1">Save</button>
        </form>
    </div>
</div>