<?php
$units = isset($data) && is_array($data) ? $data : [];
$editUnit = isset($editUnit) ? $editUnit : null;
$isEditMode = !empty($editUnit);
$unitNameValue = $isEditMode ? (string) ($editUnit->unitName ?? '') : '';
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
                <div class="container-fluid invoice-units-page">
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                        .invoice-units-page {
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
                            --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                            font-family: var(--font-body);
                        }

                        .invoice-units-page * {
                            box-sizing: border-box;
                        }

                        .invoice-units-page .content {
                            margin-bottom: 40px;
                        }

                        .invoice-units-page .alert {
                            border: none;
                            border-radius: var(--radius-lg);
                            box-shadow: var(--shadow-soft);
                        }

                        .invoice-units-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            margin: 16px 0 20px;
                            flex-wrap: wrap;
                        }

                        .invoice-units-page .page-eyebrow {
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

                        .invoice-units-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .invoice-units-page .page-title {
                            margin: 0;
                            font-family: var(--font-head);
                            font-size: 1.5rem;
                            line-height: 1.2;
                            letter-spacing: -0.02em;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .invoice-units-page .page-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.93rem;
                        }

                        .invoice-units-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            overflow: hidden;
                            margin-bottom: 20px;
                        }

                        .invoice-units-page .theme-card-head {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                            padding: 16px 20px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                            flex-wrap: wrap;
                        }

                        .invoice-units-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1rem;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                        }

                        .invoice-units-page .theme-card-body {
                            padding: 20px;
                        }

                        .invoice-units-page .form-label {
                            color: var(--text);
                            font-size: 0.85rem;
                            font-weight: 700;
                            margin-bottom: 8px;
                        }

                        .invoice-units-page .form-control {
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-md);
                            padding: 10px 14px;
                            font-size: 0.92rem;
                        }

                        .invoice-units-page .form-control:focus {
                            border-color: var(--primary);
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .invoice-units-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border: none;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                            transition: all 0.16s ease;
                        }

                        .invoice-units-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .invoice-units-page .btn-action {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border: 1px solid var(--line-strong);
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            color: var(--text);
                            background: #fff;
                            transition: all 0.16s ease;
                            text-decoration: none;
                        }

                        .invoice-units-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .invoice-units-page .table {
                            margin: 0;
                        }

                        .invoice-units-page .table thead th {
                            background: transparent;
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            border-top: none;
                            border-bottom: 1px solid var(--line);
                            white-space: nowrap;
                        }

                        .invoice-units-page .table td {
                            border-color: var(--line);
                            color: var(--text);
                            vertical-align: middle;
                        }

                        .invoice-units-page .action-btn {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            padding: 6px 12px;
                            border-radius: var(--radius-sm);
                            font-size: 0.85rem;
                            font-weight: 700;
                            text-decoration: none;
                            transition: all 0.16s ease;
                        }

                        .invoice-units-page .action-btn.edit {
                            color: var(--primary);
                            background: var(--primary-soft);
                        }

                        .invoice-units-page .action-btn.edit:hover {
                            background: #dbeafe;
                        }

                        .invoice-units-page .action-btn.delete {
                            color: var(--danger);
                            background: var(--danger-soft);
                        }

                        .invoice-units-page .action-btn.delete:hover {
                            background: #fecdd3;
                        }
                    </style>

                    <div class="row">
                        <div class="col-12">
                            <?php if ($this->session->flashdata('success')): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                            <?php if ($this->session->flashdata('danger')): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                            <?php if ($this->session->flashdata('msg')): ?>
                                <?= $this->session->flashdata('msg'); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Settings</div>
                            <h4 class="page-title">Invoice Units</h4>
                            <p class="page-subtitle">Manage the unit options shown in the invoice entry form, such as day, week, month, pcs, lot, unit, or meter.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <h4 class="theme-card-title"><?= $isEditMode ? 'Edit Invoice Unit' : 'Add Invoice Unit'; ?></h4>
                                </div>
                                <div class="theme-card-body">
                                    <form method="post" action="<?= base_url(); ?>Settings/InvoiceUnits">
                                        <?php if ($isEditMode): ?>
                                            <input type="hidden" name="unitID" value="<?= (int) ($editUnit->unitID ?? 0); ?>">
                                        <?php endif; ?>

                                        <div class="form-group">
                                            <label for="unit-name">Unit Name</label>
                                            <input
                                                type="text"
                                                id="unit-name"
                                                name="unitName"
                                                class="form-control"
                                                value="<?= htmlspecialchars($unitNameValue, ENT_QUOTES, 'UTF-8'); ?>"
                                                placeholder="e.g. day, pcs, lot"
                                                maxlength="50"
                                                required>
                                            <small class="form-text text-muted">Stored once and loaded dynamically into invoice entry rows.</small>
                                        </div>

                                        <button type="submit" class="btn btn-primary"><?= $isEditMode ? 'Update Unit' : 'Save Unit'; ?></button>
                                        <?php if ($isEditMode): ?>
                                            <a href="<?= base_url(); ?>Settings/InvoiceUnits" class="btn btn-light ml-2">Cancel</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-3">Available Units</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width: 90px;">ID</th>
                                                    <th>Unit</th>
                                                    <th style="width: 180px;" class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($units)): ?>
                                                    <?php foreach ($units as $unit): ?>
                                                        <tr>
                                                            <td><?= (int) ($unit->unitID ?? 0); ?></td>
                                                            <td><?= htmlspecialchars((string) ($unit->unitName ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-center">
                                                                <a href="<?= base_url(); ?>Settings/InvoiceUnits?edit=<?= (int) ($unit->unitID ?? 0); ?>" class="btn btn-sm btn-info">Edit</a>
                                                                <a
                                                                    href="<?= base_url(); ?>Settings/deleteInvoiceUnit?id=<?= (int) ($unit->unitID ?? 0); ?>"
                                                                    class="btn btn-sm btn-danger"
                                                                    onclick="return confirm('Delete this invoice unit?');">
                                                                    Delete
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">No invoice units available yet.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include('includes/footer.php'); ?>
            </div>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>