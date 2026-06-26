<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<style>

    .deployment-page {
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
        padding-bottom: 200px;
        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
    }

    .deployment-page * {
        box-sizing: border-box;
    }

    .deployment-page .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 16px;
        margin: 16px 0 16px;
        flex-wrap: wrap;
    }

    .deployment-page .page-eyebrow {
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

    .deployment-page .page-eyebrow::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
    }

    .deployment-page .page-title {
        margin: 0;
        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
        font-size: 1.5rem;
        line-height: 1.2;
        letter-spacing: -0.02em;
        font-weight: 700;
        color: var(--text);
    }

    .deployment-page .page-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .deployment-page .btn-action,
    .deployment-page .btn-submit {
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

    .deployment-page .btn-action {
        border: 1px solid var(--line-strong);
        color: var(--text);
        background: #fff;
    }

    .deployment-page .btn-action:hover {
        color: var(--primary);
        border-color: #bfd3ef;
        background: #f9fbff;
        text-decoration: none;
    }

    .deployment-page .btn-submit {
        border: none;
        color: #fff;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
    }

    .deployment-page .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
    }

    .deployment-page .theme-card {
        background: var(--surface);
        border: 1px solid rgba(255, 255, 255, 0.72);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-soft);
        overflow: hidden;
    }

    .deployment-page .theme-card-head {
        padding: 14px 18px;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
    }

    .deployment-page .theme-card-title {
        margin: 0;
        color: var(--text);
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .deployment-page .theme-card-subtitle {
        margin-top: 4px;
        color: var(--text-soft);
        font-size: 0.8rem;
    }

    .deployment-page .theme-card-body {
        padding: 18px;
    }

    .deployment-page .project-info-box {
        background: var(--surface-soft);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 16px;
        margin-bottom: 16px;
    }

    .deployment-page .project-info-box h5 {
        color: var(--text);
        font-weight: 700;
        margin: 0;
    }

    .deployment-page .project-info-box .text-muted {
        color: var(--text-faint) !important;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .deployment-page .table thead th {
        background: transparent;
        color: var(--text-faint);
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-top: none;
        border-bottom: 1px solid var(--line);
        vertical-align: middle;
    }

    .deployment-page .table td {
        vertical-align: middle;
        border-color: var(--line);
        color: var(--text);
    }

    .deployment-page .row-done {
        background-color: var(--success-soft);
    }

    .deployment-page .row-pending {
        background-color: var(--warning-soft);
    }

    .deployment-page .row-na {
        background-color: rgba(108, 117, 125, 0.05);
        color: var(--text-soft);
    }

    .deployment-page .status-select {
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.85rem;
    }

    .deployment-page .file-upload-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    .deployment-page .file-upload-wrapper input[type=file] {
        font-size: 100px;
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        cursor: pointer;
    }

    .deployment-page .readonly-row {
        opacity: .9;
        background: var(--surface-soft);
    }

    .deployment-page .readonly-note {
        display: inline-block;
        margin-top: 4px;
        font-size: 0.75rem;
        color: var(--text-faint);
    }

    .deployment-page .form-control:focus {
        border-color: #9cc0f5;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .deployment-page .status-breakdown {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .deployment-page .status-card {
        background: var(--surface);
        border: 1px solid rgba(255, 255, 255, 0.72);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 16px;
        text-align: center;
    }

    .deployment-page .status-card .status-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-faint);
        margin-bottom: 8px;
    }

    .deployment-page .status-card .status-percentage {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .deployment-page .status-card .status-count {
        font-size: 0.85rem;
        color: var(--text-soft);
    }

    .deployment-page .status-card.sc-pending .status-percentage {
        color: var(--warning);
    }

    .deployment-page .status-card.sc-done .status-percentage {
        color: var(--success);
    }

    .deployment-page .status-card.sc-partial .status-percentage {
        color: var(--primary);
    }

    .deployment-page .status-card.sc-na .status-percentage {
        color: var(--text-soft);
    }

    .deployment-page .status-card.sc-pending {
        border-top: 3px solid var(--warning);
    }

    .deployment-page .status-card.sc-done {
        border-top: 3px solid var(--success);
    }

    .deployment-page .status-card.sc-partial {
        border-top: 3px solid var(--primary);
    }

    .deployment-page .status-card.sc-na {
        border-top: 3px solid var(--text-soft);
    }

    @media (max-width: 991px) {
        .deployment-page .status-breakdown {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .deployment-page .status-breakdown {
            grid-template-columns: 1fr;
        }
    }
</style>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page deployment-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Project Management</div>
                            <h4 class="page-title">Deployment Management</h4>
                        </div>
                        <div class="page-actions">
                            <a href="<?= base_url('Page/projectList'); ?>" class="btn-action">
                                <i class="mdi mdi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="status-breakdown">
                        <div class="status-card sc-pending">
                            <div class="status-label">Pending</div>
                            <div class="status-percentage"><?= number_format($statusBreakdown['Pending']['percentage'], 1); ?>%</div>
                            <div class="status-count"><?= $statusBreakdown['Pending']['count']; ?> / <?= $totalItems; ?></div>
                        </div>
                        <div class="status-card sc-done">
                            <div class="status-label">Done</div>
                            <div class="status-percentage"><?= number_format($statusBreakdown['Done']['percentage'], 1); ?>%</div>
                            <div class="status-count"><?= $statusBreakdown['Done']['count']; ?> / <?= $totalItems; ?></div>
                        </div>
                        <div class="status-card sc-partial">
                            <div class="status-label">Partially Done</div>
                            <div class="status-percentage"><?= number_format($statusBreakdown['Partially Done']['percentage'], 1); ?>%</div>
                            <div class="status-count"><?= $statusBreakdown['Partially Done']['count']; ?> / <?= $totalItems; ?></div>
                        </div>
                        <div class="status-card sc-na">
                            <div class="status-label">Not Applicable</div>
                            <div class="status-percentage"><?= number_format($statusBreakdown['Not Applicable']['percentage'], 1); ?>%</div>
                            <div class="status-count"><?= $statusBreakdown['Not Applicable']['count']; ?> / <?= $totalItems; ?></div>
                        </div>
                    </div>

                    <div class="theme-card">
                        <form method="post" action="<?= base_url('Page/saveProjectDeploymentStatus'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="projectID" value="<?= (int)$project->projectID; ?>">

                            <div class="theme-card-head">
                                <div class="project-info-box">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1 small text-uppercase font-weight-bold">Project Description</p>
                                        <h5 class="mt-0"><?= htmlspecialchars($project->projectDescription, ENT_QUOTES, 'UTF-8'); ?></h5>
                                        <span class="badge badge-soft-primary">Settings ID: <?= htmlspecialchars($items[0]->settingsID ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1 small text-uppercase font-weight-bold">Client Details</p>
                                        <p class="mb-0"><strong><?= htmlspecialchars($project->Customer, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($project->Address, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="theme-card-body">
                                <?php if ($this->session->flashdata('success')): ?>
                                    <div class="alert alert-success alert-dismissible fade show"><?= $this->session->flashdata('success'); ?></div>
                                <?php endif; ?>

                                <?php if ($this->session->flashdata('danger')): ?>
                                    <div class="alert alert-danger alert-dismissible fade show"><?= $this->session->flashdata('danger'); ?></div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-centered table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 20%;">Deployment Step</th>
                                                <th style="width: 16%;">Assigned Staff</th>
                                                <th style="width: 14%;">Status</th>
                                                <th style="width: 25%;">Remarks / Details</th>
                                                <th style="width: 25%;">Attachments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($items)): ?>
                                                <?php foreach ($items as $item):
                                                    $rowClass = '';
                                                    if ($item->status_value == 'Done') $rowClass = 'row-done';
                                                    if ($item->status_value == 'Pending') $rowClass = 'row-pending';
                                                    if ($item->status_value == 'Not Applicable') $rowClass = 'row-na';

                                                    $itemName = trim((string)$item->item_name);
                                                    $isMOA = $itemName === 'Memorandum of Agreement (MOA)';
                                                    $isBackupConnection = $itemName === 'Database Connection for Backup';

                                                    $canEditRow = $isAdmin || ((int)$item->user_id === (int)$currentUserId);
                                                ?>
                                                    <tr class="<?= $rowClass; ?> <?= !$canEditRow ? 'readonly-row' : ''; ?>">
                                                        <td class="font-weight-medium">
                                                            <?= htmlspecialchars($item->item_name, ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php if (!$canEditRow && $isStaff): ?>
                                                                <div class="readonly-note">Read-only: assigned to another staff</div>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <select name="user_id[<?= (int)$item->id; ?>]" class="form-control form-control-sm" <?= !$isAdmin ? 'disabled' : ''; ?>>
                                                                <option value="">— Select Staff —</option>
                                                                <?php if (!empty($users)): ?>
                                                                    <?php foreach ($users as $user): ?>
                                                                        <?php
                                                                        $fullName = trim(
                                                                            (string)$user->lName . ', ' .
                                                                                (string)$user->fName .
                                                                                ((string)$user->mName !== '' ? ' ' . (string)$user->mName : '')
                                                                        );
                                                                        $label = $fullName;

                                                                        if (!empty($user->position)) {
                                                                            $label .= ' (' . $user->position . ')';
                                                                        }
                                                                        ?>
                                                                        <option value="<?= (int)$user->user_id; ?>" <?= ((string)$item->user_id === (string)$user->user_id) ? 'selected' : ''; ?>>
                                                                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                            <?php if (!$isAdmin): ?>
                                                                <input type="hidden" name="user_id[<?= (int)$item->id; ?>]" value="<?= (int)$item->user_id; ?>">
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <select name="status[<?= (int)$item->id; ?>]" class="form-control form-control-sm status-select" <?= !$canEditRow ? 'disabled' : ''; ?>>
                                                                <option value="Pending" <?= $item->status_value === 'Pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                                                <option value="Done" <?= $item->status_value === 'Done' ? 'selected' : ''; ?>>✅ Done</option>
                                                                <option value="Partially Done" <?= $item->status_value === 'Partially Done' ? 'selected' : ''; ?>>🚧 Partial</option>
                                                                <option value="Not Applicable" <?= $item->status_value === 'Not Applicable' ? 'selected' : ''; ?>>🚫 N/A</option>
                                                            </select>
                                                            <?php if (!$canEditRow): ?>
                                                                <input type="hidden" name="status[<?= (int)$item->id; ?>]" value="<?= htmlspecialchars($item->status_value, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <?php if ($isBackupConnection): ?>
                                                                <textarea name="remarks[<?= (int)$item->id; ?>]" class="form-control form-control-sm" rows="3" placeholder="Connection string..." <?= !$canEditRow ? 'readonly' : ''; ?>><?= htmlspecialchars((string)$item->remarks, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                            <?php else: ?>
                                                                <input type="text" name="remarks[<?= (int)$item->id; ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$item->remarks, ENT_QUOTES, 'UTF-8'); ?>" <?= !$canEditRow ? 'readonly' : ''; ?>>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <?php if ($isMOA): ?>
                                                                <?php if ($canEditRow): ?>
                                                                    <div class="d-flex align-items-center flex-wrap">
                                                                        <div class="file-upload-wrapper">
                                                                            <button type="button" class="btn btn-sm btn-outline-primary">
                                                                                <i class="mdi mdi-upload"></i> Upload PDF
                                                                            </button>
                                                                            <input type="file" name="moa_file[<?= (int)$item->id; ?>]" accept="application/pdf">
                                                                        </div>

                                                                        <?php if (!empty($item->attachment_file)): ?>
                                                                            <div class="ml-2 d-flex align-items-center mt-1 mt-md-0">
                                                                                <a href="<?= base_url($item->attachment_file); ?>" target="_blank" class="btn btn-sm btn-info mr-2" title="View File">
                                                                                    <i class="mdi mdi-eye"></i>
                                                                                </a>
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox" class="custom-control-input" id="rem_<?= (int)$item->id; ?>" name="remove_attachment[<?= (int)$item->id; ?>]" value="1">
                                                                                    <label class="custom-control-label text-danger small" for="rem_<?= (int)$item->id; ?>">Remove</label>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <?php if (!empty($item->attachment_file)): ?>
                                                                        <a href="<?= base_url($item->attachment_file); ?>" target="_blank" class="btn btn-sm btn-info" title="View File">
                                                                            <i class="mdi mdi-eye"></i> View PDF
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted small">No attachment</span>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">No deployment items initialized.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="form-group bg-light p-3 rounded">
                                            <label for="notes" class="font-weight-bold"><i class="mdi mdi-comment-text-outline mr-1"></i> Final Deployment Notes</label>
                                            <textarea name="notes" id="notes" class="form-control" rows="3" <?= !$isAdmin ? 'readonly' : ''; ?>><?= htmlspecialchars($items[0]->notes ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right" style="padding-top: 16px;">
                                    <button type="submit" class="btn-submit">
                                        <i class="mdi mdi-check-all"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/footer.php'); ?>
    </div>
    </div>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.status-select').on('change', function() {
                var val = $(this).val();
                var row = $(this).closest('tr');
                row.removeClass('row-done row-pending row-na');

                if (val === 'Done') row.addClass('row-done');
                if (val === 'Pending') row.addClass('row-pending');
                if (val === 'Not Applicable') row.addClass('row-na');
            });
        });
    </script>
</body>

</html>