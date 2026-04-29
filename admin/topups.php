<?php
require_once __DIR__ . '/../connection/config.php';
$pendingRequests = 12;
$loadedToday = 18500;
$requestQueue = 7;

$pendingTopups = [
    ["reference"=>"TOP-001","name"=>"Juan Dela Cruz","school_id"=>"GJC-001","amount"=>500,"method"=>"Cashier","time"=>"10:23 AM"],
    ["reference"=>"TOP-002","name"=>"Maria Santos","school_id"=>"GJC-002","amount"=>1000,"method"=>"Manual","time"=>"09:48 AM"],
    ["reference"=>"TOP-003","name"=>"Carlo Reyes","school_id"=>"GJC-003","amount"=>300,"method"=>"Cashier","time"=>"09:10 AM"]
];

$topupHistory = [
    ["reference"=>"TOP-090","name"=>"Angela Cruz","amount"=>700,"method"=>"Cashier","status"=>"Completed","time"=>"Yesterday"],
    ["reference"=>"TOP-089","name"=>"Campus Store","amount"=>2500,"method"=>"Manual","status"=>"Processing","time"=>"Yesterday"],
    ["reference"=>"TOP-088","name"=>"Miguel Ramos","amount"=>400,"method"=>"Cashier","status"=>"Rejected","time"=>"Apr 23"]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Top-ups | GJC EduPay</title>

    <link rel="stylesheet" href="<?= CSS_URL ?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/admin.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/topups.css">
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

                <a href="<?= ADMIN_URL ?>/topups.php" class="active">
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
                    <h1>Top-ups</h1>
                    <p>Review pending requests, process wallet loads, and monitor recent top-up activity.</p>
                </div>

                <div class="admin-user">
                    <span>Admin</span>
                    <div class="avatar">
                        <img src="<?= ICONS_URL ?>/admin.png" alt="Admin">
                    </div>
                </div>
            </header>

            <section class="topup-stats-grid mb-4">

                <div class="topup-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/pending-topups.png" alt="">
                    </div>
                    <span>Pending Requests</span>
                    <h2><?php echo $pendingRequests; ?></h2>
                    <p>Awaiting cashier approval</p>
                </div>

                <div class="topup-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/wallet.png" alt="">
                    </div>
                    <span>Loaded Today</span>
                    <h2>₱<?php echo number_format($loadedToday, 2); ?></h2>
                    <p>Total wallet load volume</p>
                </div>

                <div class="topup-stat-card">
                    <div class="stat-icon-wrap">
                        <img src="<?= ICONS_URL ?>/topups.png" alt="">
                    </div>
                    <span>Top-up Request Queue</span>
                    <h2><?php echo $requestQueue; ?></h2>
                    <p>Requests waiting in queue</p>
                </div>

            </section>

            <section class="topup-panel mb-4">

                <div class="topup-panel-header">
                    <div>
                        <h3>Pending Requests</h3>
                        <p>Approve, reject, or view details of incoming top-up requests.</p>
                    </div>

                    <a href="<?= ADMIN_URL ?>/create_topup.php" class="create-topup-btn">
                        <span>+</span> Create Top-up
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table topup-table align-middle">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Name</th>
                                <th>School ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pendingTopups as $topup): ?>
                            <tr>
                                <td><?php echo $topup["reference"]; ?></td>
                                <td>
                                    <div class="topup-user-cell">
                                        <div class="topup-avatar">
                                            <?php echo strtoupper(substr($topup["name"], 0, 1)); ?>
                                        </div>
                                        <strong><?php echo $topup["name"]; ?></strong>
                                    </div>
                                </td>
                                <td><?php echo $topup["school_id"]; ?></td>
                                <td class="amount-text">₱<?php echo number_format($topup["amount"], 2); ?></td>
                                <td><span class="method-pill"><?php echo $topup["method"]; ?></span></td>
                                <td><?php echo $topup["time"]; ?></td>
                                <td>
                                    <div class="topup-actions">
                                        <a href="view_topup.php?ref=<?php echo $topup["reference"]; ?>"
                                            class="details-btn">View</a>
                                        <a href="approve_topup.php?ref=<?php echo $topup["reference"]; ?>"
                                            class="approve-btn">Approve</a>
                                        <a href="reject_topup.php?ref=<?php echo $topup["reference"]; ?>"
                                            class="reject-btn">Reject</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            </section>

            <section class="topup-panel">

                <div class="topup-panel-header">
                    <div>
                        <h3>Recent Top-up History</h3>
                        <p>Latest completed, rejected, and processing wallet load records.</p>
                    </div>

                    <a href="<?= ADMIN_URL ?>/topup_history.php" class="history-link">View All</a>
                </div>

                <div class="table-responsive">
                    <table class="table topup-table align-middle">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($topupHistory as $history): ?>
                            <tr>
                                <td><?php echo $history["reference"]; ?></td>
                                <td><?php echo $history["name"]; ?></td>
                                <td class="amount-text">₱<?php echo number_format($history["amount"], 2); ?></td>
                                <td><span class="method-pill"><?php echo $history["method"]; ?></span></td>
                                <td>
                                    <span class="topup-status <?php echo strtolower($history["status"]); ?>">
                                        <?php echo $history["status"]; ?>
                                    </span>
                                </td>
                                <td><?php echo $history["time"]; ?></td>
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