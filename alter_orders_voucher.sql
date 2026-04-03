-- Add voucher columns to orders table
ALTER TABLE orders ADD COLUMN voucher_code VARCHAR(50) DEFAULT NULL;
ALTER TABLE orders ADD COLUMN discount_percent DECIMAL(5,2) DEFAULT 0;
ALTER TABLE orders ADD COLUMN original_price DECIMAL(10,2) DEFAULT NULL;

-- Add foreign key constraint (optional)
-- ALTER TABLE orders ADD CONSTRAINT fk_voucher FOREIGN KEY (voucher_code) REFERENCES vouchers(code);
