<div class="card shadow-sm">
    <div class="card-header gradient-4 text-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-book me-2"></i>Khata Management</h5>
        <a href="/khata/add" class="btn btn-light btn-sm nav-link"><i class="fas fa-plus me-2"></i>Add New Entry</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Party</th>
                        <th>Description</th>
                        <th>New Amount</th>
                        <th>Paid</th>
                        <th>Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once '../includes/db.php';
                    $db = getDbConnection();
                    $sql = "SELECT k.*, p.party_name as party_name 
                            FROM khata k 
                            JOIN parties p ON k.party_id = p.id 
                            ORDER BY k.date DESC";
                    $stmt = $db->query($sql);
                    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($entries as $row){
                        echo "<tr>
                                <td>{$row['date']}</td>
                                <td>{$row['party_name']}</td>
                                <td>{$row['description']}</td>
                                <td>PKR " . number_format($row['new_amount']) . "</td>
                                <td>PKR " . number_format($row['paid_amount']) . "</td>
                                <td>PKR " . number_format($row['remaining_amount']) . "</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>