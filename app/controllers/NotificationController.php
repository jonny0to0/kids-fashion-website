<?php
/**
 * Notification Controller
 * Handles admin notifications
 */

class NotificationController
{
    private $notificationModel;

    public function __construct()
    {
        $this->requireAdmin();
        require_once APP_PATH . '/models/Notification.php';
        $this->notificationModel = new Notification();
    }

    /**
     * Check authentication
     */
    private function requireAdmin()
    {
        if (!Session::isLoggedIn() || Session::getUserType() !== 'admin') {
            header('Location: ' . SITE_URL . '/admin/login');
            exit;
        }
    }

    /**
     * Notification List Page
     * Route: /admin/notifications
     */
    public function index()
    {
        $userId = Session::getUserId();
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Filters
        $filters = [];
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            $filters['type'] = $_GET['type'];
        }

        // Get notifications
        $notifications = $this->notificationModel->getUserNotifications($userId, $perPage, $offset, $filters);
        
        // Get total count for pagination
        $totalNotifications = $this->notificationModel->countUserNotifications($userId, $filters);
        
        require_once APP_PATH . '/helpers/Pagination.php';
        $pagination = new Pagination($totalNotifications, $perPage, $page);

        $pageTitle = 'Notifications';
        
        // Render view
        // Using correct path based on folder structure
        extract([
            'pageTitle' => $pageTitle,
            'notifications' => $notifications,
            'pagination' => $pagination,
            'currentFilter' => $filters['type'] ?? 'all'
        ]);
        
        require_once APP_PATH . '/views/layouts/admin_header.php';
        require_once APP_PATH . '/views/admin/notifications/index.php';
        require_once APP_PATH . '/views/layouts/admin_footer.php';
    }

    /**
     * Get latest notifications (AJAX)
     * For header dropdown
     */
    public function getLatest()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = Session::getUserId();
            $notifications = $this->notificationModel->getUserNotifications($userId, 5);
            $unreadCount = $this->notificationModel->getUnreadCount($userId);

            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark notification as read (AJAX)
     */
    public function markAsRead()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $userId = Session::getUserId();
        $data = json_decode(file_get_contents('php://input'), true);
        $notificationId = $data['id'] ?? null;

        if (!$notificationId) {
            echo json_encode(['error' => 'Missing notification ID']);
            return;
        }

        try {
            $this->notificationModel->markAsRead($notificationId, $userId);
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true, 
                'unread_count' => $unreadCount
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark all as read (AJAX)
     */
    public function markAllRead()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $userId = Session::getUserId();

        try {
            $this->notificationModel->markAllAsRead($userId);
            
            echo json_encode([
                'success' => true,
                'unread_count' => 0
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        $userId = Session::getUserId();
        $this->notificationModel->deleteNotification($id, $userId);
        
        // Redirect back
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
