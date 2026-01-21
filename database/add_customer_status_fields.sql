-- Add suspension fields to users table
ALTER TABLE users ADD COLUMN suspension_reason TEXT NULL AFTER status;
ALTER TABLE users ADD COLUMN suspension_end_date DATETIME NULL AFTER suspension_reason;
