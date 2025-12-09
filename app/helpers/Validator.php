<?php
/**
 * Validator Helper Class
 * Provides validation methods for user inputs
 */

class Validator {
    
    /**
     * Validate email
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Indian format)
     */
    public static function phone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return preg_match('/^[6-9]\d{9}$/', $phone);
    }
    
    /**
     * Validate password strength
     */
    public static function password($password, $minLength = 8) {
        if (strlen($password) < $minLength) {
            return false;
        }
        // At least one letter and one number
        return preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password);
    }
    
    /**
     * Sanitize string
     */
    public static function sanitize($string, $stripTags = true) {
        if ($stripTags) {
            $string = strip_tags($string);
        }
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate required field
     */
    public static function required($value) {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return !empty($value);
    }
    
    /**
     * Validate string length
     */
    public static function length($string, $min = null, $max = null) {
        $len = strlen(trim($string));
        if ($min !== null && $len < $min) {
            return false;
        }
        if ($max !== null && $len > $max) {
            return false;
        }
        return true;
    }
    
    /**
     * Validate numeric value
     */
    public static function numeric($value) {
        return is_numeric($value);
    }
    
    /**
     * Validate integer
     */
    public static function integer($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Validate float/decimal
     */
    public static function decimal($value) {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }
    
    /**
     * Validate URL
     */
    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate date
     */
    public static function date($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate pincode (Indian format)
     */
    public static function pincode($pincode) {
        return preg_match('/^[1-9][0-9]{5}$/', $pincode);
    }
    
    /**
     * Validate image file
     */
    public static function image($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedTypes);
    }
    
    /**
     * Validate file size
     */
    public static function fileSize($file, $maxSize = MAX_UPLOAD_SIZE) {
        return isset($file['size']) && $file['size'] <= $maxSize;
    }
    
    /**
     * Validate multiple rules
     */
    public static function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = isset($data[$field]) ? $data[$field] : null;
            $fieldErrors = [];
            
            foreach ($fieldRules as $rule => $param) {
                if ($rule === 'required' && $param && !self::required($value)) {
                    $fieldErrors[] = ucfirst($field) . ' is required';
                } elseif ($rule === 'email' && $param && !self::email($value)) {
                    $fieldErrors[] = ucfirst($field) . ' must be a valid email';
                } elseif ($rule === 'phone' && $param && !self::phone($value)) {
                    $fieldErrors[] = ucfirst($field) . ' must be a valid phone number';
                } elseif ($rule === 'password' && $param && !self::password($value)) {
                    $fieldErrors[] = ucfirst($field) . ' must be at least 8 characters with letters and numbers';
                } elseif ($rule === 'min' && !self::length($value, $param)) {
                    $fieldErrors[] = ucfirst($field) . ' must be at least ' . $param . ' characters';
                } elseif ($rule === 'max' && !self::length($value, null, $param)) {
                    $fieldErrors[] = ucfirst($field) . ' must not exceed ' . $param . ' characters';
                }
            }
            
            if (!empty($fieldErrors)) {
                $errors[$field] = $fieldErrors;
            }
        }
        
        return $errors;
    }
}

