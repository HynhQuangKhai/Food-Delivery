-- Add image_url column to reviews table
ALTER TABLE reviews ADD COLUMN image_url VARCHAR(255) NULL AFTER comment;
