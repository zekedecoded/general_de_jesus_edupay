<?php
require_once __DIR__ . '/../connection/config.php';
$totalTransactions = 284;
$todaysVolume = 12450;
$pendingTransactions = 9;
$completedToday = 42;

$transactions = [
    ["ref"=>"TXN-001","type"=>"Payment","amount"=>120,"sender"=>"Juan Dela Cruz","receiver"=>"Campus Canteen","status"=>"Completed","time"=>"10:20 AM"],
    ["ref"=>"TXN-002","type"=>"Top-up","amount"=>500,"sender"=>"Maria Santos","receiver"=>"Cashier","status"=>"Pending","time"=>"09:40 AM"],
    ["ref"=>"TXN-003","type"=>"Encashment","amount"=>2000,"sender"=>"Campus Store","receiver"=>"Finance Office","status"=>"Processing","time"=>"09:00 AM"],
    ["ref"=>"TXN-004","type"=>"Payment","amount"=>80,"sender"=>"Carlo Reyes","receiver"=>"Library","status"=>"Completed","time"=>"Yesterday"],
    ["ref"=>"TXN-005","type"=>"Refund","amount"=>150,"sender"=>"Finance Office","receiver"=>"Anna Morales","status"=>"Rejected","time"=>"Yesterday"]
];
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
                <button class="menu-btn" onclick="toggleSidebar()">☰</button>

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
                    <h2><?php echo $totalTransactions; ?></h2>
                    <p>All wallet activities</p>
                </div>

                <div class="transaction-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/volume.png" alt="">
                    </div>
                    <span>Today's Volume</span>
                    <h2>₱<?php echo number_format($todaysVolume, 2); ?></h2>
                    <p>Transactions today</p>
                </div>

                <div class="transaction-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/pending-topups.png" alt="">
                    </div>
                    <span>Pending Transactions</span>
                    <h2><?php echo $pendingTransactions; ?></h2>
                    <p>Needs review</p>
                </div>

                <div class="transaction-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/check.png" alt="">
                    </div>
                    <span>Completed Today</span>
                    <h2><?php echo $completedToday; ?></h2>
                    <p>Successful activities</p>
                </div>

            </section>

            <section class="transactions-command-panel mb-4">

                <div class="transactions-panel-header">
                    <div>
                        <h3>Transaction Filters</h3>
                        <p>Search and filter wallet activity records.</p>
                    </div>

                    <a href="<?= ADMIN_URL ?>/export_transactions.php" class="export-btn">
                        Export
                    </a>
                </div>

                <form class="transactions-filter-grid" method="GET" action="<?= ADMIN_URL ?>/transactions.php">

                    <div class="premium-field search-field">
                        <label>Search Transaction</label>
                        <input type="text" name="search" placeholder="Reference, sender, receiver, or amount">
                    </div>

                    <div class="premium-field">
                        <label>Type</label>
                        <select name="type">
                            <option value="">All Types</option>
                            <option value="payment">Payment</option>
                            <option value="topup">Top-up</option>
                            <option value="encashment">Encashment</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>

                    <div class="premium-field">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="rejected">Rejected</option>
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
                    <table class="table transactions-table align-middle">
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
                            <?php foreach($transactions as $t): ?>
                            <tr>
                                <td class="reference-text"><?php echo $t["ref"]; ?></td>

                                <td>
                                    <span class="type-pill <?php echo strtolower($t["type"]); ?>">
                                        <?php echo $t["type"]; ?>
                                    </span>
                                </td>

                                <td class="transaction-amount">₱<?php echo number_format($t["amount"], 2); ?></td>

                                <td>
                                    <div class="party-cell">
                                        <div class="party-avatar"><?php echo strtoupper(substr($t["sender"], 0, 1)); ?>
                                        </div>
                                        <strong><?php echo $t["sender"]; ?></strong>
                                    </div>
                                </td>

                                <td><?php echo $t["receiver"]; ?></td>

                                <td>
                                    <span class="transaction-status <?php echo strtolower($t["status"]); ?>">
                                        <?php echo $t["status"]; ?>
                                    </span>
                                </td>

                                <td><?php echo $t["time"]; ?></td>

                                <td>
                                    <a href="view_transaction.php?ref=<?php echo $t["ref"]; ?>"
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

    <script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("collapsed");
    }
    </script>

</body>

</html>