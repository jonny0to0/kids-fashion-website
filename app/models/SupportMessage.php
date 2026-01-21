<?php
/**
 * Support Message Model
 */
class SupportMessage extends Model {
    protected $table = 'support_messages';
    protected $primaryKey = 'message_id';

    /**
     * Get messages for a ticket with sender details
     */
    public function getMessagesByTicket($ticketId) {
        $sql = "SELECT m.*, u.first_name, u.last_name, u.profile_image 
                FROM {$this->table} m 
                JOIN users u ON m.user_id = u.user_id 
                WHERE m.ticket_id = ? 
                ORDER BY m.created_at ASC";
        return $this->query($sql, [$ticketId])->fetchAll();
    }
}
