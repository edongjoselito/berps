<?php
$sections = isset($sections) && is_array($sections) ? $sections : array();
$overallTotals = isset($overallTotals) && is_array($overallTotals) ? $overallTotals : array();
$generatedAt = isset($generatedAt) ? (string) $generatedAt : '';

$paidSection = $sections['paid'] ?? array('rows' => array(), 'count' => 0, 'balance' => 0.0, 'totalDue' => 0.0, 'label' => 'Paid Invoices', 'subtitle' => '');
$unpaidSection = $sections['unpaid'] ?? array('rows' => array(), 'count' => 0, 'balance' => 0.0, 'totalDue' => 0.0, 'label' => 'Unpaid, Not Yet Due', 'subtitle' => '');
$overdueSection = $sections['overdue'] ?? array('rows' => array(), 'count' => 0, 'balance' => 0.0, 'totalDue' => 0.0, 'label' => 'Overdue Invoices', 'subtitle' => '');
$draftSection = $sections['draft'] ?? array('rows' => array(), 'count' => 0, 'balance' => 0.0, 'totalDue' => 0.0, 'label' => 'Draft Invoices', 'subtitle' => '');

$reportSections = array($paidSection, $unpaidSection, $overdueSection, $draftSection);
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
                <div class="container-fluid invoice-status-report-page">

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
                        .invoice-status-report-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.94);
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
                            --slate-soft: #f8fafc;
                            --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.05);
                            --radius-xl: 22px;
                            --radius-lg: 16px;
                            --radius-md: 12px;
                            --radius-sm: 10px;
                            --font-body: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'Inter', 'Montserrat', 'Segoe UI', Arial, sans-serif;
                            font-family: var(--font-body);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 24px;
                        }

                        .invoice-status-report-page * {
                            box-sizing: border-box;
                        }

                        .invoice-status-report-page .isr-header {
                            margin: 24px 0 18px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .invoice-status-report-page .isr-eyebrow {
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

                        .invoice-status-report-page .isr-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .invoice-status-report-page .isr-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.08;
                        }

                        .invoice-status-report-page .isr-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                            max-width: 760px;
                        }

                        .invoice-status-report-page .isr-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .invoice-status-report-page .btn-soft,
                        .invoice-status-report-page .btn-primary {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 11px 18px;
                            border-radius: 12px;
                            font-size: 0.9rem;
                            font-weight: 700;
                            text-decoration: none;
                            transition: transform 0.16s ease, box-shadow 0.16s ease;
                        }

                        .invoice-status-report-page .btn-soft {
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                        }

                        .invoice-status-report-page .btn-soft:hover {
                            color: var(--primary);
                            background: #f9fbff;
                            border-color: #bfd3ef;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .invoice-status-report-page .btn-primary {
                            border: none;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            color: #fff;
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .invoice-status-report-page .btn-primary:hover {
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .invoice-status-report-page .note-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px;
                            margin-bottom: 18px;
                            display: flex;
                            justify-content: space-between;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .invoice-status-report-page .note-card strong {
                            display: block;
                            color: var(--text);
                            margin-bottom: 6px;
                            font-size: 0.98rem;
                        }

                        .invoice-status-report-page .note-card p {
                            margin: 0;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                        }

                        .invoice-status-report-page .note-chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 9px 12px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            border: 1px solid #dbeafe;
                            font-size: 0.8rem;
                            font-weight: 700;
                            white-space: nowrap;
                        }

                        .invoice-status-report-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 22px;
                        }

                        .invoice-status-report-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px 20px;
                            min-height: 116px;
                        }

                        .invoice-status-report-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .invoice-status-report-page .stat-card.sc-paid::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .invoice-status-report-page .stat-card.sc-unpaid::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .invoice-status-report-page .stat-card.sc-overdue::before {
                            background: linear-gradient(90deg, #f43f5e, #fb7185);
                        }

                        .invoice-status-report-page .stat-card.sc-draft::before {
                            background: linear-gradient(90deg, #94a3b8, #cbd5e1);
                        }

                        .invoice-status-report-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .invoice-status-report-page .stat-value {
                            color: var(--text);
                            font-size: 2rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                            margin-bottom: 8px;
                        }

                        .invoice-status-report-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 500;
                        }

                        .invoice-status-report-page .panel-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: 24px;
                            box-shadow: var(--shadow);
                            overflow: hidden;
                            margin-bottom: 22px;
                        }

                        .invoice-status-report-page .panel-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            justify-content: space-between;
                            gap: 14px;
                            flex-wrap: wrap;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                        }

                        .invoice-status-report-page .panel-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.05rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .invoice-status-report-page .panel-subtitle {
                            margin: 6px 0 0;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                        }

                        .invoice-status-report-page .panel-summary {
                            display: flex;
                            gap: 18px;
                            flex-wrap: wrap;
                            align-items: center;
                        }

                        .invoice-status-report-page .summary-item {
                            min-width: 110px;
                        }

                        .invoice-status-report-page .summary-item-label {
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 4px;
                        }

                        .invoice-status-report-page .summary-item-value {
                            color: var(--text);
                            font-size: 0.95rem;
                            font-weight: 700;
                            font-variant-numeric: tabular-nums;
                        }

                        .invoice-status-report-page .panel-body {
                            padding: 22px 24px 24px;
                        }

                        .invoice-status-report-page .table {
                            color: var(--text);
                        }

                        .invoice-status-report-page .table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            padding-top: 0;
                        }

                        .invoice-status-report-page .table td {
                            vertical-align: middle;
                            border-top: 1px solid var(--line);
                        }

                        .invoice-status-report-page .line-main {
                            font-weight: 700;
                            color: var(--text);
                        }

                        .invoice-status-report-page .line-sub {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            margin-top: 3px;
                        }

                        .invoice-status-report-page .chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 7px;
                            padding: 6px 10px;
                            border-radius: 999px;
                            font-size: 0.76rem;
                            font-weight: 800;
                            letter-spacing: 0.04em;
                            text-transform: uppercase;
                            border: 1px solid transparent;
                            white-space: nowrap;
                        }

                        .invoice-status-report-page .chip.is-success {
                            background: var(--success-soft);
                            color: var(--success);
                            border-color: #c7f0dd;
                        }

                        .invoice-status-report-page .chip.is-primary {
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            border-color: #dbeafe;
                        }

                        .invoice-status-report-page .chip.is-danger {
                            background: var(--danger-soft);
                            color: var(--danger);
                            border-color: #fecdd3;
                        }

                        .invoice-status-report-page .chip.is-slate {
                            background: var(--slate-soft);
                            color: #475569;
                            border-color: #e2e8f0;
                        }

                        .invoice-status-report-page .chip.is-warning {
                            background: var(--warning-soft);
                            color: var(--warning);
                            border-color: #fed7aa;
                        }

                        .invoice-status-report-page .link-quiet {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .invoice-status-report-page .link-quiet:hover {
                            color: var(--primary);
                            text-decoration: underline;
                        }

                        .invoice-status-report-page .num-cell {
                            text-align: right;
                            font-variant-numeric: tabular-nums;
                            white-space: nowrap;
                        }

                        .invoice-status-report-page .table-actions {
                            display: inline-flex;
                            gap: 8px;
                            flex-wrap: wrap;
                        }

                        .invoice-status-report-page .table-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 7px 10px;
                            border-radius: 10px;
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                            font-size: 0.8rem;
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .invoice-status-report-page .table-btn:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                            text-decoration: none;
                        }

                        .invoice-status-report-page .empty-state {
                            text-align: center;
                            color: var(--text-soft);
                            padding: 28px 0;
                        }

                        @media (max-width: 1199px) {
                            .invoice-status-report-page .stat-strip {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 767px) {
                            .invoice-status-report-page .stat-strip {
                                grid-template-columns: 1fr;
                            }

                            .invoice-status-report-page .panel-body,
                            .invoice-status-report-page .panel-header {
                                padding-left: 16px;
                                padding-right: 16px;
                            }

                            .invoice-status-report-page .isr-title {
                                font-size: 1.72rem;
                            }
                        }
                    </style>

                    <div class="isr-header">
                        <div>
                            <div class="isr-eyebrow">Invoice Reporting</div>
                            <h1 class="isr-title">Invoice Status Report</h1>
                            <!-- <p class="isr-subtitle">Track which invoices are already paid, still open but not yet due, already overdue, or currently saved as draft.</p> -->
                        </div>

                        <div class="isr-actions">
                            <a href="<?= base_url(); ?>Page/invList" class="btn-soft">
                                <i class="fa fa-list"></i>
                                Invoice List
                            </a>
                            <a href="<?= base_url(); ?>Page/invoiceEntry" class="btn-primary">
                                <i class="fa fa-plus"></i>
                                Add New Invoice
                            </a>
                        </div>
                    </div>

                    <!-- <div class="note-card">
                        <div>
                            <strong>Due date logic used in this report</strong>
                            <p>Invoices are classified by due date using `recurringScheduleDate` first, then `ReceiveDate`, then `TransDate`. Draft rows depend on `invoiceStat = Draft`, so they will stay at zero until a draft workflow starts saving that status.</p>
                        </div>
                        <div class="note-chip">
                            <i class="fa fa-clock"></i>
                            Generated: <?= htmlspecialchars($generatedAt !== '' ? $generatedAt : 'Just now', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div> -->

                    <div class="stat-strip">
                        <div class="stat-card sc-paid">
                            <div class="stat-label">Paid</div>
                            <div class="stat-value"><?= number_format((int) ($overallTotals['paidCount'] ?? 0)); ?></div>
                            <div class="stat-meta">Settled invoice amount: <?= number_format((float) ($overallTotals['paidAmount'] ?? 0), 2); ?></div>
                        </div>
                        <div class="stat-card sc-unpaid">
                            <div class="stat-label">Unpaid, Not Yet Due</div>
                            <div class="stat-value"><?= number_format((int) ($overallTotals['unpaidCount'] ?? 0)); ?></div>
                            <div class="stat-meta">Open upcoming balance: <?= number_format((float) ($overallTotals['openAmount'] ?? 0), 2); ?></div>
                        </div>
                        <div class="stat-card sc-overdue">
                            <div class="stat-label">Overdue</div>
                            <div class="stat-value"><?= number_format((int) ($overallTotals['overdueCount'] ?? 0)); ?></div>
                            <div class="stat-meta">Outstanding overdue balance: <?= number_format((float) ($overallTotals['overdueAmount'] ?? 0), 2); ?></div>
                        </div>
                        <div class="stat-card sc-draft">
                            <div class="stat-label">Draft</div>
                            <div class="stat-value"><?= number_format((int) ($overallTotals['draftCount'] ?? 0)); ?></div>
                            <div class="stat-meta">Draft invoice total: <?= number_format((float) ($overallTotals['draftAmount'] ?? 0), 2); ?></div>
                        </div>
                    </div>

                    <?php foreach ($reportSections as $section): ?>
                        <?php
                        $sectionKey = (string) ($section['key'] ?? '');
                        $sectionRows = isset($section['rows']) && is_array($section['rows']) ? $section['rows'] : array();
                        $tableId = 'invoice-status-table-' . $sectionKey;
                        ?>
                        <div class="panel-card">
                            <div class="panel-header">
                                <div>
                                    <h2 class="panel-title"><?= htmlspecialchars((string) ($section['label'] ?? 'Invoices'), ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <p class="panel-subtitle"><?= htmlspecialchars((string) ($section['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="panel-summary">
                                    <div class="summary-item">
                                        <div class="summary-item-label">Count</div>
                                        <div class="summary-item-value"><?= number_format((int) ($section['count'] ?? 0)); ?></div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-item-label">Total Due</div>
                                        <div class="summary-item-value"><?= number_format((float) ($section['totalDue'] ?? 0), 2); ?></div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-item-label">Balance</div>
                                        <div class="summary-item-value"><?= number_format((float) ($section['balance'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table id="<?= htmlspecialchars($tableId, ENT_QUOTES, 'UTF-8'); ?>" class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Invoice</th>
                                                <th>Client</th>
                                                <th>Description</th>
                                                <th>Invoice Date</th>
                                                <th>Due Date</th>
                                                <th class="num-cell">Total Due</th>
                                                <th class="num-cell">Amount Paid</th>
                                                <th class="num-cell">Balance</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($sectionRows)): ?>
                                                <?php foreach ($sectionRows as $row): ?>
                                                    <?php
                                                    $invoiceUrl = base_url() . 'Page/invoice?id=' . rawurlencode((string) ($row['orderID'] ?? '0'));
                                                    $editUrl = base_url() . 'Page/invoiceEntry?id=' . rawurlencode((string) ($row['orderID'] ?? '0'));
                                                    $printUrl = $invoiceUrl . '&print=1';
                                                    $statusClass = 'is-slate';
                                                    if ($sectionKey === 'paid') {
                                                        $statusClass = 'is-success';
                                                    } elseif ($sectionKey === 'unpaid') {
                                                        $statusClass = 'is-primary';
                                                    } elseif ($sectionKey === 'overdue') {
                                                        $statusClass = 'is-danger';
                                                    } elseif ($sectionKey === 'draft') {
                                                        $statusClass = 'is-slate';
                                                    }
                                                    $paymentStateClass = 'is-slate';
                                                    if (($row['paymentStateLabel'] ?? '') === 'Paid') {
                                                        $paymentStateClass = 'is-success';
                                                    } elseif (($row['paymentStateLabel'] ?? '') === 'Partially Paid') {
                                                        $paymentStateClass = 'is-warning';
                                                    } elseif (($row['paymentStateLabel'] ?? '') === 'Unpaid') {
                                                        $paymentStateClass = 'is-danger';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <a class="link-quiet" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                #<?= htmlspecialchars((string) ($row['InvoiceNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                            <div class="line-sub">Encoder: <?= htmlspecialchars((string) ($row['invoiceBy'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </td>
                                                        <td>
                                                            <div class="line-main"><?= htmlspecialchars((string) ($row['Customer'] ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <div class="line-sub"><?= htmlspecialchars((string) ($row['CustID'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </td>
                                                        <td>
                                                            <div class="line-main"><?= htmlspecialchars((string) ($row['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <div class="line-sub">
                                                                <?= htmlspecialchars((string) ($row['timingLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                                <?php if (!empty($row['isRecurring'])): ?>
                                                                    · <?= htmlspecialchars((string) ($row['recurringFrequencyLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> recurring
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <td><?= !empty($row['invoiceDate']) ? htmlspecialchars(date('M j, Y', strtotime((string) $row['invoiceDate'])), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                        <td><?= !empty($row['dueDate']) ? htmlspecialchars(date('M j, Y', strtotime((string) $row['dueDate'])), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not set</span>'; ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($row['totalDue'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($row['amountPaid'] ?? 0), 2); ?></td>
                                                        <td class="num-cell"><?= number_format((float) ($row['balance'] ?? 0), 2); ?></td>
                                                        <td>
                                                            <span class="chip <?= $paymentStateClass; ?>"><?= htmlspecialchars((string) ($row['paymentStateLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="table-actions">
                                                                <a class="table-btn" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <i class="fa fa-eye"></i> View
                                                                </a>
                                                                <a class="table-btn" href="<?= htmlspecialchars($editUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <i class="fa fa-edit"></i> Edit
                                                                </a>
                                                                <a class="table-btn" href="<?= htmlspecialchars($printUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                                                    <i class="fa fa-print"></i> Print
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="10" class="empty-state">No invoices matched this section.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

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
                ['paid', 'unpaid', 'overdue', 'draft'].forEach(function(sectionKey) {
                    var selector = '#invoice-status-table-' + sectionKey;
                    if (!$(selector).length) {
                        return;
                    }

                    $(selector).DataTable({
                        responsive: true,
                        autoWidth: false,
                        stateSave: true,
                        pageLength: 25,
                        order: [],
                        columnDefs: [{
                            targets: [5, 6, 7],
                            className: 'text-right'
                        }, {
                            targets: -1,
                            orderable: false,
                            searchable: false
                        }]
                    });
                });
            });
        })(jQuery);
    </script>

</body>

</html>