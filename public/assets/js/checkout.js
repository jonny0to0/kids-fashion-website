document.addEventListener('DOMContentLoaded', function () {
    const shippingRadios = document.querySelectorAll('input[name="shipping_method_id"]');
    const displayElement = document.getElementById('shipping-cost-display');
    const totalElement = document.querySelector('.font-bold.text-lg span:last-child'); // Basic selector, might need refinement

    // Check if we found the total element (it should contain "₹...")
    // If selector is weak, let's try to be more specific or look for a unique layout logic if possible.
    // Based on view: <div class="flex justify-between font-bold text-lg"><span>Total</span><span>₹...</span></div>

    // Function to parse currency string "₹1,234.00" -> 1234.00
    function parseCurrency(str) {
        return parseFloat(str.replace(/[^0-9.]/g, '')) || 0;
    }

    // Function to format currency 1234 -> "₹1,234.00"
    function formatCurrency(amount) {
        return '₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Get subtotal from the page (assuming it doesn't change here)
    // <div class="flex justify-between"><span>Subtotal</span><span>₹...</span></div>
    // Let's find the subtotal element.
    const summaryRows = document.querySelectorAll('.bg-white.rounded-lg.shadow-md.sticky .border-t .flex.justify-between');
    let subtotal = 0;

    // Iterate to find Subtotal row
    summaryRows.forEach(row => {
        if (row.children[0].textContent.trim() === 'Subtotal') {
            subtotal = parseCurrency(row.children[1].textContent);
        }
    });

    function updateTotal() {
        let shippingCost = 0;
        let codEnabled = true;
        let codLimit = 0;

        // select fresh radios in case DOM updated
        const currentShippingRadios = document.querySelectorAll('input[name="shipping_method_id"]');

        if (currentShippingRadios.length === 0) {
            if (displayElement) displayElement.textContent = '₹0.00';
            // Disable all payment methods if no shipping? Or just default behavior.
        }

        currentShippingRadios.forEach(radio => {
            if (radio.checked) {
                shippingCost = parseFloat(radio.dataset.cost) || 0;
                codEnabled = radio.dataset.codEnabled === '1';
                codLimit = parseFloat(radio.dataset.codLimit) || 0;
            }
        });

        // Update Shipping Display
        if (displayElement) {
            displayElement.textContent = shippingCost === 0 ? 'Free' : formatCurrency(shippingCost);
        }

        // Update Total Display
        const grandTotal = subtotal + shippingCost;
        if (totalElement) {
            totalElement.textContent = formatCurrency(grandTotal);
        }

        // Validate Payment Methods
        validatePaymentMethods(codEnabled, codLimit, grandTotal);
    }

    function validatePaymentMethods(codEnabled, codLimit, grandTotal) {
        const codInput = document.getElementById('payment_method_cod');
        const codOption = document.getElementById('cod-payment-option');
        const codMsg = document.getElementById('cod-unavailable-msg');
        const onlineInput = document.querySelector('input[name="payment_method"][value="online"]');
        // Note: the value might differ, using selector from view: value="<?php echo PAYMENT_METHOD_ONLINE; ?>" which is 'online' usually.
        // Or cleaner: document.querySelectorAll('input[name="payment_method"]')... but let's target COD specific.

        if (!codInput) return;

        let isDisabled = false;
        let reason = '';

        if (!codEnabled) {
            isDisabled = true;
            reason = 'Not available for selected shipping method';
        } else if (codLimit > 0 && grandTotal > codLimit) {
            isDisabled = true;
            reason = `Not available for orders above ${formatCurrency(codLimit)}`;
        }

        codInput.disabled = isDisabled;

        if (isDisabled) {
            codOption.classList.add('opacity-50', 'bg-gray-100');
            codOption.classList.remove('hover:bg-gray-50', 'cursor-pointer');
            codMsg.textContent = reason;
            codMsg.classList.remove('hidden');

            // If currently checked, uncheck and try to select another
            if (codInput.checked) {
                codInput.checked = false;
                // Try selecting online if available
                const onlineRadio = document.querySelector('input[name="payment_method"][value="online"]');
                if (onlineRadio) onlineRadio.checked = true;
            }
        } else {
            codOption.classList.remove('opacity-50', 'bg-gray-100');
            codOption.classList.add('hover:bg-gray-50', 'cursor-pointer');
            codMsg.classList.add('hidden');
        }
    }

    // Function to fetch shipping methods
    function fetchShippingMethods(addressId) {
        if (!addressId) return;

        const methodsContainer = document.getElementById('shipping-methods-container');
        const placeOrderBtn = document.getElementById('place-order-btn');

        // Show loading state
        methodsContainer.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-pink-600"></div></div>';

        // Disable button while loading
        if (placeOrderBtn) {
            placeOrderBtn.disabled = true;
            placeOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        fetch(`${window.SITE_URL}/checkout/get-shipping-methods?address_id=${addressId}`)
            .then(response => response.json())
            .then(data => {
                methodsContainer.innerHTML = '';

                if (data.methods && data.methods.length > 0) {
                    let html = '';
                    data.methods.forEach((method, index) => {
                        const cost = parseFloat(method.cost);
                        const displayCost = (method.pricing_type === 'free' || cost === 0) ? 'Free' : formatCurrency(cost);
                        const checked = index === 0 ? 'checked' : '';

                        // Parse values for display in template literal
                        const codEnabled = (method.cod_enabled === undefined || method.cod_enabled === true || method.cod_enabled === 'true') ? '1' : '0';
                        const codLimit = method.cod_limit || 0;

                        html += `
                            <label class="flex items-center justify-between border rounded p-2 cursor-pointer hover:bg-gray-50">
                                <div class="flex items-center">
                                    <input type="radio" name="shipping_method_id" value="${method.id}" 
                                           class="mr-2" ${checked}
                                           data-cost="${cost}"
                                           data-cod-enabled="${codEnabled}"
                                           data-cod-limit="${codLimit}">
                                    <span class="text-sm font-medium">${method.name}</span>
                                </div>
                                <span class="text-sm font-bold">${displayCost}</span>
                            </label>
                        `;
                    });
                    methodsContainer.innerHTML = html;

                    // Enable button
                    if (placeOrderBtn) {
                        placeOrderBtn.disabled = false;
                        placeOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }

                    // Re-attach listeners to new radios
                    const newRadios = methodsContainer.querySelectorAll('input[name="shipping_method_id"]');
                    newRadios.forEach(radio => {
                        radio.addEventListener('change', updateTotal);
                    });

                    // Update total with new default selection
                    updateTotal();
                } else {
                    methodsContainer.innerHTML = '<p class="text-sm text-red-500">No shipping methods available for this location.</p>';
                    // Keep button disabled
                    updateTotal(); // Will likely set shipping to 0
                }
            })
            .catch(error => {
                console.error('Error fetching shipping methods:', error);
                methodsContainer.innerHTML = '<p class="text-sm text-red-500">Error loading shipping methods. Please try again.</p>';
            });
    }

    // Add event listeners for address change
    const addressRadios = document.querySelectorAll('input[name="shipping_address_id"]');
    addressRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            fetchShippingMethods(this.value);
        });
    });
    // Add event listeners
    // Note: Re-selecting shippingRadios here might be redundant if we re-attach in fetchShippingMethods
    // but useful for initial load if not replaced.
    const initialShippingRadios = document.querySelectorAll('input[name="shipping_method_id"]');
    initialShippingRadios.forEach(radio => {
        radio.addEventListener('change', updateTotal);
    });

    // Initial calculation
    updateTotal();
});

// Function to open address modal
window.openAddressModal = function (addressId = null) {
    const modal = document.getElementById('address-modal');
    if (modal) {
        modal.style.display = 'flex';

        const formContainer = document.getElementById('address-form-container');
        const titleEl = modal.querySelector('h3');
        if (titleEl) {
            titleEl.textContent = addressId ? 'Edit Address' : 'Add New Address';
        }

        // Always fetch form to get clean state or filled data
        // For new address, we might want to cache, but for edit we need fresh data.
        // Let's just fetch always for simplicity and correctness.

        formContainer.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-pink-600"></div><p class="mt-2 text-gray-600">Loading form...</p></div>';

        if (window.SITE_URL) {
            const url = addressId
                ? `${window.SITE_URL}/user/address/add?id=${addressId}`
                : `${window.SITE_URL}/user/address/add`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.form) {
                        formContainer.innerHTML = data.form;
                    } else {
                        formContainer.innerHTML = `<p class="text-red-600 text-center">Failed to load form. ${data.message || ''}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error loading address form:', error);
                    formContainer.innerHTML = '<p class="text-red-600 text-center">An error occurred while loading the form.</p>';
                });
        } else {
            console.error('SITE_URL is not defined');
        }
    } else {
        console.error('Address modal with ID "address-modal" not found.');
    }
};

window.editAddress = function (id) {
    window.openAddressModal(id);
};

window.deleteAddress = function (id) {
    if (confirm('Are you sure you want to delete this address?')) {
        if (window.SITE_URL) {
            const formData = new FormData();
            formData.append('address_id', id);

            fetch(`${window.SITE_URL}/user/address/delete`, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to delete address');
                    }
                })
                .catch(error => {
                    console.error('Error deleting address:', error);
                    alert('An error occurred. Please try again.');
                });
        }
    }
};

window.closeAddressModal = function () {
    const modal = document.getElementById('address-modal');
    if (modal) modal.style.display = 'none';
};

// Function to save address
window.saveAddress = function (event) {
    if (event) event.preventDefault();

    const form = document.getElementById('address-form');
    if (!form) {
        console.error('Address form not found');
        return;
    }

    const errorContainer = document.getElementById('address-form-errors');
    if (errorContainer) {
        errorContainer.innerHTML = '';
        errorContainer.classList.add('hidden');
    }

    const formData = new FormData(form);

    // Validate Pincode Length
    const pincode = formData.get('pincode');
    if (pincode && pincode.length > 10) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Pincode',
                text: 'Pincode cannot remain more than 10 digits.',
                confirmButtonColor: '#d61f69'
            });
        } else {
            alert('Pincode cannot remain more than 10 digits.');
        }
        return;
    }

    fetch(`${window.SITE_URL}/user/address/add`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - Close modal and reload to show new address
                window.closeAddressModal();
                // Show success message or just reload
                window.location.reload();
            } else {
                // Show errors
                if (errorContainer) {
                    errorContainer.classList.remove('hidden');
                    if (data.errors && Array.isArray(data.errors)) {
                        errorContainer.innerHTML = data.errors.map(err => `<p>${err}</p>`).join('');
                    } else {
                        errorContainer.innerHTML = `<p>${data.message || 'Failed to save address'}</p>`;
                    }
                } else {
                    alert(data.message || 'Failed to save address');
                }
            }
        })
        .catch(error => {
            console.error('Error saving address:', error);
            if (errorContainer) {
                errorContainer.classList.remove('hidden');
                errorContainer.innerHTML = '<p>An error occurred. Please try again.</p>';
            } else {
                alert('An error occurred. Please try again.');
            }
        });
};
