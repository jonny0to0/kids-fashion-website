<?php
/**
 * Image Upload Helper Class
 * Handles secure image uploads
 */

class ImageUpload {
    
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxSize;
    
    public function __construct($uploadDir = null) {
        $this->uploadDir = $uploadDir ?: PUBLIC_PATH . '/assets/uploads/';
        $this->maxSize = MAX_UPLOAD_SIZE;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload image file
     */
    public function upload($file, $subfolder = '', $prefix = '') {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }
        
        // Check file size
        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'error' => 'File size exceeds maximum allowed size'];
        }
        
        // Generate unique filename
        $extension = $this->getExtension($mimeType);
        $filename = $prefix . uniqid() . '_' . time() . '.' . $extension;
        
        // Create subfolder if specified
        $targetDir = $this->uploadDir . ($subfolder ? $subfolder . '/' : '');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $targetPath = $targetDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Generate relative path
            $relativePath = str_replace(PUBLIC_PATH, '', $targetPath);
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $targetPath,
                'relative_path' => $relativePath,
                'url' => SITE_URL . $relativePath
            ];
        } else {
            return ['success' => false, 'error' => 'Failed to upload file'];
        }
    }
    
    /**
     * Upload multiple images
     */
    public function uploadMultiple($files, $subfolder = '', $prefix = '') {
        $results = [];
        
        if (!is_array($files['name'])) {
            // Single file
            return [$this->upload($files, $subfolder, $prefix)];
        }
        
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $results[] = $this->upload($file, $subfolder, $prefix);
        }
        
        return $results;
    }
    
    /**
     * Delete image file
     */
    public function delete($filepath) {
        $fullPath = strpos($filepath, PUBLIC_PATH) === 0 ? $filepath : PUBLIC_PATH . $filepath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Resize image (optional - requires GD library)
     */
    public function resize($filepath, $width, $height, $quality = 85) {
        $imageInfo = getimagesize($filepath);
        
        if (!$imageInfo) {
            return false;
        }
        
        $mimeType = $imageInfo['mime'];
        $srcWidth = $imageInfo[0];
        $srcHeight = $imageInfo[1];
        
        // Create source image resource
        switch ($mimeType) {
            case 'image/jpeg':
                $srcImage = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $srcImage = imagecreatefrompng($filepath);
                break;
            case 'image/gif':
                $srcImage = imagecreatefromgif($filepath);
                break;
            case 'image/webp':
                $srcImage = imagecreatefromwebp($filepath);
                break;
            default:
                return false;
        }
        
        // Create destination image
        $dstImage = imagecreatetruecolor($width, $height);
        
        // Resize
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
        
        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($dstImage, $filepath, $quality);
                break;
            case 'image/png':
                imagepng($dstImage, $filepath);
                break;
            case 'image/gif':
                imagegif($dstImage, $filepath);
                break;
            case 'image/webp':
                imagewebp($dstImage, $filepath, $quality);
                break;
        }
        
        imagedestroy($srcImage);
        imagedestroy($dstImage);
        
        return true;
    }
    
    /**
     * Get file extension from MIME type
     */
    private function getExtension($mimeType) {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        
        return $extensions[$mimeType] ?? 'jpg';
    }
}

