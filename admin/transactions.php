<?php
require_once __DIR__ . '/../connection/config.php';
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../connection/app.php';

gjc_require_role(['admin']);

$filters = [
    'search' => trim((string) ($_GET['search'] ?? '')),
    'type' => trim((string) ($_GET['type'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
];

$transactions = gjc_fetch_admin_transactions($db, $filters, 150);
$stats = gjc_admin_transaction_stats(gjc_fetch_admin_transactions($db, [], 0));
$typeOptions = gjc_transaction_type_options();
$statusOptions = gjc_transaction_status_options();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Transactions | GJC EduPay</title>

    <link rel="stylesheet" href="<?= CSS_URL ?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/admin.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/transactions.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/responsive.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
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
                <a href="<?= ADMIN_URL ?>/dashboard.php">
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

                <a href="<?= ADMIN_URL ?>/transactions.php" class="active">
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

        <main class="admin-main transactions-page">

            <header class="topbar">
                <button class="menu-btn" onclick="toggleSidebar()">&#9776;</button>

                <div>
                    <h1>Transactions</h1>
                    <p>Monitor payments, top-ups, encashments, refunds, and wallet movement.</p>
                </div>

                <div class="admin-user">
                    <span>Admin</span>
                    <div class="avatar">
                        <img src="<?= ICONS_URL ?>/admin.png" alt="Admin">
                    </div>
                </div>
            </header>

            <section class="transaction-stats-grid mb-4">

                <div class="transaction-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/transactions.png" alt="">
                    </div>
                    <span>Total Transactions</span>
                    <h2><?php echo (int) $stats['total_transactions']; ?></h2>
                    <p>All wallet activities</p>
                </div>

                <div class="transaction-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/volume.png" alt="">
                    </div>
                    <span>Today's Volume</span>
                    <h2><?php echo gjc_money($stats['todays_volume']); ?></h2>
                    <p>Successful transactions today</p>
                </div>

                <div class="transaction-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/pending-topups.png" alt="">
                    </div>
                    <span>Pending Transactions</span>
                    <h2><?php echo (int) $stats['pending_transactions']; ?></h2>
                    <p>Needs review</p>
                </div>

                <div class="transaction-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/check.png" alt="">
                    </div>
                    <span>Completed Today</span>
                    <h2><?php echo (int) $stats['completed_today']; ?></h2>
                    <p>Successful activities</p>
                </div>

            </section>

            <section class="transactions-command-panel mb-4">

                <div class="transactions-panel-header">
                    <div>
                        <h3>Transaction Filters</h3>
                        <p>Search and filter wallet activity records.</p>
                    </div>

                    <a href="<?= ADMIN_URL ?>/export_transactions.php?<?= http_build_query($filters); ?>" class="export-btn">
                        Export
                    </a>
                </div>

                <form class="transactions-filter-grid" method="GET" action="<?= ADMIN_URL ?>/transactions.php">

                    <div class="premium-field search-field">
                        <label>Search Transaction</label>
                        <input type="text" name="search" placeholder="Reference, sender, receiver, or amount"
                            value="<?php echo gjc_e($filters['search']); ?>">
                    </div>

                    <div class="premium-field">
                        <label>Type</label>
                        <select name="type">
                            <?php foreach ($typeOptions as $value => $label): ?>
                            <option value="<?php echo gjc_e($value); ?>"
                                <?php echo $filters['type'] === $value ? 'selected' : ''; ?>>
                                <?php echo gjc_e($label); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="premium-field">
                        <label>Status</label>
                        <select name="status">
                            <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?php echo gjc_e($value); ?>"
                                <?php echo $filters['status'] === $value ? 'selected' : ''; ?>>
                                <?php echo gjc_e($label); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="filter-btn">
                        Filter
                    </button>

                </form>

            </section>

            <section class="transactions-table-panel">

                <div class="transactions-table-header">
                    <div>
                        <h3>All Transactions</h3>
                        <p>Complete list of wallet movements across the system.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table transactions-table align-middle js-datatable" id="transactionsTable" data-page-length="10">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Sender</th>
                                <th>Receiver</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No transactions matched the current filters.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td class="reference-text"><?php echo gjc_e($transaction['ref']); ?></td>

                                <td>
                                    <span class="type-pill <?php echo gjc_e($transaction['type_slug']); ?>">
                                        <?php echo gjc_e($transaction['type_label']); ?>
                                    </span>
                                </td>

                                <td class="transaction-amount"><?php echo gjc_money($transaction['amount']); ?></td>

                                <td>
                                    <div class="party-cell">
                                        <div class="party-avatar">
                                            <?php echo gjc_e(strtoupper(substr($transaction['sender'], 0, 1))); ?>
                                        </div>
                                        <strong><?php echo gjc_e($transaction['sender']); ?></strong>
                                    </div>
                                </td>

                                <td><?php echo gjc_e($transaction['receiver']); ?></td>

                                <td>
                                    <span class="transaction-status <?php echo gjc_e($transaction['status_slug']); ?>">
                                        <?php echo gjc_e($transaction['status_label']); ?>
                                    </span>
                                </td>

                                <td><?php echo gjc_e($transaction['time_label']); ?></td>

                                <td>
                                    <a href="<?= ADMIN_URL ?>/view_transaction.php?source=<?php echo gjc_e($transaction['source']); ?>&ref=<?php echo urlencode($transaction['ref']); ?>&id=<?php echo (int) $transaction['id']; ?>"
                                        class="details-btn">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            </section>

        </main>

    </div>

    <script src="<?= JS_URL ?>/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= JS_URL ?>/admin_datatables.js"></script>

    <script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("collapsed");
    }
    </script>

</body>

</html>
