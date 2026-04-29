DROP TRIGGER IF EXISTS trg_block_expired_voucher_use;
DROP TRIGGER IF EXISTS trg_recycle_expired_voucher;

DELIMITER $$

CREATE TRIGGER trg_block_expired_voucher_use
BEFORE UPDATE ON vouchers
FOR EACH ROW
BEGIN
    IF NEW.remaining_balance < OLD.remaining_balance
       AND OLD.status IN ('expired', 'cancelled', 'redeemed')
    THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'VOUCHER_INACTIVE: Cannot deduct from an expired or redeemed voucher.';
    END IF;
END$$

CREATE TRIGGER trg_recycle_expired_voucher
AFTER UPDATE ON vouchers
FOR EACH ROW
BEGIN
    IF NEW.status = 'expired'
       AND OLD.status != 'expired'
       AND NEW.remaining_balance > 0
       AND NEW.is_refundable = 0
    THEN
        UPDATE system_settings
           SET cashier_vault_points = cashier_vault_points + NEW.remaining_balance
         WHERE id = 1;
    END IF;
END$$

DELIMITER ;
