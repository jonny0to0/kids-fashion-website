-- Update notifications table for event-driven system

-- 1. Modify `type` to VARCHAR to support granular events (was ENUM)
-- Note: We first modify it to VARCHAR, preserving existing data (which matches new format roughly)
ALTER TABLE notifications MODIFY COLUMN type VARCHAR(100) DEFAULT 'system';

-- 2. Add `priority` column
ALTER TABLE notifications ADD COLUMN priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium' AFTER type;

-- 3. Add `event_name` column (for machine-readable event codes)
ALTER TABLE notifications ADD COLUMN event_name VARCHAR(100) DEFAULT 'system_notification' AFTER type;

-- 4. Add `related_id` if it doesn't exist (Implementation Plan mentioned it might, but Model has it, Schema doesn't show it explicitly in CREATE TABLE but Notification::createNotification uses it. 
-- Wait, looking at schema.sql line 251:
-- CREATE TABLE IF NOT EXISTS notifications ( ... link VARCHAR(255), created_at ... )
-- It does NOT have related_id. The Model code has it in array but maybe it wasn't in DB?
-- Let's check Model code again.
-- Line 120: 'related_id' => $relatedId. 
-- Line 122: return $this->create($data); 
-- If the column didn't exist, this would have been failing or `BaseModel` ignores it? 
-- Safest is to ADD it if not exists.
-- Since we can't easily do "IF NOT EXISTS" for columns in MySQL 8 without procedures, I'll add it. 
-- If it errors, I'll handle it. But schema.sql didn't have it.

ALTER TABLE notifications ADD COLUMN related_id INT NULL AFTER link;

-- 5. Add index for related_id
CREATE INDEX idx_related_id ON notifications(related_id);

-- 6. Add index for event_name
CREATE INDEX idx_event_name ON notifications(event_name);
