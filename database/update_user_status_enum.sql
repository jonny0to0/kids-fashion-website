ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended', 'deactivated', 'deleted') NOT NULL DEFAULT 'active';
