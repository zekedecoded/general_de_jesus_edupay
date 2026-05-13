<?php
require_once __DIR__ . '/../connection/config.php';
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../connection/app.php';

gjc_require_role(['admin']);

$dashboard = gjc_admin_dashboard_data($db);
$financials = $dashboard['system_financials'];
$demographics = $dashboard['user_demographics'];
$recentTransactions = $dashboard['recent_transactions'];
$transactionChart = $dashboard['transaction_chart'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | GJC EduPay</title>
    <link rel="stylesheet" href="<?= CSS_URL ?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/admin.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/responsive.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
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

                <a href="<?= ADMIN_URL ?>/economy.php">
                    <img src="<?= ICONS_URL ?>/wallet.png" class="nav-icon" alt="">
                    <span class="nav-text">Economy</span>
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
                <button class="menu-btn" onclick="toggleSidebar()">&#9776;</button>

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
                        <h2><?php echo gjc_money($financials['circulating_balance']); ?></h2>
                        <p>Student, merchant, and active visitor funds</p>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <img src="<?= ICONS_URL ?>/volume.png" alt="">
                        </div>
                        <span>Today's Volume</span>
                        <h2><?php echo gjc_money($financials['todays_volume']); ?></h2>
                        <p>Successful transactions today</p>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <img src="<?= ICONS_URL ?>/pending-topups.png" alt="">
                        </div>
                        <span>Pending Top-ups</span>
                        <h2><?php echo (int) $financials['pending_topups']; ?></h2>
                        <p>Awaiting cashier approval</p>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <img src="<?= ICONS_URL ?>/pending-encashments.png" alt="">
                        </div>
                        <span>Pending Encashments</span>
                        <h2><?php echo (int) $financials['pending_encashments']; ?></h2>
                        <p>Awaiting disbursement</p>
                    </div>
                </div>

            </section>

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
                            <h3><?php echo (int) $demographics['total_users']; ?></h3>
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
                            <h3><?php echo (int) $demographics['active_students']; ?></h3>
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
                            <h3><?php echo (int) $demographics['active_merchants']; ?></h3>
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
                            <h3><?php echo (int) $demographics['active_visitors']; ?></h3>
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
                                <span>Manage Users</span>
                                <b>&rsaquo;</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/topups.php">
                                <span>Process Top-ups</span>
                                <b>&rsaquo;</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/encashments.php">
                                <span>Encashments</span>
                                <b>&rsaquo;</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/visitors.php">
                                <span>Visitors Management</span>
                                <b>&rsaquo;</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/transactions.php">
                                <span>All Transactions</span>
                                <b>&rsaquo;</b>
                            </a>

                            <a href="<?= ADMIN_URL ?>/economy.php">
                                <span>System Economy</span>
                                <b>&rsaquo;</b>
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
                    <table class="table premium-table align-middle js-datatable" id="dashboardTransactionsTable" data-page-length="10">
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
                            <?php if (empty($recentTransactions)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No transaction history is available yet.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($recentTransactions as $transaction): ?>
                            <tr>
                                <td><?php echo gjc_e($transaction['ref']); ?></td>
                                <td><?php echo gjc_e($transaction['type_label']); ?></td>
                                <td><?php echo gjc_money($transaction['amount']); ?></td>
                                <td><?php echo gjc_e($transaction['sender']); ?></td>
                                <td><?php echo gjc_e($transaction['receiver']); ?></td>
                                <td>
                                    <span class="<?php echo gjc_transaction_is_success($transaction['status']) ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo gjc_e($transaction['status_label']); ?>
                                    </span>
                                </td>
                                <td><?php echo gjc_e($transaction['time_label']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>


        </main>

    </div>

    <script>
    window.dashboardTransactionChart = <?php echo json_encode($transactionChart, JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="<?= JS_URL ?>/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= JS_URL ?>/admin_datatables.js"></script>
    <script src="<?= JS_URL ?>/dashboard_chart.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("collapsed");
        }
    </script>

</body>

</html>
