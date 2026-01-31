
<!-- HTML2PDF Library (Required for PDF generation) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;600;700&display=swap');

    /* Invoice Paper Styling */
    #invoice {
        background-color: #fff;
        width: 210mm; /* A4 width */
        min-height: 297mm; /* A4 height */
        padding: 15mm;
        position: relative;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        margin: 0 auto;
        box-sizing: border-box;
    }

    /* Screen Styles */
    @media screen {
        .invoice-container {
            padding: 2rem;
            display: flex;
            justify-content: center;
        }
    }

    /* PDF Generation Mode */
    .pdf-mode #invoice {
        box-shadow: none;
        margin: 0 auto;
        width: 210mm !important;
        max-width: 210mm !important;
    }

    /* Print Specifics - Critical for hiding admin interface */
    @media print {
        body * {
            visibility: hidden;
        }
        #invoice, #invoice * {
            visibility: visible;
        }
        #invoice {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
        }
        
        /* Explicitly hide admin elements */
        .admin-sidebar, .admin-top-header, .no-print {
            display: none !important;
        }
    }

    /* Typography & Layout overrides */
    .mono-font { font-family: 'Courier Prime', monospace; }
    .utilitarian-text { font-size: 0.85rem; line-height: 1.4; }
    
    /* Horizontal dividers */
    .divider { border-bottom: 1px solid #000; margin: 15px 0; }
    .divider-light { border-bottom: 0.5px solid #ccc; margin: 10px 0; }

    /* Table Styling */
    .invoice-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .invoice-table th {
        text-align: left;
        border-bottom: 1px solid #000;
        padding: 8px 4px;
        text-transform: uppercase;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    .invoice-table td {
        padding: 8px 4px;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }
    
    .footer-text { font-size: 0.65rem; color: #444; }

    /* Utility Helpers */
    .text-right { text-align: right !important; }
    .text-center { text-align: center !important; }
    .text-left { text-align: left !important; }
    .font-bold { font-weight: 700; }
    .font-semibold { font-weight: 600; }
    .text-sm { font-size: 0.875rem; }
    .text-xs { font-size: 0.75rem; }
</style>

<!-- Action Buttons Bar -->
<div class="mb-6 flex justify-between items-center no-print px-4 md:px-0 max-w-[210mm] mx-auto">
    <a href="<?php echo SITE_URL; ?>/admin/orders/<?php echo $order['order_id']; ?>" 
       class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 font-medium transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Order
    </a>
    
    <button onclick="generatePDF()" 
            class="inline-flex items-center gap-2 bg-pink-600 text-white px-5 py-2.5 rounded-lg hover:bg-pink-700 shadow-sm font-medium transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        Download PDF
    </button>
</div>

<!-- Invoice Document -->
<div class="invoice-container">
    <div id="invoice" class="mx-auto bg-white">
        
        <!-- Main Layout Table -->
        <table style="width: 100%; border: none; border-collapse: collapse;">
            
            <!-- Modern Header Row -->
            <tr>
                <td style="vertical-align: top; width: 50%; padding-bottom: 40px;">
                    <!-- Brand / Company Info -->
                    <div style="border-left: 4px solid #000; padding-left: 15px;">
                        <h1 class="text-3xl font-black tracking-tight uppercase mb-2 leading-none">Syntrex</h1>
                        <div class="text-xs text-gray-500 leading-relaxed font-mono">
                            Syntrex Ecom Dev<br>
                            123 Commerce St, Digital City<br>
                            GSTIN: 29ABCDE1234F1Z5
                        </div>
                    </div>
                </td>
                <td style="vertical-align: top; width: 50%; text-align: right; padding-bottom: 40px;">
                    <!-- Invoice Details -->
                    <div class="relative">
                        <h2 class="text-6xl font-bold text-gray-200 absolute -top-4 right-0 select-none z-0 tracking-tighter" style="opacity: 0.5;">INVOICE</h2>
                        <div class="relative z-10 pt-4">
                            <p class="font-mono font-bold text-xl text-black">#<?php echo htmlspecialchars($order['order_number']); ?></p>
                            <p class="text-sm text-gray-500 mb-3"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            
                            <div style="display: inline-block; text-align: right;">
                                <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=<?php echo htmlspecialchars($order['order_number']); ?>&scale=2&height=5&includetext=false&color=000000" 
                                     alt="Barcode" 
                                     style="height: 24px; width: auto; max-width: 150px; opacity: 0.7;">
                            </div>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- Divider Row -->
            <tr>
                <td colspan="2">
                    <div class="divider"></div>
                </td>
            </tr>

            <!-- Meta Info Row (Bill To / Ship To / Dates) -->
            <tr>
                <td colspan="2" style="padding-bottom: 30px;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <!-- Left Column: Bill To -->
                            <td style="vertical-align: top; width: 50%; padding-right: 20px;">
                                <div>
                                    <h3 class="font-bold text-xs uppercase text-gray-500 mb-1">Bill To</h3>
                                    <p class="font-semibold"><?php echo htmlspecialchars($billingAddress['full_name'] ?? 'N/A'); ?></p>
                                    <p class="text-xs text-gray-600 w-3/4">
                                        <?php echo htmlspecialchars($billingAddress['address_line1'] ?? ''); ?><br>
                                        <?php if(!empty($billingAddress['address_line2'])) echo htmlspecialchars($billingAddress['address_line2']) . '<br>'; ?>
                                        <?php echo htmlspecialchars($billingAddress['city'] ?? ''); ?>, <?php echo htmlspecialchars($billingAddress['state'] ?? ''); ?> - <?php echo htmlspecialchars($billingAddress['pincode'] ?? ''); ?>
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">Ph: <?php echo htmlspecialchars($billingAddress['phone'] ?? 'N/A'); ?></p>
                                </div>
                            </td>

                            <!-- Right Column: Ship To -->
                            <td style="vertical-align: top; width: 50%;">
                                <div>
                                    <h3 class="font-bold text-xs uppercase text-gray-500 mb-1">Ship To</h3>
                                    <p class="font-semibold"><?php echo htmlspecialchars($shippingAddress['full_name'] ?? 'N/A'); ?></p>
                                    <p class="text-xs text-gray-600 w-3/4">
                                        <?php echo htmlspecialchars($shippingAddress['address_line1'] ?? ''); ?><br>
                                        <?php if(!empty($shippingAddress['address_line2'])) echo htmlspecialchars($shippingAddress['address_line2']) . '<br>'; ?>
                                        <?php echo htmlspecialchars($shippingAddress['city'] ?? ''); ?>, <?php echo htmlspecialchars($shippingAddress['state'] ?? ''); ?> - <?php echo htmlspecialchars($shippingAddress['pincode'] ?? ''); ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Order Items Row -->
            <tr>
                <td colspan="2" style="padding-bottom: 20px;">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th class="text-center w-12">#</th>
                                <th class="text-center">Item Description</th>
                                <th class="text-center w-24">Qty</th>
                                <th class="text-center w-32">Price</th>
                                <th class="text-center w-32">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($items as $item): ?>
                            <tr>
                                <td class="text-center text-gray-500"><?php echo $i++; ?></td>
                                <td>
                                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                    <?php if(!empty($item['sku'])): ?>
                                        <p class="font-mono text-xs text-gray-500">SKU: <?php echo htmlspecialchars($item['sku']); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-center">₹<?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-center font-medium">₹<?php echo number_format($item['total'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
            </tr>

            <!-- Totals Row -->
            <tr>
                <td colspan="2" style="padding-bottom: 40px;">
                     <!-- Right Aligned Totals Table -->
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 50%;"></td> <!-- Spacer -->
                            <td style="width: 50%;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td class="text-gray-600 py-2 border-b border-gray-100 text-sm">Subtotal</td>
                                        <td class="font-medium text-right py-2 border-b border-gray-100 text-sm">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                    </tr>

                                    <?php if($order['discount_amount'] > 0): ?>
                                    <tr>
                                        <td class="text-gray-600 py-2 border-b border-gray-100 text-sm pl-2">Discount</td>
                                        <td class="text-gray-900 text-right py-2 border-b border-gray-100 text-sm">-₹<?php echo number_format($order['discount_amount'], 2); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if($order['shipping_amount'] > 0): ?>
                                    <tr>
                                        <td class="text-gray-600 py-2 border-b border-gray-100 text-sm">Shipping</td>
                                        <td class="font-medium text-right py-2 border-b border-gray-100 text-sm">₹<?php echo number_format($order['shipping_amount'], 2); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if($order['tax_amount'] > 0): ?>
                                    <tr>
                                        <td class="text-gray-600 py-2 border-b border-gray-100 text-sm">Tax</td>
                                        <td class="font-medium text-right py-2 border-b border-gray-100 text-sm">₹<?php echo number_format($order['tax_amount'], 2); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <tr>
                                        <td class="font-bold uppercase tracking-wide py-3 border-b-2 border-black text-base mt-2">Total</td>
                                        <td class="font-bold text-right py-3 border-b-2 border-black text-base mt-2">₹<?php echo number_format($order['final_amount'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-xs text-gray-500 text-right mt-1">All amounts in INR</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Footer Row -->
             <tr>
                <td colspan="2" style="vertical-align: bottom;">
                    <table style="width: 100%;">
                        <tr>
                            <!-- QR Code -->
                            <td style="text-align: right; width: 100%; vertical-align: bottom;">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?php echo urlencode(SITE_URL . '/order/track/' . $order['order_number']); ?>&color=000000" 
                                     alt="QR" class="w-20 h-20 mix-blend-multiply opacity-90 inline-block">
                            </td>
                        </tr>
                        <tr>
                             <td colspan="2">
                                <div class="divider-light mt-8"></div>
                             </td>
                        </tr>
                        <tr>
                            <td class="footer-text">Thank you for your business.</td>
                            <td class="footer-text text-right"><span class="font-bold tracking-tight">SYNTREX</span></td>
                        </tr>
                    </table>
                </td>
             </tr>

        </table>

    </div>
</div>

<script>
    function generatePDF() {
        document.body.classList.add('pdf-mode');
        
        const element = document.getElementById('invoice');
        const opt = {
            margin:       [0, 0, 0, 0],
            filename:     'Invoice_<?php echo htmlspecialchars($order['order_number']); ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, 
                useCORS: true,
                scrollY: 0
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            document.body.classList.remove('pdf-mode');
        });
    }
</script>
