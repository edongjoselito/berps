<?php
$record = isset($record) ? $record : null;
$clients = isset($data2) && is_array($data2) ? $data2 : [];
$invoiceItems = isset($invoiceItems) && is_array($invoiceItems) ? $invoiceItems : [];
$invoiceUnits = isset($invoiceUnits) && is_array($invoiceUnits) ? $invoiceUnits : [];
$isEditMode = !empty($isEditMode);
$invoiceNo = isset($nextInvoiceNo) ? (string) $nextInvoiceNo : ($record ? (string) $record->InvoiceNo : '');
$customerId = $record ? trim((string) ($record->CustID ?? '')) : '';
$customerAddress = $record ? trim((string) ($record->CustAddress ?? '')) : '';
$notes = $record ? trim((string) ($record->Notes ?? '')) : '';
$recurringFrequency = $record ? trim((string) ($record->recurringFrequency ?? 'none')) : 'none';
$coverageOption = $record ? trim((string) ($record->coverageOption ?? 'coming')) : 'coming';
$recurringScheduleDate = $record ? trim((string) ($record->recurringScheduleDate ?? '')) : '';
$recurringTerminationDate = $record ? trim((string) ($record->recurringTerminationDate ?? '')) : '';
$invoiceExpirationDate = $record ? trim((string) ($record->invoiceExpirationDate ?? '')) : '';
$jobDescriptionValue = $record ? trim((string) ($record->JobDescription ?? '')) : '';
$amountPaid = $record ? (float) ($record->AmountPaid ?? 0) : 0;
$totalDue = $record ? (float) ($record->TotalDue ?? 0) : 0;
$balance = $record ? (float) ($record->Balance ?? 0) : 0;
$todayDate = date('Y-m-d');
$invoiceDateValue = $record ? trim((string) ($record->TransDate ?? '')) : $todayDate;
if ($invoiceDateValue === '' || $invoiceDateValue === '0000-00-00') {
    $invoiceDateValue = $todayDate;
}
$dueDateValue = $record ? trim((string) ($record->ReceiveDate ?? '')) : $invoiceDateValue;
if ($dueDateValue === '' || $dueDateValue === '0000-00-00') {
    $dueDateValue = $invoiceDateValue;
}
$isGeneratedRecurring = $record && !empty($record->recurringTemplateID);
$isRecurringInvoice = $recurringFrequency !== 'none';
$isOpenDateInvoice = $invoiceExpirationDate === '' || $invoiceExpirationDate === '0000-00-00';
$formAction = isset($formAction) ? (string) $formAction : base_url() . 'Page/addInvoice';
$backUrl = isset($backUrl) ? (string) $backUrl : base_url() . 'Page/invList';
$backLabel = isset($backLabel) ? (string) $backLabel : 'Invoice List';
$formReturnUrl = isset($formReturnUrl) ? (string) $formReturnUrl : base_url() . 'Page/invoiceEntry';
$invoiceUnitOptions = array();
foreach ($invoiceUnits as $unitRow) {
    $unitName = '';
    if (is_object($unitRow)) {
        $unitName = $unitRow->unitName ?? '';
    } elseif (is_array($unitRow)) {
        $unitName = $unitRow['unitName'] ?? '';
    }

    $unitName = strtolower(trim((string) $unitName));
    if ($unitName !== '') {
        $invoiceUnitOptions[$unitName] = $unitName;
    }
}

if (empty($invoiceUnitOptions)) {
    $invoiceUnitOptions['each'] = 'each';
}

$defaultInvoiceUnit = isset($invoiceUnitOptions['each'])
    ? 'each'
    : (string) reset($invoiceUnitOptions);

if (empty($invoiceItems)) {
    $invoiceItems[] = array(
        'itemDescription' => '',
        'itemQuantity' => 1,
        'itemDurationUnit' => $defaultInvoiceUnit,
        'itemUnitPrice' => '0.00',
    );
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
                <div class="container-fluid invoice-entry-page">

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
                        .invoice-entry-page {
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

                        .invoice-entry-page * {
                            box-sizing: border-box;
                        }

                        .invoice-entry-page .entry-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .invoice-entry-page .entry-eyebrow {
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

                        .invoice-entry-page .entry-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .invoice-entry-page .entry-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2.1rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .invoice-entry-page .entry-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .invoice-entry-page .entry-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .invoice-entry-page .btn-action,
                        .invoice-entry-page .btn-submit,
                        .invoice-entry-page .btn-add-row,
                        .invoice-entry-page .btn-remove-row {
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

                        .invoice-entry-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .invoice-entry-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .invoice-entry-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .invoice-entry-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .invoice-entry-page .entry-layout {
                            display: grid;
                            grid-template-columns: minmax(0, 1.85fr) minmax(300px, 0.95fr);
                            gap: 20px;
                            align-items: start;
                        }

                        .invoice-entry-page .entry-main,
                        .invoice-entry-page .entry-side {
                            display: grid;
                            gap: 20px;
                        }

                        .invoice-entry-page .entry-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .invoice-entry-page .entry-card-head {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .invoice-entry-page .entry-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .invoice-entry-page .entry-card-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .invoice-entry-page .entry-card-body {
                            padding: 22px;
                        }

                        .invoice-entry-page .entry-side .entry-card {
                            position: sticky;
                            top: 88px;
                        }

                        .invoice-entry-page label {
                            color: var(--text);
                            font-size: 0.82rem;
                            font-weight: 700;
                            letter-spacing: 0.02em;
                            margin-bottom: 8px;
                        }

                        .invoice-entry-page .form-control,
                        .invoice-entry-page .custom-select {
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            min-height: 46px;
                            color: var(--text);
                            box-shadow: none;
                        }

                        .invoice-entry-page .form-control:focus,
                        .invoice-entry-page .custom-select:focus {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .invoice-entry-page .customer-select-wrap .select2-container {
                            width: 100% !important;
                        }

                        .invoice-entry-page .customer-select-wrap .select2-container--default .select2-selection--single {
                            height: 46px;
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            background: #fff;
                            box-shadow: none;
                            transition: border-color 0.16s ease, box-shadow 0.16s ease;
                        }

                        .invoice-entry-page .customer-select-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
                            line-height: 44px;
                            padding-left: 14px;
                            padding-right: 38px;
                            color: var(--text);
                        }

                        .invoice-entry-page .customer-select-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder {
                            color: var(--text-faint);
                        }

                        .invoice-entry-page .customer-select-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
                            height: 44px;
                            right: 10px;
                        }

                        .invoice-entry-page .customer-select-wrap .select2-container--default.select2-container--focus .select2-selection--single,
                        .invoice-entry-page .customer-select-wrap .select2-container--default.select2-container--open .select2-selection--single {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .select2-dropdown.invoice-entry-select2-dropdown {
                            border-color: var(--line-strong);
                            border-radius: 14px;
                            overflow: hidden;
                            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
                        }

                        .select2-dropdown.invoice-entry-select2-dropdown .select2-search--dropdown {
                            padding: 12px;
                            background: #fff;
                        }

                        .select2-dropdown.invoice-entry-select2-dropdown .select2-search__field {
                            border: 1px solid var(--line-strong);
                            border-radius: 10px;
                            padding: 9px 12px;
                        }

                        .select2-dropdown.invoice-entry-select2-dropdown .select2-results__option {
                            padding: 10px 14px;
                        }

                        .select2-dropdown.invoice-entry-select2-dropdown .select2-results__option--highlighted[aria-selected] {
                            background: var(--primary);
                        }

                        .invoice-entry-page textarea.form-control {
                            min-height: 120px;
                            resize: vertical;
                        }

                        .invoice-entry-page .open-date-toggle {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            padding: 10px 12px;
                            border: 1px dashed var(--line-strong);
                            border-radius: var(--radius-sm);
                            background: #fff;
                            margin-bottom: 12px;
                        }

                        .invoice-entry-page .open-date-toggle input[type="checkbox"] {
                            width: 16px;
                            height: 16px;
                            margin: 0;
                        }

                        .invoice-entry-page .open-date-toggle label {
                            margin: 0;
                            font-size: 0.82rem;
                            font-weight: 700;
                            cursor: pointer;
                        }

                        .invoice-entry-page .label-with-tip {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                        }

                        .invoice-entry-page .field-tooltip {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 18px;
                            height: 18px;
                            border: none;
                            border-radius: 50%;
                            padding: 0;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.72rem;
                            line-height: 1;
                            cursor: pointer;
                            transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease;
                        }

                        .invoice-entry-page .field-tooltip:hover,
                        .invoice-entry-page .field-tooltip:focus {
                            outline: none;
                            background: #dbeafe;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
                            transform: translateY(-1px);
                        }

                        .invoice-entry-page .field-tooltip i {
                            pointer-events: none;
                        }

                        .invoice-entry-page .info-pill {
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

                        .invoice-entry-page .item-builder-head {
                            display: flex;
                            align-items: flex-start;
                            justify-content: space-between;
                            gap: 12px;
                            margin-bottom: 14px;
                            flex-wrap: wrap;
                        }

                        .invoice-entry-page .btn-add-row,
                        .invoice-entry-page .btn-remove-row {
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                            padding: 9px 14px;
                        }

                        .invoice-entry-page .btn-add-row:hover,
                        .invoice-entry-page .btn-remove-row:hover {
                            border-color: var(--primary);
                            color: var(--primary);
                        }

                        .invoice-entry-page .item-row {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            background: var(--surface-strong);
                            box-shadow: var(--shadow-soft);
                            padding: 16px;
                        }

                        .invoice-entry-page .item-row + .item-row {
                            margin-top: 14px;
                        }

                        .invoice-entry-page .item-row-head {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: 12px;
                            margin-bottom: 12px;
                            flex-wrap: wrap;
                        }

                        .invoice-entry-page .item-row-label {
                            font-size: 0.76rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            color: var(--text-faint);
                        }

                        .invoice-entry-page .item-row-total {
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 0.88rem;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .invoice-entry-page .item-row-breakdown,
                        .invoice-entry-page .item-builder-warning {
                            display: block;
                            margin-top: 8px;
                            font-size: 0.84rem;
                        }

                        .invoice-entry-page .item-row-breakdown {
                            color: var(--text-soft);
                        }

                        .invoice-entry-page .item-builder-warning {
                            color: var(--danger);
                            min-height: 20px;
                        }

                        .invoice-entry-page .summary-grid {
                            display: grid;
                            gap: 14px;
                        }

                        .invoice-entry-page .summary-tile {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-md);
                            background: var(--surface-soft);
                            padding: 14px 16px;
                        }

                        .invoice-entry-page .summary-label {
                            color: var(--text-faint);
                            font-size: 0.76rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 8px;
                        }

                        .invoice-entry-page .summary-value {
                            color: var(--text);
                            font-size: 1.1rem;
                            font-weight: 800;
                            letter-spacing: -0.03em;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .invoice-entry-page .summary-value.is-balance {
                            color: var(--primary-2);
                        }

                        .invoice-entry-page .summary-meta {
                            display: flex;
                            justify-content: space-between;
                            gap: 12px;
                            align-items: center;
                            padding-top: 14px;
                            margin-top: 14px;
                            border-top: 1px solid var(--line);
                            flex-wrap: wrap;
                        }

                        .invoice-entry-page .summary-meta strong {
                            color: var(--text);
                            display: block;
                        }

                        .invoice-entry-page .summary-meta span {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                        }

                        @media (max-width: 1199px) {
                            .invoice-entry-page .entry-layout {
                                grid-template-columns: 1fr;
                            }

                            .invoice-entry-page .entry-side .entry-card {
                                position: static;
                            }
                        }

                        @media (max-width: 767px) {
                            .invoice-entry-page .entry-title {
                                font-size: 1.75rem;
                            }

                            .invoice-entry-page .entry-card-body,
                            .invoice-entry-page .entry-card-head {
                                padding-left: 16px;
                                padding-right: 16px;
                            }
                        }
                    </style>

                    <div class="entry-header">
                        <div>
                            <div class="entry-eyebrow">Invoice Workspace</div>
                            <h4 class="entry-title"><?= $isEditMode ? 'Update Invoice' : 'Create Invoice'; ?></h4>
                            <div class="entry-subtitle">Use the wider screen to encode multiple invoice entries without squeezing everything into a modal.</div>
                        </div>
                        <div class="entry-actions">
                            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn-action"><?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?></a>
                            <button type="submit" form="invoiceEntryForm" class="btn-submit"><?= $isEditMode ? 'Update Invoice' : 'Save Invoice'; ?></button>
                        </div>
                    </div>

                    <form id="invoiceEntryForm" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                        <?php if ($isEditMode && $record): ?>
                            <input type="hidden" name="id" value="<?= (int) $record->orderID; ?>">
                            <input type="hidden" name="return_to" value="invList">
                        <?php endif; ?>
                        <input type="hidden" name="form_return_url" value="<?= htmlspecialchars($formReturnUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="PaymentReference" value="">

                        <div class="entry-layout">
                            <div class="entry-main">
                                <div class="entry-card">
                                    <div class="entry-card-head">
                                        <h5 class="entry-card-title">Invoice Details</h5>
                                        <div class="entry-card-subtitle">Choose the customer and billing dates before encoding the entries.</div>
                                    </div>
                                    <div class="entry-card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for="invoice-number">Invoice No.</label>
                                                <input type="text" class="form-control" id="invoice-number" name="InvoiceNo" value="<?= htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8'); ?>" <?= in_array($this->session->userdata('level'), ['Admin', 'Staff', 'Encoder'], true) ? '' : 'readonly'; ?> required>
                                            </div>
                                            <div class="form-group col-md-9">
                                                <label for="invoice-customer" class="label-with-tip">
                                                    <span>Customer</span>
                                                    <button type="button" class="field-tooltip" data-toggle="tooltip" data-placement="top" title="Search customer, company, or CustID using the smart dropdown.">
                                                        <i class="fa fa-info"></i>
                                                    </button>
                                                </label>
                                                <div class="customer-select-wrap">
                                                    <select class="custom-select js-customer-select2" id="invoice-customer" name="CustID" required>
                                                        <option value="" data-address="">Select customer</option>
                                                        <?php foreach ($clients as $client): ?>
                                                            <option value="<?= htmlspecialchars((string) $client->CustID, ENT_QUOTES, 'UTF-8'); ?>" data-address="<?= htmlspecialchars((string) ($client->Address ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?= $customerId === (string) $client->CustID ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars((string) $client->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string) $client->CustID, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="invoice-address">Customer Address</label>
                                            <input type="text" class="form-control" id="invoice-address" name="CustAddress" value="<?= htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                        </div>

                                        <div class="form-row mt-3">
                                            <div class="form-group col-md-6">
                                                <label for="invoice-date" class="label-with-tip">
                                                    <span id="invoice-date-label"><?= $isRecurringInvoice ? 'Covered Period Start' : 'Invoice Date'; ?></span>
                                                    <button type="button" class="field-tooltip" data-toggle="tooltip" data-placement="top" title="Use the original invoice date when encoding previous invoices from another system.">
                                                        <i class="fa fa-info"></i>
                                                    </button>
                                                </label>
                                                <input type="date" class="form-control" id="invoice-date" name="TransDate" value="<?= htmlspecialchars($invoiceDateValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="invoice-due-date">Due Date</label>
                                                <input type="date" class="form-control" id="invoice-due-date" name="ReceiveDate" value="<?= htmlspecialchars($dueDateValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-head">
                                        <div class="item-builder-head">
                                            <div>
                                                <h5 class="entry-card-title">Invoice Entries</h5>
                                                <div class="entry-card-subtitle">Encode each item manually. The total due updates automatically from the lines below.</div>
                                            </div>
                                            <button type="button" class="btn-add-row" id="add-item-row">Add Entry</button>
                                        </div>
                                    </div>
                                    <div class="entry-card-body">
                                        <div id="item-rows"></div>
                                        <small class="item-builder-warning" id="item-builder-warning"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="entry-side">
                                <div class="entry-card">
                                    <div class="entry-card-head">
                                        <h5 class="entry-card-title">Invoice Settings</h5>
                                        <div class="entry-card-subtitle">Keep notes, recurrence, and totals visible while you work.</div>
                                    </div>
                                    <div class="entry-card-body">
                                        <div class="form-group">
                                            <label for="invoice-summary-description" class="label-with-tip">
                                                <span>Invoice Description</span>
                                                <button type="button" class="field-tooltip" data-toggle="tooltip" data-placement="top" title="You can edit this freely. If left blank, the system will suggest a description based on the invoice entries.">
                                                    <i class="fa fa-info"></i>
                                                </button>
                                            </label>
                                            <textarea class="form-control" id="invoice-summary-description" name="JobDescription" rows="4" placeholder="Enter the invoice description shown to the client"><?= htmlspecialchars($jobDescriptionValue, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            <small class="text-muted d-block mt-2">This description can be customized and will be saved with the invoice.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="invoice-notes">Notes</label>
                                            <textarea class="form-control" id="invoice-notes" name="Notes" placeholder="Optional notes for this invoice"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="invoice-recurring" class="label-with-tip">
                                                    <span>Recurring Frequency</span>
                                                    <button
                                                        type="button"
                                                        class="field-tooltip"
                                                        data-toggle="tooltip"
                                                        data-placement="top"
                                                        title="<?= htmlspecialchars($isGeneratedRecurring ? 'This invoice was generated from a recurring template. Edit the original template to change recurrence.' : 'Recurring invoices generate 10 days before the selected schedule date. Expired or terminated templates automatically drop out of active recurring billing.', ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa fa-info"></i>
                                                    </button>
                                                </label>
                                                <select class="custom-select" id="invoice-recurring" name="recurringFrequency" <?= $isGeneratedRecurring ? 'disabled' : ''; ?>>
                                                    <option value="none" <?= $recurringFrequency === 'none' ? 'selected' : ''; ?>>No (One-time)</option>
                                                    <option value="daily" <?= $recurringFrequency === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                                    <option value="weekly" <?= $recurringFrequency === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                                    <option value="monthly" <?= $recurringFrequency === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                                    <option value="quarterly" <?= $recurringFrequency === 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                                                    <option value="yearly" <?= $recurringFrequency === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                                </select>
                                                <small class="text-muted d-block mt-2">
                                                    Select <strong>No</strong> for a one-time invoice. Choose any frequency below to make the invoice recurring.
                                                </small>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="invoice-schedule-date">Schedule Date</label>
                                                <input type="date" class="form-control" id="invoice-schedule-date" name="recurringScheduleDate" value="<?= htmlspecialchars($recurringScheduleDate, ENT_QUOTES, 'UTF-8'); ?>" <?= $isGeneratedRecurring ? 'readonly' : ''; ?>>
                                            </div>
                                        </div>

                                        <div class="form-row" id="coverage-option-row">
                                            <div class="form-group col-md-12">
                                                <label for="coverage-option" class="label-with-tip">
                                                    <span>Coverage Period</span>
                                                    <button type="button" class="field-tooltip" data-toggle="tooltip" data-placement="top" title="For recurring invoices, select whether this invoice covers the previous period or the upcoming period relative to the schedule date.">
                                                        <i class="fa fa-info"></i>
                                                    </button>
                                                </label>
                                                <select class="custom-select" id="coverage-option" name="coverageOption" data-generated-lock="<?= $isGeneratedRecurring ? '1' : '0'; ?>" <?= $isGeneratedRecurring ? 'disabled' : ''; ?>>
                                                    <option value="coming" <?= $coverageOption === 'coming' ? 'selected' : ''; ?>>Upcoming Period</option>
                                                    <option value="previous" <?= $coverageOption === 'previous' ? 'selected' : ''; ?>>Previous Period</option>
                                                </select>
                                                <small class="text-muted d-block mt-2" id="coverage-option-help">
                                                    <?php if ($isGeneratedRecurring): ?>
                                                        This billing period is inherited from the recurring template.
                                                    <?php elseif ($isRecurringInvoice): ?>
                                                        Select whether this invoice covers the previous period or the upcoming period relative to the schedule date.
                                                    <?php else: ?>
                                                        You can choose the billing period now. It will apply once you set a recurring frequency.
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="invoice-termination-date" class="label-with-tip">
                                                    <span>Termination Date</span>
                                                    <button type="button" class="field-tooltip" data-toggle="tooltip" data-placement="top" title="Optional. Stops future recurring invoices after this date.">
                                                        <i class="fa fa-info"></i>
                                                    </button>
                                                </label>
                                                <input type="date" class="form-control" id="invoice-termination-date" name="recurringTerminationDate" value="<?= htmlspecialchars($recurringTerminationDate, ENT_QUOTES, 'UTF-8'); ?>" <?= $isGeneratedRecurring ? 'readonly' : ''; ?>>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="invoice-expiration-date" class="label-with-tip">
                                                    <span id="invoice-expiration-label"><?= $isRecurringInvoice ? 'Covered Period End' : 'Expiration Date'; ?></span>
                                                    <button type="button" class="field-tooltip" data-toggle="tooltip" data-placement="top" title="<?= htmlspecialchars($isRecurringInvoice ? 'Optional end date for the covered period of this recurring invoice. Leaving it blank keeps the invoice open-ended.' : 'Optional end date. Once this has passed, the invoice will stop showing in active recurring billing and no new recurring invoices will be generated from it.', ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa fa-info"></i>
                                                    </button>
                                                </label>
                                                <div class="open-date-toggle">
                                                    <input type="checkbox" id="invoice-open-date-toggle" name="isOpenDateInvoice" value="1" <?= $isOpenDateInvoice ? 'checked' : ''; ?>>
                                                    <label for="invoice-open-date-toggle" class="label-with-tip">
                                                        <span id="invoice-open-date-label"><?= $isRecurringInvoice ? 'Open-ended coverage' : 'Open-dated invoice'; ?></span>
                                                        <button type="button" class="field-tooltip" data-toggle="tooltip" data-placement="top" title="<?= htmlspecialchars($isRecurringInvoice ? 'Leaves this recurring invoice without a covered period end date.' : 'Leaves this invoice without an expiration date so it stays active until you manually set one or terminate the recurring schedule.', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <i class="fa fa-info"></i>
                                                        </button>
                                                    </label>
                                                </div>
                                                <input type="date" class="form-control" id="invoice-expiration-date" name="invoiceExpirationDate" value="<?= htmlspecialchars($invoiceExpirationDate, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php if ($isRecurringInvoice): ?>
                                                    <small class="text-muted d-block mt-2">You can adjust the covered period for this invoice while keeping the recurring template settings intact.</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="summary-grid mt-4">
                                            <div class="summary-tile">
                                                <div class="summary-label">Total Due</div>
                                                <input type="number" class="form-control" id="total-due" name="TotalDue" min="0" step="0.01" value="<?= htmlspecialchars(number_format($totalDue, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" readonly required>
                                            </div>

                                            <div class="summary-tile">
                                                <div class="summary-label">Amount Paid</div>
                                                <input type="number" class="form-control" id="amount-paid" name="AmountPaid" min="0" step="0.01" value="<?= htmlspecialchars(number_format($amountPaid, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>

                                            <div class="summary-tile">
                                                <div class="summary-label">Balance</div>
                                                <input type="number" class="form-control" id="balance-due" name="Balance" min="0" step="0.01" value="<?= htmlspecialchars(number_format($balance, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="summary-meta">
                                            <div>
                                                <strong id="summary-entry-count">1 entry</strong>
                                                <span id="summary-description">Start by entering the first invoice item.</span>
                                            </div>
                                            <div class="info-pill" id="summary-status-pill"><?= $isEditMode ? 'Editing Existing Invoice' : 'New Invoice'; ?></div>
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
            var form = document.getElementById('invoiceEntryForm');
            var customerSelect = document.getElementById('invoice-customer');
            var addressField = document.getElementById('invoice-address');
            var invoiceDateField = document.getElementById('invoice-date');
            var dueDateField = document.getElementById('invoice-due-date');
            var recurringField = document.getElementById('invoice-recurring');
            var coverageOptionField = document.getElementById('coverage-option');
            var coverageOptionHelp = document.getElementById('coverage-option-help');
            var invoiceDateLabel = document.getElementById('invoice-date-label');
            var scheduleDateField = document.getElementById('invoice-schedule-date');
            var terminationDateField = document.getElementById('invoice-termination-date');
            var openDateToggle = document.getElementById('invoice-open-date-toggle');
            var expirationLabel = document.getElementById('invoice-expiration-label');
            var openDateLabel = document.getElementById('invoice-open-date-label');
            var expirationDateField = document.getElementById('invoice-expiration-date');
            var itemRows = document.getElementById('item-rows');
            var addRowButton = document.getElementById('add-item-row');
            var totalDueField = document.getElementById('total-due');
            var amountPaidField = document.getElementById('amount-paid');
            var balanceField = document.getElementById('balance-due');
            var summaryDescriptionField = document.getElementById('invoice-summary-description');
            var summaryEntryCount = document.getElementById('summary-entry-count');
            var summaryDescription = document.getElementById('summary-description');
            var itemBuilderWarning = document.getElementById('item-builder-warning');
            var initialItems = <?= json_encode($invoiceItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var invoiceUnitOptions = <?= json_encode(array_values($invoiceUnitOptions), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var defaultInvoiceUnit = <?= json_encode($defaultInvoiceUnit, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var lastInvoiceDateValue = invoiceDateField ? invoiceDateField.value : '';
            var descriptionTouched = !!(summaryDescriptionField && summaryDescriptionField.value.trim() !== '');

            function escapeHtml(value) {
                return String(value == null ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function normalizeMoney(value) {
                var parsed = parseFloat(value);
                return isFinite(parsed) && parsed >= 0 ? parsed.toFixed(2) : '0.00';
            }

            function normalizeQuantity(value) {
                var parsed = parseFloat(value);
                return isFinite(parsed) && parsed > 0 ? String(Math.round(parsed)) : '1';
            }

            function defaultItem() {
                return {
                    itemDescription: '',
                    itemQuantity: '1',
                    itemDurationUnit: defaultInvoiceUnit,
                    itemUnitPrice: '0.00'
                };
            }

            function normalizeItems(items) {
                if (!Array.isArray(items) || !items.length) {
                    return [defaultItem()];
                }

                return items.map(function(item) {
                    return {
                        itemDescription: item && item.itemDescription ? String(item.itemDescription) : '',
                        itemQuantity: normalizeQuantity(item && item.itemQuantity),
                        itemDurationUnit: item && item.itemDurationUnit ? String(item.itemDurationUnit) : defaultInvoiceUnit,
                        itemUnitPrice: normalizeMoney(item && item.itemUnitPrice)
                    };
                });
            }

            function formatMoney(value) {
                var parsed = parseFloat(value) || 0;
                return new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP'
                }).format(parsed);
            }

            function formatQuantity(value) {
                var parsed = parseFloat(value) || 0;
                return Math.round(parsed) === parsed ? String(Math.round(parsed)) : parsed.toFixed(2);
            }

            function formatUnitLabel(quantity, unit) {
                if (!unit || unit === 'each') {
                    return '';
                }

                return quantity === 1 || /s$/i.test(unit) ? unit : unit + 's';
            }

            function formatRateUnit(unit) {
                return unit && String(unit).trim() !== '' ? String(unit) : 'each';
            }

            function formatUnitOptionLabel(unit) {
                if (!unit) {
                    return 'Unit';
                }

                return unit.length <= 3
                    ? unit.toUpperCase()
                    : unit.charAt(0).toUpperCase() + unit.slice(1);
            }

            function buildUnitOptions(selectedUnit) {
                var normalizedSelectedUnit = selectedUnit ? String(selectedUnit).toLowerCase() : '';
                var options = invoiceUnitOptions.slice();

                if (normalizedSelectedUnit && options.indexOf(normalizedSelectedUnit) === -1) {
                    options.push(normalizedSelectedUnit);
                }

                if (!options.length) {
                    options.push('each');
                }

                return options.map(function(unit) {
                    return '<option value="' + escapeHtml(unit) + '"' + (unit === normalizedSelectedUnit ? ' selected' : '') + '>' + escapeHtml(formatUnitOptionLabel(unit)) + '</option>';
                }).join('');
            }

            function buildRowMarkup(item, index) {
                var row = item || defaultItem();
                return '' +
                    '<div class="item-row" data-item-row>' +
                    '  <div class="item-row-head">' +
                    '    <div class="item-row-label">Entry ' + (index + 1) + '</div>' +
                    '    <div class="item-row-total" data-item-line-total>' + formatMoney(0) + '</div>' +
                    '  </div>' +
                    '  <div class="form-row">' +
                    '    <div class="form-group col-md-12">' +
                    '      <label>Description</label>' +
                    '      <input type="text" class="form-control" name="itemDescription[]" value="' + escapeHtml(row.itemDescription) + '" required>' +
                    '    </div>' +
                    '  </div>' +
                    '  <div class="form-row">' +
                    '    <div class="form-group col-md-4">' +
                    '      <label>Unit Price</label>' +
                    '      <input type="number" class="form-control" name="itemUnitPrice[]" min="0" step="0.01" value="' + escapeHtml(row.itemUnitPrice) + '">' +
                    '    </div>' +
                    '    <div class="form-group col-md-3">' +
                        '      <label>Qty</label>' +
                    '      <input type="number" class="form-control" name="itemQuantity[]" min="1" step="1" value="' + escapeHtml(row.itemQuantity) + '">' +
                    '    </div>' +
                    '    <div class="form-group col-md-3">' +
                    '      <label>Unit</label>' +
                    '      <select class="custom-select" name="itemDurationUnit[]">' + buildUnitOptions(row.itemDurationUnit) + '</select>' +
                    '    </div>' +
                    '    <div class="form-group col-md-2">' +
                    '      <label>&nbsp;</label>' +
                    '      <button type="button" class="btn-remove-row w-100" data-remove-row>Remove</button>' +
                    '    </div>' +
                    '  </div>' +
                    '  <small class="item-row-breakdown" data-item-breakdown></small>' +
                    '</div>';
            }

            function getItemsFromDom() {
                var rows = itemRows.querySelectorAll('[data-item-row]');
                return Array.prototype.map.call(rows, function(row) {
                    return {
                        itemDescription: row.querySelector('input[name="itemDescription[]"]').value || '',
                        itemQuantity: normalizeQuantity(row.querySelector('input[name="itemQuantity[]"]').value || '1'),
                        itemDurationUnit: row.querySelector('select[name="itemDurationUnit[]"]').value || defaultInvoiceUnit,
                        itemUnitPrice: normalizeMoney(row.querySelector('input[name="itemUnitPrice[]"]').value || '0')
                    };
                });
            }

            function renderRows(items) {
                var normalizedItems = normalizeItems(items);
                itemRows.innerHTML = normalizedItems.map(function(item, index) {
                    return buildRowMarkup(item, index);
                }).join('');
                refreshSummary();
            }

            function computeSummaryLabel(items) {
                if (!items.length) {
                    return 'Invoice item';
                }

                var firstDescription = (items[0].itemDescription || '').trim();
                if (firstDescription === '') {
                    firstDescription = 'Invoice item';
                }

                var extraCount = items.length - 1;
                if (extraCount <= 0) {
                    return firstDescription;
                }

                return firstDescription + ' +' + extraCount + ' more item' + (extraCount === 1 ? '' : 's');
            }

            function refreshSummary() {
                var items = getItemsFromDom();
                var amountPaid = parseFloat(amountPaidField.value) || 0;
                var totalAmount = 0;
                var computedSummary = computeSummaryLabel(items);

                Array.prototype.forEach.call(itemRows.querySelectorAll('[data-item-row]'), function(row, index) {
                    var quantity = parseFloat(row.querySelector('input[name="itemQuantity[]"]').value) || 0;
                    var unitPrice = parseFloat(row.querySelector('input[name="itemUnitPrice[]"]').value) || 0;
                    var unit = row.querySelector('select[name="itemDurationUnit[]"]').value || defaultInvoiceUnit;
                    var lineTotal = Math.max(0, quantity * unitPrice);
                    var durationLabel = formatUnitLabel(quantity, unit);
                    var rateUnitLabel = formatRateUnit(unit);
                    var prefix = durationLabel
                        ? (formatQuantity(quantity) + ' ' + durationLabel + ' x ' + formatMoney(unitPrice) + ' / ' + rateUnitLabel)
                        : (formatQuantity(quantity) + ' x ' + formatMoney(unitPrice) + ' / ' + rateUnitLabel);

                    row.querySelector('.item-row-label').textContent = 'Entry ' + (index + 1);
                    row.querySelector('[data-item-line-total]').textContent = formatMoney(lineTotal);
                    row.querySelector('[data-item-breakdown]').textContent = quantity > 0 ? (prefix + ' = ' + formatMoney(lineTotal)) : '';
                    totalAmount += lineTotal;
                });

                if (!items.length) {
                    renderRows([defaultItem()]);
                    return;
                }

                totalDueField.value = totalAmount.toFixed(2);
                balanceField.value = Math.max(0, totalAmount - amountPaid).toFixed(2);
                summaryEntryCount.textContent = items.length + ' entr' + (items.length === 1 ? 'y' : 'ies');
                if (summaryDescriptionField) {
                    if (!descriptionTouched || summaryDescriptionField.value.trim() === '') {
                        summaryDescriptionField.value = computedSummary;
                        descriptionTouched = false;
                    }

                    summaryDescription.textContent = summaryDescriptionField.value.trim() || computedSummary;
                } else {
                    summaryDescription.textContent = computedSummary;
                }
                itemBuilderWarning.textContent = totalAmount + 0.00001 < amountPaid ? 'Total due cannot be lower than the amount already paid.' : '';
                totalDueField.setCustomValidity(totalAmount + 0.00001 < amountPaid ? 'Total due cannot be lower than the amount already paid.' : '');

                Array.prototype.forEach.call(itemRows.querySelectorAll('[data-remove-row]'), function(button) {
                    button.disabled = items.length === 1;
                });
            }

            function syncCustomerAddress() {
                if (!customerSelect || !addressField) {
                    return;
                }

                var address = '';

                if (window.jQuery && window.jQuery.fn) {
                    var $selected = window.jQuery(customerSelect).find(':selected');
                    if ($selected.length) {
                        address = $selected.attr('data-address') || '';
                    }
                }

                if (!address && customerSelect.selectedOptions && customerSelect.selectedOptions.length) {
                    address = customerSelect.selectedOptions[0].getAttribute('data-address') || '';
                }

                if (!address && customerSelect.selectedIndex >= 0 && customerSelect.options[customerSelect.selectedIndex]) {
                    address = customerSelect.options[customerSelect.selectedIndex].getAttribute('data-address') || '';
                }

                addressField.value = address;
            }

            function initializeCustomerSelect2(attempt) {
                attempt = attempt || 0;

                if (!customerSelect) {
                    return;
                }

                if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
                    if (attempt < 20) {
                        window.setTimeout(function() {
                            initializeCustomerSelect2(attempt + 1);
                        }, 100);
                    }
                    return;
                }

                var $customerSelect = window.jQuery(customerSelect);

                if ($customerSelect.hasClass('select2-hidden-accessible')) {
                    $customerSelect.off('.invoiceEntryCustomerSelect2');
                    $customerSelect.select2('destroy');
                }

                $customerSelect.select2({
                    width: '100%',
                    placeholder: 'Search customer, company, or CustID',
                    allowClear: true,
                    minimumResultsForSearch: 0
                });

                $customerSelect.on('change.invoiceEntryCustomerSelect2 select2:select.invoiceEntryCustomerSelect2 select2:clear.invoiceEntryCustomerSelect2', syncCustomerAddress);
                $customerSelect.on('select2:open.invoiceEntryCustomerSelect2', function() {
                    window.jQuery('.select2-container--open .select2-dropdown').addClass('invoice-entry-select2-dropdown');
                });
                syncCustomerAddress();
            }

            function ensureCustomerSelect2() {
                if (!customerSelect || !window.jQuery || !window.jQuery.fn) {
                    return;
                }

                if (typeof window.jQuery.fn.select2 === 'function') {
                    initializeCustomerSelect2();
                    return;
                }

                var existingLoader = document.getElementById('invoice-entry-select2-loader');
                if (existingLoader) {
                    return;
                }

                var loader = document.createElement('script');
                loader.id = 'invoice-entry-select2-loader';
                loader.src = '<?= base_url(); ?>assets/libs/select2/select2.min.js?v=invoice-entry-force';
                loader.onload = function() {
                    initializeCustomerSelect2();
                };
                document.body.appendChild(loader);
            }

            function syncDueDate(force) {
                if (!invoiceDateField || !dueDateField) {
                    return;
                }

                if (force || dueDateField.value === '' || dueDateField.value === lastInvoiceDateValue) {
                    dueDateField.value = invoiceDateField.value;
                }

                lastInvoiceDateValue = invoiceDateField.value;
            }

            function syncDateBounds() {
                var anchorDate = invoiceDateField && invoiceDateField.value ? invoiceDateField.value : '';
                if (recurringField && recurringField.value !== 'none' && scheduleDateField && scheduleDateField.value) {
                    anchorDate = scheduleDateField.value;
                }

                if (terminationDateField) {
                    terminationDateField.min = scheduleDateField && scheduleDateField.value ? scheduleDateField.value : anchorDate;
                }

                if (expirationDateField) {
                    expirationDateField.min = anchorDate;
                }
            }

            function syncOpenDateOption() {
                if (!openDateToggle || !expirationDateField) {
                    return;
                }

                var isGeneratedField = expirationDateField.hasAttribute('readonly');
                var isOpenDate = openDateToggle.checked;
                expirationDateField.disabled = !isGeneratedField && isOpenDate;
                expirationDateField.required = !isGeneratedField && !isOpenDate;

                if (!isGeneratedField && isOpenDate) {
                    expirationDateField.value = '';
                }
            }

            function syncRecurringLabels() {
                var isRecurring = recurringField && recurringField.value !== 'none';
                var coverageOptionRow = document.getElementById('coverage-option-row');
                var isGeneratedCoverageField = coverageOptionField && coverageOptionField.getAttribute('data-generated-lock') === '1';

                if (invoiceDateLabel) {
                    invoiceDateLabel.textContent = isRecurring ? 'Covered Period Start' : 'Invoice Date';
                }

                if (expirationLabel) {
                    expirationLabel.textContent = isRecurring ? 'Covered Period End' : 'Expiration Date';
                }

                if (openDateLabel) {
                    openDateLabel.textContent = isRecurring ? 'Open-ended coverage' : 'Open-dated invoice';
                }

                if (coverageOptionField) {
                    coverageOptionField.disabled = isGeneratedCoverageField;
                }

                if (coverageOptionHelp) {
                    if (isGeneratedCoverageField) {
                        coverageOptionHelp.textContent = 'This billing period is inherited from the recurring template.';
                    } else if (isRecurring) {
                        coverageOptionHelp.textContent = 'Select whether this invoice covers the previous period or the upcoming period relative to the schedule date.';
                    } else {
                        coverageOptionHelp.textContent = 'You can choose the billing period now. It will apply once you set a recurring frequency.';
                    }
                }

                if (coverageOptionRow) {
                    coverageOptionRow.style.display = 'flex';
                }
            }

            addRowButton.addEventListener('click', function() {
                var items = getItemsFromDom();
                items.push(defaultItem());
                renderRows(items);
            });

            itemRows.addEventListener('click', function(event) {
                if (!event.target.matches('[data-remove-row]')) {
                    return;
                }

                var rows = getItemsFromDom();
                var rowNode = event.target.closest('[data-item-row]');
                var index = Array.prototype.indexOf.call(itemRows.querySelectorAll('[data-item-row]'), rowNode);

                if (rows.length <= 1) {
                    renderRows([defaultItem()]);
                    return;
                }

                rows.splice(index, 1);
                renderRows(rows);
            });

            itemRows.addEventListener('input', function(event) {
                if (
                    event.target.matches('input[name="itemDescription[]"]') ||
                    event.target.matches('input[name="itemQuantity[]"]') ||
                    event.target.matches('input[name="itemUnitPrice[]"]')
                ) {
                    refreshSummary();
                }
            });

            itemRows.addEventListener('change', function(event) {
                if (event.target.matches('select[name="itemDurationUnit[]"]')) {
                    refreshSummary();
                }
            });

            if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.tooltip === 'function') {
                window.jQuery('[data-toggle="tooltip"]').tooltip({
                    container: 'body',
                    trigger: 'hover focus'
                });
            }

            if (window.jQuery && window.jQuery.fn) {
                ensureCustomerSelect2();
            } else {
                customerSelect.addEventListener('change', syncCustomerAddress);
            }

            if (invoiceDateField) {
                invoiceDateField.addEventListener('change', function() {
                    syncDueDate(false);
                    syncDateBounds();
                });
            }

            if (scheduleDateField) {
                scheduleDateField.addEventListener('change', syncDateBounds);
            }

            if (recurringField) {
                recurringField.addEventListener('change', function() {
                    syncDateBounds();
                    syncRecurringLabels();
                });
            }

            if (openDateToggle) {
                openDateToggle.addEventListener('change', syncOpenDateOption);
            }

            if (summaryDescriptionField) {
                summaryDescriptionField.addEventListener('input', function() {
                    var items = getItemsFromDom();
                    var computedSummary = computeSummaryLabel(items);
                    descriptionTouched = this.value.trim() !== '';
                    summaryDescription.textContent = this.value.trim() || computedSummary;
                });
            }

            form.addEventListener('submit', function(event) {
                syncOpenDateOption();
                refreshSummary();

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            });

            syncCustomerAddress();
            syncDueDate(dueDateField && dueDateField.value === '');
            syncDateBounds();
            syncRecurringLabels();
            syncOpenDateOption();
            renderRows(initialItems);

            document.addEventListener('DOMContentLoaded', ensureCustomerSelect2);
            window.addEventListener('load', ensureCustomerSelect2);
            window.setTimeout(ensureCustomerSelect2, 250);
            window.setTimeout(ensureCustomerSelect2, 1000);
        })();
    </script>

</body>

</html>
