<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jani Pakwan Center - Supabase Edition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <style>
        .preview-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: center;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .demo-section {
            background: #f8f9fa;
            padding: 3rem 0;
            border-radius: 20px;
            margin: 2rem 0;
        }
        .tech-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin: 0.25rem;
            font-size: 0.9rem;
        }
        .login-section {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <!-- Preview Banner -->
    <div class="container mt-4">
        <div class="preview-banner">
            <h1><i class="fas fa-rocket me-2"></i>Jani Pakwan Center - Supabase Edition</h1>
            <p class="mb-0">Complete Restaurant Management System with Modern Database Backend</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Technology Stack -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="feature-card text-center">
                    <h3><i class="fas fa-cogs me-2"></i>Technology Stack</h3>
                    <div class="mt-3">
                        <span class="tech-badge"><i class="fab fa-php"></i> PHP 8.0+</span>
                        <span class="tech-badge"><i class="fas fa-database"></i> PostgreSQL (Supabase)</span>
                        <span class="tech-badge"><i class="fab fa-js"></i> JavaScript</span>
                        <span class="tech-badge"><i class="fab fa-bootstrap"></i> Bootstrap 5</span>
                        <span class="tech-badge"><i class="fas fa-shield-alt"></i> Row Level Security</span>
                        <span class="tech-badge"><i class="fas fa-bolt"></i> Real-time Ready</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div class="text-center mb-3">
                        <i class="fas fa-shopping-cart fa-3x text-primary"></i>
                    </div>
                    <h4>Order Management</h4>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Create & track orders</li>
                        <li><i class="fas fa-check text-success me-2"></i>Multiple payment options</li>
                        <li><i class="fas fa-check text-success me-2"></i>Delivery scheduling</li>
                        <li><i class="fas fa-check text-success me-2"></i>Professional receipts</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div class="text-center mb-3">
                        <i class="fas fa-users fa-3x text-info"></i>
                    </div>
                    <h4>Customer Management</h4>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Customer history tracking</li>
                        <li><i class="fas fa-check text-success me-2"></i>Contact management</li>
                        <li><i class="fas fa-check text-success me-2"></i>Payment history</li>
                        <li><i class="fas fa-check text-success me-2"></i>Search & filter</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div class="text-center mb-3">
                        <i class="fas fa-chart-line fa-3x text-success"></i>
                    </div>
                    <h4>Financial Tracking</h4>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Income & expense tracking</li>
                        <li><i class="fas fa-check text-success me-2"></i>Real-time dashboard</li>
                        <li><i class="fas fa-check text-success me-2"></i>Payment processing</li>
                        <li><i class="fas fa-check text-success me-2"></i>Financial reports</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Demo Section -->
        <div class="demo-section">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center mb-4">
                        <h2><i class="fas fa-play-circle me-2"></i>Ready to Explore?</h2>
                        <p class="lead">Your Supabase database is connected and ready to use!</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="login-section">
                            <h4><i class="fas fa-user-shield me-2"></i>Admin Access</h4>
                            <p><strong>Username:</strong> Admin</p>
                            <p><strong>Password:</strong> Admin@123</p>
                            <p class="mb-0"><small>Full access to all features including expense management</small></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="login-section" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <h4><i class="fas fa-user me-2"></i>User Access</h4>
                            <p><strong>Username:</strong> user</p>
                            <p><strong>Password:</strong> user@123</p>
                            <p class="mb-0"><small>Access to orders, customers, and basic features</small></p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="login.html" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard
                    </a>
                    <a href="setup-supabase.md" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-book me-2"></i>Setup Guide
                    </a>
                </div>
            </div>
        </div>

        <!-- Database Features -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="feature-card">
                    <h3><i class="fas fa-database me-2"></i>Supabase Database Features</h3>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-shield-alt text-success me-2"></i><strong>Row Level Security:</strong> Database-level access control</li>
                                <li><i class="fas fa-bolt text-warning me-2"></i><strong>Real-time:</strong> Live updates ready to implement</li>
                                <li><i class="fas fa-cloud text-info me-2"></i><strong>Scalable:</strong> Automatic scaling with demand</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-backup text-primary me-2"></i><strong>Auto Backups:</strong> Automatic data protection</li>
                                <li><i class="fas fa-globe text-success me-2"></i><strong>Global CDN:</strong> Fast worldwide access</li>
                                <li><i class="fas fa-key text-warning me-2"></i><strong>Built-in Auth:</strong> Secure authentication system</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="feature-card">
                    <h3><i class="fas fa-heartbeat me-2"></i>System Status</h3>
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="p-3">
                                <i class="fas fa-database fa-2x text-success mb-2"></i>
                                <h5>Database</h5>
                                <span class="badge bg-success">Connected</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="p-3">
                                <i class="fas fa-table fa-2x text-success mb-2"></i>
                                <h5>Schema</h5>
                                <span class="badge bg-success">Deployed</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="p-3">
                                <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                <h5>Security</h5>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="p-3">
                                <i class="fas fa-users fa-2x text-success mb-2"></i>
                                <h5>Users</h5>
                                <span class="badge bg-success">Ready</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">Copyright © 2025 Made With ♥️ Miraj Sol - Powered by Supabase</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add some interactive effects
            $('.feature-card').hover(
                function() { $(this).addClass('shadow-lg'); },
                function() { $(this).removeClass('shadow-lg'); }
            );
            
            // Animate tech badges
            $('.tech-badge').each(function(index) {
                $(this).delay(index * 100).fadeIn();
            });
        });
    </script>
</body>
</html>