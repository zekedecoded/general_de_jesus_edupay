<?php
/**
 * api/topup.php        — Cashier loads a student wallet
 * api/settle.php       — Cashier processes merchant encashment
 * api/circulation.php  — Dashboard circulation health snapshot
 *
 * All three are in this single combined file for clarity.
 * In production split them into separate files.
 *
 * Route: determined by the `action` POST/GET field:
 *   topup       → vault → student wallet
 *   settle      → merchant wallet → vault
 *   circulation → read-only health snapshot
 *   voucher     → vault → voucher pool
 *   pay         → student wallet → merchant wallet
 */

declare(strict_types=1);
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../../connection/config.php';
require_once __DIR__ . '/../../connection/pdo.php';
require_once __DIR__ . '/../../connection/app.php';
require_once __DIR__ . '/../../connection/CirculationEngine.php';

// ── Auth ─────────────────────────────────────────────────────────────────────
if (!isset($_SESSION['userID'], $_SESSION['roleID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthenticated.']);
    exit;
}

$userId = (int)$_SESSION['userID'];
$role = gjc_current_role();
$adminEconomyRoles = ['admin', 'cashier', 'sub-admin', 'super-admin'];

// ── Parse body ───────────────────────────────────────────────────────────────
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $body = array_merge($_GET, $_POST);
}

$action = strtolower(trim($body['action'] ?? ''));
$engine = new CirculationEngine($db);

try {
    switch ($action) {

        // ── CASH-IN: Vault → Student ────────────────────────────────────────
        case 'topup':
            if (!in_array($role, $adminEconomyRoles, true)) throw new RuntimeException('ACCESS_DENIED');

            $walletId = (int)($body['student_wallet_id'] ?? 0);
            $amount   = (float)($body['amount'] ?? 0);

            $result = $engine->cashIn($walletId, $amount, $userId);
            echo json_encode(array_merge(['success' => true], $result));
            break;

        // ── PAYMENT: Student → Merchant ──────────────────────────────────────
        case 'pay':
            $studentWallet  = (int)($body['student_wallet_id']  ?? 0);
            $merchantWallet = (int)($body['merchant_wallet_id'] ?? 0);
            $amount         = (float)($body['amount'] ?? 0);

            $result = $engine->studentPay($studentWallet, $merchantWallet, $amount, $userId);
            echo json_encode(array_merge(['success' => true], $result));
            break;

        // ── SETTLEMENT: Merchant → Vault ─────────────────────────────────────
        case 'settle':
            if (!in_array($role, $adminEconomyRoles, true)) throw new RuntimeException('ACCESS_DENIED');

            $merchantWallet = (int)($body['merchant_wallet_id'] ?? 0);
            $amount         = (float)($body['amount'] ?? 0);

            $result = $engine->merchantSettle($merchantWallet, $amount, $userId);
            echo json_encode(array_merge(['success' => true], $result));
            break;

        // ── VOUCHER CREATE: Vault → Voucher Pool ─────────────────────────────
        case 'voucher':
            if (!in_array($role, $adminEconomyRoles, true)) throw new RuntimeException('ACCESS_DENIED');

            $amount        = (float)($body['amount']           ?? 0);
            $visitorName   = trim($body['visitor_name']        ?? '');
            $visitorContact= trim($body['visitor_contact']     ?? '');
            $expiryHours   = (int)($body['expiry_hours']       ?? 24);

            if ($visitorName === '') throw new RuntimeException('Visitor name is required.');

            $result = $engine->createVoucher($amount, $visitorName, $visitorContact, $userId, $expiryHours);
            echo json_encode($result);
            break;

        // ── VOUCHER PAY: Voucher → Merchant ──────────────────────────────────
        case 'voucher_pay':
            $voucherCode    = trim($body['voucher_code']       ?? '');
            $merchantWallet = (int)($body['merchant_wallet_id'] ?? 0);
            $amount         = (float)($body['amount'] ?? 0);

            if ($voucherCode === '') throw new RuntimeException('voucher_code is required.');

            $result = $engine->voucherPay($voucherCode, $merchantWallet, $amount, $userId);
            echo json_encode($result);
            break;

        // ── EXPIRE VOUCHER: Recycle remaining balance → Vault ────────────────
        case 'expire_voucher':
            if (!in_array($role, $adminEconomyRoles, true)) throw new RuntimeException('ACCESS_DENIED');

            $voucherId = (int)($body['voucher_id'] ?? 0);
            $result    = $engine->expireVoucher($voucherId, $userId);
            echo json_encode($result);
            break;

        // ── CIRCULATION HEALTH (read-only) ───────────────────────────────────
        case 'circulation':
            $snapshot = $engine->getCirculationSnapshot();

            // Add balance flag
            $drift = abs((float)($snapshot['circulation_drift'] ?? 1));
            $snapshot['is_balanced'] = $drift < 0.01;
            $snapshot['alert']       = $snapshot['is_balanced']
                ? null
                : sprintf('⚠ Economy drift detected: ₱%s. Run integrity audit immediately.',
                    number_format($drift, 2));

            echo json_encode($snapshot);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Unknown action: '{$action}'"]);
    }

} catch (RuntimeException | InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[economy_api.php] ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error.']);
}
