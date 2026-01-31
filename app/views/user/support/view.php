<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <a href="<?php echo SITE_URL; ?>/support" class="text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Tickets
            </a>
            
             <!-- Status Badge -->
             <?php
            $statusClass = 'bg-gray-100 text-gray-800';
            if ($ticket['status'] === 'open') $statusClass = 'bg-green-100 text-green-800';
            if ($ticket['status'] === 'in_progress') $statusClass = 'bg-yellow-100 text-yellow-800';
            if ($ticket['status'] === 'resolved') $statusClass = 'bg-blue-100 text-blue-800';
            if ($ticket['status'] === 'closed') $statusClass = 'bg-gray-100 text-gray-800';
            ?>
            <span class="px-3 py-1 text-sm font-semibold rounded-full <?php echo $statusClass; ?>">
                <?php echo str_replace('_', ' ', ucfirst($ticket['status'])); ?>
            </span>
        </div>

        <!-- Ticket Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($ticket['subject']); ?></h1>
            <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <?php echo ucfirst($ticket['category']); ?>
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Ticket #<?php echo $ticket['ticket_id']; ?>
                </span>
                <span class="flex items-center gap-1">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Created: <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?>
                </span>
                <?php if($ticket['order_id']): ?>
                <span class="flex items-center gap-1 text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Order #<?php echo $ticket['order_id']; ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Messages -->
        <div class="space-y-6 mb-8">
            <?php foreach($messages as $msg): ?>
                <?php $isAdmin = $msg['is_admin_reply'] == 1; ?>
                <div class="flex <?php echo $isAdmin ? 'justify-start' : 'justify-end'; ?>">
                    <div class="flex <?php echo $isAdmin ? 'flex-row' : 'flex-row-reverse'; ?> gap-3 max-w-[85%] md:max-w-[75%]">
                        <!-- Avatar -->
                        <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-white font-bold <?php echo $isAdmin ? 'bg-pink-500' : 'bg-gray-400'; ?>">
                            <?php echo $isAdmin ? 'S' : substr($ticket['first_name'], 0, 1); ?>
                        </div>
                        
                        <!-- Message Bubble -->
                        <div>
                            <div class="mb-1 text-xs text-gray-500 <?php echo $isAdmin ? 'text-left' : 'text-right'; ?>">
                                <?php echo $isAdmin ? 'Support Agent' : 'You'; ?> • <?php echo date('M d, h:i A', strtotime($msg['created_at'])); ?>
                            </div>
                            <div class="<?php echo $isAdmin ? 'bg-white border border-gray-200 text-gray-800' : 'bg-pink-600 text-white'; ?> rounded-lg p-4 shadow-sm">
                                <div class="prose <?php echo $isAdmin ? '' : 'prose-invert'; ?> max-w-none text-sm whitespace-pre-wrap">
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Reply Section -->
        <?php if($ticket['status'] !== 'closed'): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reply to Ticket</h3>
            <form action="<?php echo SITE_URL; ?>/support/reply" method="POST">
                <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                
                <div class="mb-4">
                    <textarea name="message" rows="4" class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none focus:ring-2 focus:ring-pink-500" placeholder="Type your reply here..." required></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 transition-colors font-medium shadow-md">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 text-gray-500 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">This ticket is closed</h3>
            <p class="text-gray-500">You can no longer reply to this ticket. If you have a new issue, please create a new ticket.</p>
            <a href="<?php echo SITE_URL; ?>/support/create" class="inline-block mt-4 text-pink-600 hover:text-pink-700 font-medium">Create New Ticket &rarr;</a>
        </div>
        <?php endif; ?>
    </div>
</div>
