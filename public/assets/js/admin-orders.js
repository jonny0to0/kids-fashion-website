/**
 * Admin Orders Management JavaScript
 * Handles AJAX operations for order status updates, payment status updates, and order cancellation
 */

(function() {
    'use strict';
    
    // Utility function to show notifications
    function showNotification(message, type = 'success') {
        // Check if there's a notification system (like toastr, or custom)
        if (typeof showToast === 'function') {
            showToast(message, type);
        } else {
            // Fallback to alert
            alert(message);
        }
        
        // Also try to use session flash messages if available
        if (type === 'success') {
            console.log('Success:', message);
        } else {
            console.error('Error:', message);
        }
    }
    
    // Utility function to show loading state
    function setLoadingState(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Updating...';
        } else {
            button.disabled = false;
            button.innerHTML = button.getAttribute('data-original-text') || 'Update';
        }
    }
    
    // Update Order Status
    const orderStatusSelect = document.getElementById('order-status-select');
    const updateOrderStatusBtn = document.getElementById('update-order-status-btn');
    
    if (orderStatusSelect && updateOrderStatusBtn) {
        // Enable/disable button based on selection
        orderStatusSelect.addEventListener('change', function() {
            updateOrderStatusBtn.disabled = !this.value || this.value === this.getAttribute('data-current-status');
            if (!updateOrderStatusBtn.getAttribute('data-original-text')) {
                updateOrderStatusBtn.setAttribute('data-original-text', updateOrderStatusBtn.textContent);
            }
        });
        
        // Handle status update
        updateOrderStatusBtn.addEventListener('click', async function() {
            const orderId = orderStatusSelect.getAttribute('data-order-id');
            const newStatus = orderStatusSelect.value;
            const currentStatus = orderStatusSelect.getAttribute('data-current-status');
            
            if (!newStatus || newStatus === currentStatus) {
                return;
            }
            
            // Optional: Show confirmation for certain status changes
            const criticalStatuses = ['cancelled', 'delivered'];
            if (criticalStatuses.includes(newStatus)) {
                const confirmed = confirm(`Are you sure you want to change the order status to "${newStatus}"?`);
                if (!confirmed) {
                    return;
                }
            }
            
            setLoadingState(this, true);
            
            try {
                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('status', newStatus);
                
                const siteUrl = window.SITE_URL || '';
                const response = await fetch(siteUrl + '/admin/update-order-status', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Reload page after 1 second to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Failed to update order status', 'error');
                    setLoadingState(this, false);
                }
            } catch (error) {
                console.error('Error updating order status:', error);
                showNotification('An error occurred while updating the order status', 'error');
                setLoadingState(this, false);
            }
        });
    }
    
    // Update Payment Status
    const paymentStatusSelect = document.getElementById('payment-status-select');
    const updatePaymentStatusBtn = document.getElementById('update-payment-status-btn');
    
    if (paymentStatusSelect && updatePaymentStatusBtn) {
        // Enable/disable button based on selection
        paymentStatusSelect.addEventListener('change', function() {
            updatePaymentStatusBtn.disabled = !this.value || this.value === this.getAttribute('data-current-status');
            if (!updatePaymentStatusBtn.getAttribute('data-original-text')) {
                updatePaymentStatusBtn.setAttribute('data-original-text', updatePaymentStatusBtn.textContent);
            }
        });
        
        // Handle payment status update
        updatePaymentStatusBtn.addEventListener('click', async function() {
            const orderId = paymentStatusSelect.getAttribute('data-order-id');
            const newStatus = paymentStatusSelect.value;
            const currentStatus = paymentStatusSelect.getAttribute('data-current-status');
            
            if (!newStatus || newStatus === currentStatus) {
                return;
            }
            
            // Optional: Show confirmation for certain status changes
            const criticalStatuses = ['refunded'];
            if (criticalStatuses.includes(newStatus)) {
                const confirmed = confirm(`Are you sure you want to change the payment status to "${newStatus}"?`);
                if (!confirmed) {
                    return;
                }
            }
            
            setLoadingState(this, true);
            
            try {
                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('status', newStatus);
                
                const siteUrl = window.SITE_URL || '';
                const response = await fetch(siteUrl + '/admin/update-payment-status', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Reload page after 1 second to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Failed to update payment status', 'error');
                    setLoadingState(this, false);
                }
            } catch (error) {
                console.error('Error updating payment status:', error);
                showNotification('An error occurred while updating the payment status', 'error');
                setLoadingState(this, false);
            }
        });
    }
    
    // Cancel Order
    const cancelOrderBtn = document.getElementById('cancel-order-btn');
    
    if (cancelOrderBtn) {
        cancelOrderBtn.addEventListener('click', async function() {
            const orderId = this.getAttribute('data-order-id');
            
            // Show confirmation modal
            const confirmed = confirm('Are you sure you want to cancel this order? This action cannot be undone.');
            if (!confirmed) {
                return;
            }
            
            // Optional: Ask for cancellation reason
            const notes = prompt('Please provide a reason for cancellation (optional):');
            
            setLoadingState(this, true);
            
            try {
                const formData = new FormData();
                formData.append('order_id', orderId);
                if (notes) {
                    formData.append('notes', notes);
                }
                
                const siteUrl = window.SITE_URL || '';
                const response = await fetch(siteUrl + '/admin/cancel-order', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Reload page after 1 second to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Failed to cancel order', 'error');
                    setLoadingState(this, false);
                }
            } catch (error) {
                console.error('Error cancelling order:', error);
                showNotification('An error occurred while cancelling the order', 'error');
                setLoadingState(this, false);
            }
        });
    }
    
    // Update Shipping Details
    const updateShippingForm = document.getElementById('update-shipping-form');
    
    if (updateShippingForm) {
        updateShippingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const orderId = formData.get('order_id');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
            
            try {
                const siteUrl = window.SITE_URL || '';
                const response = await fetch(siteUrl + '/admin/update-shipping-details', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Reload page after 1 second to show updated details
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Failed to update shipping details', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (error) {
                console.error('Error updating shipping details:', error);
                showNotification('An error occurred while updating shipping details', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Admin Orders Management initialized');
    });
})();

