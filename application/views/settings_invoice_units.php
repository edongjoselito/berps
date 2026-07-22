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
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
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
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
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

                    <style>
                        /* Hero Banner */
                        .invoice-units-page .iu-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 0 0 22px;
                            border-radius: 16px;
                            background: #115e59;
                            box-shadow: 0 8px 32px rgba(17, 94, 89, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .invoice-units-page .iu-hero::before {
                            content: '';
                            position: absolute;
                            top: -50%;
                            right: -10%;
                            width: 400px;
                            height: 400px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.06);
                            pointer-events: none;
                        }

                        .invoice-units-page .iu-hero::after {
                            content: '';
                            position: absolute;
                            bottom: -60%;
                            right: 15%;
                            width: 300px;
                            height: 300px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.04);
                            pointer-events: none;
                        }

                        .invoice-units-page .iu-hero__content {
                            position: relative;
                            z-index: 1;
                        }

                        .invoice-units-page .iu-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .invoice-units-page .iu-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .invoice-units-page .iu-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.02em;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                        }

                        .invoice-units-page .iu-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                            max-width: 520px;
                        }

                        .invoice-units-page .iu-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .invoice-units-page .iu-hero-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 8px 16px;
                            border-radius: 10px;
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            background: rgba(255, 255, 255, 0.15);
                            color: #fff;
                            font-size: 0.82rem;
                            font-weight: 600;
                            text-decoration: none;
                            cursor: pointer;
                            transition: all 0.18s ease;
                        }

                        .invoice-units-page .iu-hero-btn:hover,
                        .invoice-units-page .iu-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .invoice-units-page .ruler-shake {
                            display: inline-block;
                            animation: ruler-shake 2.5s ease-in-out infinite;
                        }

                        @keyframes ruler-shake {
                            0%, 70%, 100% { transform: rotate(0deg); }
                            15% { transform: rotate(-10deg); }
                            30% { transform: rotate(8deg); }
                            45% { transform: rotate(-5deg); }
                            60% { transform: rotate(0deg); }
                        }

                        .invoice-units-page .theme-card {
                            border-top: 3px solid #115e59;
                        }

                        .invoice-units-page .theme-card-title {
                            color: #115e59;
                        }

                        @media (max-width: 767px) {
                            .invoice-units-page .iu-hero,
                            .invoice-units-page .iu-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .invoice-units-page .iu-hero {
                                padding: 20px;
                            }

                            .invoice-units-page .iu-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
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

                    <div class="iu-hero">
                        <div class="iu-hero__content">
                            <div class="iu-hero__eyebrow">
                                <i class="mdi mdi-ruler-square"></i>
                                Settings
                            </div>
                            <h1 class="iu-hero__title">Invoice Units <span class="ruler-shake">📐</span></h1>
                            <p class="iu-hero__subtitle">Manage the unit options shown in the invoice entry form, such as day, week, month, pcs, lot, unit, or meter.</p>
                        </div>
                        <div class="iu-hero__actions">
                            <a class="iu-hero-btn" href="<?= base_url(); ?>Page/admin">
                                <i class="mdi mdi-arrow-left"></i>
                                <span>Back to Dashboard</span>
                            </a>
                            <button type="button" class="iu-hero-btn iu-hero-btn--solid" data-toggle="modal" data-target="#unitModal" onclick="resetUnitForm()">
                                <i class="mdi mdi-plus"></i>
                                <span>Add Unit</span>
                            </button>
                        </div>
                    </div>

                    <div class="theme-card" style="border-top: 3px solid #115e59;">
                        <div class="theme-card-head">
                            <h4 class="theme-card-title" style="color: #115e59;">Available Units</h4>
                        </div>
                        <div class="theme-card-body">
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
                                                        <button type="button" class="btn btn-sm btn-info" onclick="editUnit(<?= (int) ($unit->unitID ?? 0); ?>, <?= htmlspecialchars(json_encode((string) ($unit->unitName ?? '')), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
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

                    <!-- Unit Modal -->
                    <div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="unitModalLabel">Add Invoice Unit</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form id="unitForm" method="post" action="<?= base_url(); ?>Settings/InvoiceUnits">
                                    <div class="modal-body">
                                        <input type="hidden" id="unitIDField" name="unitID" value="">
                                        <div class="form-group">
                                            <label for="unitNameField">Unit Name</label>
                                            <input
                                                type="text"
                                                id="unitNameField"
                                                name="unitName"
                                                class="form-control"
                                                value=""
                                                placeholder="e.g. day, pcs, lot"
                                                maxlength="50"
                                                required>
                                            <small class="form-text text-muted">Stored once and loaded dynamically into invoice entry rows.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary" id="unitSubmitBtn">Save Unit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                        function resetUnitForm() {
                            document.getElementById('unitForm').reset();
                            document.getElementById('unitIDField').value = '';
                            document.getElementById('unitModalLabel').textContent = 'Add Invoice Unit';
                            document.getElementById('unitSubmitBtn').textContent = 'Save Unit';
                        }

                        function editUnit(id, name) {
                            document.getElementById('unitIDField').value = id;
                            document.getElementById('unitNameField').value = name;
                            document.getElementById('unitModalLabel').textContent = 'Edit Invoice Unit';
                            document.getElementById('unitSubmitBtn').textContent = 'Update Unit';
                            $('#unitModal').modal('show');
                        }
                    </script>
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
