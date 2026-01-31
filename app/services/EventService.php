<?php
/**
 * EventService
 * Central handling for system events and notifications
 */

class EventService {
    // Event Constants
    const EVENT_USER_REGISTERED = 'user_registered';
    const EVENT_USER_LOGIN = 'user_login';
    const EVENT_ADD_TO_CART = 'add_to_cart'; // Maybe too noisy, keep logic to decide
    const EVENT_CHECKOUT_STARTED = 'checkout_started';
    const EVENT_CHECKOUT_ABANDONED = 'checkout_abandoned'; // Hard to trigger real-time, maybe via cron
    const EVENT_ORDER_PLACED = 'order_placed';
    const EVENT_ORDER_CANCELLED = 'order_cancelled';
    const EVENT_PAYMENT_FAILED = 'payment_failed';
    const EVENT_PAYMENT_PENDING = 'payment_pending';
    const EVENT_COD_SELECTED = 'cod_selected';
    const EVENT_ADDRESS_UPDATED = 'address_updated';
    const EVENT_SHIPPING_ZONE_MISMATCH = 'shipping_zone_mismatch';
    const EVENT_RETURN_REQUESTED = 'return_requested';
    const EVENT_REVIEW_SUBMITTED = 'review_submitted';
    const EVENT_TICKET_CREATED = 'ticket_created';

    // Priority Constants (Matching DB ENUM)
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    private $notificationModel;
    private $userModel;

    public function __construct() {
        // Ensure models are loaded
        if (!class_exists('Notification')) {
            require_once APP_PATH . '/models/Notification.php';
        }
        if (!class_exists('User')) {
            require_once APP_PATH . '/models/User.php';
        }

        $this->notificationModel = new Notification();
        $this->userModel = new User();
    }

    /**
     * Dispatch an event
     * 
     * @param string $eventName Event constant
     * @param array $data Context data (e.g., user_id, order_id, message_params)
     */
    public function dispatch($eventName, $data = []) {
        try {
            // 1. Determine Target Audience (Admins)
            $admins = $this->getAdmins();
            if (empty($admins)) {
                return; // No admins to notify
            }

            // 2. Build Notification Content based on Event
            $content = $this->buildContent($eventName, $data);
            if (!$content) {
                // Event might be ignored or invalid
                return;
            }

            // Determine Category (Type)
            $category = 'system';
            if (strpos($eventName, 'order') !== false || strpos($eventName, 'return') !== false || strpos($eventName, 'payment') !== false || strpos($eventName, 'cod') !== false) {
                $category = 'order';
            } elseif (strpos($eventName, 'user') !== false || strpos($eventName, 'address') !== false) {
                $category = 'user';
            } elseif (strpos($eventName, 'review') !== false) {
                $category = 'product';
            }

            // 3. Create Notifications
            foreach ($admins as $admin) {
                $this->notificationModel->createNotification(
                    $admin['user_id'],
                    $content['title'],
                    $content['message'],
                    $category, // Type (Broad Category)
                    $content['link'],
                    $content['related_id'] ?? null,
                    $content['priority'] ?? self::PRIORITY_MEDIUM,
                    $eventName // Specific Event Name
                );
            }

        } catch (Exception $e) {
            // Fail silently to not disrupt user flow, but log it
            error_log("EventService::dispatch error: " . $e->getMessage());
        }
    }

    private function getAdmins() {
        return $this->userModel->findAll(['user_type' => 'admin']);
    }

    private function buildContent($event, $data) {
        $priority = self::PRIORITY_MEDIUM;
        $title = 'System Notification';
        $message = 'New activity detected.';
        $link = '/admin/notifications';
        $relatedId = $data['related_id'] ?? null;

        // Extract common data
        $userName = $data['user_name'] ?? 'Guest';
        $orderId = $data['order_id'] ?? null;
        $amount = $data['amount'] ?? 0;

        switch ($event) {
            case self::EVENT_USER_REGISTERED:
                $priority = self::PRIORITY_LOW;
                $title = 'New User Registered';
                $message = "User {$userName} has registered.";
                $link = '/admin/customers';
                break;

            case self::EVENT_USER_LOGIN:
                // Only notify if needed, maybe suspicious? Or just log? 
                // User asked for it.
                $priority = self::PRIORITY_LOW;
                $title = 'User Login';
                $message = "User {$userName} just logged in.";
                $link = '/admin/customers';
                break;

                break;
            
            // Events removed to reduce noise (Cart/Checkout)
            // case self::EVENT_ADD_TO_CART:
            // case self::EVENT_CHECKOUT_STARTED:
            //     return null; // Suppress

            case self::EVENT_ORDER_PLACED:
                $priority = self::PRIORITY_HIGH;
                $title = 'New Order Placed';
                $orderNumber = $data['order_number'] ?? $orderId;
                $paymentMethod = $data['payment_method'] ?? 'online';
                
                // Logic for COD High Value
                $paymentString = 'Online';
                if ($paymentMethod === 'cod') {
                     $paymentString = 'Cash on Delivery';
                     // High Value Rule
                     if ($amount > 5000) {
                         $priority = self::PRIORITY_CRITICAL;
                         $title = 'High Value COD Order';
                     }
                } elseif ($paymentMethod === 'wallet') {
                    $paymentString = 'Wallet';
                }

                $message = "Order #{$orderNumber} placed by {$userName}.\nAmount: " . $this->formatMoney($amount) . "\nPayment: " . $paymentString;
                $link = "/admin/orders/{$orderId}";
                break;

            case self::EVENT_PAYMENT_FAILED:
                $priority = self::PRIORITY_HIGH;
                $title = 'Payment Failed';
                $orderNumber = $data['order_number'] ?? 'Unknown';
                $reason = $data['reason'] ?? 'Unknown error';
                $message = "Payment failed for Order #{$orderNumber}. Reason: {$reason}";
                $link = $orderId ? "/admin/orders/{$orderId}" : '/admin/orders';
                break;
            
            // COD Selected event suppressed (merged into Order Placed)
            // case self::EVENT_COD_SELECTED:
            //    return null;

            case self::EVENT_SHIPPING_ZONE_MISMATCH:
                $priority = self::PRIORITY_MEDIUM;
                $title = 'Shipping Zone Issue';
                $pincode = $data['pincode'] ?? 'Unknown';
                $message = "User {$userName} tried to checkout with unserviceable PIN: {$pincode}";
                $link = '/admin/settings?section=shipping';
                break;
            
            case self::EVENT_RETURN_REQUESTED:
                $priority = self::PRIORITY_HIGH;
                $title = 'Return Requested';
                $orderNumber = $data['order_number'] ?? '';
                $message = "Return requested for Order #{$orderNumber} by {$userName}.";
                $link = $orderId ? "/admin/orders/{$orderId}" : '/admin/orders';
                break;

             case self::EVENT_REVIEW_SUBMITTED:
                $priority = self::PRIORITY_MEDIUM;
                $title = 'New Product Review';
                $params = $data['product_name'] ?? 'Product';
                $rating = $data['rating'] ?? '?';
                $message = "{$userName} reviewed {$params} ({$rating}/5).";
                $link = '/admin/products/reviews';
                break;

            case self::EVENT_TICKET_CREATED:
                $priority = self::PRIORITY_HIGH;
                $title = 'New Support Ticket';
                $subject = $data['subject'] ?? 'No Subject';
                $message = "{$userName} created a new ticket: {$subject}";
                $link = $relatedId ? "/admin/support/view/{$relatedId}" : '/admin/support';
                break;
            
            default:
                // Default fallback
                return [
                    'title' => 'System Notification',
                    'message' => 'Event: ' . $event,
                    'priority' => self::PRIORITY_LOW,
                    'link' => '/admin/notifications',
                    'related_id' => $relatedId
                ];
        }

        return [
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'link' => $link,
            'related_id' => $relatedId
        ];
    }

    private function formatMoney($amount) {
        return '₹' . number_format($amount, 2);
    }
}
