-- ============================================================
-- Table: vouchers
-- Stores discount voucher codes
-- ============================================================
CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent DECIMAL(5, 2) NOT NULL,
    expiry_date DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



-- ============================================================
-- Insert Sample Vouchers
-- ============================================================
INSERT INTO vouchers (code, discount_percent, expiry_date, status) VALUES
('SAVE10', 10.00, '2026-12-31 23:59:59', 'active'),
('SAVE20', 20.00, '2026-12-31 23:59:59', 'active'),
('WELCOME15', 15.00, '2026-12-31 23:59:59', 'active'),
('EXPIRED50', 50.00, '2025-01-01 00:00:00', 'active'),
('INACTIVE25', 25.00, '2026-12-31 23:59:59', 'inactive');
