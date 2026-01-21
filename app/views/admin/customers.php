<?php
$pageTitle = 'Manage Customers';
?>


<?php
require_once __DIR__ . '/_breadcrumb.php';

renderBreadcrumb([
    ['label' => 'Home', 'url' => '/admin'],
    ['label' => 'Customers', 'url' => '/admin/customers'],
    ['label' => 'Manage Customers']
]);
?>

<div class="admin-card mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manage Customers</h2>
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/customers" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search by name, email, or phone" 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 w-full sm:w-64">
            <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium">Search</button>
            <?php if (!empty($search)): ?>
                <a href="<?php echo SITE_URL; ?>/admin/customers" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-50 text-center">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8 overflow-x-auto">
            <?php 
            $statuses = [
                '' => 'All Customers', 
                'active' => 'Active', 
                'suspended' => 'Suspended', 
                'deactivated' => 'Deactivated'
            ];
            $currentStatus = $_GET['status'] ?? '';
            foreach ($statuses as $key => $label): 
                $isActive = (string)$currentStatus === (string)$key;
                $url = '?status=' . $key . (!empty($search) ? '&search=' . urlencode($search) : '');
            ?>
                <a href="<?php echo $url; ?>" class="<?php echo $isActive ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    
    <div class="mb-4 text-sm text-gray-600">
        Total Customers: <span class="font-semibold"><?php echo number_format($total); ?></span>
    </div>
    
    <?php if (!empty($customers)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-semibold">
                                        <?php echo strtoupper(substr($customer['name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($customer['name'] ?? 'N/A'); ?></div>
                                        <div class="text-sm text-gray-500">ID: <?php echo $customer['user_id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></div>
                                <?php if (!empty($customer['phone'])): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo number_format($customer['order_count'] ?? 0); ?> orders
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status = $customer['status'] ?? 'active';
                                ?>
                                <div class="flex items-center">
                                    <?php if ($status === 'suspended'): ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 flex items-center gap-1">
                                            Suspended
                                            <svg class="w-3 h-3 cursor-help" title="<?php echo htmlspecialchars($customer['suspension_reason'] ?? 'No reason provided'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </span>
                                    <?php else: ?>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer status-toggle" 
                                                   data-user-id="<?php echo $customer['user_id']; ?>" 
                                                   data-current-status="<?php echo $status; ?>"
                                                   <?php echo $status === 'active' ? 'checked' : ''; ?>>
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-pink-600"></div>
                                        </label>
                                        <span class="ml-2 text-xs text-gray-500"><?php echo ucfirst($status); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <?php if ($status === 'suspended'): ?>
                                        <button onclick="changeStatus('<?php echo $customer['user_id']; ?>', 'active')" class="text-green-600 hover:text-green-900">Reactivate</button>
                                    <?php else: ?>
                                        <button onclick="openSuspendModal('<?php echo $customer['user_id']; ?>')" class="text-orange-600 hover:text-orange-900">Suspend</button>
                                    <?php endif; ?>
                                    <button onclick='openDetailsModal(<?php echo json_encode($customer); ?>)' class="text-indigo-600 hover:text-indigo-900">Details</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total > $perPage): ?>
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <?php echo (($page - 1) * $perPage) + 1; ?> to <?php echo min($page * $perPage, $total); ?> of <?php echo number_format($total); ?> customers
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($page * $perPage < $total): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No customers found</h3>
            <p class="mt-1 text-sm text-gray-500">
                <?php echo !empty($search) ? 'Try adjusting your search criteria.' : 'No customers have registered yet.'; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Suspend Customer</h3>
            <form id="suspendForm" class="mt-4 text-left">
                <input type="hidden" id="suspendUserId" name="user_id">
                <input type="hidden" name="status" value="suspended">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Reason Code</label>
                    <select name="reason_code" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Select a reason...</option>
                        <option value="FRAUD_ORDER">Fake / Fraudulent Orders</option>
                        <option value="PAYMENT_ABUSE">Chargeback / Payment Misuse</option>
                        <option value="REVIEW_SPAM">Fake Reviews</option>
                        <option value="POLICY_VIOLATION">T&C Violation</option>
                        <option value="MULTIPLE_ACCOUNTS">Duplicate Accounts</option>
                        <option value="ABUSE_SUPPORT">Support Abuse</option>
                        <option value="AUTO_RISK">Auto Risk Engine</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Detailed Notes</label>
                    <textarea name="reason" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Internal explanation..." required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Evidence (Optional)</label>
                    <textarea name="evidence" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Logs, Order IDs, Links..."></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Duration (Days)</label>
                    <input type="number" name="duration" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Optional (Leave empty for indefinite)">
                </div>
                
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="closeSuspendModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">Suspend</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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

                <!-- Suspension Audit Log -->
                <div id="detailsSuspensionSection" class="hidden">
                    <div class="border-t border-gray-100 pt-6">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Suspension Audit Log
                        </h4>
                        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                            <div class="max-h-48 overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailsSuspensionHistory" class="bg-white divide-y divide-gray-200">
                                        <!-- Populated by JS -->
                                    </tbody>
                                    <tfoot id="detailsSuspensionLoading" class="hidden">
                                        <tr><td colspan="4" class="px-3 py-2 text-center text-xs text-gray-500">Loading history...</td></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Actions Footer -->
            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="closeDetailsModal()" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm">
                    Close
                </button>
                <div id="modalActions">
                    <!-- JS will populate Suspend/Reactivate buttons here based on status -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Open Redesigned Details Modal
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

    // 4. Audit Log Logic (Preserved)
    const auditSection = document.getElementById('detailsSuspensionSection');
    const loading = document.getElementById('detailsSuspensionLoading');
    const list = document.getElementById('detailsSuspensionHistory');
    
    // Always clear previous history
    list.innerHTML = '';
    auditSection.classList.add('hidden'); // Default hidden

    // Load Audit Log
    auditSection.classList.remove('hidden');
    loading.classList.remove('hidden');

    loadSuspensionHistory(customer.user_id, list, loading);

    // 5. Actions Footer
    const actionsContainer = document.getElementById('modalActions');
    let actionButtons = '';
    
    if (customer.status === 'suspended') {
        actionButtons = `
            <button onclick="changeStatus('${customer.user_id}', 'active')" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:text-sm">
                Reactivate Account
            </button>`;
    } else {
        actionButtons = `
            <button onclick="closeDetailsModal(); openSuspendModal('${customer.user_id}')" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:text-sm">
                Suspend Account
            </button>`;
    }
    
    actionsContainer.innerHTML = actionButtons;

    // Show Modal
    document.getElementById('detailsModal').classList.remove('hidden');
}

function loadSuspensionHistory(userId, list, loading) {
    fetch(`<?php echo SITE_URL; ?>/admin/customer-history?user_id=${userId}`)
    .then(res => res.json())
    .then(data => {
        loading.classList.add('hidden');
        if (data.success && data.history && data.history.length > 0) {
            data.history.forEach(item => {
                const tr = document.createElement('tr');
                const date = new Date(item.created_at).toLocaleString();
                const reasonCode = item.reason_code ? `<span class="font-mono text-xs bg-gray-100 px-1 rounded">${item.reason_code}</span>` : '';
                const reasonText = item.reason_text || '-';
                
                let actionColor = 'text-gray-600';
                if (item.new_status === 'suspended') actionColor = 'text-red-600';
                if (item.new_status === 'active') actionColor = 'text-green-600';
                
                const action = `<span class="${actionColor} font-medium text-xs">${item.old_status} &rarr; ${item.new_status}</span>`;
                const admin = item.admin_name || 'System';
                
                tr.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">${date}</td>
                    <td class="px-3 py-2 whitespace-nowrap">${action}</td>
                    <td class="px-3 py-2 text-xs text-gray-700">
                        <div class="flex items-center gap-2">${reasonCode}</div>
                        <div class="truncate max-w-xs" title="${reasonText}">${reasonText}</div>
                        ${item.evidence_reference ? `<div class="text-gray-400 italic text-[10px] mt-1 truncate max-w-xs" title="${item.evidence_reference}">Ref: ${item.evidence_reference}</div>` : ''}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">${admin}</td>
                `;
                list.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="4" class="px-3 py-4 text-center text-sm text-gray-400 italic">No suspension history found.</td>';
            list.appendChild(tr);
        }
    })
    .catch(err => {
        console.error(err);
        loading.textContent = 'Failed to load history.';
    });
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}

function openSuspendModal(userId) {
    document.getElementById('suspendUserId').value = userId;
    document.getElementById('suspendModal').classList.remove('hidden');
}

function closeSuspendModal() {
    document.getElementById('suspendModal').classList.add('hidden');
}

function changeStatus(userId, status, reason = null, duration = null) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', status);
    if (reason) formData.append('reason', reason);
    if (duration) formData.append('duration', duration);

    // Use kebab-case for URL to map correctly to updateCustomerStatus in AdminController
    fetch('<?php echo SITE_URL; ?>/admin/update-customer-status', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text || 'Network response was not ok') });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Customer status updated successfully.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error updating status'
            }).then(() => {
                // Revert toggle if it was a toggle change
                if (status === 'active' || status === 'deactivated') {
                    window.location.reload(); 
                }
            });
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred: ' + error.message
        });
        // Only reload if we really need to, but for toggle sync it helps
        if (status === 'active' || status === 'deactivated') {
            window.location.reload();
        }
    });
}

// Toggle Switch Listener
document.querySelectorAll('.status-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const userId = this.getAttribute('data-user-id');
        const isChecked = this.checked;
        const newStatus = isChecked ? 'active' : 'deactivated';
        
        // Confirmation for deactivation
        if (newStatus === 'deactivated') {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will deactivate the customer account. They will not be able to login.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, deactivate user!'
            }).then((result) => {
                if (result.isConfirmed) {
                    changeStatus(userId, newStatus);
                } else {
                    this.checked = !isChecked; // Revert
                }
            });
        } else {
             changeStatus(userId, newStatus);
        }
    });
});

// Suspend Form Listener
document.getElementById('suspendForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Use kebab-case for URL to map correctly to updateCustomerStatus in AdminController
    fetch('<?php echo SITE_URL; ?>/admin/update-customer-status', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text || 'Network response was not ok') });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Suspended!',
                text: 'Customer has been suspended.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error updating status'
            });
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred: ' + error.message
        });
    });
});
</script>

