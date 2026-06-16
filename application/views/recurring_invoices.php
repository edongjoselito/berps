<?php
$templates = isset($templates) && is_array($templates) ? $templates : array();
$generatedInvoices = isset($generatedInvoices) && is_array($generatedInvoices) ? $generatedInvoices : array();
$totals = isset($totals) && is_array($totals) ? $totals : array();
$generationSummary = isset($generationSummary) && is_array($generationSummary) ? $generationSummary : array();
$lastGeneratorRunAt = isset($lastGeneratorRunAt) ? (string) $lastGeneratorRunAt : '';

$templateCount = (int) ($totals['templateCount'] ?? count($templates));
$dueSoonCount = (int) ($totals['dueSoonCount'] ?? 0);
$readyCount = (int) ($totals['readyCount'] ?? 0);
$needsGenerationCount = (int) ($totals['needsGenerationCount'] ?? 0);
$generatedInvoiceCount = (int) ($totals['generatedInvoiceCount'] ?? count($generatedInvoices));
$generatedThisRun = (int) ($generationSummary['generatedCount'] ?? 0);
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
                <div class="container-fluid recurring-page">

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
                        .recurring-page {
                            --bg: #f5f7fb;
                            --surface: rgba(255, 255, 255, 0.94);
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

                        .recurring-page * {
                            box-sizing: border-box;
                        }

                        .recurring-page .rp-header {
                            margin: 24px 0 18px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                        }

                        .recurring-page .rp-eyebrow {
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

                        .recurring-page .rp-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .recurring-page .rp-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                            color: var(--text);
                            line-height: 1.08;
                        }

                        .recurring-page .rp-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                            font-weight: 500;
                            max-width: 760px;
                        }

                        .recurring-page .rp-actions {
                            display: flex;
                            align-items: center;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .recurring-page .btn-generator,
                        .recurring-page .btn-new-template {
                            display: inline-flex;
                            align-items: center;
                            gap: 10px;
                            border-radius: 12px;
                            padding: 11px 18px;
                            font-size: 0.9rem;
                            font-weight: 700;
                            letter-spacing: 0.01em;
                            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
                            text-decoration: none;
                        }

                        .recurring-page .btn-generator {
                            border: none;
                            background: linear-gradient(135deg, var(--success), #047857);
                            color: #fff;
                            box-shadow: 0 10px 24px rgba(5, 150, 105, 0.22);
                        }

                        .recurring-page .btn-new-template {
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                        }

                        .recurring-page .btn-generator:hover,
                        .recurring-page .btn-new-template:hover {
                            transform: translateY(-1px);
                            text-decoration: none;
                        }

                        .recurring-page .btn-generator:hover {
                            color: #fff;
                            box-shadow: 0 14px 28px rgba(5, 150, 105, 0.28);
                            filter: brightness(1.02);
                        }

                        .recurring-page .btn-new-template:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .recurring-page .generator-note {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px;
                            margin-bottom: 18px;
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 14px;
                            flex-wrap: wrap;
                        }

                        .recurring-page .generator-note strong {
                            display: block;
                            color: var(--text);
                            font-size: 0.98rem;
                            margin-bottom: 5px;
                        }

                        .recurring-page .generator-note p {
                            margin: 0;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                        }

                        .recurring-page .generator-chip-wrap {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .recurring-page .generator-chip {
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
                        }

                        .recurring-page .generator-chip.is-success {
                            background: var(--success-soft);
                            color: var(--success);
                            border-color: #c7f0dd;
                        }

                        .recurring-page .stat-strip {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 22px;
                        }

                        .recurring-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px 20px;
                            min-height: 116px;
                        }

                        .recurring-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                        }

                        .recurring-page .stat-card.sc-total::before {
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .recurring-page .stat-card.sc-window::before {
                            background: linear-gradient(90deg, #f59e0b, #fbbf24);
                        }

                        .recurring-page .stat-card.sc-ready::before {
                            background: linear-gradient(90deg, #10b981, #34d399);
                        }

                        .recurring-page .stat-card.sc-generated::before {
                            background: linear-gradient(90deg, #6366f1, #818cf8);
                        }

                        .recurring-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .recurring-page .stat-value {
                            color: var(--text);
                            font-size: 2rem;
                            font-weight: 800;
                            line-height: 1;
                            letter-spacing: -0.04em;
                            margin-bottom: 8px;
                        }

                        .recurring-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            font-weight: 500;
                        }

                        .recurring-page .panel-card {
                            background: var(--surface);
                            backdrop-filter: blur(12px);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: 24px;
                            box-shadow: var(--shadow);
                            overflow: hidden;
                            margin-bottom: 22px;
                        }

                        .recurring-page .panel-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid var(--line);
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 14px;
                            flex-wrap: wrap;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(249, 251, 255, 0.94));
                        }

                        .recurring-page .panel-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.05rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .recurring-page .panel-subtitle {
                            margin: 6px 0 0;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                        }

                        .recurring-page .panel-body {
                            padding: 22px 24px 24px;
                        }

                        .recurring-page .table {
                            color: var(--text);
                        }

                        .recurring-page .table thead th {
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            padding-top: 0;
                        }

                        .recurring-page .table td {
                            vertical-align: middle;
                            border-top: 1px solid var(--line);
                        }

                        .recurring-page .line-main {
                            font-weight: 700;
                            color: var(--text);
                        }

                        .recurring-page .line-sub {
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            margin-top: 3px;
                        }

                        .recurring-page .chip {
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

                        .recurring-page .chip.is-primary {
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            border-color: #dbeafe;
                        }

                        .recurring-page .chip.is-success {
                            background: var(--success-soft);
                            color: var(--success);
                            border-color: #c7f0dd;
                        }

                        .recurring-page .chip.is-warning {
                            background: var(--warning-soft);
                            color: var(--warning);
                            border-color: #fed7aa;
                        }

                        .recurring-page .chip.is-danger {
                            background: var(--danger-soft);
                            color: var(--danger);
                            border-color: #fecdd3;
                        }

                        .recurring-page .chip.is-slate {
                            background: var(--slate-soft);
                            color: #475569;
                            border-color: #e2e8f0;
                        }

                        .recurring-page .link-quiet {
                            color: var(--primary-2);
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .recurring-page .link-quiet:hover {
                            color: var(--primary);
                            text-decoration: underline;
                        }

                        .recurring-page .num-cell {
                            text-align: right;
                            font-variant-numeric: tabular-nums;
                            white-space: nowrap;
                        }

                        .recurring-page .table-actions {
                            display: inline-flex;
                            gap: 8px;
                            flex-wrap: wrap;
                        }

                        .recurring-page .table-btn {
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

                        .recurring-page .table-btn:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                            text-decoration: none;
                        }

                        .recurring-page .empty-state {
                            text-align: center;
                            color: var(--text-soft);
                            padding: 28px 0;
                        }

                        @media (max-width: 1199px) {
                            .recurring-page .stat-strip {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 767px) {
                            .recurring-page .stat-strip {
                                grid-template-columns: 1fr;
                            }

                            .recurring-page .panel-body,
                            .recurring-page .panel-header {
                                padding-left: 16px;
                                padding-right: 16px;
                            }

                            .recurring-page .rp-title {
                                font-size: 1.72rem;
                            }
                        }
                    </style>

                    <div class="rp-header">
                        <div>
                            <div class="rp-eyebrow">Recurring Invoice Monitor</div>
                            <h1 class="rp-title">Recurring Invoices</h1>
                            <!-- <p class="rp-subtitle">Monitor recurring templates, see the next due date for each billing cycle, and confirm which upcoming invoices are covered by the template itself versus those created by the generator.</p> -->
                        </div>

                        <div class="rp-actions">
                            <a href="<?= base_url(); ?>Page/runRecurringInvoiceGenerator" class="btn-generator">
                                <i class="fa fa-sync-alt"></i>
                                Run Invoice Generator
                            </a>
                            <a href="<?= base_url(); ?>Page/invoiceEntry" class="btn-new-template">
                                <i class="fa fa-plus"></i>
                                Create Recurring Template
                            </a>
                        </div>
                    </div>

                    <div class="generator-note">
                        <div>
                            <strong>Automatic 10-day billing window is active.</strong>
                            <p>The system checks recurring invoice templates during normal billing activity and prepares the next invoice when the due date is within 10 days. The first due date is covered by the template invoice itself, and generated child invoices begin on the following recurring cycle.</p>
                        </div>
                        <div class="generator-chip-wrap">
                            <div class="generator-chip">
                                <i class="fa fa-clock"></i>
                                Last checked: <?= htmlspecialchars($lastGeneratorRunAt !== '' ? $lastGeneratorRunAt : 'Just now', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="generator-chip is-success">
                                <i class="fa fa-file-invoice"></i>
                                Generated this check: <?= number_format($generatedThisRun); ?>
                            </div>
                        </div>
                    </div>

                    <div class="stat-strip">
                        <div class="stat-card sc-total">
                            <div class="stat-label">Active Templates</div>
                            <div class="stat-value"><?= number_format($templateCount); ?></div>
                            <div class="stat-meta">Recurring invoice parents currently driving the billing schedule.</div>
                        </div>
                        <div class="stat-card sc-window">
                            <div class="stat-label">Due Within 10 Days</div>
                            <div class="stat-value"><?= number_format($dueSoonCount); ?></div>
                            <div class="stat-meta"><?= number_format($needsGenerationCount); ?> still need attention inside the generator window.</div>
                        </div>
                        <div class="stat-card sc-ready">
                            <div class="stat-label">Upcoming Ready</div>
                            <div class="stat-value"><?= number_format($readyCount); ?></div>
                            <div class="stat-meta">Templates whose next due cycle is already covered, either by the template invoice or a generated child invoice.</div>
                        </div>
                        <div class="stat-card sc-generated">
                            <div class="stat-label">Generated Records</div>
                            <div class="stat-value"><?= number_format($generatedInvoiceCount); ?></div>
                            <div class="stat-meta">Child invoices already created from recurring templates.</div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Recurring Invoice Templates</h2>
                                <p class="panel-subtitle">Each row shows the recurring template, the next billing due date, and whether that due date is covered by the template invoice itself or by a generated recurring child invoice.</p>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="recurring-template-table" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Template</th>
                                            <th>Client</th>
                                            <th>Description</th>
                                            <th>Frequency</th>
                                            <th>Start Date</th>
                                            <th>Next Due Date</th>
                                            <th>Window Opens</th>
                                            <th>Status</th>
                                            <th>Invoice Covering Due Date</th>
                                            <th class="num-cell">Total Due</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($templates)): ?>
                                            <?php foreach ($templates as $row): ?>
                                                <?php
                                                $templateInvoiceUrl = base_url() . 'Page/invoice?id=' . rawurlencode((string) ($row['orderID'] ?? '0'));
                                                $editTemplateUrl = base_url() . 'Page/invoiceEntry?id=' . rawurlencode((string) ($row['orderID'] ?? '0'));
                                                $readyInvoiceUrl = !empty($row['preparedInvoiceOrderID'])
                                                    ? base_url() . 'Page/invoice?id=' . rawurlencode((string) $row['preparedInvoiceOrderID'])
                                                    : '';
                                                $statusClass = 'is-slate';
                                                if (($row['statusKey'] ?? '') === 'ready' || ($row['statusKey'] ?? '') === 'base') {
                                                    $statusClass = 'is-success';
                                                } elseif (($row['statusKey'] ?? '') === 'attention') {
                                                    $statusClass = 'is-danger';
                                                } elseif (!empty($row['isDueSoon'])) {
                                                    $statusClass = 'is-warning';
                                                } elseif (($row['statusKey'] ?? '') === 'scheduled') {
                                                    $statusClass = 'is-primary';
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a class="link-quiet" href="<?= htmlspecialchars($templateInvoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                            #<?= htmlspecialchars((string) ($row['InvoiceNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                        <div class="line-sub">Generated children: <?= number_format((int) ($row['generatedChildCount'] ?? 0)); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($row['Customer'] ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="line-sub"><?= htmlspecialchars((string) ($row['CustID'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($row['JobDescription'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php if (!empty($row['latestGeneratedInvoiceNo'])): ?>
                                                            <div class="line-sub">Latest prepared: #<?= htmlspecialchars((string) $row['latestGeneratedInvoiceNo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="chip is-primary"><?= htmlspecialchars((string) ($row['frequencyLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td><?= !empty($row['scheduleDate']) ? htmlspecialchars(date('M j, Y', strtotime((string) $row['scheduleDate'])), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                    <td>
                                                        <?php if (!empty($row['upcomingDueDate'])): ?>
                                                            <div class="line-main"><?= htmlspecialchars(date('M j, Y', strtotime((string) $row['upcomingDueDate'])), ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <div class="line-sub"><?= number_format((int) ($row['daysUntilDue'] ?? 0)); ?> day(s) remaining</div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= !empty($row['windowOpensOn']) ? htmlspecialchars(date('M j, Y', strtotime((string) $row['windowOpensOn'])), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><span class="chip <?= $statusClass; ?>"><?= htmlspecialchars((string) ($row['statusLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td>
                                                        <?php if ($readyInvoiceUrl !== ''): ?>
                                                            <a class="link-quiet" href="<?= htmlspecialchars($readyInvoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                #<?= htmlspecialchars((string) ($row['preparedInvoiceNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                            <div class="line-sub"><?= ($row['preparedInvoiceSource'] ?? '') === 'template' ? 'First billing uses the template invoice' : 'Generated recurring invoice'; ?></div>
                                                        <?php else: ?>
                                                            <span class="chip is-danger">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="num-cell"><?= number_format((float) ($row['totalDue'] ?? 0), 2); ?></td>
                                                    <td>
                                                        <div class="table-actions">
                                                            <a class="table-btn" href="<?= htmlspecialchars($templateInvoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>
                                                            <a class="table-btn" href="<?= htmlspecialchars($editTemplateUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="fa fa-edit"></i> Edit
                                                            </a>
                                                            <?php if ($this->session->userdata('level') === 'Admin'): ?>
                                                            <?php $deleteUrl = base_url() . 'Page/deleteRecurringInvoice?id=' . rawurlencode((string) ($row['orderID'] ?? '0')); ?>
                                                            <a class="table-btn" href="<?= htmlspecialchars($deleteUrl, ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Delete this recurring template? This will void the template invoice.\n\nTo also delete all generated child invoices, click OK then check the option on the next page.');" style="color: var(--danger); border-color: var(--danger-soft);">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="11" class="empty-state">No recurring invoice templates found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Generated Recurring Invoices</h2>
                                <p class="panel-subtitle">These are the child invoices that were automatically created from the recurring templates.</p>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="recurring-generated-table" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Template</th>
                                            <th>Client</th>
                                            <th>Description</th>
                                            <th>Schedule Date</th>
                                            <th>Frequency</th>
                                            <th class="num-cell">Total Due</th>
                                            <th class="num-cell">Balance</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($generatedInvoices)): ?>
                                            <?php foreach ($generatedInvoices as $row): ?>
                                                <?php
                                                $invoiceUrl = base_url() . 'Page/invoice?id=' . rawurlencode((string) ($row['orderID'] ?? '0'));
                                                $templateUrl = !empty($row['templateOrderID'])
                                                    ? base_url() . 'Page/invoice?id=' . rawurlencode((string) $row['templateOrderID'])
                                                    : '';
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a class="link-quiet" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                            #<?= htmlspecialchars((string) ($row['InvoiceNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php if ($templateUrl !== ''): ?>
                                                            <a class="link-quiet" href="<?= htmlspecialchars($templateUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                #<?= htmlspecialchars((string) ($row['templateInvoiceNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">Template unavailable</span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($row['templateDescription'])): ?>
                                                            <div class="line-sub"><?= htmlspecialchars((string) $row['templateDescription'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($row['Customer'] ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="line-sub"><?= htmlspecialchars((string) ($row['CustID'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($row['JobDescription'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= !empty($row['scheduleDate']) ? htmlspecialchars(date('M j, Y', strtotime((string) $row['scheduleDate'])), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><span class="chip is-slate"><?= htmlspecialchars((string) ($row['frequencyLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td class="num-cell"><?= number_format((float) ($row['totalDue'] ?? 0), 2); ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($row['balance'] ?? 0), 2); ?></td>
                                                    <td>
                                                        <div class="table-actions">
                                                            <a class="table-btn" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>
                                                            <?php if ($this->session->userdata('level') === 'Admin'): ?>
                                                            <?php $deleteUrl = base_url() . 'Page/deleteRecurringInvoice?id=' . rawurlencode((string) ($row['orderID'] ?? '0')); ?>
                                                            <a class="table-btn" href="<?= htmlspecialchars($deleteUrl, ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Delete this generated recurring invoice?');" style="color: var(--danger); border-color: var(--danger-soft);">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="empty-state">No generated recurring invoices have been created yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Terminated Recurring Invoices</h2>
                                <p class="panel-subtitle">These recurring invoice templates have been terminated and are no longer generating new invoices.</p>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="recurring-terminated-table" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Client</th>
                                            <th>Description</th>
                                            <th>Frequency</th>
                                            <th>Termination Date</th>
                                            <th>Original Schedule</th>
                                            <th class="num-cell">Total Due</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($terminatedInvoices)): ?>
                                            <?php foreach ($terminatedInvoices as $row): ?>
                                                <?php
                                                $invoiceUrl = base_url() . 'Page/invoice?id=' . rawurlencode((string) ($row->orderID ?? '0'));
                                                $frequencyLabels = [
                                                    'daily' => 'Daily',
                                                    'weekly' => 'Weekly',
                                                    'monthly' => 'Monthly',
                                                    'quarterly' => 'Quarterly',
                                                    'yearly' => 'Yearly'
                                                ];
                                                $frequencyLabel = $frequencyLabels[$row->recurringFrequency] ?? ucfirst($row->recurringFrequency ?? '');
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a class="link-quiet" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                            #<?= htmlspecialchars((string) ($row->InvoiceNo ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($row->Customer ?? 'Unknown Client'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="line-sub"><?= htmlspecialchars((string) ($row->CustID ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="line-main"><?= htmlspecialchars((string) ($row->JobDescription ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td><span class="chip is-slate"><?= htmlspecialchars($frequencyLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td>
                                                        <div class="line-main"><?= !empty($row->recurringTerminationDate) ? htmlspecialchars(date('M j, Y', strtotime((string) $row->recurringTerminationDate)), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></div>
                                                        <div class="line-sub text-danger">Terminated</div>
                                                    </td>
                                                    <td><?= !empty($row->recurringScheduleDate) ? htmlspecialchars(date('M j, Y', strtotime((string) $row->recurringScheduleDate)), ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
                                                    <td class="num-cell"><?= number_format((float) ($row->TotalDue ?? 0), 2); ?></td>
                                                    <td>
                                                        <div class="table-actions">
                                                            <a class="table-btn" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>
                                                            <?php if ($this->session->userdata('level') === 'Admin'): ?>
                                                            <?php $deleteUrl = base_url() . 'Page/deleteRecurringInvoice?id=' . rawurlencode((string) ($row->orderID ?? '0')); ?>
                                                            <a class="table-btn" href="<?= htmlspecialchars($deleteUrl, ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Delete this terminated recurring invoice?');" style="color: var(--danger); border-color: var(--danger-soft);">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="empty-state">No terminated recurring invoices found.</td>
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
                $('#recurring-template-table').DataTable({
                    responsive: true,
                    autoWidth: false,
                    stateSave: true,
                    pageLength: 25,
                    order: [],
                    columnDefs: [{
                        targets: [9],
                        className: 'text-right'
                    }, {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }]
                });

                $('#recurring-generated-table').DataTable({
                    responsive: true,
                    autoWidth: false,
                    stateSave: true,
                    pageLength: 25,
                    order: [],
                    columnDefs: [{
                        targets: [6, 7],
                        className: 'text-right'
                    }, {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }]
                });

                $('#recurring-terminated-table').DataTable({
                    responsive: true,
                    autoWidth: false,
                    stateSave: true,
                    pageLength: 25,
                    order: [[4, 'desc']], // Sort by termination date by default
                    columnDefs: [{
                        targets: [6],
                        className: 'text-right'
                    }, {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }]
                });
            });
        })(jQuery);
    </script>

</body>

</html>