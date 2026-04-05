-- Add admin reply columns to reviews table
ALTER TABLE reviews ADD COLUMN admin_reply TEXT DEFAULT NULL;
ALTER TABLE reviews ADD COLUMN admin_reply_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE reviews ADD COLUMN replied_by INT NULL;
