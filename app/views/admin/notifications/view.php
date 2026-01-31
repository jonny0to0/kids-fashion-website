<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Notification Details</h1>
        <a href="<?php echo SITE_URL; ?>/admin/notifications" class="text-gray-600 hover:text-gray-900 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-start">
            <div class="flex gap-4">
                <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                    <?php if ($notification['type'] === 'order'): ?>
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    <?php elseif ($notification['type'] === 'user'): ?>
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    <?php elseif ($notification['priority'] === 'critical'): ?>
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($notification['title']); ?></h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Received on <?php echo date('M j, Y \a\t g:i A', strtotime($notification['created_at'])); ?>
                    </p>
                </div>
            </div>
            
            <?php if ($notification['priority'] === 'critical'): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    CRITICAL
                </span>
            <?php elseif ($notification['priority'] === 'high'): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    HIGH
                </span>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="p-8">
            <div class="prose max-w-none text-gray-800 leading-relaxed">
                <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
            </div>
            
            <?php if (!empty($notification['related_id'])): ?>
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-xs text-gray-400 font-mono">
                        Reference ID: <?php echo $notification['related_id']; ?> 
                        (Event: <?php echo $notification['event_name'] ?? 'N/A'; ?>)
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
