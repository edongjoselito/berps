<?php
$sale = $sale ?? null;
$items = is_array($items ?? null) ? $items : [];
$payments = is_array($payments ?? null) ? $payments : [];
$installments = is_array($installments ?? null) ? $installments : [];
$paymentModes = is_array($payment_modes ?? null) ? $payment_modes : ['Cash', 'GCash', 'Bank Transfer', 'Debit/Credit Card', 'Cheque'];
$status = trim((string) ($sale->status ?? 'Unpaid'));
$statusClass = 'status-unpaid';
if ($status === 'Paid') {
    $statusClass = 'status-paid';
} elseif ($status === 'Partially Paid') {
    $statusClass = 'status-partially';
} elseif ($status === 'Voided') {
    $statusClass = 'status-voided';
}
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
                <div class="container-fluid pos-detail-page">
                    <style>
                        .pos-detail-page {
                            background:
                                radial-gradient(circle at top left, rgba(14, 165, 233, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f7fafc 0%, #f1f5f9 100%);
                            min-height: 100vh;
                            padding: 24px 0 32px;
                        }

                        .pos-detail-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 16px;
                            flex-wrap: wrap;
                            margin-bottom: 18px;
                        }

                        .pos-detail-page .page-title {
                            margin: 0;
                            font-size: 2rem;
                            font-weight: 800;
                            color: #17324a;
                            letter-spacing: -0.04em;
                        }

                        .pos-detail-page .page-subtitle {
                            margin-top: 8px;
                            color: #63778d;
                        }

                        .pos-detail-page .page-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .pos-detail-page .btn-action {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            padding: 10px 16px;
                            border-radius: 12px;
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .pos-detail-page .btn-ghost {
                            border: 1px solid #d7e1eb;
                            background: rgba(255, 255, 255, 0.9);
                            color: #17324a;
                        }

                        .pos-detail-page .btn-primary-soft {
                            border: 1px solid rgba(14, 165, 233, 0.18);
                            background: rgba(14, 165, 233, 0.10);
                            color: #075985;
                        }

                        .pos-detail-page .status-pill {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            padding: 8px 12px;
                            border-radius: 999px;
                            font-weight: 800;
                            font-size: 0.84rem;
                        }

                        .status-paid {
                            color: #166534;
                            background: #ecfdf3;
                        }

                        .status-partially {
                            color: #92400e;
                            background: #fff7ed;
                        }

                        .status-unpaid {
                            color: #b91c1c;
                            background: #fef2f2;
                        }

                        .status-voided {
                            color: #475569;
                            background: #e2e8f0;
                        }

                        .pos-detail-page .stat-grid {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 14px;
                            margin-bottom: 18px;
                        }

                        .pos-detail-page .stat-card,
                        .pos-detail-page .theme-card {
                            background: #fff;
                            border-radius: 20px;
                            border: 1px solid rgba(255, 255, 255, 0.8);
                            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
                        }

                        .pos-detail-page .stat-card {
                            padding: 18px 20px;
                        }

                        .pos-detail-page .stat-label {
                            color: #75879b;
                            font-size: 0.74rem;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            font-weight: 700;
                        }

                        .pos-detail-page .stat-value {
                            margin-top: 8px;
                            color: #17324a;
                            font-size: 1.7rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                        }

                        .pos-detail-page .stat-meta {
                            margin-top: 6px;
                            color: #63778d;
                            font-size: 0.85rem;
                        }

                        .pos-detail-page .theme-card {
                            margin-bottom: 18px;
                        }

                        .pos-detail-page .theme-card-head {
                            padding: 18px 20px;
                            border-bottom: 1px solid #e8eef4;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 252, 0.94));
                        }

                        .pos-detail-page .theme-card-title {
                            margin: 0;
                            color: #17324a;
                            font-weight: 800;
                        }

                        .pos-detail-page .theme-card-subtitle {
                            margin-top: 6px;
                            color: #63778d;
                            font-size: 0.9rem;
                        }

                        .pos-detail-page .theme-card-body {
                            padding: 20px;
                        }

                        .pos-detail-page .detail-list {
                            display: grid;
                            gap: 12px;
                        }

                        .pos-detail-page .detail-row {
                            display: flex;
                            justify-content: space-between;
                            gap: 14px;
                            padding-bottom: 10px;
                            border-bottom: 1px dashed #e2e8f0;
                        }

                        .pos-detail-page .detail-row:last-child {
                            border-bottom: 0;
                            padding-bottom: 0;
                        }

                        .pos-detail-page .detail-label {
                            color: #63778d;
                            font-weight: 600;
                        }

                        .pos-detail-page .detail-value {
                            text-align: right;
                            color: #17324a;
                            font-weight: 700;
                        }

                        .pos-detail-page .table thead th {
                            border-top: 0;
                            color: #60758a;
                            font-size: 0.76rem;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                        }

                        .pos-detail-page .void-note {
                            padding: 14px 16px;
                            border-radius: 16px;
                            background: #fef2f2;
                            color: #991b1b;
                            border: 1px solid #fecaca;
                        }

                        @media (max-width: 991.98px) {
                            .pos-detail-page .stat-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 575.98px) {
                            .pos-detail-page .stat-grid {
                                grid-template-columns: minmax(0, 1fr);
                            }
                        }

                        @media print {
                            .navbar-custom,
                            .left-side-menu,
                            .footer,
                            .page-actions,
                            .theme-card form,
                            .no-print {
                                display: none !important;
                            }

                            .content-page,
                            .content,
                            .container-fluid,
                            .theme-card,
                            .theme-card-body {
                                padding: 0 !important;
                                margin: 0 !important;
                                box-shadow: none !important;
                                border: 0 !important;
                            }
                        }
                    </style>

                    <?php if (!empty($notice)): ?>
                        <div class="alert alert-<?= ($notice_type === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="page-header">
                        <div>
                            <div class="d-flex align-items-center flex-wrap" style="gap:12px;">
                                <h4 class="page-title">Sale <?= htmlspecialchars((string) ($sale->sale_no ?? ''), ENT_QUOTES, 'UTF-8'); ?></h4>
                                <span class="status-pill <?= $statusClass; ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <p class="page-subtitle">
                                <?= htmlspecialchars(date('M d, Y', strtotime((string) ($sale->transaction_date ?? date('Y-m-d')))), ENT_QUOTES, 'UTF-8'); ?>
                                at <?= htmlspecialchars(date('h:i A', strtotime((string) ($sale->transaction_time ?? '00:00:00'))), ENT_QUOTES, 'UTF-8'); ?>
                                by <?= htmlspecialchars((string) ($sale->cashier_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                        <div class="page-actions no-print">
                            <a class="btn-action btn-ghost" href="<?= base_url('Pos/posTransactionHistory'); ?>">
                                <i class="mdi mdi-arrow-left"></i> Back to History
                            </a>
                            <a class="btn-action btn-primary-soft" href="<?= base_url('Pos/posNewTransaction'); ?>">
                                <i class="mdi mdi-plus-box-outline"></i> New Sale
                            </a>
                            <button type="button" class="btn btn-light" onclick="window.print();">
                                <i class="mdi mdi-printer"></i> Print
                            </button>
                        </div>
                    </div>

                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">Grand Total</div>
                            <div class="stat-value"><?= number_format((float) ($sale->grand_total ?? 0), 2); ?></div>
                            <div class="stat-meta">Sale amount after VAT and discounts</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Paid</div>
                            <div class="stat-value"><?= number_format((float) ($sale->amount_paid ?? 0), 2); ?></div>
                            <div class="stat-meta"><?= htmlspecialchars(ucfirst((string) ($sale->payment_term ?? 'full')), ENT_QUOTES, 'UTF-8'); ?> collection posted</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Balance</div>
                            <div class="stat-value"><?= number_format((float) ($sale->balance_due ?? 0), 2); ?></div>
                            <div class="stat-meta">Remaining collectible amount</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">VAT / Discount</div>
                            <div class="stat-value"><?= number_format((float) ($sale->vat_amount ?? 0), 2); ?></div>
                            <div class="stat-meta">VAT with <?= number_format((float) ($sale->discount_amount ?? 0), 2); ?> discount applied</div>
                        </div>
                    </div>

                    <?php if ($status === 'Voided'): ?>
                        <div class="void-note mb-3">
                            <strong>Voided Sale.</strong>
                            <?= htmlspecialchars((string) ($sale->void_reason ?? 'No reason provided.'), ENT_QUOTES, 'UTF-8'); ?>
                            <?php if (!empty($sale->voided_by)): ?>
                                <div class="mt-1">Processed by <?= htmlspecialchars((string) $sale->voided_by, ENT_QUOTES, 'UTF-8'); ?> on <?= htmlspecialchars(date('M d, Y h:i A', strtotime((string) ($sale->voided_at ?? date('Y-m-d H:i:s')))), ENT_QUOTES, 'UTF-8'); ?>.</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Customer and Compliance</div>
                                    <div class="theme-card-subtitle">Captured billing and discount reference details for this sale.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="detail-list">
                                        <div class="detail-row">
                                            <span class="detail-label">Customer</span>
                                            <span class="detail-value"><?= htmlspecialchars((string) ($sale->customer_name ?? 'Walk-in Customer'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Address</span>
                                            <span class="detail-value"><?= htmlspecialchars((string) ($sale->customer_address ?? '—'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Customer TIN</span>
                                            <span class="detail-value"><?= htmlspecialchars((string) ($sale->customer_tin ?? '—'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Discount Type</span>
                                            <span class="detail-value"><?= htmlspecialchars((string) ($sale->discount_label ?? 'No Discount'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Senior/PWD Ref.</span>
                                            <span class="detail-value"><?= htmlspecialchars((string) ($sale->customer_discount_id ?? '—'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Payment Mode</span>
                                            <span class="detail-value"><?= htmlspecialchars((string) ($sale->payment_mode ?? '—'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Business TIN</span>
                                            <span class="detail-value"><?= htmlspecialchars((string) ($business->CompTin ?? '—'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($status !== 'Voided' && (float) ($sale->balance_due ?? 0) > 0): ?>
                                <div class="theme-card no-print">
                                    <div class="theme-card-head">
                                        <div class="theme-card-title">Record Payment</div>
                                        <div class="theme-card-subtitle">Post additional collection against the remaining balance.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <form method="post" action="<?= base_url('Pos/posRecordPayment/' . (int) ($sale->id ?? 0)); ?>">
                                            <div class="form-group">
                                                <label for="paymentAmount">Amount</label>
                                                <input type="number" step="0.01" max="<?= htmlspecialchars((string) ($sale->balance_due ?? 0), ENT_QUOTES, 'UTF-8'); ?>" class="form-control" id="paymentAmount" name="amount" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="paymentDate">Payment Date</label>
                                                <input type="date" class="form-control" id="paymentDate" name="payment_date" value="<?= date('Y-m-d'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="paymentMode">Payment Mode</label>
                                                <select class="form-control" id="paymentMode" name="payment_mode">
                                                    <?php foreach ($paymentModes as $mode): ?>
                                                        <option value="<?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="referenceNo">Reference No.</label>
                                                <input type="text" class="form-control" id="referenceNo" name="reference_no" placeholder="OR, transaction ID, or slip no.">
                                            </div>
                                            <div class="form-group">
                                                <label for="paymentRemarks">Remarks</label>
                                                <textarea class="form-control" id="paymentRemarks" name="remarks" rows="3" placeholder="Optional collection note"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success btn-block">Save Payment</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($can_void) && $status !== 'Voided'): ?>
                                <div class="theme-card no-print">
                                    <div class="theme-card-head">
                                        <div class="theme-card-title">Void Transaction</div>
                                        <div class="theme-card-subtitle">Voiding restores stock and excludes this sale from active POS reports.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <form method="post" action="<?= base_url('Pos/posVoidTransaction/' . (int) ($sale->id ?? 0)); ?>" onsubmit="return confirm('Void this sale and restore the stock to inventory?');">
                                            <div class="form-group mb-3">
                                                <label for="voidReason">Reason</label>
                                                <textarea class="form-control" id="voidReason" name="void_reason" rows="3" placeholder="Explain why this sale is being voided"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-outline-danger btn-block">Void Sale</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-8">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Items Sold</div>
                                    <div class="theme-card-subtitle">Snapshot of item pricing, VAT allocation, and discount effect at the time of sale.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Unit</th>
                                                    <th class="text-right">Qty</th>
                                                    <th class="text-right">Unit Price</th>
                                                    <th class="text-right">Discount</th>
                                                    <th class="text-right">VAT</th>
                                                    <th class="text-right">Line Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($items as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= htmlspecialchars((string) ($item->product_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                            <div class="text-muted small"><?= htmlspecialchars((string) ($item->sku ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </td>
                                                        <td><?= htmlspecialchars((string) ($item->unit ?? 'pcs'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-right"><?= number_format((float) ($item->quantity ?? 0), 0); ?></td>
                                                        <td class="text-right"><?= number_format((float) ($item->unit_price ?? 0), 2); ?></td>
                                                        <td class="text-right"><?= number_format((float) ($item->line_discount ?? 0), 2); ?></td>
                                                        <td class="text-right"><?= number_format((float) ($item->line_vat_amount ?? 0), 2); ?></td>
                                                        <td class="text-right"><?= number_format((float) ($item->line_total ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div class="theme-card-title">Payment Ledger</div>
                                    <div class="theme-card-subtitle">Every initial and follow-up payment posted against this transaction.</div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Mode</th>
                                                    <th>Reference</th>
                                                    <th>Received By</th>
                                                    <th>Remarks</th>
                                                    <th class="text-right">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($payments)): ?>
                                                    <?php foreach ($payments as $payment): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars(date('M d, Y', strtotime((string) ($payment->payment_date ?? date('Y-m-d')))), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars((string) ($payment->payment_mode ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars((string) ($payment->reference_no ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars((string) ($payment->received_by ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars((string) ($payment->remarks ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($payment->amount ?? 0), 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($installments)): ?>
                                <div class="theme-card">
                                    <div class="theme-card-head">
                                        <div class="theme-card-title">Installment Schedule</div>
                                        <div class="theme-card-subtitle">Planned due dates with automatic payment application to the earliest unpaid installment.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Due Date</th>
                                                        <th class="text-right">Amount Due</th>
                                                        <th class="text-right">Amount Paid</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($installments as $installment): ?>
                                                        <tr>
                                                            <td><?= number_format((int) ($installment->installment_no ?? 0)); ?></td>
                                                            <td><?= htmlspecialchars(date('M d, Y', strtotime((string) ($installment->due_date ?? date('Y-m-d')))), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($installment->amount_due ?? 0), 2); ?></td>
                                                            <td class="text-right"><?= number_format((float) ($installment->amount_paid ?? 0), 2); ?></td>
                                                            <td><?= htmlspecialchars((string) ($installment->status ?? 'Unpaid'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>
