<?php
$currentDateLabel = date('l, F j, Y');
$reminders = isset($reminders) && is_array($reminders) ? $reminders : array();
$totalReminders = count($reminders);
$activeReminderCount = 0;
$inactiveReminderCount = 0;
$nextReminderLabel = 'No reminder scheduled yet';

foreach ($reminders as $summaryReminder) {
    if (!empty($summaryReminder->is_active)) {
        $activeReminderCount++;
    } else {
        $inactiveReminderCount++;
    }
}

if (!empty($reminders) && !empty($reminders[0]->next_reminder_date)) {
    $nextReminderTimestamp = strtotime((string) $reminders[0]->next_reminder_date);
    if ($nextReminderTimestamp) {
        $nextReminderLabel = date('M j, Y', $nextReminderTimestamp);
    }
}

if (!function_exists('page_reminders_format_date')) {
    function page_reminders_format_date($dateValue)
    {
        $timestamp = strtotime((string) $dateValue);
        return $timestamp ? date('M j, Y', $timestamp) : 'Not set';
    }
}

if (!function_exists('page_reminders_preview')) {
    function page_reminders_preview($text, $limit = 96)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text, 'UTF-8') > $limit
                ? mb_substr($text, 0, $limit, 'UTF-8') . '...'
                : $text;
        }

        return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .notes-modern-page,
    .notes-modern-page .content-page {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .notes-modern-page {
        --notes-surface: rgba(255, 255, 255, 0.88);
        --notes-surface-strong: #ffffff;
        --notes-border: rgba(148, 163, 184, 0.2);
        --notes-border-strong: rgba(148, 163, 184, 0.34);
        --notes-text: #102033;
        --notes-text-soft: #5b6b7f;
        --notes-text-muted: #91a0b3;
        --notes-primary: #1d4ed8;
        --notes-primary-deep: #123b9f;
        --notes-accent: #0f766e;
        --notes-warning: #d97706;
        --notes-danger: #dc2626;
        --notes-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        --notes-shadow-soft: 0 14px 34px rgba(15, 23, 42, 0.06);
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 28%),
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 24%),
            linear-gradient(180deg, #f8fbff 0%, #f1f6fb 100%);
        min-height: 100vh;
    }

    .notes-modern-page .content {
        background: transparent;
    }

    .notes-modern-page .notes-workspace {
        padding: 8px 0 34px;
    }

    .notes-modern-page .notes-hero {
        position: relative;
        overflow: hidden;
        padding: 28px;
        border-radius: 28px;
        background:
            linear-gradient(135deg, rgba(13, 26, 51, 0.96) 0%, rgba(16, 86, 169, 0.92) 58%, rgba(15, 118, 110, 0.88) 100%);
        color: #ffffff;
        box-shadow: var(--notes-shadow);
        margin-bottom: 20px;
    }

    .notes-modern-page .notes-hero::before,
    .notes-modern-page .notes-hero::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
    }

    .notes-modern-page .notes-hero::before {
        width: 240px;
        height: 240px;
        right: -90px;
        top: -118px;
        background: rgba(255, 255, 255, 0.1);
    }

    .notes-modern-page .notes-hero::after {
        width: 180px;
        height: 180px;
        left: 52%;
        bottom: -108px;
        background: rgba(255, 255, 255, 0.08);
    }

    .notes-modern-page .notes-hero__top,
    .notes-modern-page .notes-hero__bottom {
        position: relative;
        z-index: 1;
    }

    .notes-modern-page .notes-hero__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .notes-modern-page .notes-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.78);
    }

    .notes-modern-page .notes-eyebrow::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f8fafc;
        box-shadow: 0 0 0 6px rgba(248, 250, 252, 0.12);
    }

    .notes-modern-page .notes-hero h1 {
        margin: 0;
        color: #ffffff !important;
        font-size: 2.3rem;
        line-height: 1.05;
        font-weight: 800;
        letter-spacing: -0.04em;
        text-shadow: 0 2px 12px rgba(15, 23, 42, 0.28);
    }

    .notes-modern-page .notes-hero p {
        max-width: 650px;
        margin: 12px 0 0;
        color: rgba(255, 255, 255, 0.9) !important;
        text-shadow: 0 1px 8px rgba(15, 23, 42, 0.22);
        font-size: 0.96rem;
        line-height: 1.7;
    }

    .notes-modern-page .notes-hero__actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .notes-modern-page .notes-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 16px;
        border-radius: 14px;
        border: 1px solid transparent;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        cursor: pointer;
    }

    .notes-modern-page .notes-btn:hover,
    .notes-modern-page .notes-btn:focus {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .notes-modern-page .notes-btn--primary {
        color: #0f172a;
        background: #ffffff;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.16);
    }

    .notes-modern-page .notes-btn--primary:hover,
    .notes-modern-page .notes-btn--primary:focus {
        color: #0f172a;
    }

    .notes-modern-page .notes-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .notes-modern-page .notes-metric {
        padding: 18px 18px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.14);
        backdrop-filter: blur(10px);
    }

    .notes-modern-page .notes-metric__label {
        margin-bottom: 8px;
        color: rgba(255, 255, 255, 0.74);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .notes-modern-page .notes-metric__value {
        color: #ffffff;
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .notes-modern-page .notes-metric__meta {
        margin-top: 6px;
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.82rem;
    }

    .notes-modern-page .notes-card {
        background: var(--notes-surface-strong);
        border: 1px solid var(--notes-border);
        border-radius: 22px;
        box-shadow: var(--notes-shadow-soft);
        overflow: hidden;
    }

    .notes-modern-page .notes-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--notes-border);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.88) 0%, rgba(255, 255, 255, 0.96) 100%);
    }

    .notes-modern-page .notes-card__title {
        margin: 0;
        color: var(--notes-text);
        font-size: 1.16rem;
        font-weight: 800;
    }

    .notes-modern-page .notes-card__subtitle {
        margin-top: 4px;
        color: var(--notes-text-soft);
        font-size: 0.9rem;
    }

    .notes-modern-page .notes-card__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        padding: 0 12px;
        height: 42px;
        border-radius: 999px;
        background: rgba(29, 78, 216, 0.1);
        color: var(--notes-primary);
        font-size: 0.96rem;
        font-weight: 800;
    }

    .notes-modern-page .notes-card__body {
        padding: 24px;
    }

    .notes-modern-page .notes-empty {
        padding: 56px 20px;
        text-align: center;
        color: var(--notes-text-muted);
    }

    .notes-modern-page .notes-empty__icon {
        width: 88px;
        height: 88px;
        margin: 0 auto 16px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(29, 78, 216, 0.12) 0%, rgba(15, 118, 110, 0.1) 100%);
        color: var(--notes-primary);
        font-size: 2.3rem;
    }

    .notes-modern-page .notes-empty h3 {
        margin: 0 0 8px;
        color: var(--notes-text);
        font-size: 1.2rem;
        font-weight: 800;
    }

    .notes-modern-page .notes-empty p {
        max-width: 430px;
        margin: 0 auto;
        line-height: 1.7;
    }

    .notes-modern-page .table-responsive {
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, 0.9);
    }

    .notes-modern-page .table {
        margin-bottom: 0;
        color: var(--notes-text);
    }

    .notes-modern-page .table thead th {
        border-top: 0;
        border-bottom: 1px solid var(--notes-border-strong);
        padding: 1rem 1rem 0.95rem;
        background: #f8fbff;
        color: var(--notes-text);
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .notes-modern-page .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-top: 1px solid var(--notes-border);
        color: var(--notes-text-soft);
    }

    .notes-modern-page .table tbody tr:hover {
        background: rgba(37, 99, 235, 0.03);
    }

    .notes-modern-page .notes-title-cell {
        min-width: 200px;
    }

    .notes-modern-page .notes-title {
        margin: 0 0 4px;
        color: var(--notes-text);
        font-size: 0.97rem;
        font-weight: 800;
    }

    .notes-modern-page .notes-subtle {
        color: var(--notes-text-muted);
        font-size: 0.82rem;
    }

    .notes-modern-page .notes-description {
        max-width: 280px;
        line-height: 1.55;
    }

    .notes-modern-page .notes-frequency {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.7rem;
        border-radius: 999px;
        background: rgba(29, 78, 216, 0.1);
        color: var(--notes-primary);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .notes-modern-page .notes-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.38rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .notes-modern-page .notes-status::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.85;
    }

    .notes-modern-page .notes-status--active {
        background: rgba(22, 163, 74, 0.12);
        color: #15803d;
    }

    .notes-modern-page .notes-status--inactive {
        background: rgba(148, 163, 184, 0.16);
        color: #64748b;
    }

    .notes-modern-page .notes-actions {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .notes-modern-page .notes-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 38px;
        padding: 0 12px;
        border-radius: 12px;
        border: 1px solid transparent;
        font-size: 0.8rem;
        font-weight: 700;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease;
    }

    .notes-modern-page .notes-action-btn:hover,
    .notes-modern-page .notes-action-btn:focus {
        transform: translateY(-1px);
    }

    .notes-modern-page .notes-action-btn--edit {
        background: rgba(29, 78, 216, 0.1);
        color: var(--notes-primary);
    }

    .notes-modern-page .notes-action-btn--delete {
        background: rgba(220, 38, 38, 0.1);
        color: var(--notes-danger);
    }

    .notes-modern-page .notes-modal .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.18);
    }

    .notes-modern-page .notes-modal .modal-header {
        border-bottom: 0;
        padding: 18px 22px;
        background: linear-gradient(135deg, var(--notes-primary) 0%, var(--notes-accent) 100%);
    }

    .notes-modern-page .notes-modal .modal-title {
        color: #ffffff;
        font-weight: 800;
    }

    .notes-modern-page .notes-modal .close {
        color: #ffffff;
        text-shadow: none;
        opacity: 0.9;
    }

    .notes-modern-page .notes-modal .modal-body {
        padding: 22px;
    }

    .notes-modern-page .notes-modal label {
        color: var(--notes-text);
        font-weight: 700;
        font-size: 0.88rem;
    }

    .notes-modern-page .notes-modal .form-control {
        min-height: 46px;
        border-radius: 14px;
        border-color: rgba(148, 163, 184, 0.34);
        box-shadow: none;
    }

    .notes-modern-page .notes-modal textarea.form-control {
        min-height: 108px;
    }

    .notes-modern-page .notes-modal .modal-footer {
        padding: 0 22px 22px;
        border-top: 0;
    }

    .notes-modern-page .notes-modal .btn {
        min-height: 44px;
        border-radius: 12px;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .notes-modern-page .notes-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .notes-modern-page .notes-workspace {
            padding-top: 0;
        }

        .notes-modern-page .notes-hero {
            padding: 22px;
            border-radius: 24px;
        }

        .notes-modern-page .notes-hero h1 {
            font-size: 1.9rem;
        }

        .notes-modern-page .notes-metrics {
            grid-template-columns: 1fr;
        }

        .notes-modern-page .notes-card__header {
            align-items: flex-start;
            flex-direction: column;
        }

        .notes-modern-page .table-responsive {
            border: 0;
            box-shadow: none;
        }

        .notes-modern-page .table thead {
            display: none;
        }

        .notes-modern-page .table,
        .notes-modern-page .table tbody,
        .notes-modern-page .table tr,
        .notes-modern-page .table td {
            display: block;
            width: 100%;
        }

        .notes-modern-page .table tbody tr {
            margin-bottom: 14px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .notes-modern-page .table tbody td {
            border-top: 1px solid rgba(226, 232, 240, 0.72);
            padding: 14px 16px;
        }

        .notes-modern-page .table tbody td:first-child {
            border-top: 0;
        }

        .notes-modern-page .table tbody td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 5px;
            color: var(--notes-text-muted);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .notes-modern-page .notes-description {
            max-width: none;
        }
    }
</style>

<body class="notes-modern-page">
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="notes-workspace">
                        <div class="notes-hero">
                            <div class="notes-hero__top">
                                <div>
                                    <div class="notes-eyebrow">
                                        <i class="mdi mdi-calendar"></i>
                                        <?= htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <h1>My Reminders</h1>
                                </div>
                                <div class="notes-hero__actions">
                                    <button type="button" class="notes-btn notes-btn--primary" id="addReminderBtn">
                                        <i class="mdi mdi-plus"></i>
                                        Add Reminder
                                    </button>
                                </div>
                            </div>

                            <div class="notes-hero__bottom">
                                <div class="notes-metrics">
                                    <div class="notes-metric">
                                        <div class="notes-metric__label">Active Reminders</div>
                                        <div class="notes-metric__value"><?= number_format($activeReminderCount); ?></div>
                                        <div class="notes-metric__meta"><?= number_format($totalReminders); ?> total saved</div>
                                    </div>
                                    <div class="notes-metric">
                                        <div class="notes-metric__label">Inactive</div>
                                        <div class="notes-metric__value"><?= number_format($inactiveReminderCount); ?></div>
                                        <div class="notes-metric__meta">Paused until you reactivate them</div>
                                    </div>
                                    <div class="notes-metric">
                                        <div class="notes-metric__label">Next Reminder</div>
                                        <div class="notes-metric__value" style="font-size: 1.25rem;"><?= htmlspecialchars($nextReminderLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="notes-metric__meta">Earliest scheduled alert</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="notes-card">
                            <div class="notes-card__header">
                                <div>
                                    <h3 class="notes-card__title">Reminder Queue</h3>
                                    <div class="notes-card__subtitle">Review upcoming dates, adjust frequency, or remove reminders you no longer need.</div>
                                </div>
                                <div class="notes-card__count"><?= number_format($totalReminders); ?></div>
                            </div>

                            <div class="notes-card__body">
                                <?php if (empty($reminders)): ?>
                                    <div class="notes-empty">
                                        <div class="notes-empty__icon">
                                            <i class="mdi mdi-bell-off-outline"></i>
                                        </div>
                                        <h3>No reminders yet</h3>
                                        <p>Create your first reminder to start tracking recurring dates without checking the calendar manually.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Reminder</th>
                                                    <th>Description</th>
                                                    <th>Frequency</th>
                                                    <th>Start Date</th>
                                                    <th>Next Reminder</th>
                                                    <th>Lead Time</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reminders as $reminder): ?>
                                                    <?php
                                                    $descriptionPreview = page_reminders_preview($reminder->description ?? '');
                                                    $daysBefore = (int) ($reminder->reminder_days_before ?? 0);
                                                    $statusClass = !empty($reminder->is_active) ? 'notes-status notes-status--active' : 'notes-status notes-status--inactive';
                                                    $statusLabel = !empty($reminder->is_active) ? 'Active' : 'Inactive';
                                                    ?>
                                                    <tr>
                                                        <td data-label="Reminder" class="notes-title-cell">
                                                            <div class="notes-title"><?= htmlspecialchars((string) ($reminder->title ?? 'Untitled Reminder'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                            <div class="notes-subtle">ID #<?= (int) ($reminder->reminder_id ?? 0); ?></div>
                                                        </td>
                                                        <td data-label="Description" class="notes-description">
                                                            <?php if ($descriptionPreview !== ''): ?>
                                                                <?= htmlspecialchars($descriptionPreview, ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php else: ?>
                                                                <span class="notes-subtle">No description added.</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td data-label="Frequency">
                                                            <span class="notes-frequency"><?= htmlspecialchars(ucfirst((string) ($reminder->frequency ?? '')), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </td>
                                                        <td data-label="Start Date"><?= htmlspecialchars(page_reminders_format_date($reminder->start_date ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td data-label="Next Reminder"><?= htmlspecialchars(page_reminders_format_date($reminder->next_reminder_date ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td data-label="Lead Time">
                                                            <?= $daysBefore; ?> day<?= $daysBefore === 1 ? '' : 's'; ?> before
                                                        </td>
                                                        <td data-label="Status">
                                                            <span class="<?= $statusClass; ?>"><?= $statusLabel; ?></span>
                                                        </td>
                                                        <td data-label="Actions">
                                                            <div class="notes-actions">
                                                                <button
                                                                    type="button"
                                                                    class="btn notes-action-btn notes-action-btn--edit edit-reminder-btn"
                                                                    data-reminder-id="<?= (int) ($reminder->reminder_id ?? 0); ?>"
                                                                    data-title="<?= htmlspecialchars((string) ($reminder->title ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-description="<?= htmlspecialchars((string) ($reminder->description ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-frequency="<?= htmlspecialchars((string) ($reminder->frequency ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-start-date="<?= htmlspecialchars((string) ($reminder->start_date ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-days-before="<?= (int) ($reminder->reminder_days_before ?? 0); ?>"
                                                                    data-is-active="<?= !empty($reminder->is_active) ? '1' : '0'; ?>">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                    Edit
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="btn notes-action-btn notes-action-btn--delete delete-reminder-btn"
                                                                    data-reminder-id="<?= (int) ($reminder->reminder_id ?? 0); ?>">
                                                                    <i class="mdi mdi-delete"></i>
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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

    <?php include('includes/themecustomizer.php'); ?>

    <div class="modal fade notes-modal" id="reminderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Reminder</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="reminderForm">
                        <div class="form-group mb-3">
                            <label for="reminderTitle">Title *</label>
                            <input type="text" class="form-control" name="title" id="reminderTitle" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="reminderDescription">Description</label>
                            <textarea class="form-control" name="description" id="reminderDescription" rows="3"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="reminderFrequency">Frequency *</label>
                            <select class="form-control" name="frequency" id="reminderFrequency" required>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="reminderStartDate">Start Date *</label>
                            <input type="date" class="form-control" name="start_date" id="reminderStartDate" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="reminderDaysBefore">Remind me (days before)</label>
                            <input type="number" class="form-control" name="reminder_days_before" id="reminderDaysBefore" value="3" min="1" max="30">
                        </div>
                        <div class="form-group mb-0">
                            <label for="reminderActive">Status</label>
                            <select class="form-control" name="is_active" id="reminderActive">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <input type="hidden" name="reminder_id" id="reminderId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveReminderBtn">Save Reminder</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script>
        $(document).ready(function() {
            const $reminderModal = $('#reminderModal');
            const $reminderForm = $('#reminderForm');

            function getTodayString() {
                return new Date().toISOString().split('T')[0];
            }

            $('#addReminderBtn').on('click', function() {
                $reminderForm[0].reset();
                $('#reminderId').val('');
                $('#reminderDaysBefore').val(3);
                $('#reminderActive').val('1');
                $('#reminderStartDate').val(getTodayString());
                $('#reminderModal .modal-title').text('Add Reminder');
                $reminderModal.modal('show');
            });

            $(document).on('click', '.edit-reminder-btn', function() {
                $('#reminderId').val($(this).data('reminder-id'));
                $('#reminderTitle').val($(this).data('title'));
                $('#reminderDescription').val($(this).data('description'));
                $('#reminderFrequency').val($(this).data('frequency'));
                $('#reminderStartDate').val($(this).data('start-date'));
                $('#reminderDaysBefore').val($(this).data('days-before'));
                $('#reminderActive').val($(this).data('is-active'));
                $('#reminderModal .modal-title').text('Edit Reminder');
                $reminderModal.modal('show');
            });

            $(document).on('click', '.delete-reminder-btn', function() {
                const reminderId = $(this).data('reminder-id');

                Swal.fire({
                    title: 'Delete this reminder?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('<?= site_url('Page/deleteReminder'); ?>', {
                            reminder_id: reminderId
                        }, function(response) {
                            if (response.success) {
                                Swal.fire('Deleted', 'Reminder removed successfully.', 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message || 'Unable to delete the reminder.', 'error');
                            }
                        }, 'json').fail(function() {
                            Swal.fire('Error', 'Unable to delete the reminder right now.', 'error');
                        });
                    }
                });
            });

            $('#saveReminderBtn').on('click', function() {
                let formData = $reminderForm.serialize();
                const startDate = $('#reminderStartDate').val();
                const frequency = $('#reminderFrequency').val();

                if (!startDate) {
                    Swal.fire('Error', 'Please select a start date.', 'error');
                    return;
                }

                formData += '&next_reminder_date=' + encodeURIComponent(calculateNextReminderDate(startDate, frequency));

                $.post('<?= site_url('Page/saveReminder'); ?>', formData, function(response) {
                    if (response.success) {
                        $reminderModal.modal('hide');
                        Swal.fire('Saved', 'Reminder saved successfully.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Unable to save the reminder.', 'error');
                    }
                }, 'json').fail(function(xhr, status, error) {
                    Swal.fire('Error', 'Unable to save the reminder: ' + error, 'error');
                });
            });

            function calculateNextReminderDate(startDate, frequency) {
                const start = new Date(startDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                let nextDate = new Date(start);

                while (nextDate <= today) {
                    switch (frequency) {
                        case 'daily':
                            nextDate.setDate(nextDate.getDate() + 1);
                            break;
                        case 'weekly':
                            nextDate.setDate(nextDate.getDate() + 7);
                            break;
                        case 'monthly':
                            nextDate.setMonth(nextDate.getMonth() + 1);
                            break;
                        case 'yearly':
                            nextDate.setFullYear(nextDate.getFullYear() + 1);
                            break;
                        default:
                            return startDate;
                    }
                }

                return nextDate.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
