<?php
/**
 * Customer Support Controller
 */
class SupportController {
    private $ticketModel;
    private $messageModel;
    private $userModel;
    private $settingsModel;
    private $eventService;

    public function __construct() {
        if (!Session::isLoggedIn()) {
            Session::set('redirect_after_login', $_SERVER['REQUEST_URI']);
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }
        
        require_once APP_PATH . '/models/SupportTicket.php';
        require_once APP_PATH . '/models/SupportMessage.php';
        require_once APP_PATH . '/models/Settings.php';
        
        $this->ticketModel = new SupportTicket();
        $this->messageModel = new SupportMessage();
        $this->userModel = new User();
        $this->settingsModel = new Settings();

        require_once APP_PATH . '/services/EventService.php';
        $this->eventService = new EventService();
    }
    
    public function index() {
        // List my tickets
        $userId = Session::getUserId();
        $tickets = $this->ticketModel->getTicketsByUser($userId);
        
        // Check if enabled
        $enabled = $this->settingsModel->get('support_enabled', '1');
        $phone = $this->settingsModel->get('support_phone');
        $email = $this->settingsModel->get('support_email');
        $hours = $this->settingsModel->get('support_hours');
        
        $this->render('user/support/index', [
            'tickets' => $tickets,
            'enabled' => $enabled,
            'contact' => [
                'phone' => $phone,
                'email' => $email,
                'hours' => $hours
            ]
        ]);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
            return;
        }
        
        // Check if enabled
        $enabled = $this->settingsModel->get('support_enabled', '1');
        if (!$enabled) {
            Session::setFlash('error', 'Support system is currently unavailable.');
            header('Location: ' . SITE_URL . '/support');
            exit;
        }
        
        // Get recent orders for dropdown
        require_once APP_PATH . '/models/Order.php';
        $orderModel = new Order();
        $orders = $orderModel->getUserOrders(Session::getUserId(), 1, 10); // Last 10 orders
        
        $this->render('user/support/create', ['orders' => $orders]);
    }
    
    private function handleCreate() {
        $subject = htmlspecialchars($_POST['subject'] ?? '');
        $category = $_POST['category'] ?? '';
        $message = htmlspecialchars($_POST['message'] ?? '');
        $orderId = !empty($_POST['order_id']) ? $_POST['order_id'] : null;

        if (empty($subject) || empty($category) || empty($message)) {
            Session::setFlash('error', 'Please fill in all required fields.');
            header('Location: ' . SITE_URL . '/support/create');
            exit;
        }

        $data = [
            'user_id' => Session::getUserId(),
            'subject' => $subject,
            'category' => $category,
            'order_id' => $orderId,
            'message' => $message, // Note: message is stored in messages table, but needed for first message
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Save ticket
        // Note: model create method doesn't take 'message' field usually if mapping 1:1 to table
        // So allow Ticket model to handle it or manually remove it
        $ticketData = $data;
        unset($ticketData['message']);

        $ticketId = $this->ticketModel->create($ticketData);
        
        if ($ticketId) {
            // Save initial message
            $msgData = [
                'ticket_id' => $ticketId,
                'user_id' => Session::getUserId(),
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->messageModel->create($msgData);
            
            // Dispatch Event
            $this->eventService->dispatch(EventService::EVENT_TICKET_CREATED, [
                'related_id' => $ticketId,
                'subject' => $subject,
                'user_id' => Session::getUserId(),
                'user_name' => Session::get('user_name')
            ]);
            
            Session::setFlash('success', 'Support ticket created successfully. Ticket ID: #' . $ticketId);
            header('Location: ' . SITE_URL . '/support/view/' . $ticketId);
            exit;
        } else {
            Session::setFlash('error', 'Failed to create ticket.');
            header('Location: ' . SITE_URL . '/support/create');
            exit;
        }
    }
    
    public function view($ticketId) {
        $ticket = $this->ticketModel->getTicketWithDetails($ticketId);
        
        // Security check
        if (!$ticket || $ticket['user_id'] != Session::getUserId()) {
            Session::setFlash('error', 'Ticket not found or access denied');
            header('Location: ' . SITE_URL . '/support');
            exit;
        }
        
        $messages = $this->messageModel->getMessagesByTicket($ticketId);
        
        $this->render('user/support/view', [
            'ticket' => $ticket,
            'messages' => $messages
        ]);
    }
    
    public function reply() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $ticketId = $_POST['ticket_id'];
        $message = $_POST['message'];
        
        if (empty($message)) {
             Session::setFlash('error', 'Message cannot be empty');
             header('Location: ' . SITE_URL . '/support/view/' . $ticketId);
             exit;
        }
        
        // Security check
        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket || $ticket['user_id'] != Session::getUserId()) {
             exit('Access denied');
        }
        
        $this->messageModel->create([
            'ticket_id' => $ticketId,
            'user_id' => Session::getUserId(),
            'message' => htmlspecialchars($message),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update ticket updated_at
        $this->ticketModel->update($ticketId, ['updated_at' => date('Y-m-d H:i:s')]);
        
        Session::setFlash('success', 'Reply submitted');
        header('Location: ' . SITE_URL . '/support/view/' . $ticketId);
        exit;
    }

    private function render($view, $data = [])
    {
        extract($data);
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/' . $view . '.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }
}
