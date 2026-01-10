<?php
// Load dashboard logo from settings for login screen (dual type support)
$logoType = 'text';
$logoImageUrl = '';
$logoText = '';
$logoTextStyle = '';

try {
    require_once APP_PATH . '/models/Settings.php';
    $settingsModel = new Settings();
    
    // Get logo type
    $logoType = $settingsModel->get('logo_type', 'text');
    
    if ($logoType === 'image') {
        // Try new logo_image setting first, fallback to legacy dashboard_logo
        $logoImagePath = $settingsModel->get('logo_image', '');
        if (empty($logoImagePath)) {
            $logoImagePath = $settingsModel->get('dashboard_logo', '');
        }
        
        if (!empty($logoImagePath)) {
            $normalizedPath = '/' . ltrim($logoImagePath, '/');
            $logoImageUrl = SITE_URL . $normalizedPath;
            
            // Verify file exists and add cache busting
            $fullPath = PUBLIC_PATH . $normalizedPath;
            if (file_exists($fullPath)) {
                $version = filemtime($fullPath);
                $logoImageUrl .= '?v=' . $version;
            } else {
                // File doesn't exist, fallback to text logo
                $logoImageUrl = '';
                $logoType = 'text';
            }
        } else {
            $logoType = 'text';
        }
    }
    
    // Prepare text logo settings (for login screen, use header font size)
    if ($logoType === 'text' || empty($logoImageUrl)) {
        $logoText = $settingsModel->get('logo_text', '');
        if (empty($logoText)) {
            $logoText = $settingsModel->get('store_name', SITE_NAME);
        }
        
        // Use header font size for login screen
        $fontSize = (int)$settingsModel->get('logo_text_font_size_header', 18);
        $fontWeight = (int)$settingsModel->get('logo_text_font_weight', 600);
        $textColor = $settingsModel->get('logo_text_color', '#1e293b'); // Darker color for login page
        $maxWidth = (int)$settingsModel->get('logo_text_max_width', 200);
        
        $logoTextStyle = sprintf(
            'font-size: %dpx; font-weight: %d; color: %s; max-width: %dpx;',
            $fontSize + 4, // Slightly larger for login
            $fontWeight,
            htmlspecialchars($textColor),
            $maxWidth
        );
    }
    
} catch (Exception $e) {
    // Silently fail if settings can't be loaded - use fallback
    error_log("Login page logo error: " . $e->getMessage());
    $logoType = 'text';
    $logoText = SITE_NAME;
    $logoTextStyle = 'font-size: 22px; font-weight: 600; color: #1e293b;';
}
?>
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <?php if ($logoType === 'image' && !empty($logoImageUrl)): ?>
            <?php
            // Get dimension constraints for login screen (slightly larger)
            $settingsModel = new Settings();
            $maxHeight = (int)$settingsModel->get('logo_image_max_height', 60) + 20; // Add 20px for login
            $maxWidth = (int)$settingsModel->get('logo_image_max_width', 200) + 40; // Add 40px for login
            ?>
            <div class="text-center mb-6">
                <img src="<?php echo htmlspecialchars($logoImageUrl); ?>" 
                     alt="<?php echo htmlspecialchars(SITE_NAME); ?> Logo" 
                     class="w-auto mx-auto object-contain"
                     style="max-height: <?php echo $maxHeight; ?>px; max-width: <?php echo $maxWidth; ?>px; height: auto;">
            </div>
        <?php elseif ($logoType === 'text'): ?>
            <div class="text-center mb-6">
                <h1 class="font-bold mx-auto" style="<?php echo htmlspecialchars($logoTextStyle); ?>">
                    <?php echo htmlspecialchars($logoText); ?>
                </h1>
            </div>
        <?php endif; ?>
        <h2 class="text-3xl font-bold text-center mb-8">Login</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo SITE_URL; ?>/user/login">
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" required
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>
            
            <div class="mb-4 flex items-center">
                <input type="checkbox" name="remember" id="remember" class="mr-2">
                <label for="remember" class="text-gray-700">Remember me</label>
            </div>
            
            <button type="submit" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700 font-bold">
                Login
            </button>
        </form>
        
        <p class="text-center mt-4 text-gray-600">
            Don't have an account? <a href="<?php echo SITE_URL; ?>/user/register" class="text-pink-600 hover:underline">Sign up</a>
        </p>
    </div>
</div>

