<?php
require_once __DIR__ . '/../connection/config.php';
$studentName = "Test Student";
$studentID = "2024-00001";
$currentBalance = 0;

$recentTopups = [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Top-Up Wallet | EduPay</title>

    <link rel="stylesheet" href="<?= CSS_URL ?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>/student.css?v=40">
    <link rel="stylesheet" href="<?= CSS_URL ?>/responsive.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body>

    <div class="student-layout">

        <aside class="student-sidebar" id="studentSidebar">

            <div class="student-brand">
                <div class="student-brand-logo">
                    <img src="<?= ICONS_URL ?>/logo.png" alt="Logo">
                </div>

                <div class="student-brand-text">
                    <h4>EduPay</h4>
                    <span>Student Portal</span>
                </div>
            </div>

            <nav class="student-menu">
                <a href="<?= STUDENT_URL ?>/dashboard.php">
                    <img src="<?= ICONS_URL ?>/dashboard.png" class="student-nav-icon" alt="">
                    <span class="student-nav-text">Dashboard</span>
                </a>

                <a href="<?= STUDENT_URL ?>/scan.php">
                    <img src="<?= ICONS_URL ?>/qr.png" class="student-nav-icon" alt="">
                    <span class="student-nav-text">Scan &amp; Pay</span>
                </a>

                <a href="<?= STUDENT_URL ?>/history.php">
                    <img src="<?= ICONS_URL ?>/transactions.png" class="student-nav-icon" alt="">
                    <span class="student-nav-text">History</span>
                </a>

                <a href="<?= STUDENT_URL ?>/profile.php">
                    <img src="<?= ICONS_URL ?>/users.png" class="student-nav-icon" alt="">
                    <span class="student-nav-text">Profile</span>
                </a>
            </nav>

            <a href="<?= BASE_URL ?>/logout.php" class="student-logout">
                <img src="<?= ICONS_URL ?>/logout.png" class="student-logout-icon" alt="">
                <span>Logout</span>
            </a>

        </aside>

        <main class="student-main">

            <header class="student-topbar">
                <button class="student-menu-btn" onclick="toggleStudentSidebar()">☰</button>

                <div>
                    <h1>Top-Up Wallet</h1>
                    <p>Submit a request to add funds to your student wallet.</p>
                </div>

                <div class="student-user">
                    <span><?php echo $studentName; ?></span>
                    <div class="student-avatar">
                        <?php echo strtoupper(substr($studentName, 0, 1)); ?>
                    </div>
                </div>
            </header>

            <section class="topup-hero-card mb-4">
                <div>
                    <span>Current Balance</span>
                    <h2>₱<?php echo number_format($currentBalance, 2); ?></h2>
                    <p><?php echo $studentName; ?> · <?php echo $studentID; ?></p>
                </div>

                <div class="topup-hero-badge">
                    Student Wallet
                </div>
            </section>

            <section class="topup-layout-grid mb-4">

                <div class="student-premium-panel topup-form-panel">
                    <div class="student-panel-header">
                        <div>
                            <h3>Request Top-Up</h3>
                            <p>Enter your desired amount and choose a payment method.</p>
                        </div>
                    </div>

                    <form action="#" method="POST" class="topup-form">

                        <div class="topup-field">
                            <label>Amount (₱)</label>

                            <div class="topup-money-input">
                                <span>₱</span>
                                <input type="number" name="amount" id="topupAmount" placeholder="0.00" min="1"
                                    step="0.01" required>
                            </div>
                        </div>

                        <div class="topup-quick-amounts">
                            <button type="button" onclick="setTopupAmount(100)">₱100</button>
                            <button type="button" onclick="setTopupAmount(200)">₱200</button>
                            <button type="button" onclick="setTopupAmount(500)">₱500</button>
                            <button type="button" onclick="setTopupAmount(1000)">₱1,000</button>
                            <button type="button" onclick="setTopupAmount(2000)">₱2,000</button>
                        </div>

                        <div class="topup-field">
                            <label>Payment Method</label>

                            <div class="topup-method-grid">

                                <label class="topup-method-card selected">
                                    <input type="radio" name="payment_method" value="Cash at Cashier" checked>
                                    <div class="topup-method-icon">
                                        <img src="<?= ICONS_URL ?>/add_cash.png" alt="">
                                    </div>
                                    <div>
                                        <strong>Cash at Cashier</strong>
                                        <span>Bring cash to the Accountancy Office</span>
                                    </div>
                                </label>

                                <label class="topup-method-card">
                                    <input type="radio" name="payment_method" value="GCash">
                                    <div class="topup-method-icon">
                                        <img src="<?= ICONS_URL ?>/gcash.png" alt="">
                                    </div>
                                    <div>
                                        <strong>GCash</strong>
                                        <span>09XX Campus GCash number</span>
                                    </div>
                                </label>

                                <label class="topup-method-card">
                                    <input type="radio" name="payment_method" value="Maya">
                                    <div class="topup-method-icon">
                                        <img src="<?= ICONS_URL ?>/maya.png" alt="">
                                    </div>
                                    <div>
                                        <strong>Maya</strong>
                                        <span>Maya linked campus account</span>
                                    </div>
                                </label>

                            </div>
                        </div>

                        <button type="submit" class="topup-submit-btn">
                            Submit Top-Up Request
                        </button>

                    </form>
                </div>

                <div class="student-premium-panel topup-guide-panel">
                    <div class="student-panel-header">
                        <div>
                            <h3>Top-Up Guide</h3>
                            <p>How your wallet top-up request works.</p>
                        </div>
                    </div>

                    <div class="topup-guide-list">
                        <div>
                            <strong>1</strong>
                            <span>Enter the amount you want to add.</span>
                        </div>

                        <div>
                            <strong>2</strong>
                            <span>Select your preferred payment method.</span>
                        </div>

                        <div>
                            <strong>3</strong>
                            <span>Submit your request for cashier verification.</span>
                        </div>

                        <div>
                            <strong>4</strong>
                            <span>Your wallet balance updates after approval.</span>
                        </div>
                    </div>

                    <div class="topup-limit-card">
                        <span>Daily Top-Up Limit</span>
                        <strong>₱5,000</strong>
                        <p>Requests above the limit may require manual approval.</p>
                    </div>
                </div>

            </section>

            <section class="student-premium-panel">

                <div class="student-panel-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3>Recent Top-Up Requests</h3>
                        <p>Track your latest wallet top-up submissions.</p>
                    </div>

                    <a href="history.php" class="student-view-btn">Full History</a>
                </div>

                <?php if (empty($recentTopups)): ?>
                <div class="student-empty-state">
                    <div class="student-empty-icon">
                        <img src="<?= ICONS_URL ?>/wallet.png" alt="">
                    </div>
                    <h3>No top-up requests yet</h3>
                    <p>Submit your first top-up request to add funds to your wallet.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table student-premium-table align-middle">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($recentTopups as $topup): ?>
                            <tr>
                                <td><?php echo $topup["reference"]; ?></td>
                                <td>₱<?php echo number_format($topup["amount"], 2); ?></td>
                                <td><span class="student-type-pill"><?php echo $topup["method"]; ?></span></td>
                                <td><span class="topup-status-pill"><?php echo $topup["status"]; ?></span></td>
                                <td><?php echo $topup["date"]; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            </section>

        </main>

    </div>

    <script src="<?= JS_URL ?>/bootstrap.bundle.min.js"></script>

    <script>
    function toggleStudentSidebar() {
        document.getElementById("studentSidebar").classList.toggle("collapsed");
    }

    function setTopupAmount(amount) {
        document.getElementById("topupAmount").value = amount;
    }

    document.querySelectorAll(".topup-method-card").forEach(function(card) {
        card.addEventListener("click", function() {
            document.querySelectorAll(".topup-method-card").forEach(function(item) {
                item.classList.remove("selected");
            });

            card.classList.add("selected");
            card.querySelector("input").checked = true;
        });
    });
    </script>

</body>

</html>