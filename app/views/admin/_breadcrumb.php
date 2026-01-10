<?php
/**
 * Admin Breadcrumb Component
 * 
 * Usage:
 *   renderBreadcrumb([
 *       ['label' => 'Dashboard', 'url' => '/admin'],
 *       ['label' => 'Categories', 'url' => '/admin/categories'],
 *       ['label' => 'Edit Category'] // Current page (no URL)
 *   ]);
 * 
 * @param array $items Array of breadcrumb items with 'label' and optional 'url'
 */
function renderBreadcrumb($items) {
    if (empty($items) || !is_array($items)) {
        return;
    }
    
    echo '<nav class="mb-4" aria-label="Breadcrumb">';
    echo '<ol class="flex items-center space-x-2 text-sm">';
    
    foreach ($items as $index => $item) {
        $isLast = ($index === count($items) - 1);
        $label = htmlspecialchars($item['label'] ?? '');
        $url = $item['url'] ?? null;
        
        if ($index > 0) {
            echo '<li class="text-gray-400">/</li>';
        }
        
        echo '<li>';
        
        if ($isLast) {
            // Current page - not clickable
            echo '<span class="text-gray-600 font-medium" aria-current="page">' . $label . '</span>';
        } else {
            // Clickable breadcrumb item
            if ($url) {
                $fullUrl = (strpos($url, 'http') === 0) ? $url : SITE_URL . $url;
                echo '<a href="' . htmlspecialchars($fullUrl) . '" class="text-gray-600 hover:text-pink-600 transition">' . $label . '</a>';
            } else {
                echo '<span class="text-gray-600">' . $label . '</span>';
            }
        }
        
        echo '</li>';
    }
    
    echo '</ol>';
    echo '</nav>';
}
?>

