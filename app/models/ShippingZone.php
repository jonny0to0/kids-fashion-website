<?php

class ShippingZone extends Model {
    protected $table = 'shipping_zones';
    protected $primaryKey = 'id';

    public function getAllZones() {
        return $this->findAll();
    }

    public function getZone($id) {
        return $this->find($id);
    }

    public function createZone($data) {
        // Prepare regions/locations as JSON
        if (isset($data['regions']) && is_array($data['regions'])) {
            $data['regions'] = json_encode($data['regions']);
        }
        if (isset($data['locations']) && is_array($data['locations'])) {
            $data['locations'] = json_encode($data['locations']);
        }
        return $this->create($data);
    }

    public function updateZone($id, $data) {
        // Prepare regions/locations as JSON
        if (isset($data['regions']) && is_array($data['regions'])) {
            $data['regions'] = json_encode($data['regions']);
        }
        if (isset($data['locations']) && is_array($data['locations'])) {
            $data['locations'] = json_encode($data['locations']);
        }
        return $this->update($id, $data);
    }

    public function getZonesByType($type) {
        return $this->findAll("zone_type = ?", [$type]);
    }

    public function getZonesByPriority() {
        // Order by specific hierarchy first (PIN > PIN Range > District > State > Country), then Priority
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 
                ORDER BY 
                CASE zone_type 
                    WHEN 'pin' THEN 1 
                    WHEN 'pin_range' THEN 2 
                    WHEN 'district' THEN 3 
                    WHEN 'state' THEN 4 
                    WHEN 'country' THEN 5 
                    ELSE 6 
                END ASC, 
                priority DESC, 
                id ASC";
        return $this->query($sql)->fetchAll();
    }

    public function deleteZone($id) {
        return $this->delete($id);
    }
}
