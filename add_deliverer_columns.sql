-- Add deliverer columns to orders table
ALTER TABLE orders ADD COLUMN deliverer_id INT NULL;
ALTER TABLE orders ADD COLUMN delivered_at TIMESTAMP NULL;
