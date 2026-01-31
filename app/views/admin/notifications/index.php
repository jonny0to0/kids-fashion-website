<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
            <p class="text-gray-600 text-sm mt-1">Manage all your system alerts and notifications</p>
        </div>
        
        <div class="flex gap-2">
            <button id="page-mark-all-read" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Mark All as Read
            </button>
            
            <button id="page-delete-all" class="bg-white border border-gray-300 text-red-600 px-4 py-2 rounded-lg font-medium hover:bg-red-50 transition-colors flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Delete All
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="overflow-x-auto">
            <div class="flex border-b border-gray-200">
                <?php 
                $tabs = [
                    'all' => 'All Notifications',
                    'order' => 'Orders', 
                    'user' => 'Users', 
                    'system' => 'System',
                    'product' => 'Products'
                ];
                $k = 0;
                foreach ($tabs as $key => $label): 
                    $active = ($currentFilter === $key) || ($key === 'all' && empty($currentFilter));
                    $bg = $active ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                ?>
                <a href="?type=<?php echo $key === 'all' ? '' : $key; ?>" 
                   class="<?php echo $bg; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-16">
                <div class="bg-gray-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No Notifications</h3>
                <p class="text-gray-500 mt-1 max-w-sm mx-auto">You're all caught up! There are no notifications to display in this category.</p>
                <div class="mt-6">
                    <a href="?type=" class="text-pink-600 hover:text-pink-700 font-medium">View all notifications</a>
                </div>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($notifications as $notification): ?>
                    <?php 
                        $isRead = (int)$notification['is_read'] === 1;
                        $bgClass = $isRead ? 'bg-white' : 'bg-blue-50/50';
                        $priority = $notification['priority'] ?? 'medium';
                        
                        // Priority Badge Styles
                        $priorityBadge = '';
                        switch($priority) {
                            case 'critical':
                                $priorityBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 uppercase tracking-wide">Critical</span>';
                                break;
                            case 'high':
                                $priorityBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 uppercase tracking-wide">High</span>';
                                break;
                            case 'low':
                                $priorityBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 uppercase tracking-wide">Low</span>';
                                break;
                            default:
                                $priorityBadge = ''; // Medium is default, no badge needed or maybe blue
                        }
                    ?>
                    <div class="p-5 hover:bg-gray-50 transition-colors <?php echo $bgClass; ?> group relative cursor-pointer" id="notification-<?php echo $notification['notification_id']; ?>" 
                         onclick='handleNotificationClick(event, {
                             id: <?php echo $notification['notification_id']; ?>,
                             type: "<?php echo $notification['type']; ?>",
                             related_id: "<?php echo $notification['related_id'] ?? ""; ?>",
                             link: "<?php echo $notification['link'] ?? ""; ?>",
                             is_read: <?php echo $isRead ? "true" : "false"; ?>
                         })'>
                        <div class="flex items-start gap-4">
                            <!-- Icon based on type/priority -->
                            <div class="flex-shrink-0 mt-1">
                                <?php if ($priority === 'critical' || $priority === 'high'): ?>
                                    <div class="p-2 bg-red-50 rounded-full">
                                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
                                <?php elseif ($notification['type'] === 'order'): ?>
                                    <div class="p-2 bg-green-50 rounded-full">
                                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                <?php elseif ($notification['type'] === 'user'): ?>
                                    <div class="p-2 bg-indigo-50 rounded-full">
                                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                <?php elseif ($notification['type'] === 'product'): ?>
                                     <div class="p-2 bg-yellow-50 rounded-full">
                                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="p-2 bg-gray-100 rounded-full">
                                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-semibold text-gray-900 group-hover:text-pink-600 transition-colors">
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                        </h4>
                                        <?php if (!$isRead): ?>
                                            <span class="inline-block w-2 h-2 bg-pink-500 rounded-full"></span>
                                        <?php endif; ?>
                                        <?php echo $priorityBadge; ?>
                                    </div>
                                    <span class="text-xs text-gray-500 whitespace-nowrap ml-4 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <?php echo date('M d, H:i', strtotime($notification['created_at'])); ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                
                                <div class="mt-3 flex gap-4 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                    <?php if (!$isRead): ?>
                                        <button onclick="event.stopPropagation(); markAsRead(<?php echo $notification['notification_id']; ?>)" class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Mark as read
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo SITE_URL; ?>/admin/notifications-delete/<?php echo $notification['notification_id']; ?>" 
                                       onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this notification?')"
                                       class="text-red-500 hover:text-red-700 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination->getTotalPages() > 1): ?>
                <div class="px-6 py-4 border-t border-gray-100">
                    <?php $pagination->render(); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Customer Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDetailsModal()"></div>

        <!-- Modal Panel -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            <!-- 1. Header Section -->
            <div class="bg-white px-6 py-5 border-b border-gray-100 flex justify-between items-start">
                <div class="flex items-center gap-4">
                    <div id="detailAvatar" class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-100 to-white border border-indigo-50 flex items-center justify-center text-xl text-indigo-600 font-bold shadow-sm">
                        <!-- JS will populate -->
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900" id="detailName">
                            <!-- JS will populate -->
                        </h3>
                        <div class="mt-1 flex items-center gap-2">
                            <span id="detailId" class="text-xs text-gray-400 font-mono">ID: #</span>
                            <span id="detailStatusBadge"></span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeDetailsModal()" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- 2. Summary Stats Row -->
            <div class="grid grid-cols-3 divide-x divide-gray-100 border-b border-gray-100 bg-gray-50/50">
                <div class="px-6 py-4 text-center">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</dt>
                    <dd class="mt-1 text-lg font-bold text-gray-900" id="detailOrderCount">-</dd>
                </div>
                <div class="px-6 py-4 text-center">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spend</dt>
                    <dd class="mt-1 text-lg font-bold text-indigo-600" id="detailTotalSpend">-</dd>
                </div>
                <div class="px-6 py-4 text-center">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Last Order</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900" id="detailLastOrder">-</dd>
                </div>
            </div>

            <!-- 3. Customer Details Section -->
            <div class="px-6 py-6 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Contact Info
                        </h4>
                        <dl class="space-y-2 text-sm">
                            <div class="flex flex-col">
                                <dt class="text-xs text-gray-500">Email Address</dt>
                                <dd class="font-medium text-gray-900 truncate" id="detailEmail">-</dd>
                            </div>
                            <div class="flex flex-col">
                                <dt class="text-xs text-gray-500">Phone Number</dt>
                                <dd class="font-medium text-gray-900" id="detailPhone">-</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Account Activity
                        </h4>
                        <dl class="space-y-2 text-sm">
                            <div class="flex flex-col">
                                <dt class="text-xs text-gray-500">Registered On</dt>
                                <dd class="font-medium text-gray-900" id="detailJoined">-</dd>
                            </div>
                            <div class="flex flex-col">
                                <dt class="text-xs text-gray-500">Last Login</dt>
                                <dd class="font-medium text-gray-900">Not tracked</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- 5. Actions Footer -->
            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="closeDetailsModal()" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm">
                    Close
                </button>
                <div id="modalActions">
                   <a id="detailViewProfileBtn" href="#" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:text-sm">
                        View Full Profile
                   </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle Notification Click
    function handleNotificationClick(event, notification) {
        // notification object: { id, type, related_id, link, is_read }
        event.preventDefault(); // Stop default navigation

        // 1. Mark as read immediately (UI optimistic update)
        if (!notification.is_read) {
             markAsRead(notification.id);
        }

        // 2. Logic based on type
        if (notification.type === 'user' && notification.related_id) {
            // Open Modal via AJAX
            fetchCustomerDetails(notification.related_id);
        } else {
            // Default Redirect
            if (notification.link) {
                window.location.href = '<?php echo SITE_URL; ?>' + notification.link;
            } else {
                 // Fallback if no link
                 window.location.reload(); 
            }
        }
    }

    // Fetch and show customer details
    function fetchCustomerDetails(userId) {
        // Show loading state or modal skeleton if desired
        // For now, let's just fetch
        
        fetch('<?php echo SITE_URL; ?>/admin/customer-details/' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openDetailsModal(data.customer);
            } else {
                console.error('Failed to load customer details');
            }
        })
        .catch(err => console.error(err));
    }

    // Populate and Open Modal
    function openDetailsModal(customer) {
        // 1. Header
        const avatar = document.getElementById('detailAvatar');
        avatar.textContent = (customer.name || 'U').charAt(0).toUpperCase();

        document.getElementById('detailName').textContent = customer.name || 'Unknown';
        document.getElementById('detailId').textContent = 'ID: #' + customer.user_id;

        // Status Badge
        const statusBadges = {
            'active': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>',
            'suspended': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Suspended</span>',
            'deactivated': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Deactivated</span>',
            'deleted': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-900 text-white">Deleted</span>'
        };
        document.getElementById('detailStatusBadge').innerHTML = statusBadges[customer.status] || statusBadges['active'];

        // 2. Summary Stats
        document.getElementById('detailOrderCount').textContent = customer.order_count || '0';
        
        const formatter = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 0
        });
        document.getElementById('detailTotalSpend').textContent = (customer.total_spend) ? formatter.format(customer.total_spend) : '₹0';
        
        document.getElementById('detailLastOrder').textContent = customer.last_order_date 
            ? new Date(customer.last_order_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })
            : '-';

        // 3. Details
        document.getElementById('detailEmail').textContent = customer.email || '-';
        document.getElementById('detailPhone').textContent = customer.phone || '-';
        document.getElementById('detailPhone').classList.toggle('text-gray-400', !customer.phone);
        
        document.getElementById('detailJoined').textContent = customer.created_at 
            ? new Date(customer.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })
            : '-';
            
        // Update View Profile Link
        document.getElementById('detailViewProfileBtn').href = '<?php echo SITE_URL; ?>/admin/customers?customer_id=' + customer.user_id;

        // Show Modal
        document.getElementById('detailsModal').classList.remove('hidden');
    }
    
    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }


    // Mark single notification as read
    function markAsRead(id) {
        fetch('<?php echo SITE_URL; ?>/admin/notifications-mark-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI without reload
                const el = document.getElementById('notification-' + id);
                if (el) {
                    el.classList.remove('bg-blue-50/50');
                    el.classList.add('bg-white');
                    const badge = el.querySelector('.bg-pink-500'); // Dot
                    if (badge) badge.remove();
                    
                    // Remove button
                    const btn = el.querySelector('button[onclick^="markAsRead"]');
                    if (btn) btn.remove();
                }
                
                // Update header badge if exists
                const headerBadge = document.querySelector('[data-notification-badge]');
                if (headerBadge) {
                    if (data.unread_count > 0) {
                        headerBadge.classList.remove('hidden');
                        headerBadge.textContent = data.unread_count; // If count is shown
                    } else {
                        headerBadge.classList.add('hidden');
                    }
                }
            }
        });
    }

    // Mark all as read button on page
    document.getElementById('page-mark-all-read')?.addEventListener('click', function() {
        if (!confirm('Mark all notifications as read?')) return;
        
        const btn = this;
        btn.disabled = true;
        btn.classList.add('opacity-50');

        fetch('<?php echo SITE_URL; ?>/admin/notifications-mark-all-read', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to mark all as read');
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            }
        })
        .catch(err => {
             console.error(err);
             btn.disabled = false;
             btn.classList.remove('opacity-50');
        });
    });
    // Delete all notifications
    document.getElementById('page-delete-all')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete ALL notifications. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = this;
                btn.disabled = true;
                btn.classList.add('opacity-50');

                fetch('<?php echo SITE_URL; ?>/admin/notifications-delete-all', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Deleted!',
                            'All notifications have been deleted.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            'Failed to delete notifications: ' + (data.error || 'Unknown error'),
                            'error'
                        );
                        btn.disabled = false;
                        btn.classList.remove('opacity-50');
                    }
                })
                .catch(err => {
                     console.error(err);
                     Swal.fire(
                        'Error!',
                        'Network error occurred',
                        'error'
                     );
                     btn.disabled = false;
                     btn.classList.remove('opacity-50');
                });
            }
        });
    });
</script>
