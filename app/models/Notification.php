<?php

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    /**
     * Get all notifications for a user
     * 
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param array $filters (type, is_read)
     * @return array
     */
    public function getUserNotifications($userId, $limit = 20, $offset = 0, $filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $params = [$userId];

        if (isset($filters['is_read']) && $filters['is_read'] !== '') {
            $sql .= " AND is_read = ?";
            $params[] = (int) $filters['is_read'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int) $limit;
        $params[] = (int) $offset;

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Count notifications
     * 
     * @param int $userId
     * @param array $filters
     * @return int
     */
    public function countUserNotifications($userId, $filters = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = ?";
        $params = [$userId];

        if (isset($filters['is_read']) && $filters['is_read'] !== '') {
            $sql .= " AND is_read = ?";
            $params[] = (int) $filters['is_read'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }

        $result = $this->query($sql, $params)->fetch();
        return $result ? (int) $result['total'] : 0;
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($userId)
    {
        return $this->countUserNotifications($userId, ['is_read' => 0]);
    }

    /**
     * Mark as read
     */
    public function markAsRead($notificationId, $userId)
    {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE notification_id = ? AND user_id = ?";
        return $this->query($sql, [$notificationId, $userId]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        return $this->query($sql, [$userId]);
    }

    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE notification_id = ? AND user_id = ?";
        return $this->query($sql, [$notificationId, $userId]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll($userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
        return $this->query($sql, [$userId]);
    }

    /**
     * Create notification
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string|null $link
     * @param int|null $relatedId
     * @param string $priority
     * @param string $eventName
     * @return int
     */
    public function createNotification($userId, $title, $message, $type = 'system', $link = null, $relatedId = null, $priority = 'medium', $eventName = 'system_notification')
    {
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type, // Broad category
            'link' => $link,
            'related_id' => $relatedId,
            'priority' => $priority, 
            'event_name' => $eventName // Specific machine-readable event
        ];
        return $this->create($data);
    }
}
