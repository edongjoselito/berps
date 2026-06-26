<?php
$delivery = isset($delivery) ? $delivery : null;
$data2 = isset($data2) ? $data2 : array();
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid delivery-entry-page">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <style>
                        .delivery-entry-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-strong: #ffffff;
                            --surface-soft: #f8fbff;
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --primary-2: #1d4ed8;
                            --primary-soft: #eaf2ff;
                            --success: #059669;
                            --danger: #e11d48;
                            --warning: #d97706;
                            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
                            --shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.05);
                            --radius-xl: 24px;
                            --radius-lg: 18px;
                            --radius-md: 14px;
                            --radius-sm: 10px;
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            --font-mono: var(--font-primary);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 28px;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .delivery-entry-page * {
                            box-sizing: border-box;
                        }

                        .delivery-entry-page .entry-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .delivery-entry-page .entry-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, 0.08);
                            color: var(--primary-2);
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 12px;
                        }

                        .delivery-entry-page .entry-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .delivery-entry-page .entry-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2.1rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .delivery-entry-page .entry-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .delivery-entry-page .entry-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .delivery-entry-page .btn-action,
                        .delivery-entry-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            transition: all 0.16s ease;
                            text-decoration: none;
                        }

                        .delivery-entry-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .delivery-entry-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .delivery-entry-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .delivery-entry-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .delivery-entry-page .entry-layout {
                            display: grid;
                            grid-template-columns: minmax(0, 1.85fr) minmax(300px, 0.95fr);
                            gap: 20px;
                            align-items: start;
                        }

                        .delivery-entry-page .entry-main,
                        .delivery-entry-page .entry-side {
                            display: grid;
                            gap: 20px;
                        }

                        .delivery-entry-page .entry-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            padding: 24px;
                            box-shadow: var(--shadow-soft);
                        }

                        .delivery-entry-page .entry-card-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 20px;
                            padding-bottom: 16px;
                            border-bottom: 1px solid var(--line);
                        }

                        .delivery-entry-page .entry-card-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 1.25rem;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .delivery-entry-page .form-group {
                            margin-bottom: 16px;
                        }

                        .delivery-entry-page .form-label {
                            display: block;
                            margin-bottom: 6px;
                            font-size: 0.85rem;
                            font-weight: 600;
                            color: var(--text);
                        }

                        .delivery-entry-page .form-control,
                        .delivery-entry-page .form-select {
                            width: 100%;
                            padding: 10px 14px;
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            font-size: 0.9rem;
                            color: var(--text);
                            background: #fff;
                            transition: all 0.16s ease;
                        }

                        .delivery-entry-page .form-control:focus,
                        .delivery-entry-page .form-select:focus {
                            outline: none;
                            border-color: var(--primary);
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
                        }

                        .delivery-entry-page .form-control[readonly] {
                            background: var(--surface-soft);
                            color: var(--text-soft);
                        }

                        .payment-summary {
                            background: var(--primary-soft);
                            border: 1px solid rgba(37, 99, 235, 0.2);
                            border-radius: var(--radius-md);
                            padding: 16px;
                            margin-bottom: 20px;
                        }

                        .payment-summary h4 {
                            margin: 0 0 12px 0;
                            color: var(--primary);
                            font-size: 1rem;
                            font-weight: 600;
                        }

                        .payment-summary .summary-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 8px;
                            font-size: 0.9rem;
                        }

                        .payment-summary .summary-row.total {
                            border-top: 2px solid var(--primary);
                            padding-top: 8px;
                            margin-top: 8px;
                            font-weight: 700;
                            color: var(--primary);
                        }

                        .payment-history {
                            max-height: 300px;
                            overflow-y: auto;
                        }

                        .payment-item {
                            background: var(--surface-soft);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-sm);
                            padding: 12px;
                            margin-bottom: 12px;
                        }

                        .payment-item .payment-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 8px;
                        }

                        .payment-item .payment-amount {
                            font-size: 1.1rem;
                            font-weight: 700;
                            color: var(--success);
                        }

                        .payment-item .payment-date {
                            font-size: 0.85rem;
                            color: var(--text-soft);
                        }

                        @media (max-width: 992px) {
                            .delivery-entry-page .entry-layout {
                                grid-template-columns: 1fr;
                            }
                        }
                    </style>

                    <div class="entry-header">
                        <div>
                            <div class="entry-eyebrow">Customer Delivery</div>
                            <h1 class="entry-title">Delivery Payment</h1>
                            <p class="entry-subtitle">Record payment for delivery items</p>
                        </div>
                        <div class="entry-actions">
                            <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn-action">
                                <i class="mdi mdi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <?php if ($delivery): ?>
                    <form method="post" action="<?= base_url('Page/saveDeliveryPayment'); ?>">
                        <input type="hidden" name="deliveryNo" value="<?= htmlspecialchars($delivery->deliveryNo, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="customerName" value="<?= htmlspecialchars($delivery->customerName, ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <div class="entry-layout">
                            <div class="entry-main">
                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Delivery Information</h2>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Delivery Number</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($delivery->deliveryNo, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Customer Name</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($delivery->customerName, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Delivery Date</label>
                                                <input type="text" class="form-control" value="<?= date('F j, Y', strtotime($delivery->first_delivery_date)); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Total Amount</label>
                                                <input type="text" class="form-control" value="<?= number_format($delivery->total_amount, 2); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-summary">
                                        <h4>Payment Summary</h4>
                                        <div class="summary-row">
                                            <span>Total Amount:</span>
                                            <span><?= number_format($delivery->total_amount, 2); ?></span>
                                        </div>
                                        <div class="summary-row">
                                            <span>Amount Paid:</span>
                                            <span><?= number_format($delivery->total_paid, 2); ?></span>
                                        </div>
                                        <div class="summary-row total">
                                            <span>Remaining Balance:</span>
                                            <span><?= number_format($delivery->total_balance, 2); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Payment Details</h2>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="paymentAmount" class="form-label">Payment Amount *</label>
                                                <input type="number" class="form-control" id="paymentAmount" name="paymentAmount" step="0.01" min="0.01" max="<?= $delivery->total_balance; ?>" required>
                                                <div class="form-note" style="color: var(--text-soft); font-size: 0.84rem; margin-top: 4px;">
                                                    Enter the actual cash received. For government payments with BIR Form 2307, add the tax below. Total credit = Amount Paid + Tax.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="paymentDate" class="form-label">Payment Date *</label>
                                                <input type="date" class="form-control" id="paymentDate" name="paymentDate" value="<?= date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="taxAmount" class="form-label">Tax Credit (BIR Form 2307)</label>
                                                <input type="number" class="form-control" id="taxAmount" name="taxAmount" step="0.01" min="0" placeholder="0.00">
                                                <div class="form-note" style="color: var(--text-soft); font-size: 0.84rem; margin-top: 4px;">
                                                    Leave this at 0.00 unless the payment is from a government client with BIR Form 2307 withholding tax.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="paymentMode" class="form-label">Payment Mode *</label>
                                                <select class="form-select" id="paymentMode" name="paymentMode" required>
                                                    <option value="">Select Payment Mode</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="GCash">GCash</option>
                                                    <option value="Bank Transfer">Bank Transfer</option>
                                                    <option value="Debit/Credit Card">Debit/Credit Card</option>
                                                    <option value="Cheque">Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="referenceNo" class="form-label">Reference Number</label>
                                                <input type="text" class="form-control" id="referenceNo" name="referenceNo" placeholder="Optional">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group" id="attachment-group" style="display: none;">
                                                <label for="birAttachment" class="form-label">BIR Form 2307 Attachment <span class="text-danger">*</span></label>
                                                <input type="file" class="form-control" id="birAttachment" name="birAttachment" accept=".pdf,.jpg,.jpeg,.png">
                                                <div class="form-note" style="color: var(--text-soft); font-size: 0.84rem; margin-top: 4px;">
                                                    Please upload the BIR Form 2307 document (PDF, JPG, PNG). Required when tax credit is greater than 0.
                                                </div>
                                                <div class="invalid-feedback" id="attachment-error" style="display: none;">
                                                    Attachment is required when Tax Credit (BIR Form 2307) has a value greater than 0.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="paymentNotes" class="form-label">Payment Notes</label>
                                                <textarea class="form-control" id="paymentNotes" name="paymentNotes" rows="3" placeholder="Optional notes about this payment"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 12px; margin-top: 24px;">
                                        <button type="submit" class="btn-submit">
                                            <i class="mdi mdi-cash-plus"></i> Record Payment
                                        </button>
                                        <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn-action">
                                            <i class="mdi mdi-cancel"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="entry-side">
                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Payment History</h2>
                                    </div>
                                    
                                    <div class="payment-history">
                                        <?php if (!empty($paymentHistory)): ?>
                                            <?php foreach ($paymentHistory as $payment): ?>
                                                <div class="payment-item">
                                                    <div class="payment-header">
                                                        <span class="payment-amount"><?= number_format($payment->AmountPaid + ($payment->TaxAmount ?? 0), 2); ?></span>
                                                        <span class="payment-date"><?= date('M j, Y', strtotime($payment->PDate)); ?></span>
                                                    </div>
                                                    <div class="payment-details">
                                                        <div><strong>Payment ID:</strong> <?= htmlspecialchars($payment->paymentID, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div><strong>Mode:</strong> <?= htmlspecialchars($payment->PaymentSource ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div><strong>Cashier:</strong> <?= htmlspecialchars($payment->Cashier ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php if (!empty($payment->TaxAmount) && $payment->TaxAmount > 0): ?>
                                                            <div><strong>Tax Credit:</strong> <?= number_format($payment->TaxAmount, 2); ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($payment->PaymentReference)): ?>
                                                            <div><strong>Reference:</strong> <?= htmlspecialchars($payment->PaymentReference, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                        <?php if (strpos($payment->TransDescription ?? '', 'BIR Form attached') !== false): ?>
                                                            <div><strong>Attachment:</strong> <span style="color: var(--success);">BIR Form uploaded</span></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center" style="padding: 40px 20px;">
                                                <i class="mdi mdi-cash-off" style="font-size: 2rem; color: var(--text-faint);"></i>
                                                <p style="color: var(--text-soft); margin-top: 12px;">No payments recorded yet</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Quick Tips</h2>
                                    </div>
                                    <ul style="margin: 0; padding-left: 20px; color: var(--text-soft); font-size: 0.9rem; line-height: 1.6;">
                                        <li>Payment amount cannot exceed remaining balance</li>
                                        <li>Payment date defaults to today's date</li>
                                        <li>Reference number is optional but recommended</li>
                                        <li>Payment mode helps track payment types</li>
                                        <li>Delivery status will update automatically</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="entry-card">
                            <div class="text-center" style="padding: 40px;">
                                <h3 style="color: var(--danger); margin-bottom: 16px;">Delivery Not Found</h3>
                                <p style="color: var(--text-soft);">The requested delivery record could not be found.</p>
                                <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn-action" style="margin-top: 16px;">
                                    <i class="mdi mdi-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
    $(document).ready(function() {
        var maxBalance = <?= $delivery->total_balance; ?>;
        var $form = $('form');
        var $amount = $('#paymentAmount');
        var $tax = $('#taxAmount');
        var $attachmentGroup = $('#attachment-group');
        var $attachment = $('#birAttachment');
        var $attachmentError = $('#attachment-error');

        function toggleAttachmentRequirement() {
            var tax = parseFloat($tax.val()) || 0;
            if (tax > 0) {
                $attachmentGroup.show();
                $attachment.prop('required', true);
            } else {
                $attachmentGroup.hide();
                $attachment.prop('required', false);
                $attachment.val('');
                $attachment.removeClass('is-invalid');
                $attachmentError.hide();
            }
        }

        function validateAttachment() {
            var tax = parseFloat($tax.val()) || 0;
            if (tax > 0) {
                var file = $attachment[0].files[0];
                if (!file) {
                    $attachment.addClass('is-invalid');
                    $attachmentError.show();
                    return false;
                } else {
                    var allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        $attachment.addClass('is-invalid');
                        $attachmentError.text('Please upload a valid file (PDF, JPG, or PNG).').show();
                        return false;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        $attachment.addClass('is-invalid');
                        $attachmentError.text('File size must be less than 5MB.').show();
                        return false;
                    }
                }
            }
            $attachment.removeClass('is-invalid');
            $attachmentError.hide();
            return true;
        }

        function validatePaymentAmount() {
            var amount = parseFloat($amount.val()) || 0;
            var tax = parseFloat($tax.val()) || 0;
            var totalCredit = amount + tax;
            
            if (totalCredit > maxBalance) {
                $amount.addClass('is-invalid');
                $amount.next('.form-note').text('Total credit cannot exceed remaining balance of ' + maxBalance.toFixed(2)).css('color', 'var(--danger)');
                return false;
            } else {
                $amount.removeClass('is-invalid');
                $amount.next('.form-note').text('Enter the actual cash received. For government payments with BIR Form 2307, add the tax below. Total credit = Amount Paid + Tax.').css('color', 'var(--text-soft)');
                return true;
            }
        }

        // Validate payment amount doesn't exceed balance
        $amount.on('input', function() {
            validatePaymentAmount();
        });

        // Handle tax amount changes
        $tax.on('input change', function() {
            toggleAttachmentRequirement();
            validatePaymentAmount();
        });

        // Handle attachment changes
        $attachment.on('change', function() {
            validateAttachment();
        });

        // Form validation
        $form.on('submit', function(e) {
            if (!validateAttachment() || !validatePaymentAmount()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Auto-format reference number for certain payment modes
        $('#paymentMode').on('change', function() {
            var mode = $(this).val();
            var referenceField = $('#referenceNo');
            
            if (mode === 'GCash') {
                referenceField.attr('placeholder', 'GCash reference number');
            } else if (mode === 'Bank Transfer') {
                referenceField.attr('placeholder', 'Transaction ID');
            } else if (mode === 'Debit/Credit Card') {
                referenceField.attr('placeholder', 'Last 4 digits');
            } else if (mode === 'Cheque') {
                referenceField.attr('placeholder', 'Cheque number');
            } else {
                referenceField.attr('placeholder', 'Optional');
            }
        });

        // Initialize
        toggleAttachmentRequirement();
    });
    </script>

</body>
</html>
