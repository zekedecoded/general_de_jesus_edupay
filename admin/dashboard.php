<?php
require_once __DIR__ . '/../connection/config.php';
require_once __DIR__ . '/../connection/pdo.php';
$totalUsers = 145;
$activeStudents = 120;
$activeMerchants = 8;
$activeVisitors = 17;
$circulatingBalance = 58250;
$todaysVolume = 12450;
$pendingTopups = 6;
$pendingEncashments = 3;
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | GJC EduPay</title>
    <link rel="stylesheet" href="<?= CSS_URL ?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/admin.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/responsive.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <div class="admin-layout">

        <aside class="admin-sidebar" id="sidebar">

            <div class="brand-box">
                <div class="brand-logo">
                    <img src="<?= ICONS_URL ?>/edupay.png" alt="Logo">
                </div>

                <div class="brand-text">
                    <h4>GJC EduPay</h4>
                    <span>Admin Portal</span>
                </div>
            </div>

            <nav class="sidebar-menu">
                <a href="<?= ADMIN_URL ?>/dashboard.php" class="active">
                    <img src="<?= ICONS_URL ?>/dashboard.png" class="nav-icon" alt="">
                    <span class="nav-text">Dashboard</span>
                </a>

                <a href="<?= ADMIN_URL ?>/users.php">
                    <img src="<?= ICONS_URL ?>/users.png" class="nav-icon" alt="">
                    <span class="nav-text">Users</span>
                </a>

                <a href="<?= ADMIN_URL ?>/topups.php">
                    <img src="<?= ICONS_URL ?>/topups.png" class="nav-icon" alt="">
                    <span class="nav-text">Top-ups</span>
                </a>

                <a href="<?= ADMIN_URL ?>/encashments.php">
                    <img src="<?= ICONS_URL ?>/encashments.png" class="nav-icon" alt="">
                    <span class="nav-text">Encashments</span>
                </a>

                <a href="<?= ADMIN_URL ?>/transactions.php">
                    <img src="<?= ICONS_URL ?>/transactions.png" class="nav-icon" alt="">
                    <span class="nav-text">Transactions</span>
                </a>

                <a href="<?= ADMIN_URL ?>/visitors.php">
                    <img src="<?= ICONS_URL ?>/visitors.png" class="nav-icon" alt="">
                    <span class="nav-text">Visitors</span>
                </a>

                <a href="<?= ADMIN_URL ?>/settings.php">
                    <img src="<?= ICONS_URL ?>/settings.png" class="nav-icon" alt="">
                    <span class="nav-text">Settings</span>
                </a>
            </nav>

            <a href="<?= BASE_URL ?>/logout.php" class="logout-btn">
                <img src="<?= ICONS_URL ?>/logout.png" class="logout-icon" alt="">
                <span>Logout</span>
            </a>

        </aside>

        <main class="admin-main">

            <header class="topbar">
                <button class="menu-btn" onclick="toggleSidebar()">☰</button>

                <div>
                    <h1>Admin Dashboard</h1>
                    <p>Monitor wallet activity, top-ups, encashments, and system users.</p>
                </div>

                <div class="admin-user">
                    <span>Admin</span>
                    <div class="avatar">
                        <img src="<?= ICONS_URL ?>/admin.png" alt="Admin">
                    </div>
                </div>
            </header>

            <!-- Financial Overview -->
            <div class="section-title mb-3 mt-2">
                <h4 style="font-size: 18px; font-weight: 800; color: var(--emerald-950); margin: 0;">System Financials</h4>
            </div>
            <section class="row g-4 mb-4">

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <img src="<?= ICONS_URL ?>/wallet.png" alt="">
                        </div>
                        <span>Circulating Balance</span>
                        <h2>₱<?php echo number_format($circulatingBalance, 2); ?></h2>
                        <p>All active wallets</p>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <img src="<?= ICONS_URL ?>/volume.png" alt="">
                        </div>
                        <span>Today's Volume</span>
                        <h2>₱<?php echo number_format($todaysVolume, 2); ?></h2>
                        <p>Transactions</p>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <img src="<?= ICONS_URL ?>/pending-topups.png" alt="">
                        </div>
                        <span>Pending Top-ups</span>
                        <h2><?php echo $pendingTopups; ?></h2>
                        <p>Awaiting cashier</p>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <img src="<?= ICONS_URL ?>/pending-encashments.png" alt="">
                        </div>
                        <span>Pending Encashments</span>
                        <h2><?php echo $pendingEncashments; ?></h2>
                        <p>Awaiting disbursement</p>
                    </div>
                </div>

            </section>

            <!-- User Demographics -->
            <div class="section-title mb-3">
                <h4 style="font-size: 18px; font-weight: 800; color: var(--emerald-950); margin: 0;">User Demographics</h4>
            </div>
            <section class="row g-3 mb-5">

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="mini-metric-card">
                        <div class="mini-icon-wrap">
                            <img src="<?= ICONS_URL ?>/users.png" alt="">
                        </div>
                        <div class="mini-metric-info">
                            <span>Total Users</span>
                            <h3><?php echo $totalUsers; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="mini-metric-card">
                        <div class="mini-icon-wrap">
                            <img src="<?= ICONS_URL ?>/students.png" alt="">
                        </div>
                        <div class="mini-metric-info">
                            <span>Active Students</span>
                            <h3><?php echo $activeStudents; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="mini-metric-card">
                        <div class="mini-icon-wrap">
                            <img src="<?= ICONS_URL ?>/merchants.png" alt="">
                        </div>
                        <div class="mini-metric-info">
                            <span>Active Merchants</span>
                            <h3><?php echo $activeMerchants; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="mini-metric-card">
                        <div class="mini-icon-wrap">
                            <img src="<?= ICONS_URL ?>/visitors.png" alt="">
                        </div>
                        <div class="mini-metric-info">
                            <span>Active Visitors</span>
                            <h3><?php echo $activeVisitors; ?></h3>
                        </div>
                    </div>
                </div>

            </section>

            <section class="row g-4 mb-4">

                <div class="col-12 col-xl-8">
                    <div class="premium-panel">
                        <div class="panel-header">
                            <div>
                                <h3>7-Day Transaction Volume</h3>
                                <p>Daily wallet transaction performance</p>
                            </div>
                        </div>

                        <div class="chart-box">
                            <canvas id="transactionChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="premium-panel h-100">
                        <div class="panel-header">
                            <div>
                                <h3>Quick Actions</h3>
                                <p>Frequently used admin tools</p>
                            </div>
                        </div>

                        <div class="quick-actions">
                            <a href="<?= ADMIN_URL ?>/users.php">
                                <span>
                                    Manage Users
                                </span>
                                <b>›</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/topups.php">
                                <span>
                                    Process Top-ups
                                </span>
                                <b>›</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/encashments.php">
                                <span>
                                    Encashments
                                </span>
                                <b>›</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/visitors.php">
                                <span>
                                    Visitors Management
                                </span>
                                <b>›</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/transactions.php">
                                <span>
                                    All Transactions
                                </span>
                                <b>›</b>
                            </a>
                        </div>
                    </div>
                </div>

            </section>

            <section class="premium-panel">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3>Recent / Latest Transactions</h3>
                        <p>Latest wallet activity across the system</p>
                    </div>

                    <a href="<?= ADMIN_URL ?>/transactions.php" class="view-btn">View All</a>
                </div>

                <div class="table-responsive">
                    <table class="table premium-table align-middle">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Sender</th>
                                <th>Receiver</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>TXN-001</td>
                                <td>
                                    Payment
                                </td>
                                <td>₱120.00</td>
                                <td>Juan Dela Cruz</td>
                                <td>Campus Canteen</td>
                                <td><span class="badge-success">Completed</span></td>
                                <td>10:23 AM</td>
                            </tr>

                            <tr>
                                <td>TXN-002</td>
                                <td>
                                    Top-up
                                </td>
                                <td>₱500.00</td>
                                <td>Maria Santos</td>
                                <td>Cashier</td>
                                <td><span class="badge-warning">Pending</span></td>
                                <td>09:45 AM</td>
                            </tr>

                            <tr>
                                <td>TXN-003</td>
                                <td>
                                    Encashment
                                </td>
                                <td>₱2,000.00</td>
                                <td>Campus Store</td>
                                <td>Finance Office</td>
                                <td><span class="badge-warning">Pending</span></td>
                                <td>08:30 AM</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>


            <?php require_once INCLUDES_PATH . '/circulation_widget.php'; ?>

        </main>

    </div>

    <script src="<?= JS_URL ?>/bootstrap.bundle.min.js"></script>
    <script src="<?= JS_URL ?>/dashboard_chart.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("collapsed");
        }
    </script>

</body>

</html>