-- Add role column to users table
ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user';

-- Set existing admin user (if exists) or create admin user
-- Update a specific user to be admin (change 'admin' to your admin username)
-- UPDATE users SET role = 'admin' WHERE username = 'admin';

-- Insert admin user if not exists (username: admin, password: admin123 - MD5 hashed)
INSERT INTO users (username, password, email, full_name, phone, address, role) 
VALUES ('admin', MD5('admin123'), 'admin@fooddelivery.com', 'Administrator', '0000000000', 'Admin Office', 'admin')
ON DUPLICATE KEY UPDATE role = 'admin';
