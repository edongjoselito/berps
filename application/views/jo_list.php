<?php
$jobOrders = isset($data) && is_array($data) ? $data : array();
$clients = isset($data2) && is_array($data2) ? $data2 : array();
$nextInvoiceNo = (!empty($data1) && isset($data1[0]->InvoiceNo)) ? $data1[0]->InvoiceNo + 1 : 100001;
$userLevel = strtolower(trim((string) $this->session->userdata('level')));
$isAdmin = $userLevel === 'admin';
$canAcceptPayment = in_array($userLevel, array('admin', 'staff'), true);

$jobOrderCount = count($jobOrders);
$totalDueAmount = 0.0;
$totalPaidAmount = 0.0;
$totalBalanceAmount = 0.0;
$paidCount = 0;
$unpaidCount = 0;

foreach ($jobOrders as $jobOrder) {
    $totalDueAmount += (float) ($jobOrder->TotalDue ?? 0);
    $totalPaidAmount += (float) ($jobOrder->AmountPaid ?? 0);
    $totalBalanceAmount += (float) ($jobOrder->Balance ?? 0);
    
    $balance = (float) ($jobOrder->Balance ?? 0);
    if ($balance <= 0.00001) {
        $paidCount++;
    } else {
        $unpaidCount++;
    }
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
                <div class="container-fluid job-order-page">

                    <style>

                        .job-order-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.92);
                            --surface-2: #ffffff;
                            --line: #e7ecf3;
                            --line-strong: #d7e0ec;
                            --text: #122033;
                            --text-soft: #5e7188;
                            --text-faint: #8ea0b5;
                            --primary: #2563eb;
                            --primary-2: #1d4ed8;
                            --primary-soft: #eaf2ff;
                            --success: #059669;
                            --success-soft: #ecfdf5;
                            --warning: #d97706;
                            --warning-soft: #fff7ed;
                            --danger: #e11d48;
                            --danger-soft: #fff1f2;
                            --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                            --radius-xl: 16px;
                            --radius-lg: 12px;
                            --radius-md: 10px;
                            --radius-sm: 8px;
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            --font-mono: var(--font-primary);
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 20px;
                        }

                        .job-order-page * {
                            box-sizing: border-box;
                        }

                        .job-order-page .jo-header {
                            margin: 16px 0 16px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 14px;
                            flex-wrap: wrap;
                        }

                        .job-order-page .jo-eyebrow {
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

                        .job-order-page .jo-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .job-order-page .jo-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 1.5rem;
                            font-weight: 700;
                            letter-spacing: -0.02em;
                            color: var(--text);
                            line-height: 1.2;
                        }

                        .job-order-page .jo-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.9rem;
                            font-weight: 400;
                        }

                        .job-order-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 12px;
                            margin-bottom: 16px;
                        }

                        .job-order-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.7);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 14px 16px 14px;
                            min-height: 90px;
                            transition: transform 0.18s ease, box-shadow 0.18s ease;
                        }

                        .job-order-page .stat-card:hover {
                            transform: translateY(-3px);
                            box-shadow: var(--shadow);
                        }

                        .job-order-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .job-order-page .stat-card.sc-total::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .job-order-page .stat-card.sc-due::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .job-order-page .stat-card.sc-paid::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .job-order-page .stat-card.sc-balance::before {
                            background: linear-gradient(90deg, #f43f5e, #fb7185);
                        }

                        .job-order-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.65rem;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            margin-bottom: 8px;
                        }

                        .job-order-page .stat-value {
                            color: var(--text);
                            font-size: 1.25rem;
                            font-weight: 700;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            margin-bottom: 4px;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .job-order-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.72rem;
                            font-weight: 400;
                        }

                        .job-order-page .jo-card {
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                        }

                        .job-order-page .jo-card-header {
                            padding: 16px 18px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 12px;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                        }

                        .job-order-page .filter-group {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            background: var(--surface-soft);
                            border: 1px solid var(--line);
                            border-radius: 12px;
                            padding: 6px;
                        }

                        .job-order-page .filter-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 8px 14px;
                            border: none;
                            border-radius: 10px;
                            background: transparent;
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.15s ease;
                            white-space: nowrap;
                        }

                        .job-order-page .filter-btn:hover {
                            background: rgba(37, 99, 235, 0.08);
                            color: var(--primary-2);
                        }

                        .job-order-page .filter-btn.active {
                            background: var(--primary);
                            color: #fff;
                            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
                        }

                        .job-order-page .filter-badge {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 20px;
                            height: 20px;
                            padding: 0 6px;
                            border-radius: 999px;
                            background: rgba(0, 0, 0, 0.08);
                            font-size: 0.7rem;
                            font-weight: 700;
                        }

                        .job-order-page .filter-btn.active .filter-badge {
                            background: rgba(255, 255, 255, 0.2);
                            color: #fff;
                        }

                        .job-order-page .jo-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 0.95rem;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                        }

                        .job-order-page .jo-card-subtitle {
                            margin-top: 4px;
                            color: var(--text-soft);
                            font-size: 0.8rem;
                        }

                        .job-order-page .jo-card-body {
                            padding: 18px;
                        }

                        .job-order-page .btn-add-jo {
                            display: inline-flex;
                            align-items: center;
                            gap: 10px;
                            border: none;
                            border-radius: 12px;
                            padding: 11px 18px;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            font-size: 0.9rem;
                            font-weight: 700;
                            letter-spacing: 0.01em;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.25);
                            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
                        }

                        .job-order-page .btn-add-jo:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                            filter: brightness(1.02);
                            color: #fff;
                            text-decoration: none;
                        }

                        .job-order-page .btn-add-jo .plus-icon {
                            width: 22px;
                            height: 22px;
                            border-radius: 8px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            background: rgba(255, 255, 255, 0.16);
                            font-size: 1rem;
                            line-height: 1;
                        }

                        .job-order-page .table-responsive {
                            border-radius: 18px;
                            overflow: hidden;
                            border: 1px solid var(--line);
                            background: #fff;
                        }

                        .job-order-page .table {
                            margin-bottom: 0;
                        }

                        .job-order-page .table thead th {
                            border-top: 0;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            background: #f9fbfe;
                            padding-top: 14px;
                            padding-bottom: 14px;
                            white-space: nowrap;
                        }

                        .job-order-page .table td {
                            vertical-align: middle;
                            border-top: 1px solid #eef3f8;
                            color: var(--text);
                        }

                        .job-order-page .table tbody tr:hover {
                            background: rgba(37, 99, 235, 0.03);
                        }

                        .job-order-page .inv-no-link {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            color: var(--primary-2);
                            font-weight: 800;
                            text-decoration: none;
                        }

                        .job-order-page .inv-no-link:hover {
                            text-decoration: underline;
                        }

                        .job-order-page .customer-line {
                            font-weight: 700;
                            color: var(--text);
                        }

                        .job-order-page .address-line {
                            margin-top: 4px;
                            color: var(--text-soft);
                            font-size: 0.82rem;
                        }

                        .job-order-page .desc-main {
                            color: var(--text);
                            font-weight: 600;
                        }

                        .job-order-page .desc-meta {
                            margin-top: 5px;
                            color: var(--text-soft);
                            font-size: 0.8rem;
                        }

                        .job-order-page .num-cell {
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-variant-numeric: tabular-nums;
                        }

                        .job-order-page .action-link {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .job-order-page .action-link:hover {
                            text-decoration: underline;
                        }

                        .job-order-page .payment-state {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            padding: 4px 10px;
                            border-radius: 999px;
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.01em;
                            white-space: nowrap;
                        }

                        .job-order-page .payment-state--paid {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .job-order-page .payment-state--partial {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .job-order-page .payment-state--unpaid {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .job-order-page .tbl-btn {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 94px;
                            padding: 9px 14px;
                            border-radius: 10px;
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                            font-size: 0.82rem;
                            font-weight: 700;
                            transition: all 0.15s ease;
                        }

                        .job-order-page .tbl-btn:hover,
                        .job-order-page .tbl-btn:focus {
                            background: var(--surface-soft);
                            border-color: var(--text-faint);
                            color: var(--text);
                            text-decoration: none;
                        }

                        .job-order-page .dropdown-menu {
                            border: 1px solid var(--line);
                            box-shadow: var(--shadow-soft);
                            border-radius: 14px;
                            padding: 8px;
                            min-width: 210px;
                            z-index: 9999;
                            position: absolute;
                        }

                        .job-order-page .dropdown-item {
                            border-radius: 10px;
                            font-size: 0.86rem;
                            font-weight: 600;
                            padding: 9px 12px;
                            color: var(--text);
                        }

                        .job-order-page .dropdown-item:hover,
                        .job-order-page .dropdown-item:focus {
                            background: #f6f9fd;
                            color: var(--primary-2);
                        }

                        .job-order-page .dropdown-item i {
                            width: 18px;
                            margin-right: 8px;
                        }

                        .job-order-page .dropdown-divider {
                            margin: 8px 0;
                            border-top-color: var(--line);
                        }

                        .job-order-page .inv-modal-header {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            border-bottom: 0;
                            padding: 18px 22px;
                        }

                        .job-order-page .inv-modal-header .modal-title {
                            font-size: 1rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .job-order-page .inv-modal-body {
                            padding: 22px;
                            background: linear-gradient(180deg, #fbfdff 0%, #f7faff 100%);
                        }

                        .job-order-page .form-group label {
                            color: var(--text);
                            font-size: 0.8rem;
                            font-weight: 700;
                            margin-bottom: 7px;
                            letter-spacing: 0.01em;
                        }

                        .job-order-page .form-control,
                        .job-order-page .select2-container--default .select2-selection--single {
                            min-height: 44px;
                            border-radius: 12px;
                            border: 1px solid var(--line-strong);
                            box-shadow: none;
                        }

                        .job-order-page .form-control:focus {
                            border-color: rgba(37, 99, 235, 0.55);
                            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12);
                        }

                        .job-order-page .select2-container--default .select2-selection--single {
                            display: flex;
                            align-items: center;
                            padding: 6px 12px;
                        }

                        .job-order-page .select2-container--default .select2-selection--single .select2-selection__arrow {
                            height: 42px;
                            right: 8px;
                        }

                        .job-order-page .select2-container--default .select2-selection--single .select2-selection__rendered {
                            line-height: 28px;
                            padding-left: 0;
                            color: var(--text);
                        }

                        .job-order-page .form-note {
                            margin: 2px 0 18px;
                            padding: 11px 13px;
                            border-radius: 12px;
                            background: #eff6ff;
                            border: 1px solid #dbeafe;
                            color: var(--primary-2);
                            font-size: 0.82rem;
                            font-weight: 600;
                        }

                        .job-order-page .text-empty {
                            color: var(--text-soft);
                            text-align: center;
                            padding: 42px 16px;
                        }

                        .job-order-page .dataTables_wrapper .dataTables_filter input,
                        .job-order-page .dataTables_wrapper .dataTables_length select {
                            border-radius: 10px;
                            border: 1px solid var(--line-strong);
                            padding: 6px 10px;
                        }

                        .job-order-page .dataTables_wrapper .dataTables_info,
                        .job-order-page .dataTables_wrapper .dataTables_paginate {
                            margin-top: 16px;
                            color: var(--text-soft);
                        }

                        @media (max-width: 1199.98px) {
                            .job-order-page .stat-strip {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 767.98px) {
                            .job-order-page .jo-title {
                                font-size: 1.7rem;
                            }

                            .job-order-page .stat-strip {
                                grid-template-columns: 1fr;
                            }

                            .job-order-page .jo-card-header,
                            .job-order-page .jo-card-body,
                            .job-order-page .inv-modal-body {
                                padding-left: 18px;
                                padding-right: 18px;
                            }

                            .job-order-page .btn-add-jo {
                                width: 100%;
                                justify-content: center;
                            }
                        }
                    </style>

                    <div class="jo-header">
                        <div>
                            <div class="jo-eyebrow">Job Orders</div>
                            <h1 class="jo-title">Job Order Management</h1>
                            <!-- <p class="jo-subtitle">Track open work, collect payments, and open the generated invoice right after creating a new job order.</p> -->
                        </div>
                    </div>

                    <div class="stat-strip">
                        <div class="stat-card sc-total">
                            <div class="stat-label">Open Job Orders</div>
                            <div class="stat-value"><?= number_format($jobOrderCount); ?></div>
                            <div class="stat-meta">Active records currently visible in this list</div>
                        </div>
                        <div class="stat-card sc-due">
                            <div class="stat-label">Total Due</div>
                            <div class="stat-value"><?= number_format($totalDueAmount, 2); ?></div>
                            <div class="stat-meta">Combined billed amount for open job orders</div>
                        </div>
                        <div class="stat-card sc-paid">
                            <div class="stat-label">Collected</div>
                            <div class="stat-value"><?= number_format($totalPaidAmount, 2); ?></div>
                            <div class="stat-meta">Payments already posted against these job orders</div>
                        </div>
                        <div class="stat-card sc-balance">
                            <div class="stat-label">Outstanding</div>
                            <div class="stat-value"><?= number_format($totalBalanceAmount, 2); ?></div>
                            <div class="stat-meta">Remaining balance still to be paid</div>
                        </div>
                    </div>

                    <div class="jo-card">
                        <div class="jo-card-header">
                            <div>
                                <h2 class="jo-card-title">Job Order List</h2>
                                <!-- <p class="jo-card-subtitle">Each saved job order creates its invoice record immediately and can be opened from the list or right after saving.</p> -->
                            </div>

                            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                <div class="filter-group">
                                    <button class="filter-btn active" data-filter="all" onclick="filterJobOrders('all')">
                                        All
                                        <span class="filter-badge"><?= $jobOrderCount; ?></span>
                                    </button>
                                    <button class="filter-btn" data-filter="paid" onclick="filterJobOrders('paid')">
                                        Paid
                                        <span class="filter-badge"><?= $paidCount; ?></span>
                                    </button>
                                    <button class="filter-btn" data-filter="unpaid" onclick="filterJobOrders('unpaid')">
                                        Unpaid
                                        <span class="filter-badge"><?= $unpaidCount; ?></span>
                                    </button>
                                </div>
                                <a href="<?= base_url(); ?>Page/jobOrderEntry" class="btn-add-jo">
                                    <span class="plus-icon">+</span>
                                    Add New Job Order
                                </a>
                            </div>
                        </div>

                        <div class="jo-card-body">
                            <div class="table-responsive">
                                <table id="jo-table" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Invoice No.</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th class="text-right">Total Due</th>
                                            <th class="text-right">Amount Paid</th>
                                            <th class="text-right">Balance</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($jobOrders)): ?>
                                            <?php foreach ($jobOrders as $row): ?>
                                                <?php
                                                $balance = (float) ($row->Balance ?? 0);
                                                $amountPaid = (float) ($row->AmountPaid ?? 0);
                                                $isFullyPaid = $balance <= 0.00001;
                                                $hasPayment = $amountPaid > 0;
                                                $paymentStateClass = 'payment-state--unpaid';
                                                $paymentStateLabel = 'Unpaid';

                                                if ($isFullyPaid) {
                                                    $paymentStateClass = 'payment-state--paid';
                                                    $paymentStateLabel = 'Fully Paid';
                                                } elseif ($hasPayment) {
                                                    $paymentStateClass = 'payment-state--partial';
                                                    $paymentStateLabel = 'Partially Paid';
                                                }

                                                $invoiceHref = base_url() . 'Page/invoice?id=' . rawurlencode((string) $row->orderID);
                                                $paymentHistoryHref = base_url() . 'Page/paymentHistory?id=' . rawurlencode((string) $row->orderID);
                                                $paymentStatus = $isFullyPaid ? 'paid' : 'unpaid';
                                                ?>
                                                <tr data-payment-status="<?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <td>
                                                        <a class="inv-no-link" href="<?= htmlspecialchars($invoiceHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                            #<?= htmlspecialchars((string) $row->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="customer-line"><?= htmlspecialchars((string) $row->Customer, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="address-line"><?= htmlspecialchars((string) ($row->CustAddress ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td class="num-cell" style="font-size:0.8rem;color:var(--text-soft);"><?= htmlspecialchars((string) $row->TransDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td style="max-width:240px;">
                                                        <div class="desc-main"><?= htmlspecialchars((string) $row->JobDescription, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="desc-meta"><?= htmlspecialchars((string) ($row->Notes ?? 'No notes added'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td class="text-right num-cell"><?= number_format((float) $row->TotalDue, 2); ?></td>
                                                    <td class="text-right">
                                                        <?php if ($hasPayment): ?>
                                                            <a class="action-link num-cell" href="<?= htmlspecialchars($paymentHistoryHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= number_format($amountPaid, 2); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="num-cell"><?= number_format($amountPaid, 2); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                                                            <span class="num-cell"><?= number_format(max($balance, 0), 2); ?></span>
                                                            <span class="payment-state <?= $paymentStateClass; ?>"><?= $paymentStateLabel; ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button class="tbl-btn dropdown-toggle" type="button" id="dropdownMenu<?= (int) $row->orderID; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                Actions
                                                            </button>

                                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu<?= (int) $row->orderID; ?>">
                                                                <a class="dropdown-item" href="<?= htmlspecialchars($invoiceHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <i class="fa fa-file-invoice"></i> View Invoice
                                                                </a>
                                                                <a class="dropdown-item" href="<?= htmlspecialchars($invoiceHref . '&print=1', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                                                    <i class="fa fa-print"></i> Print Invoice
                                                                </a>
                                                                <a class="dropdown-item" href="<?= base_url(); ?>Page/printJobOrderForm?id=<?= (int) $row->orderID; ?>" target="_blank" rel="noopener">
                                                                    <i class="fa fa-file-alt"></i> Print Job Order Form
                                                                </a>

                                                                <?php if ($hasPayment): ?>
                                                                    <a class="dropdown-item" href="<?= htmlspecialchars($paymentHistoryHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <i class="fa fa-credit-card"></i> View Payment Details
                                                                    </a>
                                                                <?php endif; ?>

                                                                <?php if ($canAcceptPayment && !$isFullyPaid): ?>
                                                                    <a class="dropdown-item" href="<?= base_url(); ?>Page/addPaymentJO?id=<?= (int) $row->orderID; ?>&InvoiceNo=<?= rawurlencode((string) $row->InvoiceNo); ?>&PaymentSource=Job Order&return_to=joList">
                                                                        <i class="fa fa-credit-card"></i> Accept Payment
                                                                    </a>
                                                                <?php elseif (!$isFullyPaid): ?>
                                                                    <span class="dropdown-item disabled text-muted">Payment access unavailable</span>
                                                                <?php else: ?>
                                                                    <div class="dropdown-divider"></div>
                                                                    <span class="dropdown-item disabled text-success">Paid</span>
                                                                <?php endif; ?>

                                                                <?php if ($isAdmin): ?>
                                                                    <div class="dropdown-divider"></div>
                                                                    <a class="dropdown-item" href="<?= base_url(); ?>Page/jobOrderEntry?id=<?= (int) $row->orderID; ?>">
                                                                        <i class="fa fa-edit"></i> Edit Record
                                                                    </a>
                                                                    <?php if (!$isFullyPaid): ?>
                                                                        <a class="dropdown-item text-danger" href="<?= base_url(); ?>Page/deleteJO?id=<?= (int) $row->orderID; ?>&return_to=joList" onclick="return confirm('Are you sure you want to delete this record?');">
                                                                            <i class="fa fa-trash"></i> Delete
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="dropdown-item disabled text-muted">
                                                                            <i class="fa fa-trash"></i> Delete (Paid)
                                                                        </span>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-empty">No job orders found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script>
        (function($) {
            'use strict';

            $(function() {
                var table = $('#jo-table').DataTable({
                    responsive: true,
                    autoWidth: false,
                    stateSave: true,
                    pageLength: 25,
                    order: [],
                    language: {
                        emptyTable: 'No job orders found.'
                    },
                    columnDefs: [{
                        targets: [4, 5, 6],
                        className: 'text-right'
                    }, {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }]
                });

                // Custom filter function
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        var activeFilter = $('.filter-btn.active').data('filter');
                        var row = table.row(dataIndex);
                        var paymentStatus = row.nodes().to$().data('payment-status');

                        if (activeFilter === 'all') {
                            return true;
                        } else if (activeFilter === 'paid') {
                            return paymentStatus === 'paid';
                        } else if (activeFilter === 'unpaid') {
                            return paymentStatus === 'unpaid';
                        }
                        return true;
                    }
                );
            });

            // Global filter function
            window.filterJobOrders = function(filterType) {
                $('.filter-btn').removeClass('active');
                $('.filter-btn[data-filter="' + filterType + '"]').addClass('active');
                $('#jo-table').DataTable().draw();
            };
        })(jQuery);
    </script>

</body>

</html>