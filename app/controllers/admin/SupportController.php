<?php
/**
 * Admin Support Controller
 * Handles support tickets and complaints
 */

class AdminSupportController extends AdminController
{
    private $ticketModel;
    private $messageModel;
    private $userModel;
    private $settingsModel;

    public function __construct()
    {
        parent::__construct();
        require_once APP_PATH . '/models/SupportTicket.php';
        require_once APP_PATH . '/models/SupportMessage.php';
        require_once APP_PATH . '/models/Settings.php';
        
        $this->ticketModel = new SupportTicket();
        $this->messageModel = new SupportMessage();
        $this->userModel = new User();
        $this->settingsModel = new Settings();
    }

    /**
     * List all support tickets
     */
    public function index()
    {
        // Check if support is enabled
        $supportEnabled = $this->settingsModel->get('support_enabled', '1');
        
        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'priority' => $_GET['priority'] ?? 'all',
            'search' => $_GET['search'] ?? null
        ];

        // Clean filters
        if ($filters['status'] === 'all') unset($filters['status']);
        if ($filters['priority'] === 'all') unset($filters['priority']);

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $tickets = $this->ticketModel->getAllTicketsWithUsers($filters, $limit, $offset);
        $totalTickets = $this->ticketModel->countTickets($filters);
        
        require_once APP_PATH . '/helpers/Pagination.php';
        $pagination = new Pagination($totalTickets, $limit, $page);

        $this->render('admin/support/index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'pagination' => $pagination,
            'tickets_count' => $totalTickets,
            'supportEnabled' => $supportEnabled
        ]);
    }

    /**
     * View Ticket Details
     */
    public function view($ticketId)
    {
        $ticket = $this->ticketModel->getTicketWithDetails($ticketId);
        
        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found');
            header('Location: ' . SITE_URL . '/admin/support');
            exit;
        }

        $messages = $this->messageModel->getMessagesByTicket($ticketId);
        
        $this->render('admin/support/view', [
            'ticket' => $ticket,
            'messages' => $messages
        ]);
    }

    /**
     * Reply to ticket
     */
    public function reply()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . SITE_URL . '/admin/support');
            exit;
        }

        $ticketId = $_POST['ticket_id'] ?? null;
        $message = $_POST['message'] ?? '';
        $status = $_POST['status'] ?? null; // Optional status update with reply

        if (!$ticketId || empty($message)) {
            Session::setFlash('error', 'Message cannot be empty');
            header('Location: ' . SITE_URL . '/admin/support/view/' . $ticketId);
            exit;
        }
        
        // Save message
        $data = [
            'ticket_id' => $ticketId,
            'user_id' => Session::getUserId(),
            'message' => $message,
            'is_admin_reply' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Handle attachments if any (placeholder for now)
        
        $this->messageModel->create($data);
        
        // Update ticket
        $updateData = [
            'last_reply_at' => date('Y-m-d H:i:s')
        ];
        
        if ($status) {
            $updateData['status'] = $status;
        } else {
            // Default to 'in_progress' if replying and it was open
            $ticket = $this->ticketModel->find($ticketId);
            if ($ticket['status'] === 'open') {
                $updateData['status'] = 'in_progress';
            }
        }
        
        $this->ticketModel->update($ticketId, $updateData);
        
        Session::setFlash('success', 'Reply sent successfully');
        header('Location: ' . SITE_URL . '/admin/support/view/' . $ticketId);
        exit;
    }

    /**
     * Update Ticket Status/Priority (AJAX)
     */
    public function update_ticket()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $ticketId = $_POST['ticket_id'] ?? null;
        $field = $_POST['field'] ?? null; // status or priority
        $value = $_POST['value'] ?? null;
        
        if (!$ticketId || !$field || !$value) {
            echo json_encode(['error' => 'Missing fields']);
            return;
        }
        
        if ($this->ticketModel->update($ticketId, [$field => $value])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Update failed']);
        }
    }

    /**
     * Settings Configuration
     */
    public function settings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = [
                'support_enabled' => [
                    'value' => isset($_POST['support_enabled']) ? '1' : '0',
                    'group' => 'support',
                    'type' => 'checkbox'
                ],
                'support_phone' => [
                    'value' => $_POST['support_phone'] ?? '',
                    'group' => 'support',
                    'type' => 'text'
                ],
                'support_email' => [
                    'value' => $_POST['support_email'] ?? '',
                    'group' => 'support',
                    'type' => 'text'
                ],
                'support_hours' => [
                    'value' => $_POST['support_hours'] ?? '',
                    'group' => 'support',
                    'type' => 'text'
                ]
            ];
            
            $this->settingsModel->updateBatch($settings);
            Session::setFlash('success', 'Support settings updated');
            header('Location: ' . SITE_URL . '/admin/support/settings');
            exit;
        }
        
        $settings = $this->settingsModel->getByGroup('support');
        
        $this->render('admin/support/settings', [
            'settings' => $settings
        ]);
    }
}
