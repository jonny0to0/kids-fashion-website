<?php
/**
 * Admin Back Button Component
 * 
 * Usage:
 *   renderBackButton('Categories', '/admin/categories');
 *   renderBackButton('Products', '/admin/products');
 * 
 * @param string $parentLabel The label for the parent page (e.g., "Categories", "Products")
 * @param string $parentUrl The URL to the parent listing page
 * @param string $position Optional: 'top-left' (default) or 'next-to-title'
 */
function renderBackButton($parentLabel, $parentUrl, $position = 'top-left') {
    $fullUrl = (strpos($parentUrl, 'http') === 0) ? $parentUrl : SITE_URL . $parentUrl;
    $label = htmlspecialchars($parentLabel);
    
    $classes = 'inline-flex items-center gap-2 text-gray-600 hover:text-pink-600 transition font-medium';
    
    if ($position === 'next-to-title') {
        $classes .= ' ml-4';
    } else {
        $classes .= ' mb-4';
    }
    
    echo '<a href="' . htmlspecialchars($fullUrl) . '" class="' . $classes . '">';
    echo '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>';
    echo '</svg>';
    echo '<span>Back to ' . $label . '</span>';
    echo '</a>';
}
?>

