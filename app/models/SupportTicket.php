<?php
/**
 * Support Ticket Model
 */
class SupportTicket extends Model {
    protected $table = 'support_tickets';
    protected $primaryKey = 'ticket_id';

    /**
     * Get tickets for a specific user
     */
    public function getTicketsByUser($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";
        return $this->query($sql, [$userId])->fetchAll();
    }
    
    /**
     * Get single ticket with user details
     */
    public function getTicketWithDetails($ticketId) {
        $sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.phone 
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.user_id
                WHERE t.ticket_id = ?";
        return $this->query($sql, [$ticketId])->fetch();
    }

    /**
     * Get all tickets with user details (for Admin)
     */
    public function getAllTicketsWithUsers($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT t.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.user_id";
        
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = "t.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $where[] = "t.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(t.subject LIKE ? OR t.ticket_id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $val = '%' . $filters['search'] . '%';
            $params[] = $val;
            $params[] = $val;
            $params[] = $val;
            $params[] = $val;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY t.created_at DESC LIMIT $limit OFFSET $offset";
        
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Count tickets for pagination
     */
    public function countTickets($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} t JOIN users u ON t.user_id = u.user_id";
        
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = "t.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $where[] = "t.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(t.subject LIKE ? OR t.ticket_id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $val = '%' . $filters['search'] . '%';
            $params[] = $val;
            $params[] = $val;
            $params[] = $val;
            $params[] = $val;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        return $this->query($sql, $params)->fetch()['total'];
    }
}
