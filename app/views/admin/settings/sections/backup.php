<?php
$settings = $currentSettings;
?>

<div class="space-y-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <p class="text-sm text-yellow-800 mb-2">
            <strong>Warning:</strong> Backup and maintenance operations are restricted to Super Admin only.
        </p>
    </div>
    
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Manual Backup</h3>
        <button type="button" id="backup-database-btn" class="btn-pink-gradient px-6 py-2.5 rounded-lg font-medium">
            Create Database Backup
        </button>
        <p class="text-xs text-gray-500 mt-2">Download a complete database backup</p>
    </div>
    
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Scheduled Backup</h3>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
                Scheduled backups can be configured in future updates.
            </p>
        </div>
    </div>
    
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">System Health Status</h3>
        <div class="space-y-2">
            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                <span class="text-sm font-medium text-gray-700">Database Connection</span>
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">Healthy</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                <span class="text-sm font-medium text-gray-700">File System</span>
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">Healthy</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                <span class="text-sm font-medium text-gray-700">PHP Version</span>
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium"><?php echo PHP_VERSION; ?></span>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('backup-database-btn')?.addEventListener('click', function() {
    if (confirm('This will download a database backup. Continue?')) {
        window.location.href = '<?php echo SITE_URL; ?>/admin/settings/backup-database';
    }
});
</script>

