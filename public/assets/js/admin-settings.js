/**
 * Admin Settings Management
 * Handles AJAX form submissions, validation, and connection testing
 */

(function() {
    'use strict';
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        // Handle form submissions
        const forms = document.querySelectorAll('.settings-form');
        forms.forEach(form => {
            form.addEventListener('submit', handleFormSubmit);
        });
        
        // Handle connection test buttons
        const testButtons = document.querySelectorAll('.test-connection-btn');
        testButtons.forEach(btn => {
            btn.addEventListener('click', handleTestConnection);
        });
        
        // Handle encrypted field changes - clear masked values
        const encryptedInputs = document.querySelectorAll('input[type="text"][value*="••••"], input[type="password"][value*="••••"]');
        encryptedInputs.forEach(input => {
            input.addEventListener('focus', function() {
                if (this.value === '••••••••••••') {
                    this.value = '';
                }
            });
        });
    }
    
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const group = form.dataset.group;
        const submitBtn = form.querySelector('button[type="submit"]');
        const saveText = submitBtn.querySelector('.save-text');
        const saveLoading = submitBtn.querySelector('.save-loading');
        
        // Validate form
        if (!validateForm(form)) {
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        if (saveText) saveText.classList.add('hidden');
        if (saveLoading) saveLoading.classList.remove('hidden');
        
        // Prepare form data
        const submitData = new FormData();
        submitData.append('group', group);
        
        // Process all form inputs
        const inputs = form.querySelectorAll('input, select, textarea');
        const processedRadioGroups = new Set(); // Track processed radio button groups
        
        inputs.forEach(input => {
            if (input.name && input.name.startsWith('settings[')) {
                if (input.type === 'file') {
                    // Handle file inputs
                    if (input.files && input.files.length > 0) {
                        submitData.append(input.name, input.files[0]);
                    }
                } else if (input.type === 'checkbox') {
                    // Handle checkboxes
                    submitData.append(input.name, input.checked ? '1' : '0');
                } else if (input.type === 'radio') {
                    // CRITICAL FIX: Only include checked radio buttons
                    // This ensures logo_type is correctly submitted
                    if (input.checked) {
                        submitData.append(input.name, input.value || '');
                        processedRadioGroups.add(input.name); // Mark this group as processed
                    }
                } else {
                    // Handle text, number, email, select, textarea
                    submitData.append(input.name, input.value || '');
                }
            }
        });
        
        // Submit via AJAX
        fetch(`${window.SITE_URL}/admin/settings/save`, {
            method: 'POST',
            body: submitData
        })
        .then(response => {
            // Check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showSuccess(data.message || 'Settings saved successfully');
                // Reload page after short delay to show updated values
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Show detailed error message
                let errorMessage = data.message || 'Failed to save settings';
                if (data.error) {
                    // Show technical details in development mode (check console for full error)
                    console.error('Settings save error details:', data.error);
                    if (data.errors && Array.isArray(data.errors)) {
                        console.error('Individual errors:', data.errors);
                    }
                }
                showError(errorMessage);
                submitBtn.disabled = false;
                if (saveText) saveText.classList.remove('hidden');
                if (saveLoading) saveLoading.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Settings save error:', error);
            let errorMessage = 'An error occurred while saving settings';
            
            // Provide more specific error messages
            if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                errorMessage = 'Network error: Could not connect to server. Please check your internet connection.';
            } else if (error.message.includes('HTTP error')) {
                errorMessage = 'Server error: The server returned an error. Please try again later.';
            } else if (error.message) {
                errorMessage = 'Error: ' + error.message;
            }
            
            showError(errorMessage);
            submitBtn.disabled = false;
            if (saveText) saveText.classList.remove('hidden');
            if (saveLoading) saveLoading.classList.add('hidden');
        });
    }
    
    function validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('border-red-500');
                isValid = false;
            } else {
                input.classList.remove('border-red-500');
            }
        });
        
        // Validate email fields
        const emailInputs = form.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            if (input.value && !isValidEmail(input.value)) {
                input.classList.add('border-red-500');
                isValid = false;
                showError(`Invalid email format: ${input.value}`);
            } else {
                input.classList.remove('border-red-500');
            }
        });
        
        // Validate number fields
        const numberInputs = form.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            const min = parseFloat(input.getAttribute('min'));
            const max = parseFloat(input.getAttribute('max'));
            const value = parseFloat(input.value);
            
            if (input.value && !isNaN(value)) {
                if (min !== null && value < min) {
                    input.classList.add('border-red-500');
                    isValid = false;
                    showError(`${input.previousElementSibling?.textContent || 'Field'} must be at least ${min}`);
                } else if (max !== null && value > max) {
                    input.classList.add('border-red-500');
                    isValid = false;
                    showError(`${input.previousElementSibling?.textContent || 'Field'} must be at most ${max}`);
                } else {
                    input.classList.remove('border-red-500');
                }
            }
        });
        
        return isValid;
    }
    
    function handleTestConnection(e) {
        e.preventDefault();
        
        const btn = e.target;
        const integration = btn.dataset.integration;
        const originalText = btn.textContent;
        
        btn.disabled = true;
        btn.textContent = 'Testing...';
        
        fetch(`${window.SITE_URL}/admin/settings/test-connection`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `integration=${encodeURIComponent(integration)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message || 'Connection successful');
            } else {
                showError(data.message || 'Connection failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('An error occurred while testing connection');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
        });
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert('Success: ' + message);
        }
    }
    
    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000
            });
        } else {
            alert('Error: ' + message);
        }
    }
})();

