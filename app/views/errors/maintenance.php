<?php
/**
 * Maintenance Mode Page
 * 
 * Modern • Classic • Production-Ready Design
 * 
 * This page is part of the product, not a placeholder.
 * Follows PM/Architect specifications for user experience, trust, and brand continuity.
 */

// Ensure required helpers are loaded (may already be loaded from index.php)
if (!class_exists('MaintenanceMode')) {
    require_once APP_PATH . '/helpers/MaintenanceMode.php';
}
if (!class_exists('Settings')) {
    require_once APP_PATH . '/models/Settings.php';
}

// Get maintenance info from MaintenanceMode helper
// This page is only shown when maintenance is enabled, so we can safely get the info
$maintenanceInfo = MaintenanceMode::isEnabled();

// Fallback defaults if maintenance info is not available
if (empty($maintenanceInfo) || !is_array($maintenanceInfo)) {
    $maintenanceInfo = [
        'message' => 'We\'re performing scheduled maintenance to improve performance and security.',
        'reason' => 'Scheduled maintenance',
        'eta' => '',
        'status_page_url' => '',
        'support_email' => defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'support@kidsbazaar.com'
    ];
}

// Try to get settings if available (for logo, site name)
$siteName = defined('SITE_NAME') ? SITE_NAME : 'Kids Bazaar';
$storeLogo = '';

try {
    if (class_exists('Settings')) {
        $settingsModel = new Settings();
        $siteName = $settingsModel->get('store_name', $siteName);
        $storeLogo = $settingsModel->get('store_logo', '');
        
        // Override support email if maintenance-specific one is set
        $maintenanceEmail = $settingsModel->get('maintenance_support_email', '');
        if (!empty($maintenanceEmail)) {
            $maintenanceInfo['support_email'] = $maintenanceEmail;
        } else {
            $maintenanceInfo['support_email'] = $settingsModel->get('support_email', $maintenanceInfo['support_email']);
        }
        
        // Get status page URL if set
        $maintenanceInfo['status_page_url'] = $settingsModel->get('maintenance_status_page_url', '');
    }
} catch (Exception $e) {
    // Silently fail - use defaults
    error_log("Maintenance page settings error: " . $e->getMessage());
}

// Build logo URL
$logoUrl = '';
if (!empty($storeLogo)) {
    $logoUrl = SITE_URL . '/' . ltrim($storeLogo, '/');
    if (file_exists(PUBLIC_PATH . '/' . ltrim($storeLogo, '/'))) {
        $logoUrl .= '?v=' . filemtime(PUBLIC_PATH . '/' . ltrim($storeLogo, '/'));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - <?php echo htmlspecialchars($siteName); ?></title>
    <meta name="description" content="We're currently performing scheduled maintenance. We'll be back shortly!">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Modern, minimal design with soft gradients */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .maintenance-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 3rem 2rem;
            text-align: center;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Logo styling */
        .maintenance-logo {
            max-width: 150px;
            max-height: 80px;
            margin: 0 auto 2rem;
            display: block;
            object-fit: contain;
        }
        
        /* Title */
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        /* Message */
        .maintenance-message {
            font-size: 1.125rem;
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        
        /* ETA Section */
        .eta-section {
            background: #f7fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid #667eea;
        }
        
        .eta-label {
            font-size: 0.875rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .eta-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        /* Support Section */
        .support-section {
            margin-bottom: 2rem;
        }
        
        .support-title {
            font-size: 1rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 1rem;
        }
        
        .support-email {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border: 2px solid #667eea;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .support-email:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        /* Status Page Link */
        .status-link {
            display: inline-block;
            color: #718096;
            text-decoration: none;
            font-size: 0.875rem;
            margin-top: 1rem;
            transition: color 0.3s ease;
        }
        
        .status-link:hover {
            color: #667eea;
        }
        
        /* Footer */
        .maintenance-footer {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: 0.875rem;
        }
        
        /* Animated spinner/indicator */
        .maintenance-indicator {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Mobile responsiveness */
        @media (max-width: 640px) {
            .maintenance-container {
                padding: 2rem 1.5rem;
                border-radius: 16px;
            }
            
            .maintenance-title {
                font-size: 2rem;
            }
            
            .maintenance-message {
                font-size: 1rem;
            }
            
            .maintenance-logo {
                max-width: 120px;
                max-height: 60px;
            }
        }
        
        /* Accessibility: High contrast mode support */
        @media (prefers-contrast: high) {
            .maintenance-container {
                border: 2px solid #000;
            }
            
            .support-email {
                border-width: 3px;
            }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .maintenance-container,
            .support-email,
            .maintenance-indicator {
                animation: none;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container" role="main">
        <!-- Header: Brand Logo -->
        <?php if (!empty($logoUrl)): ?>
            <img src="<?php echo htmlspecialchars($logoUrl); ?>" 
                 alt="<?php echo htmlspecialchars($siteName); ?> Logo" 
                 class="maintenance-logo"
                 loading="eager">
        <?php else: ?>
            <h1 class="text-3xl font-bold text-gray-800 mb-2" style="margin-bottom: 2rem;">
                <?php echo htmlspecialchars($siteName); ?>
            </h1>
        <?php endif; ?>
        
        <!-- Main Message Section -->
        <h2 class="maintenance-title">We'll Be Right Back</h2>
        
        <p class="maintenance-message">
            <?php echo htmlspecialchars($maintenanceInfo['message']); ?>
        </p>
        
        <!-- ETA Section -->
        <?php if (!empty($maintenanceInfo['eta'])): ?>
            <div class="eta-section">
                <div class="eta-label">Estimated Completion</div>
                <div class="eta-value">
                    <span class="maintenance-indicator"></span>
                    <?php echo htmlspecialchars($maintenanceInfo['eta']); ?>
                </div>
            </div>
        <?php elseif (!empty($maintenanceInfo['end_time'])): ?>
            <?php
            $endTimestamp = strtotime($maintenanceInfo['end_time']);
            $now = time();
            $remainingSeconds = max(0, $endTimestamp - $now);
            
            if ($remainingSeconds > 0) {
                $hours = floor($remainingSeconds / 3600);
                $minutes = floor(($remainingSeconds % 3600) / 60);
                
                if ($hours > 0) {
                    $eta = $hours . ' hour' . ($hours > 1 ? 's' : '');
                    if ($minutes > 0) {
                        $eta .= ' and ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                    }
                } else {
                    $eta = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                }
            } else {
                $eta = 'Soon';
            }
            ?>
            <div class="eta-section">
                <div class="eta-label">Estimated Completion</div>
                <div class="eta-value">
                    <span class="maintenance-indicator"></span>
                    <?php echo htmlspecialchars($eta); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Support Section -->
        <div class="support-section">
            <div class="support-title">Need Help?</div>
            <a href="mailto:<?php echo htmlspecialchars($maintenanceInfo['support_email']); ?>" 
               class="support-email"
               aria-label="Contact support via email">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <?php echo htmlspecialchars($maintenanceInfo['support_email']); ?>
            </a>
        </div>
        
        <!-- Status Page Link (if available) -->
        <?php if (!empty($maintenanceInfo['status_page_url'])): ?>
            <a href="<?php echo htmlspecialchars($maintenanceInfo['status_page_url']); ?>" 
               class="status-link"
               target="_blank"
               rel="noopener noreferrer"
               aria-label="Check system status">
                Check live system status →
            </a>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="maintenance-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. All rights reserved.</p>
            <p class="mt-2 text-xs">Scheduled maintenance in progress</p>
        </div>
    </div>
    
    <!-- Auto-refresh script (optional - refreshes every 60 seconds) -->
    <script>
        // Auto-refresh every 60 seconds to check if maintenance is complete
        // Respects reduced motion preference
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (!prefersReducedMotion) {
            setTimeout(function() {
                window.location.reload();
            }, 60000); // 60 seconds
        }
    </script>
</body>
</html>

