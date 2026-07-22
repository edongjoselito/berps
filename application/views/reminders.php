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

<body class="notes-modern-page">
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid reminders-workspace-page berps-page">
                    <div class="notes-workspace">
                        <header class="berps-page-header">
                            <div class="berps-page-header__content">
                                <span class="berps-page-header__eyebrow"><i class="mdi mdi-calendar mr-1"></i><?= htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                <h1 class="berps-page-title">My Reminders</h1>
                                <p class="berps-page-subtitle">Track recurring dates without checking the calendar manually.</p>
                            </div>
                            <div class="berps-page-header__actions">
                                <button type="button" class="btn btn-primary" id="addReminderBtn">
                                    <i class="mdi mdi-plus mr-1" aria-hidden="true"></i>Add Reminder
                                </button>
                            </div>
                        </header>

                        <div class="berps-stat-grid">
                            <div class="berps-stat-card berps-tone-success">
                                <div>
                                    <p class="berps-stat-card__value"><?= number_format($activeReminderCount); ?></p>
                                    <p class="berps-stat-card__label">Active Reminders</p>
                                    <p class="berps-stat-card__meta"><?= number_format($totalReminders); ?> total saved</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-bell-ring-outline"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-warning">
                                <div>
                                    <p class="berps-stat-card__value"><?= number_format($inactiveReminderCount); ?></p>
                                    <p class="berps-stat-card__label">Inactive</p>
                                    <p class="berps-stat-card__meta">Paused until you reactivate them</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-bell-off-outline"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-info">
                                <div>
                                    <p class="berps-stat-card__value" style="font-size: 1.1rem;"><?= htmlspecialchars($nextReminderLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="berps-stat-card__label">Next Reminder</p>
                                    <p class="berps-stat-card__meta">Earliest scheduled alert</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-calendar-clock"></i></span>
                            </div>
                        </div>

                        <section class="berps-table-card">
                            <div class="berps-table-card__header">
                                <div>
                                    <h2 class="berps-section-title">Reminder Queue</h2>
                                    <p class="berps-section-copy">Review upcoming dates, adjust frequency, or remove reminders you no longer need.</p>
                                </div>
                                <span class="berps-status berps-status--info"><?= number_format($totalReminders); ?> reminders</span>
                            </div>

                            <div class="berps-table-card__body">
                                <?php if (empty($reminders)): ?>
                                    <div class="berps-empty-state">
                                        <i class="mdi mdi-bell-off-outline berps-empty-state__icon" aria-hidden="true"></i>
                                        <span>No reminders yet. Create your first reminder to start tracking recurring dates.</span>
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
                                                    $statusClass = !empty($reminder->is_active) ? 'berps-status berps-status--success' : 'berps-status';
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
                                                            <div class="berps-row-actions">
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-secondary edit-reminder-btn"
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
                                                                    class="btn btn-sm btn-outline-danger delete-reminder-btn"
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
                        </section>
                    </div>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <div class="modal fade berps-form-modal" id="reminderModal" tabindex="-1" aria-hidden="true">
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
                        <div class="form-row mb-3">
                            <div class="form-group col-md-6">
                                <label for="reminderFrequency">Frequency *</label>
                                <select class="form-control" name="frequency" id="reminderFrequency" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="reminderStartDate">Start Date *</label>
                                <input type="date" class="form-control" name="start_date" id="reminderStartDate" required>
                            </div>
                        </div>
                        <div class="form-row mb-3">
                            <div class="form-group col-md-6">
                                <label for="reminderDaysBefore">Remind me (days before)</label>
                                <input type="number" class="form-control" name="reminder_days_before" id="reminderDaysBefore" value="3" min="1" max="30">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="reminderActive">Status</label>
                                <select class="form-control" name="is_active" id="reminderActive">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="reminder_id" id="reminderId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
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
