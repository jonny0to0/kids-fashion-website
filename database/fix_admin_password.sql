-- Fix Admin Password Script
-- Run this script if admin login is not working
-- This updates the admin password to: admin123

USE kids_bazaar;

-- Update admin password to admin123
UPDATE users 
SET password = '$2y$10$QuRsy8diRPO3ziCrUmlRnuxV4PNEY7VAtxCmcsYmzHl0EFx5i415C',
    status = 'active',
    email_verified = TRUE
WHERE email = 'admin@kidsbazaar.com' AND user_type = 'admin';

-- Verify the update
SELECT email, user_type, status, email_verified 
FROM users 
WHERE email = 'admin@kidsbazaar.com';

