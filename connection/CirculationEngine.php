<?php
/**
 * CirculationEngine — GJC EduPay Token Economic System
 *
 * Enforces the closed-loop economy invariant:
 *   vault + SUM(student_balances) + SUM(merchant_balances) + SUM(active_voucher_balances)
 *   === system_settings.total_circulation_cap
 *
 * All public methods run inside a serializable DB transaction
 * and call validateCirculation() before committing.
 */

require_once __DIR__ . '/pdo.php';

class CirculationEngine
{
    private PDO $db;

    // ── Transaction type constants ──────────────────────────
    const TXN_CASH_IN         = 'cash_in';
    const TXN_PAYMENT         = 'payment';
    const TXN_VOUCHER_PAYMENT = 'voucher_payment';
    const TXN_MERCHANT_SETTLE = 'merchant_settle';
    const TXN_VOUCHER_CREATE  = 'voucher_create';
    const TXN_VOUCHER_EXPIRE  = 'voucher_expire';
    const TXN_CAP_INCREASE    = 'cap_increase';

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // ══════════════════════════════════════════════════════════
    //  PUBLIC API
    // ══════════════════════════════════════════════════════════

    /**
     * CASH-IN: Move points from the vault → student wallet.
     * Only cashier/sub-admin may call this.
     *
     * @param int   $studentWalletId
     * @param float $amount
     * @param int   $initiatedBy  users.id of the cashier
     * @throws RuntimeException on vault shortage or integrity failure
     */
    public function cashIn(int $studentWalletId, float $amount, int $initiatedBy): array
    {
        $this->assertPositive($amount);

        $this->db->beginTransaction();
        try {
            // Lock the singleton row
            $settings = $this->lockSettings();

            if ($settings['cashier_vault_points'] < $amount) {
                throw new RuntimeException(
                    "VAULT_INSUFFICIENT: Vault only has ₱" .
                    number_format($settings['cashier_vault_points'], 2) .
                    " — cannot load ₱" . number_format($amount, 2) .
                    ". Request a vault replenishment from the Super-Admin."
                );
            }

            // Debit vault
            $this->db->prepare(
                "UPDATE system_settings
                    SET cashier_vault_points = cashier_vault_points - ?
                  WHERE id = 1"
            )->execute([$amount]);

            // Credit student wallet
            $this->db->prepare(
                "UPDATE student_wallets
                    SET balance = balance + ?
                  WHERE id = ?"
            )->execute([$amount, $studentWalletId]);

            $vaultAfter = $settings['cashier_vault_points'] - $amount;

            // Integrity gate
            $this->validateCirculation($settings['total_circulation_cap']);

            $ref = $this->logTransaction(
                self::TXN_CASH_IN, $initiatedBy, $amount,
                $settings['cashier_vault_points'], $vaultAfter,
                studentWalletId: $studentWalletId
            );

            $this->db->commit();
            return ['success' => true, 'reference' => $ref, 'vault_after' => $vaultAfter];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * PAYMENT: Move points from student wallet → merchant wallet.
     *
     * @param int   $studentWalletId
     * @param int   $merchantWalletId
     * @param float $amount
     * @param int   $initiatedBy  users.id of the student
     */
    public function studentPay(
        int $studentWalletId,
        int $merchantWalletId,
        float $amount,
        int $initiatedBy
    ): array {
        $this->assertPositive($amount);

        $this->db->beginTransaction();
        try {
            $settings = $this->lockSettings();

            // Check student balance
            $student = $this->db->prepare(
                "SELECT balance FROM student_wallets WHERE id = ? FOR UPDATE"
            );
            $student->execute([$studentWalletId]);
            $studentRow = $student->fetch();

            if (!$studentRow || $studentRow['balance'] < $amount) {
                throw new RuntimeException(
                    "STUDENT_INSUFFICIENT_BALANCE: Student balance ₱" .
                    number_format($studentRow['balance'] ?? 0, 2) .
                    " is below the required ₱" . number_format($amount, 2) . "."
                );
            }

            // Debit student
            $this->db->prepare(
                "UPDATE student_wallets SET balance = balance - ? WHERE id = ?"
            )->execute([$amount, $studentWalletId]);

            // Credit merchant
            $this->db->prepare(
                "UPDATE merchant_wallets SET balance = balance + ? WHERE id = ?"
            )->execute([$amount, $merchantWalletId]);

            // Vault does NOT change for a peer-to-peer payment
            $this->validateCirculation($settings['total_circulation_cap']);

            $ref = $this->logTransaction(
                self::TXN_PAYMENT, $initiatedBy, $amount,
                $settings['cashier_vault_points'], $settings['cashier_vault_points'],
                studentWalletId: $studentWalletId,
                merchantWalletId: $merchantWalletId
            );

            $this->db->commit();
            return ['success' => true, 'reference' => $ref];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * MERCHANT SETTLEMENT (Encashment):
     * Move points from merchant wallet → vault (recycling loop).
     * Cashier physically pays out cash to merchant.
     *
     * @param int   $merchantWalletId
     * @param float $amount
     * @param int   $initiatedBy  users.id (cashier or admin)
     */
    public function merchantSettle(int $merchantWalletId, float $amount, int $initiatedBy): array
    {
        $this->assertPositive($amount);

        $this->db->beginTransaction();
        try {
            $settings = $this->lockSettings();

            $merchant = $this->db->prepare(
                "SELECT balance FROM merchant_wallets WHERE id = ? FOR UPDATE"
            );
            $merchant->execute([$merchantWalletId]);
            $merchantRow = $merchant->fetch();

            if (!$merchantRow || $merchantRow['balance'] < $amount) {
                throw new RuntimeException(
                    "MERCHANT_INSUFFICIENT_BALANCE: Cannot settle ₱" .
                    number_format($amount, 2) . "."
                );
            }

            // Debit merchant
            $this->db->prepare(
                "UPDATE merchant_wallets SET balance = balance - ? WHERE id = ?"
            )->execute([$amount, $merchantWalletId]);

            // Credit vault (recycling)
            $this->db->prepare(
                "UPDATE system_settings
                    SET cashier_vault_points = cashier_vault_points + ?
                  WHERE id = 1"
            )->execute([$amount]);

            $vaultAfter = $settings['cashier_vault_points'] + $amount;

            $this->validateCirculation($settings['total_circulation_cap']);

            $ref = $this->logTransaction(
                self::TXN_MERCHANT_SETTLE, $initiatedBy, $amount,
                $settings['cashier_vault_points'], $vaultAfter,
                merchantWalletId: $merchantWalletId
            );

            $this->db->commit();
            return ['success' => true, 'reference' => $ref, 'vault_after' => $vaultAfter];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * CREATE VOUCHER: Pull points from vault → voucher pool.
     * Visitor pays cash at cashier window; cashier creates QR voucher.
     *
     * @param float  $amount
     * @param string $visitorName
     * @param string $visitorContact
     * @param int    $expiryHours   How long (hours) until voucher expires
     * @param int    $issuedBy      users.id of the cashier
     */
    public function createVoucher(
        float $amount,
        string $visitorName,
        string $visitorContact,
        int $expiryHours,
        int $issuedBy
    ): array {
        $this->assertPositive($amount);

        $this->db->beginTransaction();
        try {
            $settings = $this->lockSettings();

            if ($settings['cashier_vault_points'] < $amount) {
                throw new RuntimeException(
                    "VAULT_INSUFFICIENT: Cannot issue voucher. Vault has ₱" .
                    number_format($settings['cashier_vault_points'], 2) . "."
                );
            }

            $code      = $this->generateVoucherCode();
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));

            // Debit vault
            $this->db->prepare(
                "UPDATE system_settings
                    SET cashier_vault_points = cashier_vault_points - ?
                  WHERE id = 1"
            )->execute([$amount]);

            // Insert voucher
            $this->db->prepare(
                "INSERT INTO vouchers
                    (voucher_code, issued_by, visitor_name, visitor_contact,
                     original_amount, remaining_balance, expires_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            )->execute([$code, $issuedBy, $visitorName, $visitorContact,
                        $amount, $amount, $expiresAt]);

            $voucherId  = (int) $this->db->lastInsertId();
            $vaultAfter = $settings['cashier_vault_points'] - $amount;

            $this->validateCirculation($settings['total_circulation_cap']);

            $ref = $this->logTransaction(
                self::TXN_VOUCHER_CREATE, $issuedBy, $amount,
                $settings['cashier_vault_points'], $vaultAfter,
                voucherId: $voucherId
            );

            $this->db->commit();
            return [
                'success'      => true,
                'voucher_code' => $code,
                'voucher_id'   => $voucherId,
                'expires_at'   => $expiresAt,
                'reference'    => $ref,
                'non_refundable_notice' =>
                    'This voucher is NON-REFUNDABLE. Any unspent balance cannot be converted to cash.',
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * VOUCHER PAYMENT: Visitor uses QR voucher to pay a merchant.
     * Remaining balance stays in the system (non-refundable).
     *
     * @param string $voucherCode
     * @param int    $merchantWalletId
     * @param float  $amount
     * @param int    $initiatedBy  users.id (merchant or cashier scanning)
     */
    public function voucherPay(
        string $voucherCode,
        int $merchantWalletId,
        float $amount,
        int $initiatedBy
    ): array {
        $this->assertPositive($amount);

        $this->db->beginTransaction();
        try {
            $settings = $this->lockSettings();

            // Lock and validate voucher
            $vStmt = $this->db->prepare(
                "SELECT * FROM vouchers WHERE voucher_code = ? AND status = 'active' FOR UPDATE"
            );
            $vStmt->execute([$voucherCode]);
            $voucher = $vStmt->fetch();

            if (!$voucher) {
                throw new RuntimeException(
                    "INVALID_VOUCHER: Code not found or already used/expired."
                );
            }
            if (new DateTime() > new DateTime($voucher['expires_at'])) {
                throw new RuntimeException(
                    "VOUCHER_EXPIRED: This voucher expired at {$voucher['expires_at']}."
                );
            }
            if ($voucher['remaining_balance'] < $amount) {
                throw new RuntimeException(
                    "VOUCHER_INSUFFICIENT: Voucher has ₱" .
                    number_format($voucher['remaining_balance'], 2) .
                    " remaining — cannot pay ₱" . number_format($amount, 2) . "." .
                    " Note: change cannot be given as cash (non-refundable)."
                );
            }

            $newBalance = $voucher['remaining_balance'] - $amount;
            $newStatus  = ($newBalance == 0) ? 'used' : 'active';

            // Debit voucher
            $this->db->prepare(
                "UPDATE vouchers
                    SET remaining_balance = ?,
                        status = ?
                  WHERE id = ?"
            )->execute([$newBalance, $newStatus, $voucher['id']]);

            // Credit merchant
            $this->db->prepare(
                "UPDATE merchant_wallets SET balance = balance + ? WHERE id = ?"
            )->execute([$amount, $merchantWalletId]);

            $this->validateCirculation($settings['total_circulation_cap']);

            $ref = $this->logTransaction(
                self::TXN_VOUCHER_PAYMENT, $initiatedBy, $amount,
                $settings['cashier_vault_points'], $settings['cashier_vault_points'],
                merchantWalletId: $merchantWalletId,
                voucherId: $voucher['id']
            );

            $this->db->commit();

            $notice = ($newBalance > 0)
                ? "₱" . number_format($newBalance, 2) . " remains on voucher. " .
                  "Change CANNOT be given as cash — it stays on the voucher (non-refundable)."
                : "Voucher fully consumed.";

            return [
                'success'           => true,
                'reference'         => $ref,
                'remaining_balance' => $newBalance,
                'voucher_notice'    => $notice,
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * INCREASE MONEY SUPPLY (Super-Admin ONLY).
     * Raises the total_circulation_cap and injects the difference
     * directly into the cashier vault.
     *
     * @param float  $increaseBy   Amount to add to the cap
     * @param int    $superAdminId users.id — MUST be verified as super-admin before calling
     * @param string $reason       Mandatory justification
     */
    public function increaseCirculationCap(
        float $increaseBy,
        int $superAdminId,
        string $reason
    ): array {
        $this->assertPositive($increaseBy);

        if (trim($reason) === '') {
            throw new InvalidArgumentException(
                "A mandatory reason/justification is required to increase the money supply."
            );
        }

        $this->db->beginTransaction();
        try {
            $settings = $this->lockSettings();
            $oldCap   = $settings['total_circulation_cap'];
            $newCap   = $oldCap + $increaseBy;

            // Update cap and inject new points directly into vault
            $this->db->prepare(
                "UPDATE system_settings
                    SET total_circulation_cap   = ?,
                        cashier_vault_points    = cashier_vault_points + ?,
                        last_cap_increased_by   = ?,
                        last_cap_increased_at   = NOW()
                  WHERE id = 1"
            )->execute([$newCap, $increaseBy, $superAdminId]);

            // Append-only audit record
            $this->db->prepare(
                "INSERT INTO cap_increase_log
                    (super_admin_id, old_cap, new_cap, amount_added, reason)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([$superAdminId, $oldCap, $newCap, $increaseBy, $reason]);

            $vaultAfter = $settings['cashier_vault_points'] + $increaseBy;

            $this->validateCirculation($newCap);

            $ref = $this->logTransaction(
                self::TXN_CAP_INCREASE, $superAdminId, $increaseBy,
                $settings['cashier_vault_points'], $vaultAfter,
                notes: "Cap raised from ₱{$oldCap} to ₱{$newCap}. Reason: {$reason}"
            );

            $this->db->commit();
            return [
                'success'     => true,
                'old_cap'     => $oldCap,
                'new_cap'     => $newCap,
                'vault_after' => $vaultAfter,
                'reference'   => $ref,
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * EXPIRE VOUCHER: Return unused balance to the vault.
     * Called by a scheduled job or admin action.
     *
     * @param int $voucherId
     * @param int $initiatedBy
     */
    public function expireVoucher(int $voucherId, int $initiatedBy): array
    {
        $this->db->beginTransaction();
        try {
            $settings = $this->lockSettings();
            $vaultBefore = (float) $settings['cashier_vault_points'];

            $vStmt = $this->db->prepare(
                "SELECT * FROM vouchers WHERE id = ? AND status = 'active' FOR UPDATE"
            );
            $vStmt->execute([$voucherId]);
            $voucher = $vStmt->fetch();

            if (!$voucher) {
                throw new RuntimeException("VOUCHER_NOT_FOUND or already inactive.");
            }

            $recycled = $voucher['remaining_balance'];

            // Mark expired. Some installs have a DB trigger that recycles the
            // balance automatically; older installs need the PHP fallback below.
            $this->db->prepare(
                "UPDATE vouchers SET status = 'expired' WHERE id = ?"
            )->execute([$voucherId]);

            $vaultAfterExpire = $this->getVaultBalance();
            $triggerRecycled = abs($vaultAfterExpire - ($vaultBefore + (float) $recycled)) <= 0.01;

            if ((float) $recycled > 0 && !$triggerRecycled) {
                $this->db->prepare(
                    "UPDATE system_settings
                        SET cashier_vault_points = cashier_vault_points + ?
                      WHERE id = 1"
                )->execute([$recycled]);
            }

            $vaultAfter = $this->getVaultBalance();

            $this->validateCirculation($settings['total_circulation_cap']);

            $ref = $this->logTransaction(
                self::TXN_VOUCHER_EXPIRE, $initiatedBy, max($recycled, 0.01),
                $vaultBefore, $vaultAfter,
                voucherId: $voucherId,
                notes: "Recycled ₱{$recycled} from expired voucher #{$voucherId}"
            );

            $this->db->commit();
            return ['success' => true, 'recycled' => $recycled, 'reference' => $ref];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ══════════════════════════════════════════════════════════
    //  INTEGRITY CHECK (Public — usable in admin dashboards)
    // ══════════════════════════════════════════════════════════

    /**
     * Returns the current circulation snapshot.
     * drift === 0 means the economy is balanced.
     */
    public function getCirculationSnapshot(): array
    {
        foreach (['v_circulation_health', 'v_circulation_snapshot'] as $view) {
            try {
                $row = $this->db->query("SELECT * FROM {$view}")->fetch();
                if ($row) {
                    return $row;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $this->buildCirculationSnapshot();
    }

    // ══════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════

    /**
     * THE CORE INVARIANT CHECK.
     * Must be called inside an open transaction, after all balance
     * mutations, before commit.
     *
     * @param float $expectedCap  The cap value from the locked settings row
     * @throws RuntimeException if circulation is out of balance
     */
    private function validateCirculation(float $expectedCap): void
    {
        $row = $this->db->query(
            "SELECT
                (SELECT cashier_vault_points FROM system_settings WHERE id = 1)
                + COALESCE((SELECT SUM(balance) FROM student_wallets), 0)
                + COALESCE((SELECT SUM(balance) FROM merchant_wallets), 0)
                + COALESCE((SELECT SUM(remaining_balance)
                            FROM vouchers WHERE status = 'active'), 0)
             AS total_in_circulation"
        )->fetchColumn();

        $total = (float) $row;
        $drift = abs($total - $expectedCap);

        // Allow a tiny floating-point epsilon
        if ($drift > 0.01) {
            throw new RuntimeException(sprintf(
                "CIRCULATION_INTEGRITY_FAILURE: Expected cap ₱%s but total " .
                "in circulation is ₱%s (drift ₱%s). Transaction aborted.",
                number_format($expectedCap, 2),
                number_format($total, 2),
                number_format($drift, 2)
            ));
        }
    }

    /**
     * Lock the system_settings row for the duration of the transaction.
     */
    private function lockSettings(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM system_settings WHERE id = 1 FOR UPDATE"
        );
        $row = $stmt->fetch();
        if (!$row) {
            throw new RuntimeException("SYSTEM_SETTINGS_MISSING: Cannot find singleton row.");
        }
        return $row;
    }

    private function getVaultBalance(): float
    {
        return (float) $this->db
            ->query("SELECT cashier_vault_points FROM system_settings WHERE id = 1")
            ->fetchColumn();
    }

    private function buildCirculationSnapshot(): array
    {
        $row = $this->db->query(
            "SELECT
                ss.total_circulation_cap AS cap,
                ss.cashier_vault_points AS vault,
                COALESCE((SELECT SUM(balance) FROM student_wallets), 0) AS student_wallets_total,
                COALESCE((SELECT SUM(balance) FROM merchant_wallets), 0) AS merchant_wallets_total,
                COALESCE((SELECT SUM(remaining_balance) FROM vouchers WHERE status = 'active'), 0) AS active_vouchers_total,
                (
                    ss.cashier_vault_points
                    + COALESCE((SELECT SUM(balance) FROM student_wallets), 0)
                    + COALESCE((SELECT SUM(balance) FROM merchant_wallets), 0)
                    + COALESCE((SELECT SUM(remaining_balance) FROM vouchers WHERE status = 'active'), 0)
                ) AS total_in_circulation,
                (
                    ss.total_circulation_cap
                    - ss.cashier_vault_points
                    - COALESCE((SELECT SUM(balance) FROM student_wallets), 0)
                    - COALESCE((SELECT SUM(balance) FROM merchant_wallets), 0)
                    - COALESCE((SELECT SUM(remaining_balance) FROM vouchers WHERE status = 'active'), 0)
                ) AS circulation_drift,
                ss.updated_at AS as_of
             FROM system_settings ss
             WHERE ss.id = 1"
        )->fetch();

        return $row ?: [];
    }

    /**
     * Write an immutable ledger record.
     */
    private function logTransaction(
        string $type,
        int    $initiatedBy,
        float  $amount,
        float  $vaultBefore,
        float  $vaultAfter,
        int    $studentWalletId  = null,
        int    $merchantWalletId = null,
        int    $voucherId        = null,
        string $notes            = null
    ): string {
        // Compute total in circulation for the snapshot column
        $total = (float) $this->db->query(
            "SELECT
                (SELECT cashier_vault_points FROM system_settings WHERE id = 1)
                + COALESCE((SELECT SUM(balance) FROM student_wallets), 0)
                + COALESCE((SELECT SUM(balance) FROM merchant_wallets), 0)
                + COALESCE((SELECT SUM(remaining_balance)
                            FROM vouchers WHERE status = 'active'), 0)"
        )->fetchColumn();

        $ref = 'TXN-' . strtoupper(date('Ymd')) . '-' . str_pad(
            (string) random_int(1, 99999), 5, '0', STR_PAD_LEFT
        );

        $this->db->prepare(
            "INSERT INTO transactions
                (reference_no, transaction_type, initiated_by, student_wallet_id,
                 merchant_wallet_id, voucher_id, amount, vault_before, vault_after,
                 total_in_circulation, status, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)"
        )->execute([
            $ref, $type, $initiatedBy, $studentWalletId,
            $merchantWalletId, $voucherId, $amount,
            $vaultBefore, $vaultAfter, $total, $notes,
        ]);

        return $ref;
    }

    private function generateVoucherCode(): string
    {
        return 'VCH-' . strtoupper(bin2hex(random_bytes(6)));
    }

    private function assertPositive(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Amount must be greater than zero.");
        }
    }
}
