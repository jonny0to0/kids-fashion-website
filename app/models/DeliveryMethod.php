<?php

class DeliveryMethod extends Model {
    protected $table = 'delivery_methods';
    protected $primaryKey = 'id';

    public function getMethodsByZone($zoneId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE zone_id = :zone_id AND is_active = 1 ORDER BY cost ASC");
        $stmt->execute(['zone_id' => $zoneId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createMethod($data) {
        $jsonFields = ['integration_settings', 'cod_settings', 'audit_log'];
        foreach ($jsonFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }
        return $this->create($data);
    }

    public function updateMethod($id, $data) {
        $jsonFields = ['integration_settings', 'cod_settings', 'audit_log'];
        foreach ($jsonFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }
        return $this->update($id, $data);
    }

    public function deleteMethod($id) {
        return $this->delete($id);
    }
}
