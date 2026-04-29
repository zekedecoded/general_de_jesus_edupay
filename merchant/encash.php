<?php
require_once __DIR__ . '/../connection/config.php';
$availableBalance = 165;
$merchantName = "Greg Bautista";

$encashHistory = [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Request Encashment | EduPay</title>

    <link rel="stylesheet" href="<?= CSS_URL ?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/merchant.css?v=12">
    <link rel="stylesheet" href="<?= CSS_URL ?>/responsive.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body>

    <div class="merchant-layout">

        <aside class="merchant-sidebar" id="merchantSidebar">

            <div class="merchant-brand">
                <div class="merchant-brand-logo">
                    <img src="<?= ICONS_URL ?>/logo.png" alt="Logo">
                </div>

                <div class="merchant-brand-text">
                    <h4>EduPay</h4>
                    <span>Merchant Portal</span>
                </div>
            </div>

            <nav class="merchant-menu">
                <a href="<?= MERCHANT_URL ?>/dashboard.php">
                    <img src="<?= ICONS_URL ?>/dashboard.png" class="merchant-nav-icon" alt="">
                    <span class="merchant-nav-text">Dashboard</span>
                </a>

                <a href="<?= MERCHANT_URL ?>/qrcode.php">
                    <img src="<?= ICONS_URL ?>/qr.png" class="merchant-nav-icon" alt="">
                    <span class="merchant-nav-text">Generate QR</span>
                </a>

                <a href="<?= MERCHANT_URL ?>/encash.php" class="active">
                    <img src="<?= ICONS_URL ?>/encashments.png" class="merchant-nav-icon" alt="">
                    <span class="merchant-nav-text">Encash</span>
                </a>

                <a href="<?= MERCHANT_URL ?>/history.php">
                    <img src="<?= ICONS_URL ?>/transactions.png" class="merchant-nav-icon" alt="">
                    <span class="merchant-nav-text">History</span>
                </a>
            </nav>

            <a href="<?= BASE_URL ?>/logout.php" class="merchant-logout">
                <img src="<?= ICONS_URL ?>/logout.png" class="merchant-logout-icon" alt="">
                <span>Logout</span>
            </a>

        </aside>

        <main class="merchant-main">

            <header class="merchant-topbar">
                <button class="merchant-menu-btn" onclick="toggleMerchantSidebar()">☰</button>

                <div>
                    <h1>Request Encashment</h1>
                    <p>Withdraw available merchant earnings through the Accountancy Office.</p>
                </div>

                <div class="merchant-user">
                    <span>Greg</span>
                    <div class="merchant-avatar">
                        <img src="<?= ICONS_URL ?>/store.png" alt="Merchant">
                    </div>
                </div>
            </header>

            <section class="encash-hero-card mb-4">
                <div>
                    <span>Available to Encash</span>
                    <h2>₱<?php echo number_format($availableBalance, 2); ?></h2>
                    <p><?php echo $merchantName; ?> · Digital earnings wallet</p>
                </div>

                <div class="encash-hero-badge">
                    Ready for Request
                </div>
            </section>

            <section class="encash-layout-grid mb-4">

                <div class="merchant-premium-panel encash-form-panel">
                    <div class="merchant-panel-header">
                        <div>
                            <h3>New Encashment Request</h3>
                            <p>Enter the amount you want to withdraw from your merchant balance.</p>
                        </div>
                    </div>

                    <form action="#" method="POST" class="encash-form">

                        <div class="encash-field">
                            <label>Encashment Amount (₱)</label>

                            <div class="encash-money-input">
                                <span>₱</span>
                                <input type="number" name="amount" placeholder="0.00" min="1"
                                    max="<?php echo $availableBalance; ?>" step="0.01" required>
                            </div>

                            <small>Maximum available amount: ₱<?php echo number_format($availableBalance, 2); ?></small>
                        </div>

                        <button type="button" class="encash-withdraw-all-btn"
                            onclick="document.querySelector('input[name=amount]').value='<?php echo $availableBalance; ?>'">
                            Withdraw All (₱<?php echo number_format($availableBalance, 2); ?>)
                        </button>

                        <button type="submit" class="encash-submit-btn">
                            Submit Request
                        </button>

                    </form>
                </div>

                <div class="merchant-premium-panel encash-info-panel">
                    <div class="merchant-panel-header">
                        <div>
                            <h3>Request Guidelines</h3>
                            <p>Keep these reminders before submitting an encashment.</p>
                        </div>
                    </div>

                    <div class="encash-guidelines">
                        <div>
                            <strong>1</strong>
                            <span>Submit your request with the correct amount.</span>
                        </div>

                        <div>
                            <strong>2</strong>
                            <span>Bring your ID to the Accountancy Office.</span>
                        </div>

                        <div>
                            <strong>3</strong>
                            <span>Cashier verifies your request and releases cash.</span>
                        </div>
                    </div>

                    <div class="encash-note">
                        After submitting, your request will be reviewed by the cashier or finance staff before
                        disbursement.
                    </div>
                </div>

            </section>

            <section class="merchant-premium-panel">

                <div class="merchant-panel-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3>Encashment History</h3>
                        <p>Track previous and pending merchant withdrawal requests.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table merchant-premium-table align-middle">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Processed By</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($encashHistory)): ?>
                            <tr>
                                <td colspan="4" class="encash-empty-state">
                                    No encashment history.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($encashHistory as $row): ?>
                            <tr>
                                <td>₱<?php echo number_format($row["amount"], 2); ?></td>
                                <td><span class="merchant-type-pill"><?php echo $row["status"]; ?></span></td>
                                <td><?php echo $row["processed_by"]; ?></td>
                                <td><?php echo $row["date"]; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </section>

        </main>

    </div>

    <script src="<?= JS_URL ?>/bootstrap.bundle.min.js"></script>

    <script>
    function toggleMerchantSidebar() {
        document.getElementById("merchantSidebar").classList.toggle("collapsed");
    }
    </script>

</body>

</html>