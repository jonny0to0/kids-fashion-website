<div class="container mx-auto px-6 py-8">
    <div class="mb-6 flex items-center gap-4">
        <a href="<?php echo SITE_URL; ?>/admin/support" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Ticket #<?php echo $ticket['ticket_id']; ?></h1>
        
        <!-- Status Badge -->
        <?php
        $statusClass = 'bg-gray-100 text-gray-800';
        if ($ticket['status'] === 'open') $statusClass = 'bg-green-100 text-green-800';
        if ($ticket['status'] === 'in_progress') $statusClass = 'bg-yellow-100 text-yellow-800';
        if ($ticket['status'] === 'resolved') $statusClass = 'bg-blue-100 text-blue-800';
        if ($ticket['status'] === 'closed') $statusClass = 'bg-gray-100 text-gray-800';
        ?>
        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
            <?php echo str_replace('_', ' ', ucfirst($ticket['status'])); ?>
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Chat Area -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Info Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-4">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <?php echo ucfirst($ticket['category']); ?>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php echo date('M d, Y h:i A', strtotime($ticket['created_at'])); ?>
                    </span>
                    <?php if($ticket['order_id']): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/orders/view/<?php echo $ticket['order_id']; ?>" class="flex items-center gap-1 text-blue-600 hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Order #<?php echo $ticket['order_id']; ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messages -->
            <div class="space-y-4">
                <?php foreach($messages as $msg): ?>
                    <?php $isAdmin = $msg['is_admin_reply'] == 1; ?>
                    <div class="flex <?php echo $isAdmin ? 'justify-end' : 'justify-start'; ?>">
                        <div class="flex <?php echo $isAdmin ? 'flex-row-reverse' : 'flex-row'; ?> gap-3 max-w-3/4">
                            <!-- Avatar -->
                            <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-white font-bold <?php echo $isAdmin ? 'bg-pink-500' : 'bg-gray-400'; ?>">
                                <?php echo $isAdmin ? 'A' : substr($ticket['first_name'], 0, 1); ?>
                            </div>
                            
                            <!-- Message Bubble -->
                            <div class="<?php echo $isAdmin ? 'bg-pink-500 text-white' : 'bg-white border border-gray-200 text-gray-800'; ?> rounded-lg p-4 shadow-sm">
                                <div class="text-xs <?php echo $isAdmin ? 'text-pink-100' : 'text-gray-500'; ?> mb-1">
                                    <?php echo $isAdmin ? 'Support Agent' : htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?> • <?php echo date('M d, h:i A', strtotime($msg['created_at'])); ?>
                                </div>
                                <div class="prose <?php echo $isAdmin ? 'prose-invert' : ''; ?> max-w-none text-sm">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Reply Box -->
            <?php if ($ticket['status'] !== 'closed'): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Post Reply</h3>
                <form action="<?php echo SITE_URL; ?>/admin/support/reply" method="POST">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                    
                    <div class="mb-4">
                        <textarea name="message" rows="4" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-pink-500" placeholder="Type your reply here..." required></textarea>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Update Status:</label>
                            <select name="status" class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                                <option value="" selected>Don't change</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition-colors font-medium">
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-500">
                This ticket is closed. Reopen it to reply.
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Customer Details</h3>
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xl font-bold">
                        <?php echo substr($ticket['first_name'], 0, 1); ?>
                    </div>
                    <div class="ml-3">
                        <div class="text-gray-900 font-medium"><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></div>
                        <div class="text-gray-500 text-sm">Customer since <?php echo date('Y'); ?></div>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <a href="mailto:<?php echo $ticket['email']; ?>" class="hover:text-pink-600"><?php echo htmlspecialchars($ticket['email']); ?></a>
                    </div>
                    <?php if($ticket['phone']): ?>
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <?php echo htmlspecialchars($ticket['phone']); ?>
                    </div>
                    <?php endif; ?>
                    <div class="pt-4 mt-4 border-t border-gray-100">
                         <!-- TODO: Add proper link to user profile -->
                        <a href="#" class="text-pink-600 hover:text-pink-700 text-sm font-medium">View User Profile &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Ticket Controls -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Management</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select id="statusSelect" class="w-full border border-gray-300 rounded p-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                            <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Priority</label>
                        <select id="prioritySelect" class="w-full border border-gray-300 rounded p-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                            <option value="low" <?php echo $ticket['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo $ticket['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo $ticket['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo $ticket['priority'] === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('statusSelect').addEventListener('change', function(e) {
    updateTicket('status', e.target.value);
});

document.getElementById('prioritySelect').addEventListener('change', function(e) {
    updateTicket('priority', e.target.value);
});

function updateTicket(field, value) {
    const ticketId = <?php echo $ticket['ticket_id']; ?>;
    
    fetch('<?php echo SITE_URL; ?>/admin/support/update_ticket', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ticket_id=${ticketId}&field=${field}&value=${value}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated',
                text: 'Ticket ' + field + ' updated successfully',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            // Reload if status changed to closed or something visual needs update
            if (field === 'status') {
                 setTimeout(() => location.reload(), 1000);
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update ticket'
            });
        }
    });
}
</script>
