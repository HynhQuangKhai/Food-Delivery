-- Add role column to users table
ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user';

-- Update existing users to have 'user' role
UPDATE users SET role = 'user' WHERE role IS NULL;
