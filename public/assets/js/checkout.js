/**
 * Checkout JavaScript
 * Handles address modal and form submission
 */

// Get SITE_URL from window (set by PHP) or fallback to detection
const SITE_URL = window.SITE_URL || (function() {
    const path = window.location.pathname;
    const match = path.match(/^(.+?)\/(?:checkout|cart|product|user|order|admin)/);
    if (match) {
        return window.location.origin + match[1];
    }
    if (path.includes('/kid-bazar-ecom')) {
        return window.location.origin + '/kid-bazar-ecom/public';
    }
    return window.location.origin;
})();

// Load address form when modal opens
function loadAddressForm() {
    const formContainer = document.getElementById('address-form-container');
    if (!formContainer) return;
    
    fetch(SITE_URL + '/user/address/add', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.form) {
            formContainer.innerHTML = data.form;
        } else {
            formContainer.innerHTML = '<p class="text-red-600">Failed to load form. Please refresh the page.</p>';
        }
    })
    .catch(error => {
        console.error('Error loading address form:', error);
        formContainer.innerHTML = '<p class="text-red-600">Error loading form. Please refresh the page.</p>';
    });
}

// Open address modal
function openAddressModal() {
    const modal = document.getElementById('address-modal');
    if (modal) {
        modal.style.display = 'flex';
        loadAddressForm();
    }
}

// Close address modal
function closeAddressModal() {
    const modal = document.getElementById('address-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Save address
function saveAddress(event) {
    const form = document.getElementById('address-form');
    if (!form) {
        alert('Form not loaded. Please wait...');
        return;
    }
    
    const formData = new FormData(form);
    const errorsContainer = document.getElementById('address-form-errors');
    
    // Clear previous errors
    if (errorsContainer) {
        errorsContainer.classList.add('hidden');
        errorsContainer.innerHTML = '';
    }
    
    // Show loading state
    const saveButton = event ? event.target : document.querySelector('button[onclick="saveAddress();"]');
    const originalText = saveButton ? saveButton.textContent : 'Save Address';
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';
    }
    
    fetch(SITE_URL + '/user/address/add', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Address added successfully!');
            // Reload page to show the new address
            window.location.reload();
        } else {
            // Show errors
            if (errorsContainer) {
                let errorHtml = '<ul class="list-disc list-inside space-y-1">';
                if (data.errors && Array.isArray(data.errors)) {
                    data.errors.forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                } else if (data.message) {
                    errorHtml += `<li>${data.message}</li>`;
                }
                errorHtml += '</ul>';
                errorsContainer.innerHTML = errorHtml;
                errorsContainer.classList.remove('hidden');
            } else {
                alert(data.message || 'Failed to save address. Please try again.');
            }
        }
    })
    .catch(error => {
        console.error('Error saving address:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        if (saveButton) {
            saveButton.disabled = false;
            saveButton.textContent = originalText;
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if modal should be shown
    const modal = document.getElementById('address-modal');
    if (modal) {
        // Load form immediately if modal is visible
        loadAddressForm();
        
        // Prevent modal from closing when clicking outside (only allow Close button)
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                // Do nothing - prevent closing on outside click
                e.stopPropagation();
            }
        });
    }
});

