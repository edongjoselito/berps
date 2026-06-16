<?php
$company = isset($company) ? $company : null;
$billingSummary = isset($billingSummary) && is_array($billingSummary) ? $billingSummary : array();
$billingRecords = isset($billingRecords) && is_array($billingRecords) ? $billingRecords : array();
$paymentHistory = isset($paymentHistory) && is_array($paymentHistory) ? $paymentHistory : array();
$billingModeOptions = isset($billingModeOptions) && is_array($billingModeOptions) ? $billingModeOptions : array();
$defaultBillingMonth = isset($defaultBillingMonth) ? (string) $defaultBillingMonth : date('Y-m');
$defaultBillableUnits = isset($defaultBillableUnits) ? (int) $defaultBillableUnits : 1;
$generatedRecurringCount = isset($generatedRecurringCount) ? (int) $generatedRecurringCount : 0;
$companyName = trim((string) (($company->CompName ?? '') !== '' ? $company->CompName : ($company->BusinessName ?? 'Unknown Company')));
$companyBusinessName = trim((string) ($company->BusinessName ?? ''));
$settingsID = (int) ($company->settingsID ?? 0);
$currentBillingMode = (string) ($billingSummary['billing_mode'] ?? 'company');
$currentMonthlyRate = round((float) ($billingSummary['monthly_rate'] ?? 0), 2);
$nextBillingMonth = trim((string) ($billingSummary['next_billing_month'] ?? date('Y-m-01', strtotime('+1 month'))));
$nextBillingMonthLabel = $nextBillingMonth !== '' ? date('F Y', strtotime($nextBillingMonth)) : date('F Y', strtotime('+1 month'));
?>
<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<style>
    body {
        background: #f6f7fb;
        font-size: 14px;
        color: #111827;
    }

    .page-shell {
        display: grid;
        gap: 20px;
    }

    .card {
        border: 1px solid rgba(0, 0, 0, .06);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        background: #fff;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) repeat(3, minmax(0, 1fr));
        gap: 16px;
        align-items: stretch;
    }

    .summary-main {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .summary-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .summary-copy {
        color: #6b7280;
        margin-bottom: 16px;
    }

    .summary-meta {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .metric-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        background: #fff;
    }

    .metric-label {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .metric-value {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
    }

    .metric-meta {
        margin-top: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    .panel-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .panel-copy {
        color: #6b7280;
        margin-bottom: 0;
    }

    .table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #111827;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .status-unpaid {
        background: #fef3c7;
        color: #92400e;
    }

    .status-partial {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-paid {
        background: #dcfce7;
        color: #166534;
    }

    .status-free {
        background: #ede9fe;
        color: #6d28d9;
    }

    .payment-modal .modal-content {
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, .06);
        box-shadow: 0 18px 50px rgba(18, 38, 63, .18);
    }

    .payment-modal .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    @media (max-width: 1199.98px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="page-shell">
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap: 12px;">
                                    <div>
                                        <div class="summary-title mb-1">Company Billing Ledger</div>
                                        <p class="text-muted mb-0">Recurring monthly billing is generated from the saved company setup, and payments are recorded against each monthly charge.</p>
                                    </div>
                                    <div class="d-flex flex-wrap" style="gap: 10px;">
                                        <a href="<?= site_url('Page/superAdminCompanies'); ?>" class="btn btn-outline-secondary">
                                            <i class="mdi mdi-domain mr-1"></i> Manage Companies
                                        </a>
                                        <a href="<?= site_url('Page/superAdminBilling'); ?>" class="btn btn-primary">
                                            <i class="mdi mdi-arrow-left mr-1"></i> Back to Billing
                                        </a>
                                    </div>
                                </div>

                                <div class="summary-grid">
                                    <div class="summary-main">
                                        <div class="summary-title"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <p class="summary-copy">The billing mode selected on the company setup controls the recurring monthly billing generated for this company.</p>
                                        <div class="summary-meta"><strong>Settings ID:</strong> <?= $settingsID; ?></div>
                                        <?php if ($companyBusinessName !== ''): ?>
                                        <div class="summary-meta"><strong>Business Name:</strong> <?= htmlspecialchars($companyBusinessName, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($company->CompEmail)): ?>
                                        <div class="summary-meta"><strong>Email:</strong> <?= htmlspecialchars((string) $company->CompEmail, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($company->CompPhone)): ?>
                                        <div class="summary-meta"><strong>Phone:</strong> <?= htmlspecialchars((string) $company->CompPhone, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="metric-card">
                                        <div class="metric-label">Billing Mode</div>
                                        <div class="metric-value"><?= htmlspecialchars((string) ($billingSummary['billing_mode_label'] ?? 'Paid by Company'), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="metric-meta">Default setup stored on the company profile.</div>
                                    </div>

                                    <div class="metric-card">
                                        <div class="metric-label">Monthly Rate</div>
                                        <div class="metric-value">PHP <?= number_format($currentMonthlyRate, 2); ?></div>
                                        <div class="metric-meta"><?= $currentBillingMode === 'individual' ? 'Applied per active company user.' : ($currentBillingMode === 'free' ? 'Free companies can keep this at zero.' : 'Applied once per month for the company.'); ?></div>
                                    </div>

                                    <div class="metric-card">
                                        <div class="metric-label">Billable Users</div>
                                        <div class="metric-value"><?= number_format((int) ($billingSummary['billable_users'] ?? 0)); ?></div>
                                        <div class="metric-meta">Active internal users counted for individual billing.</div>
                                    </div>
                                </div>

                                <div class="alert alert-info border-0 mt-3 mb-0" style="background: #eef6ff; color: #1e3a8a;">
                                    <strong>Recurring monthly billing is active.</strong>
                                    The next recurring month in the cycle is <strong><?= htmlspecialchars($nextBillingMonthLabel, ENT_QUOTES, 'UTF-8'); ?></strong>.
                                    Missing months up to the current month are generated automatically whenever this page loads.
                                </div>

                                <?php if ($generatedRecurringCount > 0): ?>
                                <div class="alert alert-success border-0 mt-3 mb-0" style="background: #ecfdf5; color: #166534;">
                                    <?= number_format($generatedRecurringCount); ?> recurring billing month<?= $generatedRecurringCount === 1 ? '' : 's'; ?> <?= $generatedRecurringCount === 1 ? 'was' : 'were'; ?> generated automatically during this visit.
                                </div>
                                <?php endif; ?>

                                <div class="summary-grid mt-3">
                                    <div class="metric-card">
                                        <div class="metric-label">Expected Monthly Charge</div>
                                        <div class="metric-value">PHP <?= number_format((float) ($billingSummary['expected_monthly_charge'] ?? 0), 2); ?></div>
                                        <div class="metric-meta">Current estimate from the company billing setup.</div>
                                    </div>
                                    <div class="metric-card">
                                        <div class="metric-label">Total Due</div>
                                        <div class="metric-value">PHP <?= number_format((float) ($billingSummary['total_due'] ?? 0), 2); ?></div>
                                        <div class="metric-meta">All billing entries created for this company.</div>
                                    </div>
                                    <div class="metric-card">
                                        <div class="metric-label">Collected Payments</div>
                                        <div class="metric-value">PHP <?= number_format((float) ($billingSummary['total_paid'] ?? 0), 2); ?></div>
                                        <div class="metric-meta">Total payments recorded on the ledger.</div>
                                    </div>
                                    <div class="metric-card">
                                        <div class="metric-label">Outstanding Balance</div>
                                        <div class="metric-value">PHP <?= number_format((float) ($billingSummary['outstanding_balance'] ?? 0), 2); ?></div>
                                        <div class="metric-meta">Remaining balance still unpaid.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 12px;">
                                    <div>
                                        <div class="panel-title">Backfill Or Advance Billing Month</div>
                                        <p class="panel-copy">Regular monthly charges are recurring. Use this form only when you need to add a missing past month manually or prepare an advance month beyond the current recurring cycle.</p>
                                    </div>
                                </div>

                                <form id="billingEntryForm">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Billing Month</label>
                                                <input type="month" class="form-control" name="billing_month" id="billingMonth" value="<?= htmlspecialchars($defaultBillingMonth, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                <small class="form-text text-muted">Defaulted to the next month after the latest recurring billing entry.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Billing Mode</label>
                                                <select class="form-control" name="billing_mode" id="entryBillingMode">
                                                    <?php foreach ($billingModeOptions as $billingModeKey => $billingMode): ?>
                                                    <option value="<?= htmlspecialchars((string) $billingModeKey, ENT_QUOTES, 'UTF-8'); ?>" <?= $currentBillingMode === (string) $billingModeKey ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars((string) ($billingMode['label'] ?? $billingModeKey), ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Billable Units</label>
                                                <input type="number" class="form-control" name="billable_units" id="billableUnits" min="0" step="1" value="<?= $defaultBillableUnits; ?>" required>
                                                <small class="form-text text-muted" id="billableUnitsHelp">Use `1` for company billing or the number of active users for individual billing.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Rate Per Month</label>
                                                <input type="number" class="form-control" name="rate_per_month" id="ratePerMonth" min="0" step="0.01" value="<?= number_format($currentMonthlyRate, 2, '.', ''); ?>" required>
                                                <small class="form-text text-muted" id="ratePerMonthHelp">This uses the rate saved on the company profile by default.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Optional note for this billing month"></textarea>
                                    </div>
                                    <input type="hidden" name="settingsID" value="<?= $settingsID; ?>">
                                    <button type="button" class="btn btn-primary" id="saveBillingEntryBtn">
                                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Billing Month
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 12px;">
                                    <div>
                                        <div class="panel-title">Recurring Billing History</div>
                                        <p class="panel-copy">Each recurring month keeps its own billing snapshot, collected amount, and remaining balance.</p>
                                    </div>
                                </div>

                                <?php if (!empty($billingRecords)): ?>
                                <div class="table-wrap">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Billing Month</th>
                                                <th>Mode</th>
                                                <th>Units</th>
                                                <th>Rate</th>
                                                <th>Amount Due</th>
                                                <th>Amount Paid</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($billingRecords as $record): ?>
                                            <?php
                                            $amountDue = round((float) ($record->amount_due ?? 0), 2);
                                            $amountPaid = round((float) ($record->amount_paid ?? 0), 2);
                                            $balance = max(0, round($amountDue - $amountPaid, 2));
                                            $recordStatus = strtolower(trim((string) ($record->status ?? 'unpaid')));
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold"><?= date('F Y', strtotime((string) $record->billing_month)); ?></div>
                                                    <?php if (!empty($record->notes)): ?>
                                                    <div class="summary-meta mt-1"><?= htmlspecialchars((string) $record->notes, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($billingModeOptions[$record->billing_mode]['label'] ?? ucfirst((string) $record->billing_mode)), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= number_format((int) ($record->billable_units ?? 0)); ?></td>
                                                <td>PHP <?= number_format((float) ($record->rate_per_month ?? 0), 2); ?></td>
                                                <td>PHP <?= number_format($amountDue, 2); ?></td>
                                                <td>PHP <?= number_format($amountPaid, 2); ?></td>
                                                <td>PHP <?= number_format($balance, 2); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?= htmlspecialchars($recordStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?= htmlspecialchars(ucfirst($recordStatus), ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($balance > 0 && $recordStatus !== 'free'): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary record-payment-btn"
                                                        data-billing-id="<?= (int) ($record->billing_id ?? 0); ?>"
                                                        data-billing-month="<?= htmlspecialchars(date('F Y', strtotime((string) $record->billing_month)), ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-balance="<?= number_format($balance, 2, '.', ''); ?>">
                                                        <i class="mdi mdi-cash-fast mr-1"></i> Record Payment
                                                    </button>
                                                    <?php else: ?>
                                                    <span class="text-muted small">No action needed</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    No billing entries created yet for this company.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-4">
                                <div class="panel-title">Payment History</div>
                                <p class="panel-copy mb-3">Every recorded payment for this company appears here, including the method and any reference number entered by the super admin.</p>

                                <?php if (!empty($paymentHistory)): ?>
                                <div class="table-wrap">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Payment Date</th>
                                                <th>Billing Entry</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Reference</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($paymentHistory as $payment): ?>
                                            <tr>
                                                <td><?= !empty($payment->payment_date) ? date('M d, Y', strtotime((string) $payment->payment_date)) : '-'; ?></td>
                                                <td>#<?= (int) ($payment->billing_id ?? 0); ?></td>
                                                <td>PHP <?= number_format((float) ($payment->amount_paid ?? 0), 2); ?></td>
                                                <td><?= !empty($payment->payment_method) ? htmlspecialchars((string) $payment->payment_method, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                <td><?= !empty($payment->reference_no) ? htmlspecialchars((string) $payment->reference_no, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                <td><?= !empty($payment->notes) ? htmlspecialchars((string) $payment->notes, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-light border mb-0">
                                    No payments recorded yet for this company.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <div class="modal fade payment-modal" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Record Payment</h5>
                        <div class="small text-muted" id="paymentModalSubtitle">Apply a payment to the selected billing entry.</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <input type="hidden" name="billing_id" id="paymentBillingId">
                        <div class="form-group">
                            <label class="font-weight-bold">Amount Paid</label>
                            <input type="number" class="form-control" name="amount_paid" id="paymentAmount" min="0.01" step="0.01" required>
                            <small class="form-text text-muted" id="paymentBalanceHelp">Balance remaining will be updated after this payment.</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Payment Method</label>
                            <input type="text" class="form-control" name="payment_method" placeholder="Cash, Bank Transfer, GCash, etc.">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Reference No.</label>
                            <input type="text" class="form-control" name="reference_no" placeholder="Optional payment reference">
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Optional payment note"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="submitPaymentBtn">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveBillingEntryUrl = '<?= site_url("Page/saveCompanyBillingEntry"); ?>';
            const recordCompanyBillingPaymentUrl = '<?= site_url("Page/recordCompanyBillingPayment"); ?>';
            const currentBillingMode = '<?= htmlspecialchars($currentBillingMode, ENT_QUOTES, 'UTF-8'); ?>';
            const billableUserCount = <?= (int) ($billingSummary['billable_users'] ?? 0); ?>;
            const currentMonthlyRate = <?= number_format($currentMonthlyRate, 2, '.', ''); ?>;
            const $entryBillingMode = $('#entryBillingMode');
            const $billableUnits = $('#billableUnits');
            const $billableUnitsHelp = $('#billableUnitsHelp');
            const $ratePerMonth = $('#ratePerMonth');
            const $ratePerMonthHelp = $('#ratePerMonthHelp');
            const $billingEntryForm = $('#billingEntryForm');
            const $paymentForm = $('#paymentForm');

            function updateEntryHelp() {
                const mode = $entryBillingMode.val() || 'company';

                if (mode === 'individual') {
                    if (!$billableUnits.val() || Number($billableUnits.val()) <= 1) {
                        $billableUnits.val(billableUserCount);
                    }
                    $billableUnitsHelp.text('This should match the number of active internal users you want to bill for the selected month.');
                    $ratePerMonthHelp.text('This amount is the per-user monthly rate for the selected billing month.');
                } else if (mode === 'free') {
                    $billableUnits.val(0);
                    $billableUnitsHelp.text('Free entries do not create a charge, so the billable unit count can stay at 0.');
                    $ratePerMonthHelp.text('Free entries can keep the rate at 0.00.');
                } else {
                    $billableUnits.val(1);
                    $billableUnitsHelp.text('Company billing uses a single fixed monthly charge, so the billable unit count stays at 1.');
                    $ratePerMonthHelp.text('This amount is charged once for the whole company for the selected month.');
                }

                if (!$ratePerMonth.val()) {
                    $ratePerMonth.val(currentMonthlyRate.toFixed(2));
                }
            }

            $('#saveBillingEntryBtn').on('click', function() {
                $.post(saveBillingEntryUrl, $billingEntryForm.serialize(), function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.message || 'Error creating billing entry');
                    }
                }, 'json').fail(function(xhr) {
                    const response = xhr.responseJSON || {};
                    alert(response.message || 'Error creating billing entry');
                });
            });

            $(document).on('click', '.record-payment-btn', function() {
                const billingId = $(this).data('billing-id');
                const billingMonth = $(this).data('billing-month');
                const balance = $(this).data('balance');

                $('#paymentBillingId').val(billingId || '');
                $('#paymentAmount').val(balance || '');
                $('#paymentModalSubtitle').text('Record a payment for ' + (billingMonth || 'this billing entry') + '.');
                $('#paymentBalanceHelp').text('Outstanding balance before this payment: PHP ' + (balance || '0.00'));
                $('#paymentModal').modal('show');
            });

            $('#submitPaymentBtn').on('click', function() {
                $.post(recordCompanyBillingPaymentUrl, $paymentForm.serialize(), function(response) {
                    if (response.success) {
                        $('#paymentModal').modal('hide');
                        window.location.reload();
                    } else {
                        alert(response.message || 'Error saving payment');
                    }
                }, 'json').fail(function(xhr) {
                    const response = xhr.responseJSON || {};
                    alert(response.message || 'Error saving payment');
                });
            });

            $('#paymentModal').on('hidden.bs.modal', function() {
                $paymentForm[0].reset();
                $paymentForm.find('input[name="payment_date"]').val('<?= date('Y-m-d'); ?>');
            });

            $entryBillingMode.on('change', updateEntryHelp);
            $entryBillingMode.val(currentBillingMode);
            updateEntryHelp();
        });
    </script>
</body>
</html>
