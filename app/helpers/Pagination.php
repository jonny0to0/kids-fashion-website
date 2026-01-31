<?php
/**
 * Pagination Helper Class
 * Generates pagination links and data
 */

class Pagination {
    
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $baseUrl;
    
    public function __construct($totalItems, $itemsPerPage = ITEMS_PER_PAGE, $currentPage = 1, $baseUrl = '') {
        $this->totalItems = (int)$totalItems;
        $this->itemsPerPage = (int)$itemsPerPage;
        $this->currentPage = max(1, (int)$currentPage);
        $this->baseUrl = $baseUrl;
    }
    
    /**
     * Get total number of pages
     */
    public function getTotalPages() {
        return $this->itemsPerPage > 0 ? ceil($this->totalItems / $this->itemsPerPage) : 1;
    }
    
    /**
     * Get current page number
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    /**
     * Get offset for database query
     */
    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    /**
     * Get limit for database query
     */
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    /**
     * Check if has previous page
     */
    public function hasPrevious() {
        return $this->currentPage > 1;
    }
    
    /**
     * Check if has next page
     */
    public function hasNext() {
        return $this->currentPage < $this->getTotalPages();
    }
    
    /**
     * Get previous page number
     */
    public function getPreviousPage() {
        return $this->hasPrevious() ? $this->currentPage - 1 : null;
    }
    
    /**
     * Get next page number
     */
    public function getNextPage() {
        return $this->hasNext() ? $this->currentPage + 1 : null;
    }
    
    /**
     * Get pagination data
     */
    public function getPaginationData() {
        return [
            'total_items' => $this->totalItems,
            'items_per_page' => $this->itemsPerPage,
            'current_page' => $this->currentPage,
            'total_pages' => $this->getTotalPages(),
            'has_previous' => $this->hasPrevious(),
            'has_next' => $this->hasNext(),
            'previous_page' => $this->getPreviousPage(),
            'next_page' => $this->getNextPage(),
            'offset' => $this->getOffset(),
            'limit' => $this->getLimit()
        ];
    }
    
    /**
     * Generate pagination HTML
     */
    public function render($class = 'pagination') {
        $totalPages = $this->getTotalPages();
        
        if ($totalPages <= 1) {
            return '';
        }
        
        $html = '<nav class="' . $class . '">';
        $html .= '<ul class="flex items-center justify-center space-x-2">';
        
        // Previous button
        if ($this->hasPrevious()) {
            $prevUrl = $this->buildUrl($this->getPreviousPage());
            $html .= '<li><a href="' . $prevUrl . '" class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">Previous</a></li>';
        } else {
            $html .= '<li><span class="px-4 py-2 bg-gray-100 border border-gray-300 rounded text-gray-400 cursor-not-allowed">Previous</span></li>';
        }
        
        // Page numbers
        $startPage = max(1, $this->currentPage - 2);
        $endPage = min($totalPages, $this->currentPage + 2);
        
        if ($startPage > 1) {
            $html .= '<li><a href="' . $this->buildUrl(1) . '" class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">1</a></li>';
            if ($startPage > 2) {
                $html .= '<li><span class="px-4 py-2">...</span></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $this->currentPage) {
                $html .= '<li><span class="px-4 py-2 bg-blue-600 text-white border border-blue-600 rounded">' . $i . '</span></li>';
            } else {
                $html .= '<li><a href="' . $this->buildUrl($i) . '" class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">' . $i . '</a></li>';
            }
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $html .= '<li><span class="px-4 py-2">...</span></li>';
            }
            $html .= '<li><a href="' . $this->buildUrl($totalPages) . '" class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">' . $totalPages . '</a></li>';
        }
        
        // Next button
        if ($this->hasNext()) {
            $nextUrl = $this->buildUrl($this->getNextPage());
            $html .= '<li><a href="' . $nextUrl . '" class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">Next</a></li>';
        } else {
            $html .= '<li><span class="px-4 py-2 bg-gray-100 border border-gray-300 rounded text-gray-400 cursor-not-allowed">Next</span></li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Build URL with page parameter
     */
    private function buildUrl($page) {
        if (empty($this->baseUrl)) {
            $query = $_GET;
            $query['page'] = $page;
            return '?' . http_build_query($query);
        }
        
        // Parse existing query parameters from baseUrl and current GET params
        $urlParts = parse_url($this->baseUrl);
        $queryParams = [];
        
        // Get existing query params from baseUrl
        if (!empty($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);
        }
        
        // Merge with current GET params (excluding page)
        foreach ($_GET as $key => $value) {
            if ($key !== 'page' && !isset($queryParams[$key])) {
                $queryParams[$key] = $value;
            }
        }
        
        // Set the page parameter
        $queryParams['page'] = $page;
        
        // Rebuild URL
        $basePath = isset($urlParts['path']) ? $urlParts['path'] : $this->baseUrl;
        $separator = strpos($basePath, '?') !== false ? '&' : '?';
        return $basePath . $separator . http_build_query($queryParams);
    }
}

