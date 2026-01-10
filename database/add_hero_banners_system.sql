-- Hero Banners System
-- Enterprise-level hero banner management for e-commerce

CREATE TABLE IF NOT EXISTS hero_banners (
    banner_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    desktop_image VARCHAR(255) NOT NULL,
    mobile_image VARCHAR(255) NOT NULL,
    cta_text VARCHAR(100),
    cta_url VARCHAR(500),
    priority INT DEFAULT 0 COMMENT 'Higher priority appears first',
    status ENUM('active', 'inactive') DEFAULT 'active',
    device_visibility ENUM('desktop', 'mobile', 'both') DEFAULT 'both',
    target_type ENUM('homepage', 'category', 'campaign') DEFAULT 'homepage',
    target_id INT NULL COMMENT 'Category ID or Campaign ID based on target_type',
    start_date DATETIME NULL COMMENT 'Banner start date/time',
    end_date DATETIME NULL COMMENT 'Banner end date/time',
    display_order INT DEFAULT 0 COMMENT 'Manual ordering for drag-and-drop',
    auto_slide_enabled BOOLEAN DEFAULT TRUE,
    slide_duration INT DEFAULT 5000 COMMENT 'Auto-slide duration in milliseconds (4-6 seconds)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_priority (priority DESC, display_order ASC),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_target (target_type, target_id),
    INDEX idx_active_banners (status, start_date, end_date, priority, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample banners (optional - for testing)
INSERT INTO hero_banners (title, description, desktop_image, mobile_image, cta_text, cta_url, priority, status, device_visibility, target_type, display_order, auto_slide_enabled, slide_duration) VALUES
('Summer Sale 2024', 'Get up to 50% off on summer collection', '/assets/images/banners/summer-desktop.webp', '/assets/images/banners/summer-mobile.webp', 'Shop Now', '/product?category=summer', 10, 'active', 'both', 'homepage', 1, TRUE, 5000),
('New Arrivals', 'Check out our latest collection', '/assets/images/banners/new-arrivals-desktop.webp', '/assets/images/banners/new-arrivals-mobile.webp', 'Explore', '/product?filter=new', 8, 'active', 'both', 'homepage', 2, TRUE, 6000),
('Festival Special', 'Special offers for festive season', '/assets/images/banners/festival-desktop.webp', '/assets/images/banners/festival-mobile.webp', 'Buy Now', '/product?campaign=festival', 9, 'active', 'both', 'homepage', 3, TRUE, 5500)
ON DUPLICATE KEY UPDATE title=title;

