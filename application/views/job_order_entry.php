<?php
$record = isset($record) ? $record : null;
$clients = isset($data2) && is_array($data2) ? $data2 : [];
$serviceFees = isset($serviceFees) && is_array($serviceFees) ? $serviceFees : [];
$isEditMode = !empty($isEditMode);
$invoiceNo = isset($nextInvoiceNo) ? (string) $nextInvoiceNo : ($record ? (string) $record->InvoiceNo : '');
$customerId = $record ? trim((string) ($record->CustID ?? '')) : '';
$customerAddress = $record ? trim((string) ($record->CustAddress ?? '')) : '';
$jobDescription = $record ? trim((string) ($record->JobDescription ?? '')) : '';
$notes = $record ? trim((string) ($record->Notes ?? '')) : '';
$amountPaid = $record ? (float) ($record->AmountPaid ?? 0) : 0;
$totalDue = $record ? (float) ($record->TotalDue ?? 0) : 0;
$balance = $record ? (float) ($record->Balance ?? 0) : 0;
$itemQuantity = $record && isset($record->itemQuantity) && (float) $record->itemQuantity > 0
    ? (int) $record->itemQuantity
    : 1;
$itemDurationUnit = $record && trim((string) ($record->itemDurationUnit ?? '')) !== ''
    ? trim((string) $record->itemDurationUnit)
    : 'each';
$itemUnitPrice = $record
    ? (float) (($record->itemUnitPrice ?? 0) > 0 ? $record->itemUnitPrice : (($itemQuantity > 0) ? ($totalDue / $itemQuantity) : $totalDue))
    : 0;
$formAction = isset($formAction) ? (string) $formAction : base_url() . 'Page/addJO';
$backUrl = isset($backUrl) ? (string) $backUrl : base_url() . 'Page/joList';
$backLabel = isset($backLabel) ? (string) $backLabel : 'Job Order List';
$formReturnUrl = isset($formReturnUrl) ? (string) $formReturnUrl : base_url() . 'Page/jobOrderEntry';

$buildServiceFeeDescription = static function ($serviceRow) {
    $category = trim((string) (is_object($serviceRow) ? ($serviceRow->FeesDescription ?? '') : ($serviceRow['FeesDescription'] ?? '')));
    $subCategory = trim((string) (is_object($serviceRow) ? ($serviceRow->subCategory ?? '') : ($serviceRow['subCategory'] ?? '')));
    $details = trim((string) (is_object($serviceRow) ? ($serviceRow->feeDetails ?? '') : ($serviceRow['feeDetails'] ?? '')));

    $parts = array();
    if ($category !== '') {
        $parts[] = $category;
    }
    if ($subCategory !== '') {
        $parts[] = $subCategory;
    }

    $description = !empty($parts) ? implode(' - ', $parts) : ($category !== '' ? $category : 'Service item');
    if ($details !== '') {
        $description .= ' (' . $details . ')';
    }

    return $description;
};

$serviceCatalog = array();
$serviceCategories = array();
$selectedServiceCategory = '';
$selectedServiceFeeId = '';
foreach ($serviceFees as $serviceRow) {
    $category = trim((string) ($serviceRow->FeesDescription ?? ''));
    if ($category === '') {
        continue;
    }

    $serviceCategories[$category] = $category;
    $catalogItem = array(
        'id' => (int) ($serviceRow->feesID ?? 0),
        'category' => $category,
        'subCategory' => trim((string) ($serviceRow->subCategory ?? '')),
        'details' => trim((string) ($serviceRow->feeDetails ?? '')),
        'amount' => number_format((float) ($serviceRow->Amount ?? 0), 2, '.', ''),
        'description' => $buildServiceFeeDescription($serviceRow),
    );

    if ($selectedServiceFeeId === '' && $jobDescription !== '') {
        $descriptionMatches = strcasecmp($catalogItem['description'], $jobDescription) === 0;
        $amountMatches = abs((float) $catalogItem['amount'] - $totalDue) < 0.005;
        if ($descriptionMatches && $amountMatches) {
            $selectedServiceFeeId = (string) $catalogItem['id'];
            $selectedServiceCategory = $catalogItem['category'];
        }
    }

    $serviceCatalog[] = $catalogItem;
}

if (!empty($serviceCategories)) {
    natcasesort($serviceCategories);
}
$serviceCategories = array_values($serviceCategories);
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
                <div class="container-fluid job-order-entry-page">

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
                        .job-order-entry-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.96);
                            --surface-strong: #ffffff;
                            --surface-soft: #f8fbff;
                            --line: #e4ebf4;
                            --line-strong: #cfdbea;
                            --text: #142235;
                            --text-soft: #617489;
                            --text-faint: #8ea0b5;
                            --primary: #1d4ed8;
                            --primary-2: #1e40af;
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

                        .job-order-entry-page * {
                            box-sizing: border-box;
                        }

                        .job-order-entry-page .entry-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .job-order-entry-page .entry-eyebrow {
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

                        .job-order-entry-page .entry-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .job-order-entry-page .entry-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2.1rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .job-order-entry-page .entry-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .job-order-entry-page .entry-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .job-order-entry-page .btn-action,
                        .job-order-entry-page .btn-submit {
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

                        .job-order-entry-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .job-order-entry-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .job-order-entry-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .job-order-entry-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .job-order-entry-page .entry-layout {
                            display: grid;
                            grid-template-columns: minmax(0, 1.7fr) minmax(320px, 0.95fr);
                            gap: 20px;
                            align-items: start;
                        }

                        .job-order-entry-page .entry-main,
                        .job-order-entry-page .entry-side {
                            display: grid;
                            gap: 20px;
                        }

                        .job-order-entry-page .entry-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .job-order-entry-page .entry-card-head {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .job-order-entry-page .entry-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .job-order-entry-page .entry-card-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .job-order-entry-page .entry-card-body {
                            padding: 22px;
                        }

                        .job-order-entry-page .entry-side .entry-card {
                            position: sticky;
                            top: 88px;
                        }

                        .job-order-entry-page label {
                            color: var(--text);
                            font-size: 0.82rem;
                            font-weight: 700;
                            letter-spacing: 0.02em;
                            margin-bottom: 8px;
                        }

                        .job-order-entry-page .form-control,
                        .job-order-entry-page .custom-select {
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            min-height: 46px;
                            color: var(--text);
                            box-shadow: none;
                        }

                        .job-order-entry-page .form-control:focus,
                        .job-order-entry-page .custom-select:focus {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .job-order-entry-page textarea.form-control {
                            min-height: 140px;
                            resize: vertical;
                        }

                        .job-order-entry-page .summary-grid {
                            display: grid;
                            gap: 14px;
                        }

                        .job-order-entry-page .summary-tile {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-md);
                            background: var(--surface-soft);
                            padding: 14px 16px;
                        }

                        .job-order-entry-page .summary-label {
                            color: var(--text-faint);
                            font-size: 0.76rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 8px;
                        }

                        .job-order-entry-page .summary-meta {
                            display: flex;
                            justify-content: space-between;
                            gap: 12px;
                            align-items: center;
                            padding-top: 14px;
                            margin-top: 14px;
                            border-top: 1px solid var(--line);
                            flex-wrap: wrap;
                        }

                        .job-order-entry-page .summary-meta strong {
                            color: var(--text);
                            display: block;
                        }

                        .job-order-entry-page .summary-meta span {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                        }

                        .job-order-entry-page .info-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 8px 12px;
                            border-radius: 999px;
                            border: 1px solid #dbeafe;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.8rem;
                            font-weight: 700;
                        }

                        .job-order-entry-page .helper-note {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 8px;
                        }

                        @media (max-width: 1199px) {
                            .job-order-entry-page .entry-layout {
                                grid-template-columns: 1fr;
                            }

                            .job-order-entry-page .entry-side .entry-card {
                                position: static;
                            }
                        }

                        @media (max-width: 767px) {
                            .job-order-entry-page .entry-title {
                                font-size: 1.75rem;
                            }

                            .job-order-entry-page .entry-card-body,
                            .job-order-entry-page .entry-card-head {
                                padding-left: 16px;
                                padding-right: 16px;
                            }
                        }
                    </style>

                    <div class="entry-header">
                        <div>
                            <div class="entry-eyebrow">Job Order Workspace</div>
                            <h4 class="entry-title"><?= $isEditMode ? 'Update Job Order' : 'Create Job Order'; ?></h4>
                            <div class="entry-subtitle">Use the full page to encode customer, service, and billing details without squeezing the form into a modal.</div>
                        </div>
                        <div class="entry-actions">
                            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn-action"><?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?></a>
                            <button type="submit" form="jobOrderEntryForm" class="btn-submit"><?= $isEditMode ? 'Update Job Order' : 'Save Job Order'; ?></button>
                        </div>
                    </div>

                    <form id="jobOrderEntryForm" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                        <?php if ($isEditMode && $record): ?>
                            <input type="hidden" name="id" value="<?= (int) $record->orderID; ?>">
                            <input type="hidden" name="return_to" value="joList">
                        <?php else: ?>
                            <input type="hidden" name="open_invoice" value="1">
                            <input type="hidden" name="redirect_to" value="joList">
                        <?php endif; ?>
                        <input type="hidden" name="form_return_url" value="<?= htmlspecialchars($formReturnUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="itemQuantity" id="job-item-quantity" value="<?= htmlspecialchars((string) $itemQuantity, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="itemDurationUnit" id="job-item-unit" value="<?= htmlspecialchars((string) $itemDurationUnit, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="itemUnitPrice" id="job-item-unit-price" value="<?= htmlspecialchars(number_format($itemUnitPrice, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="entry-layout">
                            <div class="entry-main">
                                <div class="entry-card">
                                    <div class="entry-card-head">
                                        <h5 class="entry-card-title">Job Order Details</h5>
                                        <div class="entry-card-subtitle">Choose the customer first so the invoice number and address stay visible while you encode.</div>
                                    </div>
                                    <div class="entry-card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for="job-order-invoice-number">Invoice No.</label>
                                                <input type="text" class="form-control" id="job-order-invoice-number" name="InvoiceNo" value="<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>" readonly required>
                                            </div>
                                            <div class="form-group col-md-9">
                                                <label for="job-order-customer">Customer</label>
                                                <select class="custom-select" id="job-order-customer" name="CustID" required>
                                                    <option value="" data-address="">Select customer</option>
                                                    <?php foreach ($clients as $client): ?>
                                                        <option value="<?= htmlspecialchars((string) $client->CustID, ENT_QUOTES, 'UTF-8'); ?>" data-address="<?= htmlspecialchars((string) ($client->Address ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?= $customerId === (string) $client->CustID ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars((string) $client->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string) $client->CustID, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="job-order-address">Customer Address</label>
                                            <input type="text" class="form-control" id="job-order-address" name="CustAddress" value="<?= htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-head">
                                        <h5 class="entry-card-title">Service Encoding</h5>
                                        <div class="entry-card-subtitle">Use a saved service and sub-category to auto-fill pricing, or encode the job manually.</div>
                                    </div>
                                    <div class="entry-card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="job-order-service-category">Service Category</label>
                                                <select class="custom-select" id="job-order-service-category" data-initial-category="<?= htmlspecialchars($selectedServiceCategory, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <option value="">Manual entry / no preset</option>
                                                    <?php foreach ($serviceCategories as $serviceCategory): ?>
                                                        <option value="<?= htmlspecialchars((string) $serviceCategory, ENT_QUOTES, 'UTF-8'); ?>" <?= $selectedServiceCategory === (string) $serviceCategory ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars((string) $serviceCategory, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="job-order-service-plan">Sub Category / Plan</label>
                                                <select class="custom-select" id="job-order-service-plan" data-initial-fee-id="<?= htmlspecialchars($selectedServiceFeeId, ENT_QUOTES, 'UTF-8'); ?>" <?= empty($serviceCategories) ? 'disabled' : ''; ?>>
                                                    <option value=""><?= $selectedServiceCategory !== '' ? 'Select a sub category / plan' : 'Select a service category first'; ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="job-order-service-details">Price List Details</label>
                                            <input type="text" class="form-control" id="job-order-service-details" value="" readonly>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-7">
                                                <label for="job-order-description">Job Description</label>
                                                <input type="text" class="form-control" id="job-order-description" name="JobDescription" value="<?= htmlspecialchars($jobDescription, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                            <div class="form-group col-md-5">
                                                <label for="job-order-total-due">Total Due</label>
                                                <input type="number" class="form-control" id="job-order-total-due" name="TotalDue" min="0" step="0.01" value="<?= htmlspecialchars(number_format($totalDue, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                                <div class="helper-note">This can be filled from the selected price list entry or encoded manually.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="entry-side">
                                <div class="entry-card">
                                    <div class="entry-card-head">
                                        <h5 class="entry-card-title">Billing Summary</h5>
                                        <div class="entry-card-subtitle">Keep notes and billing status visible while you encode.</div>
                                    </div>
                                    <div class="entry-card-body">
                                        <div class="form-group">
                                            <label for="job-order-notes">Notes</label>
                                            <textarea class="form-control" id="job-order-notes" name="Notes" placeholder="Optional notes for this job order"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>

                                        <div class="summary-grid mt-4">
                                            <div class="summary-tile">
                                                <div class="summary-label">Amount Paid</div>
                                                <input type="number" class="form-control" id="job-order-amount-paid" name="AmountPaid" min="0" step="0.01" value="<?= htmlspecialchars(number_format($amountPaid, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                            <div class="summary-tile">
                                                <div class="summary-label">Balance</div>
                                                <input type="number" class="form-control" id="job-order-balance" name="Balance" min="0" step="0.01" value="<?= htmlspecialchars(number_format($balance, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="summary-meta">
                                            <div>
                                                <strong><?= $isEditMode ? 'Editing Existing Job Order' : 'New Job Order'; ?></strong>
                                                <span><?= $isEditMode ? 'Saved payments stay protected while totals update.' : 'Saving will open the generated invoice right away.'; ?></span>
                                            </div>
                                            <div class="info-pill"><?= $isEditMode ? 'Edit Mode' : 'Create Mode'; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        (function() {
            var form = document.getElementById('jobOrderEntryForm');
            var customerSelect = document.getElementById('job-order-customer');
            var addressField = document.getElementById('job-order-address');
            var serviceCategoryField = document.getElementById('job-order-service-category');
            var servicePlanField = document.getElementById('job-order-service-plan');
            var serviceDetailsField = document.getElementById('job-order-service-details');
            var descriptionField = document.getElementById('job-order-description');
            var totalDueField = document.getElementById('job-order-total-due');
            var amountPaidField = document.getElementById('job-order-amount-paid');
            var balanceField = document.getElementById('job-order-balance');
            var itemQuantityField = document.getElementById('job-item-quantity');
            var itemUnitField = document.getElementById('job-item-unit');
            var itemUnitPriceField = document.getElementById('job-item-unit-price');
            var serviceCatalog = <?= json_encode(array_values($serviceCatalog), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var serviceCatalogById = {};
            var serviceCatalogByCategory = {};

            function normalizeAmount(value) {
                var parsed = parseFloat(value);
                return isFinite(parsed) && parsed >= 0 ? parsed.toFixed(2) : '0.00';
            }

            function syncAddress() {
                if (!customerSelect || !addressField) {
                    return;
                }

                var selected = customerSelect.options[customerSelect.selectedIndex];
                var address = selected ? (selected.getAttribute('data-address') || '') : '';
                if (customerSelect.value || !addressField.value) {
                    addressField.value = address;
                }
            }

            function recalculateBalance() {
                var totalDue = parseFloat(totalDueField.value) || 0;
                var amountPaid = parseFloat(amountPaidField.value) || 0;
                balanceField.value = Math.max(0, totalDue - amountPaid).toFixed(2);
            }

            function syncInvoiceItemPrice() {
                var quantity = itemQuantityField ? (parseFloat(itemQuantityField.value) || 1) : 1;

                if (itemQuantityField) {
                    itemQuantityField.value = quantity > 0 ? String(Math.round(quantity)) : '1';
                }

                if (itemUnitField && !itemUnitField.value) {
                    itemUnitField.value = 'each';
                }

                if (itemUnitPriceField) {
                    itemUnitPriceField.value = normalizeAmount((parseFloat(totalDueField.value) || 0) / (quantity > 0 ? quantity : 1));
                }
            }

            function buildServicePlanLabel(serviceItem) {
                var label = serviceItem.subCategory || serviceItem.details || 'Default rate';
                var amount = parseFloat(serviceItem.amount || 0) || 0;
                return label + ' - PHP ' + amount.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function populateServicePlans(categoryValue, selectedPlanId) {
                if (!servicePlanField) {
                    return;
                }

                var selectedCategory = categoryValue ? String(categoryValue) : '';
                var plans = selectedCategory && serviceCatalogByCategory[selectedCategory]
                    ? serviceCatalogByCategory[selectedCategory]
                    : [];

                servicePlanField.innerHTML = '';

                var placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = plans.length ? 'Select a sub category / plan' : 'Select a service category first';
                servicePlanField.appendChild(placeholder);

                plans.forEach(function(plan) {
                    var option = document.createElement('option');
                    option.value = String(plan.id || '');
                    option.textContent = buildServicePlanLabel(plan);
                    if (String(plan.id || '') === String(selectedPlanId || '')) {
                        option.selected = true;
                    }
                    servicePlanField.appendChild(option);
                });

                servicePlanField.disabled = plans.length === 0;

                if (!servicePlanField.value && plans.length === 1) {
                    servicePlanField.value = String(plans[0].id || '');
                }
            }

            function applySelectedServicePlan(planId) {
                if (!servicePlanField) {
                    return;
                }

                var serviceItem = serviceCatalogById[String(planId || '')] || null;
                serviceDetailsField.value = serviceItem ? (serviceItem.details || serviceItem.subCategory || '') : '';

                if (!serviceItem) {
                    syncInvoiceItemPrice();
                    recalculateBalance();
                    return;
                }

                if (descriptionField) {
                    descriptionField.value = serviceItem.description || '';
                }

                if (itemQuantityField) {
                    itemQuantityField.value = '1';
                }
                if (itemUnitField) {
                    itemUnitField.value = 'each';
                }

                totalDueField.value = normalizeAmount(serviceItem.amount);
                syncInvoiceItemPrice();
                recalculateBalance();
            }

            if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
                window.jQuery(customerSelect).select2({
                    width: '100%',
                    placeholder: 'Select customer'
                });
                window.jQuery(customerSelect).on('change', syncAddress);
            } else if (customerSelect) {
                customerSelect.addEventListener('change', syncAddress);
            }

            serviceCatalog.forEach(function(serviceItem) {
                var normalized = {
                    id: String(serviceItem.id || ''),
                    category: serviceItem.category ? String(serviceItem.category) : '',
                    subCategory: serviceItem.subCategory ? String(serviceItem.subCategory) : '',
                    details: serviceItem.details ? String(serviceItem.details) : '',
                    amount: normalizeAmount(serviceItem.amount),
                    description: serviceItem.description ? String(serviceItem.description) : ''
                };

                serviceCatalogById[normalized.id] = normalized;
                if (!serviceCatalogByCategory[normalized.category]) {
                    serviceCatalogByCategory[normalized.category] = [];
                }
                serviceCatalogByCategory[normalized.category].push(normalized);
            });

            serviceCategoryField.addEventListener('change', function() {
                populateServicePlans(serviceCategoryField.value || '', '');
                applySelectedServicePlan(servicePlanField.value || '');
            });

            servicePlanField.addEventListener('change', function() {
                applySelectedServicePlan(servicePlanField.value || '');
            });

            totalDueField.addEventListener('input', function() {
                syncInvoiceItemPrice();
                recalculateBalance();
            });

            form.addEventListener('submit', function(event) {
                syncInvoiceItemPrice();
                recalculateBalance();

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            });

            syncAddress();
            populateServicePlans(
                serviceCategoryField.getAttribute('data-initial-category') || serviceCategoryField.value || '',
                servicePlanField.getAttribute('data-initial-fee-id') || servicePlanField.value || ''
            );
            applySelectedServicePlan(servicePlanField.value || servicePlanField.getAttribute('data-initial-fee-id') || '');
            syncInvoiceItemPrice();
            recalculateBalance();
        })();
    </script>

    <!-- Tarpaulin Calculation Modal -->
    <div class="modal fade" id="tarpaulinCalcModal" tabindex="-1" role="dialog" aria-labelledby="tarpaulinCalcModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);">
                <div class="modal-header" style="border-bottom: 1px solid #e4ebf4; background: linear-gradient(135deg, #2563eb, #1d4ed8); border-radius: 16px 16px 0 0; padding: 18px 22px;">
                    <h5 class="modal-title" style="font-weight: 700; color: #fff; margin: 0;">
                        <i class="mdi mdi-calculator" style="margin-right: 8px;"></i>
                        Tarpaulin Calculator
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <div id="tarpaulin-print-only-section">
                        <p style="color: #617489; font-size: 0.9rem; margin-bottom: 20px;">Enter the tarpaulin dimensions to calculate the total area and cost.</p>
                    </div>
                    <div id="tarpaulin-layout-section" style="display: none;">
                        <p style="color: #617489; font-size: 0.9rem; margin-bottom: 20px;">Enter the tarpaulin dimensions and layout fee to calculate the total cost.</p>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #8ea0b5; margin-bottom: 8px; display: block;">Width (feet)</label>
                            <div class="input-group" style="margin-bottom: 16px;">
                                <input type="number" class="form-control" id="tarpaulin-width" min="0" step="0.01" placeholder="e.g., 4" style="border: 1px solid #e4ebf4; border-radius: 10px; padding: 12px 14px; font-size: 1rem; color: #142235; width: 100%;">
                                <div class="input-group-append" style="margin-left: 8px; display: flex; align-items: center;">
                                    <span style="color: #8ea0b5; font-size: 0.9rem;">ft</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #8ea0b5; margin-bottom: 8px; display: block;">Height (feet)</label>
                            <div class="input-group" style="margin-bottom: 16px;">
                                <input type="number" class="form-control" id="tarpaulin-height" min="0" step="0.01" placeholder="e.g., 6" style="border: 1px solid #e4ebf4; border-radius: 10px; padding: 12px 14px; font-size: 1rem; color: #142235; width: 100%;">
                                <div class="input-group-append" style="margin-left: 8px; display: flex; align-items: center;">
                                    <span style="color: #8ea0b5; font-size: 0.9rem;">ft</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="tarpaulin-layout-fee-row" style="display: none;">
                        <div class="col-12">
                            <label style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #8ea0b5; margin-bottom: 8px; display: block;">Layout Fee (PHP)</label>
                            <div class="input-group" style="margin-bottom: 16px;">
                                <input type="number" class="form-control" id="tarpaulin-layout-fee" min="0" step="0.01" placeholder="e.g., 500" style="border: 1px solid #e4ebf4; border-radius: 10px; padding: 12px 14px; font-size: 1rem; color: #142235; width: 100%;">
                            </div>
                        </div>
                    </div>

                    <div class="row" id="tarpaulin-price-row">
                        <div class="col-12">
                            <label style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #8ea0b5; margin-bottom: 8px; display: block;">Price per Square Foot (PHP)</label>
                            <div class="input-group" style="margin-bottom: 16px;">
                                <input type="number" class="form-control" id="tarpaulin-price-per-sqft" min="0" step="0.01" value="15" style="border: 1px solid #e4ebf4; border-radius: 10px; padding: 12px 14px; font-size: 1rem; color: #142235; width: 100%;">
                            </div>
                        </div>
                    </div>

                    <div style="background: #f8fbff; border: 1px solid #e4ebf4; border-radius: 12px; padding: 16px; margin-top: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="color: #617489; font-size: 0.9rem;">Area:</span>
                            <span id="tarpaulin-area-display" style="color: #142235; font-weight: 600; font-size: 1rem;">0 sq ft</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;" id="tarpaulin-printing-cost-row">
                            <span style="color: #617489; font-size: 0.9rem;">Printing Cost:</span>
                            <span id="tarpaulin-printing-cost-display" style="color: #142235; font-weight: 600; font-size: 1rem;">PHP 0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; display: none;" id="tarpaulin-layout-fee-display-row">
                            <span style="color: #617489; font-size: 0.9rem;">Layout Fee:</span>
                            <span id="tarpaulin-layout-fee-display" style="color: #142235; font-weight: 600; font-size: 1rem;">PHP 0.00</span>
                        </div>
                        <div style="border-top: 1px solid #e4ebf4; padding-top: 12px; margin-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #142235; font-size: 1rem; font-weight: 700;">Total Due:</span>
                            <span id="tarpaulin-total-display" style="color: #059669; font-weight: 800; font-size: 1.4rem;">PHP 0.00</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e4ebf4; padding: 16px 24px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn" data-dismiss="modal" style="border: 1px solid #cfdbea; color: #142235; background: #fff; border-radius: 10px; padding: 10px 18px; font-weight: 600;">Cancel</button>
                    <button type="button" id="tarpaulin-apply-btn" style="border: none; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border-radius: 10px; padding: 10px 22px; font-weight: 700; cursor: pointer;">Apply to Job Order</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tarpaulin Calculator Logic
        (function() {
            var serviceCategoryField = document.getElementById('job-order-service-category');
            var servicePlanField = document.getElementById('job-order-service-plan');
            var totalDueField = document.getElementById('job-order-total-due');
            var descriptionField = document.getElementById('job-order-description');

            var tarpaulinModal = document.getElementById('tarpaulinCalcModal');
            var widthField = document.getElementById('tarpaulin-width');
            var heightField = document.getElementById('tarpaulin-height');
            var pricePerSqftField = document.getElementById('tarpaulin-price-per-sqft');
            var layoutFeeField = document.getElementById('tarpaulin-layout-fee');
            var layoutFeeRow = document.getElementById('tarpaulin-layout-fee-row');
            var layoutFeeDisplayRow = document.getElementById('tarpaulin-layout-fee-display-row');
            var printOnlySection = document.getElementById('tarpaulin-print-only-section');
            var layoutSection = document.getElementById('tarpaulin-layout-section');

            var areaDisplay = document.getElementById('tarpaulin-area-display');
            var printingCostDisplay = document.getElementById('tarpaulin-printing-cost-display');
            var layoutFeeDisplay = document.getElementById('tarpaulin-layout-fee-display');
            var totalDisplay = document.getElementById('tarpaulin-total-display');
            var applyBtn = document.getElementById('tarpaulin-apply-btn');

            var isTarpaulinPrintAndLayout = false;
            var isTarpaulinPrintOnly = false;

            function checkTarpaulinService() {
                var category = serviceCategoryField ? (serviceCategoryField.value || '') : '';
                var planText = servicePlanField && servicePlanField.options[servicePlanField.selectedIndex] ?
                    servicePlanField.options[servicePlanField.selectedIndex].textContent : '';

                isTarpaulinPrintAndLayout = false;
                isTarpaulinPrintOnly = false;

                if (category === 'Tarpaulin Printing') {
                    if (planText.toLowerCase().indexOf('print and layout') !== -1) {
                        isTarpaulinPrintAndLayout = true;
                    } else if (planText.toLowerCase().indexOf('print only') !== -1) {
                        isTarpaulinPrintOnly = true;
                    }
                }

                return isTarpaulinPrintAndLayout || isTarpaulinPrintOnly;
            }

            function calculateTarpaulin() {
                var width = parseFloat(widthField.value) || 0;
                var height = parseFloat(heightField.value) || 0;
                var pricePerSqft = parseFloat(pricePerSqftField.value) || 15;
                var layoutFee = isTarpaulinPrintAndLayout ? (parseFloat(layoutFeeField.value) || 0) : 0;

                var area = width * height;
                var printingCost = area * pricePerSqft;
                var total = printingCost + layoutFee;

                areaDisplay.textContent = area.toFixed(2) + ' sq ft';
                printingCostDisplay.textContent = 'PHP ' + printingCost.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                layoutFeeDisplay.textContent = 'PHP ' + layoutFee.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                totalDisplay.textContent = 'PHP ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                return { area: area, printingCost: printingCost, layoutFee: layoutFee, total: total };
            }

            function showTarpaulinModal() {
                if (!checkTarpaulinService()) {
                    return;
                }

                if (isTarpaulinPrintAndLayout) {
                    layoutFeeRow.style.display = 'flex';
                    layoutFeeDisplayRow.style.display = 'flex';
                    printOnlySection.style.display = 'none';
                    layoutSection.style.display = 'block';
                } else {
                    layoutFeeRow.style.display = 'none';
                    layoutFeeDisplayRow.style.display = 'none';
                    printOnlySection.style.display = 'block';
                    layoutSection.style.display = 'none';
                    layoutFeeField.value = '';
                }

                // Show modal using Bootstrap
                if (window.jQuery && window.jQuery.fn.modal) {
                    window.jQuery(tarpaulinModal).modal('show');
                }

                calculateTarpaulin();
            }

            function applyTarpaulinCalculation() {
                var calc = calculateTarpaulin();

                if (totalDueField) {
                    totalDueField.value = calc.total.toFixed(2);
                }

                if (descriptionField) {
                    var width = parseFloat(widthField.value) || 0;
                    var height = parseFloat(heightField.value) || 0;
                    var desc = 'Tarpaulin ' + width.toFixed(2) + 'ft x ' + height.toFixed(2) + 'ft';
                    if (isTarpaulinPrintAndLayout && calc.layoutFee > 0) {
                        desc += ' (with Layout)';
                    }
                    descriptionField.value = desc;
                }

                // Trigger sync events
                if (totalDueField) {
                    totalDueField.dispatchEvent(new Event('input'));
                }

                // Close modal
                if (window.jQuery && window.jQuery.fn.modal) {
                    window.jQuery(tarpaulinModal).modal('hide');
                }
            }

            // Watch for service plan changes
            if (servicePlanField) {
                servicePlanField.addEventListener('change', function() {
                    if (checkTarpaulinService()) {
                        showTarpaulinModal();
                    }
                });
            }

            // Watch for service category changes
            if (serviceCategoryField) {
                serviceCategoryField.addEventListener('change', function() {
                    // When category changes to Tarpaulin, wait for plan selection
                    if (serviceCategoryField.value === 'Tarpaulin Printing') {
                        // Don't show modal yet, wait for sub-category selection
                    }
                });
            }

            // Calculator input events
            if (widthField) widthField.addEventListener('input', calculateTarpaulin);
            if (heightField) heightField.addEventListener('input', calculateTarpaulin);
            if (pricePerSqftField) pricePerSqftField.addEventListener('input', calculateTarpaulin);
            if (layoutFeeField) layoutFeeField.addEventListener('input', calculateTarpaulin);

            // Apply button
            if (applyBtn) applyBtn.addEventListener('click', applyTarpaulinCalculation);
        })();
    </script>

</body>

</html>
