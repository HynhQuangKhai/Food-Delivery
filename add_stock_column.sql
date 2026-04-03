-- Add stock column to food_items table
ALTER TABLE food_items ADD COLUMN stock INT DEFAULT 100;

-- Update existing items with default stock
UPDATE food_items SET stock = 100 WHERE stock IS NULL;

-- Set some items as low stock for testing
UPDATE food_items SET stock = 5 WHERE id IN (1, 3, 5);
UPDATE food_items SET stock = 0 WHERE id = 10; -- Out of stock example
