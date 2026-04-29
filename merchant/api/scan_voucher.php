<?php
/**
 * merchant/api/scan_voucher.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Merchant-side API endpoint for scanning and processing visitor QR vouchers.
 *
 * POST actions:
 *   validate  → run lazy-expiry middleware, return voucher info (no payment yet)
 *   pay       → confirm and execute the payment
 * ─────────────────────────────────────────────────────────────────────────────
 */

declare(strict_types=1);
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../../connection/config.php';
require_once __DIR__ . '/../../connection/pdo.php';
require_once __DIR__ . '/../../connection/VoucherEngine.php';

// ── Auth ─────────────────────────────────────────────────────────────────────
// Mock session if not set
if (!isset($_SESSION['userID'])) {
    $_SESSION['userID'] = 3; // Example merchant user ID
    $_SESSION['roleID'] = 4; // Merchant role
}

if ((int)$_SESSION['roleID'] !== 4) {     // 4 = Merchant
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Merchant access only.']);
    exit;
}
$merchantUserId = (int)$_SESSION['userID'];

// ── Body parse ───────────────────────────────────────────────────────────────
$ct   = $_SERVER['CONTENT_TYPE'] ?? '';
$body = str_contains($ct, 'application/json')
    ? (json_decode(file_get_contents('php://input'), true) ?? [])
    : $_POST;

$action       = strtolower(trim($body['action'] ?? ''));
$qrHash       = trim($body['qr_hash']            ?? '');
$merchantWalletId = (int)($body['merchant_wallet_id'] ?? 0);
$amount       = (float)($body['amount']           ?? 0);

$ve = new VoucherEngine($db);

try {
    switch ($action) {

        // ── VALIDATE ONLY (no payment) ─────────────────────────────────────
        // Call this immediately after the merchant's camera scans the QR.
        // Returns voucher info + lazy expiry result without debiting anything.
        case 'validate':
            if ($qrHash === '') throw new InvalidArgumentException('qr_hash is required.');
            $result = $ve->scanValidate($qrHash, $merchantUserId);
            echo json_encode(array_merge(['success' => true], $result));
            break;

        // ── PAY (validate + debit) ─────────────────────────────────────────
        // Merchant inputs the amount, confirms, and this endpoint executes.
        case 'pay':
            if ($qrHash           === '') throw new InvalidArgumentException('qr_hash is required.');
            if ($merchantWalletId === 0)  throw new InvalidArgumentException('merchant_wallet_id is required.');
            if ($amount           <= 0)   throw new InvalidArgumentException('Amount must be greater than zero.');

            $result = $ve->voucherPay($qrHash, $merchantWalletId, $amount, $merchantUserId);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Unknown action: '{$action}'"]);
    }

} catch (RuntimeException | InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[scan_voucher] ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error.']);
}
