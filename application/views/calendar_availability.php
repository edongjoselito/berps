<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<style>
    body {
        background: #f6f7fb;
        font-size: 14px;
        color: #111827;
    }

    .card {
        border: 1px solid rgba(0, 0, 0, .06);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        background: #fff;
    }

    .day-row {
        display: flex;
        align-items: center;
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.2s;
    }

    .day-row:last-child {
        border-bottom: none;
    }

    .day-row:hover {
        background: #f9fafb;
    }

    .day-name {
        width: 120px;
        font-weight: 700;
        font-size: 14px;
        color: #111827;
    }

    .day-toggle {
        width: 80px;
    }

    .day-time {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .day-time.disabled {
        opacity: 0.4;
        pointer-events: none;
    }

    .form-help {
        color: #6b7280;
        font-size: 12px;
        margin-top: 4px;
    }
</style>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="mb-1">Availability</h4>
                                    <p class="text-muted mb-0">Set your working hours for each day of the week</p>
                                </div>
                                <button class="btn btn-info" id="saveAvailabilityBtn">
                                    <i class="mdi mdi-content-save-outline mr-1"></i> Save Availability
                                </button>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body p-0">
                                    <div id="availabilityList">
                                        <!-- Availability will be loaded here -->
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="mdi mdi-information mr-2"></i>
                                <strong>Note:</strong> Guests can only book meetings during your available hours. Set your availability for each day below.
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const $availabilityList = $('#availabilityList');
            const $saveBtn = $('#saveAvailabilityBtn');

            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            function loadAvailability() {
                $.get('<?= site_url("calendar/get_availability") ?>', function(response) {
                    if (response.success) {
                        renderAvailability(response.data);
                    }
                }, 'json');
            }

            function renderAvailability(availability) {
                let html = '';
                availability.forEach(function(avail) {
                    const isAvailable = avail.is_available == 1;
                    const disabledClass = isAvailable ? '' : 'disabled';
                    
                    html += `
                        <div class="day-row" data-day="${avail.day_of_week}">
                            <div class="day-name">${dayNames[avail.day_of_week]}</div>
                            <div class="day-toggle">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input day-available" 
                                           id="day_${avail.day_of_week}" 
                                           data-day="${avail.day_of_week}"
                                           ${isAvailable ? 'checked' : ''}>
                                    <label class="custom-control-label" for="day_${avail.day_of_week}">Available</label>
                                </div>
                            </div>
                            <div class="day-time ${disabledClass}">
                                <div>
                                    <label class="small text-muted mb-1">Start</label>
                                    <input type="time" class="form-control start-time" 
                                           data-day="${avail.day_of_week}"
                                           value="${avail.start_time ? avail.start_time.substring(0, 5) : '09:00'}">
                                </div>
                                <div class="text-muted">to</div>
                                <div>
                                    <label class="small text-muted mb-1">End</label>
                                    <input type="time" class="form-control end-time" 
                                           data-day="${avail.day_of_week}"
                                           value="${avail.end_time ? avail.end_time.substring(0, 5) : '17:00'}">
                                </div>
                            </div>
                        </div>
                    `;
                });

                $availabilityList.html(html);
            }

            function getAvailabilityData() {
                const data = [];
                $('.day-row').each(function() {
                    const day = $(this).data('day');
                    const isAvailable = $(this).find('.day-available').is(':checked') ? 1 : 0;
                    const startTime = $(this).find('.start-time').val() + ':00';
                    const endTime = $(this).find('.end-time').val() + ':00';

                    data.push({
                        day_of_week: day,
                        is_available: isAvailable,
                        start_time: startTime,
                        end_time: endTime
                    });
                });
                return data;
            }

            $(document).on('change', '.day-available', function() {
                const dayRow = $(this).closest('.day-row');
                const dayTime = dayRow.find('.day-time');
                
                if ($(this).is(':checked')) {
                    dayTime.removeClass('disabled');
                } else {
                    dayTime.addClass('disabled');
                }
            });

            $saveBtn.on('click', function() {
                const availabilityData = getAvailabilityData();
                
                $saveBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mr-1"></i> Saving...');
                
                $.post('<?= site_url("calendar/save_availability") ?>', { availability: availabilityData }, function(response) {
                    if (response.success) {
                        $saveBtn.prop('disabled', false).html('<i class="mdi mdi-content-save-outline mr-1"></i> Save Availability');
                        alert('Availability saved successfully');
                    } else {
                        $saveBtn.prop('disabled', false).html('<i class="mdi mdi-content-save-outline mr-1"></i> Save Availability');
                        alert(response.message || 'Error saving availability');
                    }
                }, 'json').fail(function() {
                    $saveBtn.prop('disabled', false).html('<i class="mdi mdi-content-save-outline mr-1"></i> Save Availability');
                    alert('Error saving availability');
                });
            });

            loadAvailability();
        });
    </script>
</body>
</html>
