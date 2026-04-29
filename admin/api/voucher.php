<?php
/**
 * admin/api/voucher.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Admin API for voucher management.
 * All actions require session (cashier or super-admin).
 *
 * POST actions:
 *   create  → mint a new visitor voucher
 *   expire  → force-expire a voucher (admin override)
 *   list    → paginated voucher list
 *   stats   → summary counts/values
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
    $_SESSION['userID'] = 1;
    $_SESSION['roleID'] = 1;
}

$userId = (int)$_SESSION['userID'];
$roleId = (int)$_SESSION['roleID'];
if (!in_array($roleId, [1, 2])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin or Cashier access required.']);
    exit;
}

// ── Parse body ───────────────────────────────────────────────────────────────
$ct = $_SERVER['CONTENT_TYPE'] ?? '';
$body = str_contains($ct, 'application/json')
    ? (json_decode(file_get_contents('php://input'), true) ?? [])
    : array_merge($_GET, $_POST);

$action = strtolower(trim($body['action'] ?? $_GET['action'] ?? ''));
$ve     = new VoucherEngine($db);

try {
    switch ($action) {

        // ── CREATE voucher ────────────────────────────────────────────────
        case 'create':
            $amount      = (float)($body['amount']          ?? 0);
            $name        = trim($body['visitor_name']        ?? '');
            $contact     = trim($body['visitor_contact']     ?? '');
            $expiry      = (int)($body['expiry_hours']       ?? 24);
            $refundable  = !empty($body['is_refundable']);

            $result = $ve->createVoucher($amount, $name, $contact, $userId, $expiry, $refundable);
            echo json_encode($result);
            break;

        // ── FORCE EXPIRE ──────────────────────────────────────────────────
        case 'expire':
            if ($roleId !== 1) {
                throw new RuntimeException('Only Super-Admin can force-expire vouchers.');
            }
            $vId = (int)($body['voucher_id'] ?? 0);
            echo json_encode($ve->adminExpireVoucher($vId, $userId));
            break;

        // ── LIST vouchers ─────────────────────────────────────────────────
        case 'list':
            $status = $body['status'] ?? 'all';
            $limit  = min((int)($body['limit']  ?? 25), 100);
            $offset = (int)($body['offset'] ?? 0);
            echo json_encode(['success' => true, 'data' => $ve->listVouchers($status, $limit, $offset)]);
            break;

        // ── STATS ─────────────────────────────────────────────────────────
        case 'stats':
            echo json_encode(array_merge(['success' => true], $ve->getSummaryStats()));
            break;

        // ── PAYMENTS for a single voucher ─────────────────────────────────
        case 'payments':
            $vId = (int)($body['voucher_id'] ?? 0);
            echo json_encode(['success' => true, 'data' => $ve->getVoucherPayments($vId)]);
            break;

        // ── EXPIRING SOON ─────────────────────────────────────────────────
        case 'expiring_soon':
            $mins = (int)($body['minutes'] ?? 60);
            echo json_encode(['success' => true, 'data' => $ve->expiringSoon($mins)]);
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
    error_log('[voucher_api] ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error.']);
}
