<?php
/**
 * Maintenance Mode Settings Section
 * 
 * Allows admins to:
 * - Enable/disable maintenance mode
 * - Set maintenance message
 * - Set ETA and end time
 * - Configure auto-disable timeout
 * - Set support email and status page URL
 * - Whitelist IP addresses
 */

$settings = $currentSettings;

// Get current maintenance status
$maintenanceEnabled = isset($settings['maintenance_mode_enabled']['value']) ? (bool) $settings['maintenance_mode_enabled']['value'] : false;
$maintenanceMessage = isset($settings['maintenance_message']['value']) ? $settings['maintenance_message']['value'] : 'We\'re performing scheduled maintenance to improve performance and security. We\'ll be back shortly!';
$maintenanceReason = isset($settings['maintenance_reason']['value']) ? $settings['maintenance_reason']['value'] : 'Scheduled maintenance';
$maintenanceEta = isset($settings['maintenance_eta']['value']) ? $settings['maintenance_eta']['value'] : '';
$maintenanceEndTime = isset($settings['maintenance_end_time']['value']) ? $settings['maintenance_end_time']['value'] : '';
$autoDisableHours = isset($settings['maintenance_auto_disable_after']['value']) ? (int) $settings['maintenance_auto_disable_after']['value'] : 0;
$statusPageUrl = isset($settings['maintenance_status_page_url']['value']) ? $settings['maintenance_status_page_url']['value'] : '';
$supportEmail = isset($settings['maintenance_support_email']['value']) ? $settings['maintenance_support_email']['value'] : (isset($settings['support_email']['value']) ? $settings['support_email']['value'] : ADMIN_EMAIL);
$allowedIPs = isset($settings['maintenance_allowed_ips']['value']) ? $settings['maintenance_allowed_ips']['value'] : '';

// Get start time if available
$startTime = isset($settings['maintenance_start_time']['value']) ? $settings['maintenance_start_time']['value'] : '';
?>

<div class="space-y-6">
    <!-- Important Notice -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-yellow-800 mb-1">Maintenance Mode Rules</h3>
                <ul class="text-xs text-yellow-700 space-y-1 list-disc list-inside">
                    <li>Admin users will always have access to admin routes during maintenance</li>
                    <li>All non-admin users will see the maintenance page</li>
                    <li>Maintenance mode returns HTTP 503 (SEO-safe) with Retry-After header</li>
                    <li>Set an auto-disable timeout as a fail-safe measure</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Current Status -->
    <div
        class="bg-white border-2 <?php echo $maintenanceEnabled ? 'border-red-300 bg-red-50' : 'border-green-300 bg-green-50'; ?> rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3
                    class="text-lg font-semibold <?php echo $maintenanceEnabled ? 'text-red-800' : 'text-green-800'; ?> mb-1">
                    Maintenance Mode: <?php echo $maintenanceEnabled ? 'ENABLED' : 'DISABLED'; ?>
                </h3>
                <?php if ($maintenanceEnabled): ?>
                    <p class="text-sm text-red-700">
                        <?php if ($startTime): ?>
                            Started: <?php echo date('M j, Y g:i A', strtotime($startTime)); ?>
                        <?php else: ?>
                            Currently active
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p class="text-sm text-green-700">Your site is live and accessible to all users.</p>
                <?php endif; ?>
            </div>
            <div>
                <button type="button" id="toggle-maintenance-btn"
                    class="px-6 py-2.5 rounded-lg font-medium <?php echo $maintenanceEnabled ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white'; ?> transition-colors">
                    <?php echo $maintenanceEnabled ? 'Disable Maintenance' : 'Enable Maintenance'; ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Enable/Disable -->
    <input type="hidden" name="settings[maintenance_mode_enabled]" id="maintenance_mode_enabled"
        value="<?php echo $maintenanceEnabled ? '1' : '0'; ?>">

    <!-- Maintenance Message -->
    <div>
        <label for="maintenance_message" class="block text-sm font-medium text-gray-700 mb-2">
            Maintenance Message <span class="text-red-500">*</span>
        </label>
        <textarea name="settings[maintenance_message]" id="maintenance_message" rows="4"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
            placeholder="We're performing scheduled maintenance to improve performance and security. We'll be back shortly!"><?php echo htmlspecialchars($maintenanceMessage); ?></textarea>
        <p class="text-xs text-gray-500 mt-1">This message will be shown to users. Keep it friendly and non-technical.
        </p>
    </div>

    <!-- Maintenance Reason -->
    <div>
        <label for="maintenance_reason" class="block text-sm font-medium text-gray-700 mb-2">
            Reason (Non-Technical) <span class="text-red-500">*</span>
        </label>
        <input type="text" name="settings[maintenance_reason]" id="maintenance_reason"
            value="<?php echo htmlspecialchars($maintenanceReason); ?>"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
            placeholder="Scheduled maintenance">
        <p class="text-xs text-gray-500 mt-1">Short, user-friendly reason (e.g., "Scheduled maintenance", "Performance
            upgrade")</p>
    </div>

    <!-- ETA and End Time -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="maintenance_eta" class="block text-sm font-medium text-gray-700 mb-2">
                Estimated Time (ETA)
            </label>
            <input type="text" name="settings[maintenance_eta]" id="maintenance_eta"
                value="<?php echo htmlspecialchars($maintenanceEta); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                placeholder="2 hours, 30 minutes, etc.">
            <p class="text-xs text-gray-500 mt-1">Human-readable format (e.g., "2 hours", "30 minutes")</p>
        </div>

        <div>
            <label for="maintenance_end_time" class="block text-sm font-medium text-gray-700 mb-2">
                Expected End Time
            </label>
            <input type="datetime-local" name="settings[maintenance_end_time]" id="maintenance_end_time"
                value="<?php echo $maintenanceEndTime ? date('Y-m-d\TH:i', strtotime($maintenanceEndTime)) : ''; ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">When maintenance is expected to complete (for Retry-After header)</p>
        </div>
    </div>

    <!-- Auto-Disable Timeout (Fail-Safe) -->
    <div>
        <label for="maintenance_auto_disable_after" class="block text-sm font-medium text-gray-700 mb-2">
            Auto-Disable After (Hours) <span class="text-gray-400">(Fail-Safe)</span>
        </label>
        <input type="number" name="settings[maintenance_auto_disable_after]" id="maintenance_auto_disable_after"
            value="<?php echo $autoDisableHours; ?>" min="0" step="1"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
            placeholder="0 = disabled">
        <p class="text-xs text-gray-500 mt-1">Automatically disable maintenance after X hours (0 = disabled).
            Recommended: 6-12 hours as fail-safe.</p>
    </div>

    <!-- Support Email -->
    <div>
        <label for="maintenance_support_email" class="block text-sm font-medium text-gray-700 mb-2">
            Support Email During Maintenance
        </label>
        <input type="email" name="settings[maintenance_support_email]" id="maintenance_support_email"
            value="<?php echo htmlspecialchars($supportEmail); ?>"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
            placeholder="support@example.com">
        <p class="text-xs text-gray-500 mt-1">Email address shown on maintenance page (falls back to general support
            email if empty)</p>
    </div>

    <!-- Status Page URL -->
    <div>
        <label for="maintenance_status_page_url" class="block text-sm font-medium text-gray-700 mb-2">
            Status Page URL (Optional)
        </label>
        <input type="url" name="settings[maintenance_status_page_url]" id="maintenance_status_page_url"
            value="<?php echo htmlspecialchars($statusPageUrl); ?>"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
            placeholder="https://status.example.com">
        <p class="text-xs text-gray-500 mt-1">Link to external status page (e.g., StatusPage.io, UptimeRobot)</p>
    </div>

    <!-- Allowed IPs -->
    <div>
        <label for="maintenance_allowed_ips" class="block text-sm font-medium text-gray-700 mb-2">
            Allowed IP Addresses (Optional)
        </label>
        <input type="text" name="settings[maintenance_allowed_ips]" id="maintenance_allowed_ips"
            value="<?php echo htmlspecialchars($allowedIPs); ?>"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
            placeholder="192.168.1.1, 10.0.0.1">
        <p class="text-xs text-gray-500 mt-1">Comma-separated IP addresses that can access the site during maintenance
            (in addition to admins)</p>
    </div>

    <!-- Info Box -->
    <div class="bg-pink-50 border border-pink-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-pink-800 mb-2">Maintenance Mode Best Practices</h3>
        <ul class="text-xs text-pink-700 space-y-1 list-disc list-inside">
            <li>Always announce maintenance at least 24-72 hours in advance</li>
            <li>Choose lowest traffic hours for maintenance windows</li>
            <li>Provide clear ETA and update it if delayed</li>
            <li>Use auto-disable timeout as a fail-safe (recommended: 6-12 hours)</li>
            <li>Keep messages user-friendly and non-technical</li>
            <li>Admin users will always have access - test your changes safely</li>
        </ul>
    </div>
</div>

<script>
    // Toggle maintenance mode button handler
    document.getElementById('toggle-maintenance-btn')?.addEventListener('click', function () {
        const isEnabled = document.getElementById('maintenance_mode_enabled').value === '1';
        const newStatus = !isEnabled;
        const action = newStatus ? 'enable' : 'disable';

        if (confirm(`Are you sure you want to ${action} maintenance mode?` +
            (newStatus ? '\n\nRemember: Admin users will still have access to admin routes.' : ''))) {
            document.getElementById('maintenance_mode_enabled').value = newStatus ? '1' : '0';

            // Update button appearance
            this.textContent = newStatus ? 'Disable Maintenance' : 'Enable Maintenance';
            this.className = this.className.replace(newStatus ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700',
                newStatus ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700');

            // Update status box
            const statusBox = document.querySelector('.bg-white.border-2');
            if (statusBox) {
                if (newStatus) {
                    statusBox.className = statusBox.className.replace('border-green-300 bg-green-50', 'border-red-300 bg-red-50');
                    statusBox.querySelector('h3').textContent = 'Maintenance Mode: ENABLED';
                    statusBox.querySelector('h3').className = 'text-lg font-semibold text-red-800 mb-1';
                    statusBox.querySelector('p').textContent = 'Started: ' + new Date().toLocaleString();
                    statusBox.querySelector('p').className = 'text-sm text-red-700';
                } else {
                    statusBox.className = statusBox.className.replace('border-red-300 bg-red-50', 'border-green-300 bg-green-50');
                    statusBox.querySelector('h3').textContent = 'Maintenance Mode: DISABLED';
                    statusBox.querySelector('h3').className = 'text-lg font-semibold text-green-800 mb-1';
                    statusBox.querySelector('p').textContent = 'Your site is live and accessible to all users.';
                    statusBox.querySelector('p').className = 'text-sm text-green-700';
                }
            }

            // Ensure the hidden input is always in the form and has the correct value
            const hiddenInput = document.getElementById('maintenance_mode_enabled');
            if (hiddenInput) {
                hiddenInput.value = newStatus ? '1' : '0';
                // Force the input to be included in form submission
                hiddenInput.setAttribute('name', 'settings[maintenance_mode_enabled]');
            }

            // Auto-save if enabled (or prompt user to save)
            // For now, just update the form field - user needs to click "Save Changes"
        }
    });

    // Calculate end time from ETA when ETA changes
    document.getElementById('maintenance_eta')?.addEventListener('change', function () {
        const eta = this.value.trim();
        const endTimeInput = document.getElementById('maintenance_end_time');

        if (eta && !endTimeInput.value) {
            // Try to parse ETA and calculate end time
            const etaLower = eta.toLowerCase();
            let hoursToAdd = 1; // Default: 1 hour

            if (etaLower.match(/\d+\s*(hour|hr|h)/)) {
                const match = etaLower.match(/(\d+)\s*(hour|hr|h)/);
                hoursToAdd = parseInt(match[1]);
            } else if (etaLower.match(/\d+\s*(minute|min|m)/)) {
                const match = etaLower.match(/(\d+)\s*(minute|min|m)/);
                hoursToAdd = parseInt(match[1]) / 60; // Convert minutes to hours
            }

            const endTime = new Date();
            endTime.setHours(endTime.getHours() + Math.ceil(hoursToAdd));

            // Format as datetime-local input value
            const year = endTime.getFullYear();
            const month = String(endTime.getMonth() + 1).padStart(2, '0');
            const day = String(endTime.getDate()).padStart(2, '0');
            const hours = String(endTime.getHours()).padStart(2, '0');
            const minutes = String(endTime.getMinutes()).padStart(2, '0');

            endTimeInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    });
</script>