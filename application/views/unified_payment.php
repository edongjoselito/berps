<?php
$paymentData = isset($paymentData) ? $paymentData : null;
$paymentType = isset($paymentType) ? $paymentType : 'invoice';
$paymentSource = isset($paymentSource) ? $paymentSource : 'Invoice';
$returnTo = isset($returnTo) ? $returnTo : 'paymentList';
$settingsID = $this->session->userdata('settingsID');
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
                <div class="container-fluid unified-payment-page">

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

                    <?php if ($this->session->flashdata('payment_notice')): ?>
                        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('payment_notice'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <style>
                        .unified-payment-page {
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
                            --font-body: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'Inter', 'Montserrat', 'Segoe UI', Arial, sans-serif;
                            --font-mono: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 28px;
                            font-family: var(--font-body);
                        }

                        .unified-payment-page * {
                            box-sizing: border-box;
                        }

                        .unified-payment-page .entry-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .unified-payment-page .entry-eyebrow {
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

                        .unified-payment-page .entry-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .unified-payment-page .entry-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2.1rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .unified-payment-page .entry-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .unified-payment-page .entry-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .unified-payment-page .btn-action,
                        .unified-payment-page .btn-submit {
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

                        .unified-payment-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .unified-payment-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .unified-payment-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .unified-payment-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .unified-payment-page .entry-layout {
                            display: grid;
                            grid-template-columns: minmax(0, 1.85fr) minmax(300px, 0.95fr);
                            gap: 20px;
                            align-items: start;
                        }

                        .unified-payment-page .entry-main,
                        .unified-payment-page .entry-side {
                            display: grid;
                            gap: 20px;
                        }

                        .unified-payment-page .entry-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            padding: 24px;
                            box-shadow: var(--shadow-soft);
                        }

                        .unified-payment-page .entry-card-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 20px;
                            padding-bottom: 16px;
                            border-bottom: 1px solid var(--line);
                        }

                        .unified-payment-page .entry-card-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 1.25rem;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .unified-payment-page .form-group {
                            margin-bottom: 16px;
                        }

                        .unified-payment-page .form-label {
                            display: block;
                            margin-bottom: 6px;
                            font-size: 0.85rem;
                            font-weight: 600;
                            color: var(--text);
                        }

                        .unified-payment-page .form-control,
                        .unified-payment-page .form-select {
                            width: 100%;
                            padding: 10px 14px;
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            font-size: 0.9rem;
                            color: var(--text);
                            background: #fff;
                            transition: all 0.16s ease;
                        }

                        .unified-payment-page .form-control:focus,
                        .unified-payment-page .form-select:focus {
                            outline: none;
                            border-color: var(--primary);
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
                        }

                        .unified-payment-page .form-control[readonly] {
                            background: var(--surface-soft);
                            color: var(--text-soft);
                        }

                        .unified-payment-page .form-note {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 4px;
                            line-height: 1.4;
                        }

                        .unified-payment-page .payment-summary {
                            background: var(--primary-soft);
                            border: 1px solid rgba(37, 99, 235, 0.2);
                            border-radius: var(--radius-md);
                            padding: 16px;
                            margin-bottom: 20px;
                        }

                        .unified-payment-page .payment-summary h4 {
                            margin: 0 0 12px 0;
                            color: var(--primary);
                            font-size: 1rem;
                            font-weight: 600;
                        }

                        .unified-payment-page .payment-summary .summary-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 8px;
                            font-size: 0.9rem;
                        }

                        .unified-payment-page .payment-summary .summary-row.total {
                            border-top: 2px solid var(--primary);
                            padding-top: 8px;
                            margin-top: 8px;
                            font-weight: 700;
                            color: var(--primary);
                        }

                        .unified-payment-page .progress-shell {
                            margin-top: 18px;
                            padding: 16px 17px 17px;
                            border-radius: 18px;
                            background: linear-gradient(180deg, #fbfdff 0%, #f7faff 100%);
                            border: 1px solid #e8eef7;
                        }

                        .unified-payment-page .progress-meta {
                            display: flex;
                            justify-content: space-between;
                            gap: 10px;
                            flex-wrap: wrap;
                            margin-bottom: 10px;
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            font-weight: 600;
                        }

                        .unified-payment-page .progress {
                            height: 10px;
                            border-radius: 999px;
                            background: #eaf0f8;
                            overflow: hidden;
                        }

                        .unified-payment-page .progress-bar {
                            background: linear-gradient(90deg, var(--primary), var(--primary-2));
                            border-radius: 999px;
                            height: 100%;
                            transition: width 0.3s ease;
                        }

                        .unified-payment-page .payment-type-tabs {
                            display: flex;
                            gap: 8px;
                            margin-bottom: 20px;
                            border-bottom: 2px solid var(--line);
                        }

                        .unified-payment-page .tab-button {
                            padding: 10px 16px;
                            border: none;
                            background: none;
                            color: var(--text-soft);
                            font-weight: 600;
                            cursor: pointer;
                            border-bottom: 3px solid transparent;
                            transition: all 0.16s ease;
                        }

                        .unified-payment-page .tab-button.active {
                            color: var(--primary);
                            border-bottom-color: var(--primary);
                        }

                        .unified-payment-page .tab-button:hover {
                            color: var(--primary);
                        }

                        .unified-payment-page .search-form {
                            display: grid;
                            grid-template-columns: 1fr auto;
                            gap: 12px;
                            margin-bottom: 20px;
                        }

                        .unified-payment-page .search-results {
                            max-height: 400px;
                            overflow-y: auto;
                        }

                        .unified-payment-page .search-result-item {
                            padding: 12px;
                            border: 1px solid var(--line);
                            border-radius: var(--radius-sm);
                            margin-bottom: 8px;
                            cursor: pointer;
                            transition: all 0.16s ease;
                        }

                        .unified-payment-page .search-result-item:hover {
                            border-color: var(--primary);
                            background: var(--primary-soft);
                        }

                        .unified-payment-page .search-result-item.selected {
                            border-color: var(--primary);
                            background: var(--primary-soft);
                        }

                        .unified-payment-page .item-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 4px;
                        }

                        .unified-payment-page .item-number {
                            font-weight: 600;
                            color: var(--primary);
                        }

                        .unified-payment-page .item-amount {
                            font-weight: 700;
                            color: var(--text);
                        }

                        .unified-payment-page .item-details {
                            font-size: 0.85rem;
                            color: var(--text-soft);
                        }

                        .unified-payment-page .attachment-group {
                            display: none;
                        }

                        .unified-payment-page .attachment-group.show {
                            display: block;
                        }

                        .unified-payment-page .invalid-feedback {
                            color: var(--danger);
                            font-size: 0.84rem;
                            margin-top: 4px;
                            display: none;
                        }

                        .unified-payment-page .invalid-feedback.show {
                            display: block;
                        }

                        .unified-payment-page .form-control.is-invalid {
                            border-color: var(--danger);
                        }

                        @media (max-width: 992px) {
                            .unified-payment-page .entry-layout {
                                grid-template-columns: 1fr;
                            }

                            .unified-payment-page .search-form {
                                grid-template-columns: 1fr;
                            }
                        }
                    </style>

                    <div class="entry-header">
                        <div>
                            <div class="entry-eyebrow">Payment Processing</div>
                            <h1 class="entry-title">Universal Payment Center</h1>
                            <p class="entry-subtitle">Single window for processing all payments - Invoices, Job Orders, and Deliveries</p>
                        </div>
                        <div class="entry-actions">
                            <a href="<?= base_url('Page/paymentList'); ?>" class="btn-action">
                                <i class="mdi mdi-arrow-left"></i> Back to Payments
                            </a>
                        </div>
                    </div>

                    <form method="post" action="<?= base_url('Page/saveUnifiedPayment'); ?>" id="paymentForm">
                        <input type="hidden" name="paymentType" id="paymentType" value="<?= htmlspecialchars($paymentType, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="referenceId" id="referenceId" value="">
                        <input type="hidden" name="referenceNumber" id="referenceNumber" value="">
                        
                        <div class="entry-layout">
                            <div class="entry-main">
                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Payment Type Selection</h2>
                                    </div>

                                    <div class="payment-type-tabs">
                                        <button type="button" class="tab-button active" data-type="invoice">
                                            <i class="mdi mdi-file-document"></i> Invoice Payments
                                        </button>
                                        <button type="button" class="tab-button" data-type="job-order">
                                            <i class="mdi mdi-clipboard-text"></i> Job Order Payments
                                        </button>
                                        <button type="button" class="tab-button" data-type="delivery">
                                            <i class="mdi mdi-truck-delivery"></i> Delivery Payments
                                        </button>
                                    </div>

                                    <div id="searchSection">
                                        <div class="search-form">
                                            <input type="text" class="form-control" id="searchInput" placeholder="Search invoices, job orders, or deliveries by number or customer name...">
                                            <button type="button" class="btn-action" id="searchButton">
                                                <i class="mdi mdi-magnify"></i> Search
                                            </button>
                                        </div>
                                        <div id="searchResults" class="search-results">
                                            <?php
                                            // Debug: Check if data is available
                                            $allInvoices = isset($invoices) ? $invoices : array();
                                            $allJobOrders = isset($jobOrders) ? $jobOrders : array();
                                            $allDeliveries = isset($deliveries) ? $deliveries : array();
                                            
                                            // Show debug info on page
                                            $CI =& get_instance();
                                            echo '<div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin-bottom: 15px; font-family: monospace; font-size: 12px;">';
                                            echo '<strong>Debug Info:</strong><br>';
                                            echo 'Invoices found: ' . count($allInvoices) . '<br>';
                                            echo 'Job Orders found: ' . count($allJobOrders) . '<br>';
                                            echo 'Deliveries found: ' . count($allDeliveries) . '<br>';
                                            echo 'Session settingsID: ' . ($CI->session->userdata('settingsID') ?? 'not set') . '<br>';
                                            echo '</div>';
                                            
                                            // Display initial unpaid invoices
                                            if (!empty($allInvoices)) {
                                                echo '<div style="margin-bottom: 12px; font-weight: 600; color: var(--text);">' . count($allInvoices) . ' Unpaid Invoices</div>';
                                                foreach ($allInvoices as $invoice) {
                                                    echo '<div class="search-result-item" data-item=\'' . json_encode($invoice) . '\'>';
                                                    echo '<div class="item-header">';
                                                    echo '<span class="item-number">' . htmlspecialchars($invoice->number, ENT_QUOTES, 'UTF-8') . '</span>';
                                                    echo '<span class="item-amount">' . number_format($invoice->amount, 2) . '</span>';
                                                    echo '</div>';
                                                    echo '<div class="item-details">';
                                                    echo '<div>Customer: ' . htmlspecialchars($invoice->customer, ENT_QUOTES, 'UTF-8') . '</div>';
                                                    echo '<div>Date: ' . $invoice->date . '</div>';
                                                    echo '<div>Balance: ' . number_format($invoice->remainingBalance, 2) . '</div>';
                                                    if ($invoice->status) {
                                                        echo '<div>Status: ' . htmlspecialchars($invoice->status, ENT_QUOTES, 'UTF-8') . '</div>';
                                                    }
                                                    echo '</div>';
                                                    echo '</div>';
                                                }
                                            } else {
                                                echo '<div style="text-align: center; padding: 20px; color: var(--text-soft);">No unpaid invoices found</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="entry-card" id="paymentDetailsCard" style="display: none;">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Payment Details</h2>
                                    </div>

                                    <div id="selectedItemInfo"></div>

                                    <div class="payment-summary">
                                        <h4>Payment Summary</h4>
                                        <div class="summary-row">
                                            <span>Total Amount:</span>
                                            <span id="totalAmount">0.00</span>
                                        </div>
                                        <div class="summary-row">
                                            <span>Amount Paid:</span>
                                            <span id="amountPaid">0.00</span>
                                        </div>
                                        <div class="summary-row total">
                                            <span>Remaining Balance:</span>
                                            <span id="remainingBalance">0.00</span>
                                        </div>
                                    </div>

                                    <div class="progress-shell">
                                        <div class="progress-meta">
                                            <span>Payment Progress</span>
                                            <span id="progressPercentage">0%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="paymentAmount" class="form-label">Payment Amount *</label>
                                                <input type="number" class="form-control" id="paymentAmount" name="paymentAmount" step="0.01" min="0.01" required>
                                                <div class="form-note">Enter the actual cash received. For government payments with BIR Form 2307, add the tax below. Total credit = Amount Paid + Tax.</div>
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
                                                <div class="form-note">Leave this at 0.00 unless the payment is from a government client with BIR Form 2307 withholding tax.</div>
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
                                            <div class="form-group attachment-group" id="attachmentGroup">
                                                <label for="birAttachment" class="form-label">BIR Form 2307 Attachment <span class="text-danger">*</span></label>
                                                <input type="file" class="form-control" id="birAttachment" name="birAttachment" accept=".pdf,.jpg,.jpeg,.png">
                                                <div class="form-note">Please upload the BIR Form 2307 document (PDF, JPG, PNG). Required when tax credit is greater than 0.</div>
                                                <div class="invalid-feedback" id="attachmentError">Attachment is required when Tax Credit (BIR Form 2307) has a value greater than 0.</div>
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
                                        <button type="submit" class="btn-submit" id="submitPayment">
                                            <i class="mdi mdi-cash-plus"></i> Record Payment
                                        </button>
                                        <a href="<?= base_url('Page/paymentList'); ?>" class="btn-action">
                                            <i class="mdi mdi-cancel"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="entry-side">
                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Quick Actions</h2>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 12px;">
                                        <a href="<?= base_url('Page/paymentList'); ?>" class="btn-action" style="text-align: center;">
                                            <i class="mdi mdi-format-list-bulleted"></i> View All Payments
                                        </a>
                                        <a href="<?= base_url('Page/accountingReports'); ?>" class="btn-action" style="text-align: center;">
                                            <i class="mdi mdi-chart-line"></i> Accounting Reports
                                        </a>
                                    </div>
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Quick Tips</h2>
                                    </div>
                                    <ul style="margin: 0; padding-left: 20px; color: var(--text-soft); font-size: 0.9rem; line-height: 1.6;">
                                        <li>Select payment type (Invoice, Job Order, or Delivery)</li>
                                        <li>Search for the specific document to pay</li>
                                        <li>Enter payment amount and optional tax credit</li>
                                        <li>Upload BIR Form 2307 attachment if tax > 0</li>
                                        <li>System automatically updates document status</li>
                                    </ul>
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Recent Activity</h2>
                                    </div>
                                    <div id="recentPayments">
                                        <div style="text-align: center; padding: 20px; color: var(--text-soft);">
                                            <i class="mdi mdi-history" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                                            <p>No recent payments</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
    $(document).ready(function() {
        var currentType = 'invoice';
        var selectedItem = null;
        var maxBalance = 0;

        // Don't automatically load via AJAX - use PHP-loaded data instead
        console.log('Page loaded, using PHP data');

        // Tab switching
        $('.tab-button').on('click', function() {
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            currentType = $(this).data('type');
            $('#paymentType').val(currentType);
            clearSelection();
            // Automatically load all unpaid transactions for this type
            performSearch('');
        });

        // Search functionality
        $('#searchButton').on('click', function() {
            var searchTerm = $('#searchInput').val().trim();
            performSearch(searchTerm);
        });

        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                var searchTerm = $(this).val().trim();
                performSearch(searchTerm);
                return false;
            }
        });

        function performSearch(searchTerm) {
            // Always perform search (even empty) to show all unpaid transactions
            $('#searchResults').html('<div style="text-align: center; padding: 20px; color: var(--text-soft);">Loading unpaid transactions...</div>');

            console.log('Performing search for type:', currentType, 'search:', searchTerm);

            $.ajax({
                url: '<?= base_url('Page/searchPaymentDocuments'); ?>',
                method: 'POST',
                data: {
                    type: currentType,
                    search: searchTerm
                },
                success: function(response) {
                    console.log('Search response:', response);
                    displaySearchResults(response);
                },
                error: function(xhr, status, error) {
                    console.log('Search error:', error);
                    console.log('Response text:', xhr.responseText);
                    $('#searchResults').html('<div style="text-align: center; padding: 20px; color: var(--danger);">Error searching documents: ' + error + '</div>');
                }
            });
        }

        function displaySearchResults(results) {
            var html = '';
            
            if (results.length === 0) {
                var typeLabel = currentType === 'invoice' ? 'invoices' : (currentType === 'job-order' ? 'job orders' : 'deliveries');
                html = '<div style="text-align: center; padding: 20px; color: var(--text-soft);">No unpaid ' + typeLabel + ' found</div>';
            } else {
                var typeLabel = currentType === 'invoice' ? 'Unpaid Invoices' : (currentType === 'job-order' ? 'Unpaid Job Orders' : 'Unpaid Deliveries');
                html = '<div style="margin-bottom: 12px; font-weight: 600; color: var(--text);">' + results.length + ' ' + typeLabel + '</div>';
                
                results.forEach(function(item) {
                    html += '<div class="search-result-item" data-item=\'' + JSON.stringify(item) + '\'>';
                    html += '<div class="item-header">';
                    html += '<span class="item-number">' + item.number + '</span>';
                    html += '<span class="item-amount">' + formatMoney(item.amount) + '</span>';
                    html += '</div>';
                    html += '<div class="item-details">';
                    html += '<div>Customer: ' + item.customer + '</div>';
                    html += '<div>Date: ' + item.date + '</div>';
                    html += '<div>Balance: ' + formatMoney(item.remainingBalance) + '</div>';
                    if (item.status) {
                        html += '<div>Status: ' + item.status + '</div>';
                    }
                    html += '</div>';
                    html += '</div>';
                });
            }
            
            $('#searchResults').html(html);
        }

        function formatMoney(amount) {
            return parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Item selection
        $(document).on('click', '.search-result-item', function() {
            $('.search-result-item').removeClass('selected');
            $(this).addClass('selected');
            
            selectedItem = JSON.parse($(this).data('item'));
            selectItem(selectedItem);
        });

        function selectItem(item) {
            $('#referenceId').val(item.id);
            $('#referenceNumber').val(item.number);
            
            // Update selected item info
            var infoHtml = '<div style="padding: 16px; background: var(--surface-soft); border-radius: var(--radius-sm); margin-bottom: 20px;">';
            infoHtml += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">';
            infoHtml += '<div><strong>Number:</strong> ' + item.number + '</div>';
            infoHtml += '<div><strong>Customer:</strong> ' + item.customer + '</div>';
            infoHtml += '<div><strong>Date:</strong> ' + item.date + '</div>';
            infoHtml += '<div><strong>Total Amount:</strong> ' + formatMoney(item.amount) + '</div>';
            if (item.status) {
                infoHtml += '<div><strong>Status:</strong> ' + item.status + '</div>';
            }
            infoHtml += '</div>';
            infoHtml += '</div>';
            
            $('#selectedItemInfo').html(infoHtml);
            
            // Update payment summary
            maxBalance = parseFloat(item.remainingBalance) || 0;
            $('#totalAmount').text(formatMoney(item.amount));
            $('#amountPaid').text(formatMoney(item.amountPaid || 0));
            $('#remainingBalance').text(formatMoney(maxBalance));
            
            // Update progress
            var progress = item.amount > 0 ? ((item.amountPaid || 0) / item.amount) * 100 : 0;
            $('#progressBar').css('width', progress + '%');
            $('#progressPercentage').text(Math.round(progress) + '%');
            
            // Show payment details card
            $('#paymentDetailsCard').show();
            
            // Set max amount for payment
            $('#paymentAmount').attr('max', maxBalance);
        }

        function clearSelection() {
            selectedItem = null;
            $('#referenceId').val('');
            $('#referenceNumber').val('');
            $('#selectedItemInfo').html('');
            $('#paymentDetailsCard').hide();
            $('#searchResults').html('');
            $('#searchInput').val('');
        }

        // Tax and attachment handling
        $('#taxAmount').on('input change', function() {
            var tax = parseFloat($(this).val()) || 0;
            if (tax > 0) {
                $('#attachmentGroup').addClass('show');
                $('#birAttachment').prop('required', true);
            } else {
                $('#attachmentGroup').removeClass('show');
                $('#birAttachment').prop('required', false).val('');
                $('#birAttachment').removeClass('is-invalid');
                $('#attachmentError').removeClass('show');
            }
            validatePaymentAmount();
        });

        function validatePaymentAmount() {
            var amount = parseFloat($('#paymentAmount').val()) || 0;
            var tax = parseFloat($('#taxAmount').val()) || 0;
            var totalCredit = amount + tax;
            
            if (totalCredit > maxBalance) {
                $('#paymentAmount').addClass('is-invalid');
                $('#paymentAmount').next('.form-note').text('Total credit cannot exceed remaining balance of ' + formatMoney(maxBalance)).css('color', 'var(--danger)');
                return false;
            } else {
                $('#paymentAmount').removeClass('is-invalid');
                $('#paymentAmount').next('.form-note').text('Enter the actual cash received. For government payments with BIR Form 2307, add the tax below. Total credit = Amount Paid + Tax.').css('color', 'var(--text-soft)');
                return true;
            }
        }

        $('#paymentAmount').on('input', function() {
            validatePaymentAmount();
        });

        // Form validation
        $('#paymentForm').on('submit', function(e) {
            if (!selectedItem) {
                e.preventDefault();
                alert('Please select a document to pay');
                return false;
            }
            
            var tax = parseFloat($('#taxAmount').val()) || 0;
            if (tax > 0) {
                var file = $('#birAttachment')[0].files[0];
                if (!file) {
                    e.preventDefault();
                    $('#birAttachment').addClass('is-invalid');
                    $('#attachmentError').addClass('show');
                    return false;
                } else {
                    var allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        e.preventDefault();
                        $('#birAttachment').addClass('is-invalid');
                        $('#attachmentError').text('Please upload a valid file (PDF, JPG, or PNG)').addClass('show');
                        return false;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        e.preventDefault();
                        $('#birAttachment').addClass('is-invalid');
                        $('#attachmentError').text('File size must be less than 5MB').addClass('show');
                        return false;
                    }
                }
            }
            
            if (!validatePaymentAmount()) {
                e.preventDefault();
                return false;
            }
        });

        // Payment mode placeholder updates
        $('#paymentMode').on('change', function() {
            var mode = $(this).val();
            var referenceField = $('#referenceNo');
            
            var placeholders = {
                'Cash': 'Optional',
                'GCash': 'GCash reference number',
                'Bank Transfer': 'Transaction ID',
                'Debit/Credit Card': 'Last 4 digits',
                'Cheque': 'Cheque number'
            };
            
            referenceField.attr('placeholder', placeholders[mode] || 'Optional');
        });

        // Utility function
        function formatMoney(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
            }).format(amount || 0);
        }

        // Initialize
        clearSelection();
        // Automatically load unpaid transactions for the default type
        performSearch('');
    });
    </script>

</body>
</html>
