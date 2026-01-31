<?php
/**
 * Image Upload Helper Class
 * Handles secure image uploads
 */

class ImageUpload {
    
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'];
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
     * @param array $file File data from $_FILES
     * @param string $subfolder Subfolder within upload directory
     * @param string $prefix Prefix for filename
     * @param int|null $maxWidth Maximum width in pixels (optional, for validation only)
     * @param int|null $maxHeight Maximum height in pixels (optional, for validation only)
     */
    public function upload($file, $subfolder = '', $prefix = '', $maxWidth = null, $maxHeight = null) {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Special handling for SVG files (they may be detected as text/plain or application/xml)
        if ($mimeType === 'text/plain' || $mimeType === 'application/xml' || $mimeType === 'text/xml') {
            $content = file_get_contents($file['tmp_name']);
            if (strpos($content, '<svg') !== false || strpos($content, '<?xml') !== false) {
                $mimeType = 'image/svg+xml';
            }
        }
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Allowed: PNG, JPG, SVG, WEBP, GIF, ICO'];
        }
        
        // Check file size
        if ($file['size'] > $this->maxSize) {
            $maxSizeMB = round($this->maxSize / 1024 / 1024, 2);
            return ['success' => false, 'error' => "File size exceeds maximum allowed size ({$maxSizeMB}MB)"];
        }
        
        // Validate image dimensions (skip for SVG as they're scalable)
        if ($mimeType !== 'image/svg+xml' && ($maxWidth !== null || $maxHeight !== null)) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['success' => false, 'error' => 'Invalid image file or corrupted image'];
            }
            
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            
            if ($maxWidth !== null && $width > $maxWidth) {
                return ['success' => false, 'error' => "Image width ({$width}px) exceeds maximum allowed width ({$maxWidth}px)"];
            }
            
            if ($maxHeight !== null && $height > $maxHeight) {
                return ['success' => false, 'error' => "Image height ({$height}px) exceeds maximum allowed height ({$maxHeight}px)"];
            }
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
            // Generate relative path - normalize path separators for cross-platform compatibility
            $normalizedPublicPath = str_replace('\\', '/', PUBLIC_PATH);
            $normalizedTargetPath = str_replace('\\', '/', $targetPath);
            $relativePath = str_replace($normalizedPublicPath, '', $normalizedTargetPath);
            
            // Ensure path starts with / for consistency
            if (substr($relativePath, 0, 1) !== '/') {
                $relativePath = '/' . $relativePath;
            }
            
            // Verify file was actually saved
            if (!file_exists($targetPath)) {
                return ['success' => false, 'error' => 'File was not saved correctly'];
            }
            
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
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/x-icon' => 'ico',
            'image/vnd.microsoft.icon' => 'ico'
        ];
        
        return $extensions[$mimeType] ?? 'jpg';
    }
    
    /**
     * Validate and resize image to fit within constraints while maintaining aspect ratio
     * @param string $filepath Full path to image file
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @return array Result with success status and dimensions
     */
    public function constrainImage($filepath, $maxWidth, $maxHeight) {
        // Skip SVG files as they're vector-based
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        if ($mimeType === 'image/svg+xml') {
            return ['success' => true, 'message' => 'SVG files do not need resizing'];
        }
        
        $imageInfo = @getimagesize($filepath);
        if ($imageInfo === false) {
            return ['success' => false, 'error' => 'Invalid image file'];
        }
        
        $currentWidth = $imageInfo[0];
        $currentHeight = $imageInfo[1];
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $currentWidth, $maxHeight / $currentHeight);
        
        // Only resize if image exceeds constraints
        if ($ratio < 1) {
            $newWidth = (int)($currentWidth * $ratio);
            $newHeight = (int)($currentHeight * $ratio);
            
            $result = $this->resize($filepath, $newWidth, $newHeight);
            if ($result) {
                return ['success' => true, 'width' => $newWidth, 'height' => $newHeight, 'resized' => true];
            } else {
                return ['success' => false, 'error' => 'Failed to resize image'];
            }
        }
        
        return ['success' => true, 'width' => $currentWidth, 'height' => $currentHeight, 'resized' => false];
    }
}

