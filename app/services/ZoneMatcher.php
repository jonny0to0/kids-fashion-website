<?php

class ZoneMatcher {
    private $shippingZoneModel;
    private $debugMode = false;

    public function __construct() {
        $this->shippingZoneModel = new ShippingZone();
        // Enable debug logging in development
        $this->debugMode = defined('ENVIRONMENT') && ENVIRONMENT === 'development';
    }

    /**
     * Log debug messages if in development mode
     */
    private function log($message) {
        if ($this->debugMode) {
            error_log("[ZoneMatcher] " . $message);
        }
    }

    /**
     * Match an address to the best shipping zone
     * 
     * @param array|object $address Address data with pincode, city, state, country
     * @return array|null Matched zone or null
     */
    public function match($address) {
        // Normalize address to array
        if (is_object($address)) {
            $address = (array) $address;
        }

        // Get all active zones ordered by priority
        $zones = $this->shippingZoneModel->getZonesByPriority();

        // Address fields (handle variations) - normalize all to lowercase for consistent comparison
        $pincode = trim($address['pincode'] ?? $address['zip_code'] ?? '');
        $district = strtolower(trim($address['district'] ?? $address['city'] ?? '')); 
        $state = strtolower(trim($address['state'] ?? ''));
        $country = strtolower(trim($address['country'] ?? ''));

        $this->log("Matching address - PIN: '$pincode', District: '$district', State: '$state', Country: '$country'");

        // IMPORTANT: If country is empty, we CANNOT reliably match - return null
        if (empty($country)) {
            $this->log("REJECTED: No country specified in address");
            return null;
        }

        foreach ($zones as $zone) {
            $matchResult = $this->isMatch($zone, $pincode, $district, $state, $country);
            if ($matchResult) {
                $this->log("MATCHED Zone [{$zone['id']}] '{$zone['zone_name']}' (type: {$zone['zone_type']})");
                return $zone;
            }
        }

        $this->log("NO ZONE MATCHED for this address");
        return null;
    }

    /**
     * Check if an address matches a specific zone
     */
    private function isMatch($zone, $pincode, $district, $state, $country) {
        $type = $zone['zone_type'] ?? 'country';
        $zoneCountry = strtolower(trim($zone['country_code'] ?? 'in'));
        
        // Parse locations - handle both string and array
        $locations = $zone['locations'] ?? '[]';
        if (is_string($locations)) {
            $locations = json_decode($locations, true) ?? [];
        }
        
        // Handle legacy regions column as fallback
        if (empty($locations) && !empty($zone['regions'])) {
            $regions = $zone['regions'];
            if (is_string($regions)) {
                $locations = json_decode($regions, true) ?? [];
            }
        }

        $this->log("  Checking Zone [{$zone['id']}] '{$zone['zone_name']}' (type: $type, locations: " . count($locations) . ")");

        // CRITICAL: First check if country matches for this zone
        // All zone types require the country to match the zone's country_code
        if ($zoneCountry !== $country && $zoneCountry !== $this->normalizeCountryCode($country)) {
            $this->log("  - Skip: Country mismatch (zone: $zoneCountry, address: $country)");
            return false;
        }

        switch ($type) {
            case 'pin':
                // Exact PIN match - locations is array of PIN strings
                if (empty($pincode)) {
                    $this->log("  - Skip: No pincode in address");
                    return false;
                }
                if (empty($locations)) {
                    $this->log("  - Skip: No PIN locations defined in zone");
                    return false;
                }
                // Normalize: compare as strings after trimming
                $normalizedPincode = trim($pincode);
                foreach ($locations as $loc) {
                    if (trim($loc) === $normalizedPincode) {
                        $this->log("  - MATCH: PIN '$pincode' found in zone");
                        return true;
                    }
                }
                $this->log("  - No match: PIN '$pincode' not in zone list");
                return false;

            case 'pin_range':
                // PIN Range match - locations is array of ['start' => '...', 'end' => '...']
                if (empty($pincode)) {
                    $this->log("  - Skip: No pincode in address");
                    return false;
                }
                if (empty($locations)) {
                    $this->log("  - Skip: No PIN ranges defined in zone");
                    return false;
                }
                // CRITICAL FIX: Convert to integers for proper numeric comparison
                $numericPincode = (int)preg_replace('/[^0-9]/', '', $pincode);
                foreach ($locations as $range) {
                    $start = (int)preg_replace('/[^0-9]/', '', $range['start'] ?? '0');
                    $end = (int)preg_replace('/[^0-9]/', '', $range['end'] ?? '0');
                    if ($start > 0 && $end > 0 && $numericPincode >= $start && $numericPincode <= $end) {
                        $this->log("  - MATCH: PIN $numericPincode in range [$start - $end]");
                        return true;
                    }
                }
                $this->log("  - No match: PIN $numericPincode not in any range");
                return false;

            case 'district':
                // District/City match - case insensitive
                if (empty($district)) {
                    $this->log("  - Skip: No district/city in address");
                    return false;
                }
                if (empty($locations)) {
                    $this->log("  - Skip: No district locations defined in zone");
                    return false;
                }
                foreach ($locations as $loc) {
                    if (strtolower(trim($loc)) === $district) {
                        $this->log("  - MATCH: District '$district' found in zone");
                        return true;
                    }
                }
                $this->log("  - No match: District '$district' not in zone list");
                return false;

            case 'state':
                // State match - case insensitive
                if (empty($state)) {
                    $this->log("  - Skip: No state in address");
                    return false;
                }
                // CRITICAL FIX: Empty locations means NO states are configured - should NOT match
                if (empty($locations)) {
                    $this->log("  - Skip: No state locations defined in zone (empty locations = no match)");
                    return false;
                }
                foreach ($locations as $loc) {
                    if (strtolower(trim($loc)) === $state) {
                        $this->log("  - MATCH: State '$state' found in zone");
                        return true;
                    }
                }
                $this->log("  - No match: State '$state' not in zone list");
                return false;

            case 'country':
                // Country match - this is the catch-all for the country
                // Country already validated above, so if we get here, it matches
                $this->log("  - MATCH: Country zone (catch-all for country: $country)");
                return true;

            default:
                $this->log("  - Skip: Unknown zone type '$type'");
                return false;
        }
    }

    /**
     * Normalize country names to ISO codes for comparison
     */
    private function normalizeCountryCode($country) {
        $country = strtolower(trim($country));
        $mapping = [
            'india' => 'in',
            'ind' => 'in',
            'united states' => 'us',
            'usa' => 'us',
            'united kingdom' => 'gb',
            'uk' => 'gb',
            // Add more as needed
        ];
        return $mapping[$country] ?? $country;
    }
}
