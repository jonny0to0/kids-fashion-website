<?php

class ShippingController {
    private $shippingZoneModel;
    private $deliveryMethodModel;

    public function __construct() {
        // parent::__construct(); // Removed because there is no base Controller class
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            header('Location: ' . SITE_URL . '/login');
            exit;
        }
        $this->shippingZoneModel = new ShippingZone();
        $this->deliveryMethodModel = new DeliveryMethod();
    }

    private function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    public function index() {
        // Redirect to settings page logic, or just handle AJAX req based on needs
        header('Location: ' . SITE_URL . '/admin/settings?section=shipping');
        exit;
    }

    /**
     * Save/Update Shipping Zone (AJAX)
     */
    public function saveZone() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $id = $_POST['id'] ?? null;
            $name = trim($_POST['zone_name'] ?? '');
            
            if (empty($name)) {
                throw new Exception('Zone name is required');
            }

            $data = [
                'zone_name' => $name,
                'zone_type' => $_POST['zone_type'] ?? 'country',
                'priority' => (int)($_POST['priority'] ?? 0),
                'country_code' => $_POST['country_code'] ?? 'IN',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            // Handle Locations based on type
            // Frontend might send 'locations' as JSON string or we construct it
            // Assuming frontend sends simple inputs and we format matches
            $locations = [];
            if (isset($_POST['locations_json'])) {
                $locations = json_decode($_POST['locations_json'], true);
            } elseif (isset($_POST['locations'])) {
                 // Backward compat or simple textarea
                 // If type is PIN, Split by comma
                 if ($data['zone_type'] === 'pin') {
                     $locations = array_map('trim', explode(',', $_POST['locations']));
                 } elseif ($data['zone_type'] === 'district' || $data['zone_type'] === 'state') {
                     $locations = array_map('trim', explode(',', $_POST['locations']));
                 } elseif ($data['zone_type'] === 'pin_range') {
                     // Expect specific format or handled by json input
                 }
            }
            
            // Clean empty values
            if (is_array($locations)) {
                 $locations = array_filter($locations, function($v) { return !empty($v); });
                 // Re-index
                 $locations = array_values($locations);
            }
            
            $data['locations'] = $locations; // Model handles json_encode
            // $data['regions'] = ... // Deprecated or sync'd if needed
            $data['regions'] = json_encode($locations); // Keep in sync for now if legacy uses it

            if ($id) {
                $result = $this->shippingZoneModel->updateZone($id, $data);
                $message = 'Zone updated successfully';
            } else {
                $result = $this->shippingZoneModel->createZone($data);
                $message = 'Zone created successfully';
            }

            if ($result) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                throw new Exception('Database error while saving zone');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Delete Zone (AJAX)
     */
    public function deleteZone() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID required');
            }

            if ($this->shippingZoneModel->deleteZone($id)) {
                echo json_encode(['success' => true, 'message' => 'Zone deleted']);
            } else {
                throw new Exception('Failed to delete zone');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Save/Update Delivery Method (AJAX)
     */
    public function saveMethod() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $id = $_POST['id'] ?? null;
            $zoneId = $_POST['zone_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $cost = $_POST['cost'] ?? 0;

            if (empty($name) || !$zoneId) {
                throw new Exception('Name and Zone ID required');
            }

            $data = [
                'zone_id' => $zoneId,
                'name' => $name,
                'description' => $_POST['description'] ?? '',
                'delivery_type' => $_POST['delivery_type'] ?? 'home',
                'pricing_type' => $_POST['pricing_type'] ?? 'flat',
                'cost' => $cost,
                'condition_min' => $_POST['condition_min'] !== '' ? $_POST['condition_min'] : null,
                'condition_max' => $_POST['condition_max'] !== '' ? $_POST['condition_max'] : null,
                'estimated_days' => $_POST['estimated_days'] ?? '',
                'has_slots' => isset($_POST['has_slots']) ? 1 : 0,
                'has_same_day' => isset($_POST['has_same_day']) ? 1 : 0,
                'cutoff_time' => $_POST['cutoff_time'] ?? null,
                'min_weight' => $_POST['min_weight'] !== '' ? $_POST['min_weight'] : null,
                'max_weight' => $_POST['max_weight'] !== '' ? $_POST['max_weight'] : null,
                'max_dimensions' => $_POST['max_dimensions'] ?? '',
                'is_fragile' => isset($_POST['is_fragile']) ? 1 : 0,
                'cod_charge' => $_POST['cod_charge'] ?? 0,
                'fuel_surcharge' => $_POST['fuel_surcharge'] ?? 0,
                'is_taxable' => isset($_POST['is_taxable']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'courier_partner' => $_POST['courier_partner'] ?? null,
                'integration_settings' => [
                    'api_enabled' => isset($_POST['api_enabled']) ? 1 : 0,
                    'auto_assign' => isset($_POST['auto_assign']) ? 1 : 0,
                    'tracking_url' => $_POST['tracking_url'] ?? ''
                ],
                'cod_settings' => [
                    'enable_cod' => isset($_POST['enable_cod']) ? 1 : 0,
                    'max_cod_amount' => $_POST['max_cod_amount'] ?? ''
                ],
                'badge_text' => $_POST['badge_text'] ?? '',
                'sort_order' => $_POST['sort_order'] ?? 0,
                'show_at_checkout' => isset($_POST['show_at_checkout']) ? 1 : 0
            ];

            if ($id) {
                // For update, we might want to log changes in audit_log, but simplified for now
                $result = $this->deliveryMethodModel->updateMethod($id, $data);
                $message = 'Method updated successfully';
            } else {
                $result = $this->deliveryMethodModel->createMethod($data);
                $message = 'Method created successfully';
            }

            if ($result) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                throw new Exception('Database error while saving method');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Delete Method (AJAX)
     */
    public function deleteMethod() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID required');
            }

            if ($this->deliveryMethodModel->deleteMethod($id)) {
                echo json_encode(['success' => true, 'message' => 'Method deleted']);
            } else {
                throw new Exception('Failed to delete method');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
