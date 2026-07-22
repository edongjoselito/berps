<?php
$filterDateFromValue = isset($filterDateFrom) ? (string) $filterDateFrom : date('Y-m-d');
$filterDateToValue = isset($filterDateTo) ? (string) $filterDateTo : $filterDateFromValue;
$todayDateValue = isset($currentDayDate) ? (string) $currentDayDate : date('Y-m-d');
$showingToday = !empty($showingTodayOnly);
$isAdmin = strtolower(trim((string) $this->session->userdata('level'))) === 'admin';
$clients = isset($data2) && is_array($data2) ? $data2 : array();

$paymentRecords = isset($data) ? $data : array();
if ($paymentRecords instanceof Traversable) {
    $paymentRecords = iterator_to_array($paymentRecords, false);
}
$paymentRecords = is_array($paymentRecords) ? array_values($paymentRecords) : array();

if (!empty($paymentRecords)) {
    usort($paymentRecords, function ($a, $b) {
        $aDate = (isset($a->PDate) && $a->PDate !== '') ? strtotime((string) $a->PDate) : 0;
        $bDate = (isset($b->PDate) && $b->PDate !== '') ? strtotime((string) $b->PDate) : 0;

        if ($aDate === $bDate) {
            $aId = isset($a->paymentID) ? (int) $a->paymentID : 0;
            $bId = isset($b->paymentID) ? (int) $b->paymentID : 0;
            if ($aId === $bId) {
                return 0;
            }

            return ($aId < $bId) ? 1 : -1;
        }

        return ($aDate < $bDate) ? 1 : -1;
    });
}

$displayLimit = 300;
$totalPayments = count($paymentRecords);
$limitedView = ($displayLimit > 0) && ($totalPayments > $displayLimit);
$displayPayments = $limitedView ? array_slice($paymentRecords, 0, $displayLimit) : $paymentRecords;

$todayTotalValue = (!empty($todayTotal) && isset($todayTotal[0]->Total))
    ? (float) $todayTotal[0]->Total
    : 0.0;
$filteredTotalValue = 0.0;
foreach ($paymentRecords as $paymentRow) {
    $filteredTotalValue += (float) ($paymentRow->GrossAmountPaid ?? ((float) ($paymentRow->AmountPaid ?? 0) + (float) ($paymentRow->TaxAmount ?? 0)));
}
if ($showingToday) {
    $todayTotalValue = $filteredTotalValue;
}

$averageCreditValue = $totalPayments > 0 ? ($filteredTotalValue / $totalPayments) : 0.0;

$rangeSummaryLabel = $filterDateFromValue;
if ($filterDateFromValue !== '' && $filterDateToValue !== '') {
    $formattedFrom = date('F j, Y', strtotime($filterDateFromValue));
    $formattedTo = date('F j, Y', strtotime($filterDateToValue));
    $rangeSummaryLabel = ($filterDateFromValue === $filterDateToValue)
        ? $formattedFrom
        : $formattedFrom . ' to ' . $formattedTo;
}

$filterQueryString = http_build_query(array(
    'date_from' => $filterDateFromValue,
    'date_to' => $filterDateToValue,
));
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body class="payment-list-body">

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid payment-list-page berps-page">


                    <div class="pay-hero">
                        <div class="pay-hero__content">
                            <div class="pay-hero__eyebrow">
                                <i class="mdi mdi-credit-card-clock-outline"></i>
                                Collections
                            </div>
                            <h1 class="pay-hero__title">Payment Collections <span class="coin-spin">💰</span></h1>
                            <p class="pay-hero__subtitle">Review collected payments and filter activity by date range.</p>
                        </div>
                        <div class="pay-hero__actions">
                            <button type="button" class="pay-hero-btn" data-toggle="modal" data-target="#searchPaymentModal">
                                <i class="mdi mdi-magnify"></i>
                                <span>Find Payment</span>
                            </button>
                            <button type="button" class="pay-hero-btn" data-toggle="modal" data-target="#filterModal">
                                <i class="mdi mdi-filter-outline"></i>
                                <span>Filter</span>
                            </button>
                            <a href="<?= base_url(); ?>Page/unifiedPayment" class="pay-hero-btn pay-hero-btn--solid">
                                <i class="mdi mdi-credit-card-plus-outline"></i>
                                <span>Add New Payment</span>
                            </a>
                        </div>
                    </div>

                    <div class="berps-stat-grid">
                        <div class="berps-stat-card berps-tone-success">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($todayTotalValue, 2); ?></p>
                                <p class="berps-stat-card__label">Current Day Collections</p>
                                <p class="berps-stat-card__meta"><?= date('F j, Y', strtotime($todayDateValue)); ?></p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-cash-multiple"></i></span>
                        </div>
                        <div class="berps-stat-card berps-tone-info">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($filteredTotalValue, 2); ?></p>
                                <p class="berps-stat-card__label">Filtered Collections</p>
                                <p class="berps-stat-card__meta"><?= htmlspecialchars($rangeSummaryLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-filter-outline"></i></span>
                        </div>
                        <div class="berps-stat-card">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($totalPayments); ?></p>
                                <p class="berps-stat-card__label">Payments Shown</p>
                                <p class="berps-stat-card__meta"><?= $showingToday ? 'Showing today only' : 'Filtered date range'; ?></p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-receipt"></i></span>
                        </div>
                        <div class="berps-stat-card berps-tone-warning">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($averageCreditValue, 2); ?></p>
                                <p class="berps-stat-card__label">Average Credit</p>
                                <p class="berps-stat-card__meta">Average total credit per payment entry.</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-chart-line"></i></span>
                        </div>
                    </div>

                    <div class="card-stack">
                        <div class="theme-card">
                            <div class="theme-card-head">
                                <h5 class="theme-card-title">Payment Records</h5>
                                <!-- <div class="theme-card-subtitle">Browse posted collections, BIR Form 2307 credits, references, and linked payors in one table.</div> -->
                            </div>
                            <div class="theme-card-body">
                                <div class="table-responsive">
                                    <table id="payment-table" class="table">
                                        <thead>
                                            <tr>
                                                <th>Payment ID</th>
                                                <th>Invoice No</th>
                                                <th>Date</th>
                                                <th class="text-right">Amount Paid</th>
                                                <th class="text-right">Tax 2307</th>
                                                <th class="text-right">Total Credit</th>
                                                <th>O.R. No.</th>
                                                <th>Reference</th>
                                                <th>Payor</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($displayPayments)): ?>
                                                <?php foreach ($displayPayments as $row): ?>
                                                    <?php
                                                    $cashPaid = (float) ($row->AmountPaid ?? 0);
                                                    $taxAmount = (float) ($row->TaxAmount ?? 0);
                                                    $grossAmount = (float) ($row->GrossAmountPaid ?? ($cashPaid + $taxAmount));

                                                    $customerHistoryParams = array();
                                                    if (!empty($row->CustID)) {
                                                        $customerHistoryParams['cust_id'] = (string) $row->CustID;
                                                    } else {
                                                        $customerHistoryParams['customer'] = (string) $row->Customer;
                                                    }
                                                    if (!empty($filterQueryString)) {
                                                        parse_str($filterQueryString, $filterQueryArray);
                                                        $customerHistoryParams = array_merge($customerHistoryParams, $filterQueryArray);
                                                    }
                                                    $customerHistoryUrl = base_url() . 'Page/customerHistory?' . http_build_query($customerHistoryParams);
                                                    ?>
                                                    <tr>
                                                        <td><?= !empty($row->paymentID) ? '#' . htmlspecialchars((string) $row->paymentID, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                        <td>
                                                            <?php if (!empty($row->InvoiceNo)): ?>
                                                                <a href="<?= base_url(); ?>Page/invoice?id=<?= htmlspecialchars((string) ($row->orderID ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="invoice-link">
                                                                    #<?= htmlspecialchars((string) $row->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="payment-date"><?= htmlspecialchars((string) $row->PDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-right"><?= number_format($cashPaid, 2); ?></td>
                                                        <td class="text-right"><?= number_format($taxAmount, 2); ?></td>
                                                        <td class="text-right"><?= number_format($grossAmount, 2); ?></td>
                                                        <td><?= !empty($row->ORNo) ? htmlspecialchars((string) $row->ORNo, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                        <td><?= !empty($row->PaymentReference) ? htmlspecialchars((string) $row->PaymentReference, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                        <td>
                                                            <a class="payor-link" href="<?= htmlspecialchars($customerHistoryUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars((string) $row->Customer, ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                            <?php if (!empty($row->CustID)): ?>
                                                                <div class="payor-sub">Client ID <?= htmlspecialchars((string) $row->CustID, ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($isAdmin): ?>
                                                                <div class="payment-actions">
                                                                    <div class="payment-actions-desktop">
                                                                        <a class="action-icon edit" href="<?= base_url(); ?>Page/updatePayment?id=<?= (int) $row->paymentID; ?>" data-label="Edit" title="Edit">
                                                                            <i class="mdi mdi-square-edit-outline"></i>
                                                                            <span class="sr-only">Edit</span>
                                                                        </a>
                                                                        <a class="action-icon void" href="javascript:void(0);" data-toggle="modal" data-target="#voidPaymentModal" data-paymentid="<?= (int) $row->paymentID; ?>" data-orno="<?= htmlspecialchars($row->ORNo ?? ''); ?>" onclick="prepareVoidPaymentModal(this)" data-label="Void" title="Void">
                                                                            <i class="mdi mdi-cancel"></i>
                                                                            <span class="sr-only">Void</span>
                                                                        </a>
                                                                        <a class="action-icon delete" href="<?= base_url(); ?>Page/deletePayment?id=<?= (int) $row->paymentID; ?>" onclick="return confirm('Are you sure you want to delete this record?');" data-label="Delete" title="Delete">
                                                                            <i class="mdi mdi-trash-can-outline"></i>
                                                                            <span class="sr-only">Delete</span>
                                                                        </a>
                                                                    </div>
                                                                    <div class="dropdown payment-actions-mobile">
                                                                        <button
                                                                            class="action-overflow-toggle dropdown-toggle"
                                                                            type="button"
                                                                            id="paymentActionsMenu<?= (int) $row->paymentID; ?>"
                                                                            data-toggle="dropdown"
                                                                            aria-haspopup="true"
                                                                            aria-expanded="false"
                                                                            title="More actions">
                                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                                            <span class="sr-only">Open payment actions</span>
                                                                        </button>
                                                                        <div class="dropdown-menu dropdown-menu-right payment-actions-menu" aria-labelledby="paymentActionsMenu<?= (int) $row->paymentID; ?>">
                                                                            <a class="dropdown-item" href="<?= base_url(); ?>Page/updatePayment?id=<?= (int) $row->paymentID; ?>">
                                                                                <i class="mdi mdi-square-edit-outline"></i>
                                                                                <span>Edit Payment</span>
                                                                            </a>
                                                                            <a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#voidPaymentModal" data-paymentid="<?= (int) $row->paymentID; ?>" data-orno="<?= htmlspecialchars($row->ORNo ?? ''); ?>" onclick="prepareVoidPaymentModal(this)">
                                                                                <i class="mdi mdi-cancel"></i>
                                                                                <span>Void Payment</span>
                                                                            </a>
                                                                            <a class="dropdown-item text-danger" href="<?= base_url(); ?>Page/deletePayment?id=<?= (int) $row->paymentID; ?>" onclick="return confirm('Are you sure you want to delete this record?');">
                                                                                <i class="mdi mdi-trash-can-outline"></i>
                                                                                <span>Delete Payment</span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="empty-action">View only</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="10" class="text-center text-muted py-4">No payments found for the selected date range.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Modal -->
                    <div class="modal fade payment-list-page berps-form-modal" id="filterModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title mb-0">
                                        <i class="mdi mdi-filter-outline mr-2"></i>Filter Collections
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="get" action="<?= base_url(); ?>Page/paymentList" id="filterForm">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="payment-filter-from">From Date</label>
                                                <input type="date" class="form-control" id="payment-filter-from" name="date_from" value="<?= htmlspecialchars($filterDateFromValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="payment-filter-to">To Date</label>
                                                <input type="date" class="form-control" id="payment-filter-to" name="date_to" value="<?= htmlspecialchars($filterDateToValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="alert alert-info mt-3 mb-0" style="border-radius: var(--radius-md); background: var(--primary-soft); border: none; color: var(--primary-2);">
                                        <i class="mdi mdi-information-outline mr-2"></i>
                                        Showing payments for <strong><?= htmlspecialchars($rangeSummaryLabel, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a href="<?= base_url(); ?>Page/paymentList" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-calendar-today"></i>
                                        Today
                                    </a>
                                    <button type="submit" form="filterForm" class="btn-submit">
                                        <i class="mdi mdi-filter-outline"></i>
                                        Apply Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Void Payment Modal -->
                    <div class="modal fade berps-form-modal" id="voidPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content" style="border-radius: 22px; overflow: hidden; box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);">
                                <div class="modal-header" style="background: linear-gradient(135deg, #d97706, #f59e0b); border: none; padding: 22px 24px;">
                                    <h5 class="modal-title" style="color: #fff; font-size: 1.08rem; font-weight: 800; letter-spacing: -0.02em;">
                                        <i class="mdi mdi-cancel mr-2"></i>Void Payment
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; text-shadow: none; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); width: 38px; height: 38px; border-radius: 50%; padding: 0; margin: 0; line-height: 1;">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%); padding: 24px;">
                                    <form id="voidPaymentForm" method="post" action="<?= base_url(); ?>Page/voidPayment">
                                        <input type="hidden" name="paymentID" id="voidPaymentID" value="">

                                        <div class="alert" style="border: none; border-radius: 14px; background: #fffbeb; color: #92400e; font-size: 0.9rem;">
                                            <i class="mdi mdi-alert mr-2"></i>
                                            <strong>Warning:</strong> Voiding a payment will cancel this record and reverse its effect on the invoice balance. This action cannot be undone.
                                        </div>

                                        <div class="form-group" style="margin-top: 20px;">
                                            <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                                OR Number
                                            </label>
                                            <input type="text" id="voidORNo" class="form-control" readonly style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);">
                                        </div>

                                        <div class="form-group" style="margin-top: 16px;">
                                            <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                                Reason for Voiding <span style="color: #d97706;">*</span>
                                            </label>
                                            <textarea name="voidReason" id="voidPaymentReason" class="form-control" rows="3" required placeholder="Enter reason for voiding this payment..." style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; resize: vertical;"></textarea>
                                        </div>

                                        <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                                            <button type="button" class="btn" data-dismiss="modal" style="background: #fff; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 20px; font-weight: 600; margin-right: 10px;">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn" style="background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-weight: 700; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3);">
                                                <i class="mdi mdi-cancel mr-1"></i>Void Payment
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <div class="modal fade payment-list-page berps-form-modal" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mb-0">New Payment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addPayment" novalidate id="paymentModalForm">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="payment-date">Date</label>
                                <input type="date" class="form-control" id="payment-date" name="PDate" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="payment-amount">Amount Paid</label>
                                <input type="number" class="form-control" id="payment-amount" name="AmountPaid" min="0" step="0.01" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="payment-orno">O.R. No.</label>
                                <input type="text" class="form-control" id="payment-orno" name="ORNo">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="payment-reference">Reference</label>
                                <input type="text" class="form-control" id="payment-reference" name="PaymentReference" placeholder="Optional">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="payment-invoice">Invoice No.</label>
                                <input type="text" class="form-control" id="payment-invoice" name="InvoiceNo" placeholder="Optional">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="payment-customer">Payor</label>
                                <select class="custom-select" id="payment-customer" name="CustID" required>
                                    <option value="">Select payor</option>
                                    <?php if (!empty($clients)): ?>
                                        <?php foreach ($clients as $customer): ?>
                                            <option value="<?= htmlspecialchars((string) $customer->CustID, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars((string) $customer->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string) $customer->CustID, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="payment-description">Transaction / Description</label>
                                <input type="text" class="form-control" id="payment-description" name="TransDescription" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit" form="paymentModalForm" class="btn-submit">
                        <i class="mdi mdi-check-circle-outline"></i>
                        Accept Payment
                    </button>
                    <button type="reset" form="paymentModalForm" class="btn btn-outline-secondary">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        (function($) {
            'use strict';

            $(document).ready(function() {
                var $paymentTable = $('#payment-table');

                if ($paymentTable.length && $paymentTable.find('tbody tr').not(':has(td[colspan])').length > 0) {
                    try {
                        if ($.fn.DataTable.isDataTable('#payment-table')) {
                            $paymentTable.DataTable().destroy();
                        }
                        var table = $paymentTable.DataTable({
                            responsive: true,
                            autoWidth: false,
                            order: [],
                            pageLength: 10,
                            lengthMenu: [10, 25, 50, 100],
                            dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6 text-sm-right"f>>' +
                                 'rt' +
                                 '<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6"p>>',
                            language: {
                                emptyTable: 'No payments found.',
                                search: 'Search:',
                                searchPlaceholder: 'Invoice no, payor, OR no...',
                                lengthMenu: 'Show _MENU_ entries',
                                info: 'Showing _START_ to _END_ of _TOTAL_ payments'
                            },
                            columnDefs: [{
                                targets: [3, 4, 5],
                                className: 'text-right'
                            }, {
                                targets: -1,
                                orderable: false,
                                searchable: false
                            }]
                        });
                    } catch (e) {
                        console.error('DataTables initialization error:', e);
                    }
                }

                $('#paymentModal').on('hidden.bs.modal', function() {
                    var form = document.getElementById('paymentModalForm');
                    if (form) {
                        form.reset();
                        form.classList.remove('was-validated');
                    }
                });

                // Void Payment Modal Handler
                window.prepareVoidPaymentModal = function(element) {
                    var paymentID = $(element).data('paymentid');
                    var orNo = $(element).data('orno');
                    $('#voidPaymentID').val(paymentID);
                    $('#voidORNo').val(orNo);
                    $('#voidPaymentReason').val('');
                };

                // Search Payment Modal Handler
                var searchPaymentModal = $('#searchPaymentModal');
                var searchInput = $('#searchPaymentInput');
                var searchBtn = $('#searchPaymentBtn');
                var searchResults = $('#searchResults');
                var searchResultsContent = $('#searchResultsContent');
                var searchLoading = $('#searchLoading');
                var searchNoResults = $('#searchNoResults');

                // Search on Enter key
                searchInput.on('keypress', function(e) {
                    if (e.which === 13) {
                        searchPayment();
                    }
                });

                // Search on button click
                searchBtn.on('click', searchPayment);

                function searchPayment() {
                    var searchTerm = searchInput.val().trim();

                    if (!searchTerm) {
                        alert('Please enter a Payment ID or Invoice Number');
                        return;
                    }

                    // Show loading
                    searchResults.hide();
                    searchNoResults.hide();
                    searchLoading.show();

                    // Make AJAX call to search for payment
                    $.ajax({
                        url: '<?= base_url(); ?>Page/searchPayment',
                        method: 'POST',
                        data: {
                            search_term: searchTerm
                        },
                        success: function(response) {
                            searchLoading.hide();

                            if (response.success && response.data) {
                                displaySearchResults(response.data);
                            } else {
                                searchNoResults.show();
                            }
                        },
                        error: function() {
                            searchLoading.hide();
                            searchNoResults.show();
                            alert('Error searching for payment. Please try again.');
                        }
                    });
                }

                function displaySearchResults(data) {
                    var html = '';

                    if (Array.isArray(data)) {
                        data.forEach(function(payment) {
                            html += createPaymentResultHTML(payment);
                        });
                    } else {
                        html += createPaymentResultHTML(data);
                    }

                    searchResultsContent.html(html);
                    searchResults.show();
                }

                function createPaymentResultHTML(payment) {
                    var amountPaid = parseFloat(payment.AmountPaid || 0);
                    var taxAmount = parseFloat(payment.TaxAmount || 0);
                    var totalCredit = amountPaid + taxAmount;

                    return `
                    <div class="search-result-item">
                        <div class="search-result-header">
                            <h6 class="search-result-title">
                                Payment ID: #${payment.paymentID || 'N/A'}
                            </h6>
                            <span class="badge badge-primary">${payment.PDate || 'No Date'}</span>
                        </div>
                        <div class="search-result-details">
                            <div class="search-result-detail">
                                <strong>Invoice:</strong> ${payment.InvoiceNo || 'N/A'}
                            </div>
                            <div class="search-result-detail">
                                <strong>Customer:</strong> ${payment.Customer || 'N/A'}
                            </div>
                            <div class="search-result-detail">
                                <strong>O.R. No:</strong> ${payment.ORNo || 'N/A'}
                            </div>
                            <div class="search-result-detail">
                                <strong>Total Credit:</strong> ${totalCredit.toFixed(2)}
                            </div>
                        </div>
                        <div class="search-result-actions">
                            <a href="<?= base_url(); ?>Page/updatePayment?id=${payment.paymentID}"
                               class="btn-edit-payment"
                               target="_blank">
                                <i class="mdi mdi-pencil"></i> Edit Payment
                            </a>
                        </div>
                    </div>
                `;
                }

                // Reset modal when hidden
                searchPaymentModal.on('hidden.bs.modal', function() {
                    searchInput.val('');
                    searchResults.hide();
                    searchNoResults.hide();
                    searchLoading.hide();
                    searchResultsContent.html('');
                });
            });
        })(jQuery);
    </script>

    <!-- Special Search Payment Modal -->
    <div class="modal fade berps-form-modal" id="searchPaymentModal" tabindex="-1" role="dialog" aria-labelledby="searchPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchPaymentModalLabel">
                        <i class="mdi mdi-magnify"></i>
                        Find & Edit Payment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="search-form">
                        <div class="form-group">
                            <label for="searchPaymentInput">Search by Payment ID or Invoice Number</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchPaymentInput"
                                    placeholder="Enter Payment ID (e.g., 123) or Invoice No (e.g., 456)"
                                    autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="searchPaymentBtn">
                                        <i class="mdi mdi-magnify"></i> Search
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="mdi mdi-information"></i>
                                Enter just the number (e.g., "123" for Payment ID or "456" for Invoice No)
                            </small>
                        </div>
                    </div>

                    <div id="searchResults" style="display: none;">
                        <hr>
                        <h6><i class="mdi mdi-clipboard-check"></i> Search Results</h6>
                        <div id="searchResultsContent"></div>
                    </div>

                    <div id="searchLoading" style="display: none; text-align: center; padding: 20px;">
                        <i class="mdi mdi-loading mdi-spin"></i> Searching...
                    </div>

                    <div id="searchNoResults" style="display: none; text-align: center; padding: 20px;">
                        <i class="mdi mdi-clipboard-off"></i>
                        <p>No payment found matching your search.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="mdi mdi-close"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>


</body>

</html>