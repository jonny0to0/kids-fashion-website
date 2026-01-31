<?php
$pageTitle = 'Settings';
$sections = [
    'general' => ['name' => 'General Settings', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
    'store' => ['name' => 'Store Settings', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    'payment' => ['name' => 'Payment Settings', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
    'shipping' => ['name' => 'Shipping & Delivery', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
    'tax' => ['name' => 'Tax Configuration', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
    'notification' => ['name' => 'Notification Settings', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
    'seo' => ['name' => 'SEO & Analytics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    'security' => ['name' => 'Security Settings', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
    'integration' => ['name' => 'Integrations', 'icon' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a1 1 0 01-1-1V9a1 1 0 011-1h1a2 2 0 100-4H4a1 1 0 01-1-1V4a1 1 0 011-1h3a1 1 0 011 1v1z'],
    'maintenance' => ['name' => 'Maintenance Mode', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
    'backup' => ['name' => 'Backup & Maintenance', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4']
];

$currentSettings = $settingsByGroup[$activeSection] ?? [];

// Include Breadcrumb Component
require_once __DIR__ . '/_breadcrumb.php';

// Generate breadcrumbs
$breadcrumbs = [
    ['label' => 'Home', 'url' => '/admin'],
    ['label' => 'Settings', 'url' => '/admin/settings']
];

// Add section breadcrumb
if (empty($activeSubsection)) {
    $breadcrumbs[] = ['label' => $sections[$activeSection]['name'] ?? 'Settings'];
} else {
    $breadcrumbs[] = ['label' => $sections[$activeSection]['name'] ?? 'Settings', 'url' => '/admin/settings?section=' . $activeSection];
    
    // Add subsection breadcrumb
    $subsectionLabel = ucfirst($activeSubsection);
    if ($activeSection === 'shipping') {
        if ($activeSubsection === 'zones') $subsectionLabel = 'Shipping Zones';
        if ($activeSubsection === 'delivery') $subsectionLabel = 'Delivery Configuration';
    }
    $breadcrumbs[] = ['label' => $subsectionLabel];
}
?>

<div>
    <!-- Content Area -->
    <div class="w-full">
        <!-- Add Breadcrumb -->
        <?php renderBreadcrumb($breadcrumbs); ?>

        <div class="admin-card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800"><?php echo $sections[$activeSection]['name'] ?? 'Settings'; ?></h2>
            </div>
            
            <?php if ($activeSection === 'shipping'): ?>
                <?php include 'settings/sections/shipping.php'; ?>
            <?php else: ?>
                <form id="settings-form-<?php echo $activeSection; ?>" class="settings-form" data-group="<?php echo $activeSection; ?>" enctype="multipart/form-data">
                    <?php if ($activeSection === 'general'): ?>
                        <?php include 'settings/sections/general.php'; ?>
                    <?php elseif ($activeSection === 'store'): ?>
                        <?php include 'settings/sections/store.php'; ?>
                    <?php elseif ($activeSection === 'payment'): ?>
                        <?php include 'settings/sections/payment.php'; ?>
                    <?php elseif ($activeSection === 'tax'): ?>
                        <?php include 'settings/sections/tax.php'; ?>
                    <?php elseif ($activeSection === 'notification'): ?>
                        <?php include 'settings/sections/notification.php'; ?>
                    <?php elseif ($activeSection === 'seo'): ?>
                        <?php include 'settings/sections/seo.php'; ?>
                    <?php elseif ($activeSection === 'security'): ?>
                        <?php include 'settings/sections/security.php'; ?>
                    <?php elseif ($activeSection === 'integration'): ?>
                        <?php include 'settings/sections/integration.php'; ?>
                    <?php elseif ($activeSection === 'maintenance'): ?>
                        <?php include 'settings/sections/maintenance.php'; ?>
                    <?php elseif ($activeSection === 'backup'): ?>
                        <?php include 'settings/sections/backup.php'; ?>
                    <?php endif; ?>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200 flex flex-col md:flex-row">
                        <button type="submit" class="btn-pink-gradient px-6 py-2.5 rounded-lg font-medium w-full md:w-auto mb-3 md:mb-0">
                            <span class="save-text">Save Changes</span>
                            <span class="save-loading hidden">Saving...</span>
                        </button>
                        <button type="button" class="px-6 py-2.5 rounded-lg font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 w-full md:w-auto md:ml-3" onclick="location.reload()">
                            Cancel
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/admin-settings.js"></script>

