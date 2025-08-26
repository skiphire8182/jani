<?php
require_once 'includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jani Pakwan Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="img/logo.png" alt="Jani Pakwan Center" class="me-2" height="40">
                <span>Jani Pakwan Center</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/new-order" id="newOrderBtn">
                            <i class="fas fa-plus-circle"></i> New Order
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/customer-history" id="customerHistoryBtn">
                            <i class="fas fa-history"></i> Customer History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/khata" id="khataBtn">
                            <i class="fas fa-book"></i> Khata
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/expenses" id="expensesBtn">
                            <i class="fas fa-money-bill"></i> Expenses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/income" id="incomeBtn">
                            <i class="fas fa-coins"></i> Income
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/supply-parties" id="supplyPartiesBtn">
                            <i class="fas fa-users"></i> Supply Parties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/manage-users" id="manageUsersBtn">
                            <i class="fas fa-user-cog"></i> Admin
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content">
        <!-- Dashboard Cards -->
        <div class="row mt-4 dashboard-cards">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card gradient-1">
                    <div class="card-body">
                        <h5 class="card-title">Today's Orders</h5>
                        <h2 class="card-text" id="todayOrders">0</h2>
                        <i class="fas fa-shopping-cart card-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card gradient-2">
                    <div class="card-body">
                        <h5 class="card-title">Today's Sales</h5>
                        <h2 class="card-text" id="todaySales">PKR 0</h2>
                        <i class="fas fa-chart-line card-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card gradient-3">
                    <div class="card-body">
                        <h5 class="card-title">Pending Amount</h5>
                        <h2 class="card-text" id="pendingAmount">PKR 0</h2>
                        <i class="fas fa-clock card-icon"></i>
                    </div>
                </div>
            </div>
            <?php if (isAdmin()): ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card gradient-4">
                    <div class="card-body">
                        <h5 class="card-title">Today's Expenses</h5>
                        <h2 class="card-text" id="todayExpenses">PKR 0</h2>
                        <i class="fas fa-money-bill-wave card-icon"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Content Area -->
        <div class="row">
            <div class="col-12">
                <div class="content-area" id="mainContent">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="text-center mb-0">Copyright © 2025 Made With ♥️ Miraj Sol</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html> 