<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f6f7fb;
            font-size: 14px;
            color: #111827;
        }

        .booking-container {
            max-width: 900px;
            margin: 40px auto;
        }

        .card {
            border: 1px solid rgba(0, 0, 0, .06);
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            background: #fff;
        }

        .event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 14px 14px 0 0;
        }

        .event-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .event-duration {
            font-size: 14px;
            opacity: 0.9;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }

        .step {
            display: flex;
            align-items: center;
            padding: 0 20px;
            opacity: 0.4;
        }

        .step.active {
            opacity: 1;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 8px;
        }

        .step.active .step-number {
            background: #667eea;
            color: white;
        }

        .step-text {
            font-weight: 600;
            font-size: 12px;
        }

        .date-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin: 20px 0;
        }

        .date-cell {
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .date-cell:hover {
            border-color: #667eea;
            background: #f5f3ff;
        }

        .date-cell.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .date-cell.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .date-day {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .date-number {
            font-size: 18px;
            font-weight: 700;
        }

        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }

        .time-slot {
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
        }

        .time-slot:hover {
            border-color: #667eea;
            background: #f5f3ff;
        }

        .time-slot.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .time-slot.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .form-group label {
            font-weight: 600;
            font-size: 13px;
            color: #374151;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }

        .success-message {
            text-align: center;
            padding: 40px;
        }

        .success-icon {
            font-size: 64px;
            color: #10b981;
            margin-bottom: 20px;
        }
    </style>
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
</head>
<body>
    <div class="booking-container">
        <div class="card">
            <div class="event-header">
                <div class="event-title"><?= htmlspecialchars($event_type->name, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="event-duration">
                    <?= $event_type->duration; ?> <?= $event_type->duration_unit === 'hour' ? 'hour' : 'minute'; ?> meeting
                    <?php if ($event_type->location_type === 'online'): ?>
                        • Online
                    <?php elseif ($event_type->location_type === 'phone'): ?>
                        • Phone Call
                    <?php endif; ?>
                </div>
                <?php if ($event_type->description): ?>
                    <div style="margin-top: 12px; opacity: 0.9; font-size: 14px;">
                        <?= htmlspecialchars($event_type->description, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-body p-4">
                <div class="step-indicator">
                    <div class="step active" id="step1-indicator">
                        <div class="step-number">1</div>
                        <div class="step-text">Select Date</div>
                    </div>
                    <div class="step" id="step2-indicator">
                        <div class="step-number">2</div>
                        <div class="step-text">Select Time</div>
                    </div>
                    <div class="step" id="step3-indicator">
                        <div class="step-number">3</div>
                        <div class="step-text">Your Details</div>
                    </div>
                </div>

                <!-- Step 1: Select Date -->
                <div id="step1">
                    <h5 class="mb-3">Select a Date</h5>
                    <div class="date-grid" id="dateGrid">
                        <!-- Dates will be loaded here -->
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" id="step1Next" disabled>Next</button>
                    </div>
                </div>

                <!-- Step 2: Select Time -->
                <div id="step2" style="display: none;">
                    <h5 class="mb-3">Select a Time</h5>
                    <div class="text-muted mb-3" id="selectedDateDisplay"></div>
                    <div class="time-slots" id="timeSlots">
                        <!-- Time slots will be loaded here -->
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-secondary mr-2" id="step2Back">Back</button>
                        <button class="btn btn-primary" id="step2Next" disabled>Next</button>
                    </div>
                </div>

                <!-- Step 3: Your Details -->
                <div id="step3" style="display: none;">
                    <h5 class="mb-3">Your Details</h5>
                    <div class="text-muted mb-3" id="selectedDateTimeDisplay"></div>
                    <form id="bookingForm">
                        <div class="form-group mb-3">
                            <label for="inviteeName">Name *</label>
                            <input type="text" class="form-control" id="inviteeName" name="invitee_name" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="inviteeEmail">Email *</label>
                            <input type="email" class="form-control" id="inviteeEmail" name="invitee_email" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="inviteePhone">Phone</label>
                            <input type="tel" class="form-control" id="inviteePhone" name="invitee_phone">
                        </div>
                        <div class="form-group mb-3">
                            <label for="inviteeNotes">Additional Notes</label>
                            <textarea class="form-control" id="inviteeNotes" name="invitee_notes" rows="3"></textarea>
                        </div>
                        <input type="hidden" name="event_type_id" value="<?= $event_type->id; ?>">
                        <input type="hidden" name="settingsID" value="<?= $settingsID; ?>">
                        <input type="hidden" id="selectedDate" name="date">
                        <input type="hidden" id="selectedTime" name="time">
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-secondary mr-2" id="step3Back">Back</button>
                            <button type="submit" class="btn btn-primary" id="submitBooking">Confirm Booking</button>
                        </div>
                    </form>
                </div>

                <!-- Success Message -->
                <div id="successMessage" style="display: none;">
                    <div class="success-message">
                        <div class="success-icon">
                            <i class="mdi mdi-check-circle"></i>
                        </div>
                        <h4>Booking Confirmed!</h4>
                        <p class="text-muted mb-3">Your meeting has been scheduled successfully.</p>
                        <div class="card" style="background: #f9fafb; border: none;">
                            <div class="card-body">
                                <p><strong>Confirmation Code:</strong> <span id="confirmationCode"></span></p>
                                <p><strong>Date:</strong> <span id="confirmDate"></span></p>
                                <p><strong>Time:</strong> <span id="confirmTime"></span></p>
                            </div>
                        </div>
                        <p class="text-muted small mt-3">A confirmation email has been sent to your email address.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const eventTypeId = <?= $event_type->id; ?>;
            const settingsID = <?= $settingsID; ?>;
            let selectedDate = null;
            let selectedTime = null;

            // Generate dates for next 30 days
            function generateDates() {
                const dateGrid = $('#dateGrid');
                const today = new Date();
                const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                let html = '';
                for (let i = 0; i < 30; i++) {
                    const date = new Date(today);
                    date.setDate(today.getDate() + i);
                    
                    const dateStr = date.toISOString().split('T')[0];
                    const dayName = days[date.getDay()];
                    const dayNumber = date.getDate();
                    const monthName = months[date.getMonth()];

                    html += `
                        <div class="date-cell" data-date="${dateStr}">
                            <div class="date-day">${dayName}</div>
                            <div class="date-number">${dayNumber}</div>
                            <div class="date-day">${monthName}</div>
                        </div>
                    `;
                }

                dateGrid.html(html);
            }

            function loadTimeSlots(date) {
                $('#timeSlots').html('<div class="col-12 text-center"><i class="mdi mdi-loading mdi-spin"></i> Loading available times...</div>');
                
                $.get('<?= site_url("calendar/get_available_slots"); ?>', {
                    event_type_id: eventTypeId,
                    date: date,
                    settingsID: settingsID
                }, function(response) {
                    if (response.success) {
                        renderTimeSlots(response.slots);
                    } else {
                        $('#timeSlots').html('<div class="col-12 text-center text-muted">No available times for this date.</div>');
                    }
                }, 'json').fail(function() {
                    $('#timeSlots').html('<div class="col-12 text-center text-muted">Error loading time slots.</div>');
                });
            }

            function renderTimeSlots(slots) {
                if (slots.length === 0) {
                    $('#timeSlots').html('<div class="col-12 text-center text-muted">No available times for this date.</div>');
                    return;
                }

                let html = '';
                slots.forEach(function(slot) {
                    html += `
                        <div class="time-slot" data-time="${slot.start}">
                            ${slot.start}
                        </div>
                    `;
                });

                $('#timeSlots').html(html);
            }

            function showStep(step) {
                $('#step1, #step2, #step3').hide();
                $('#step' + step).show();
                
                $('.step').removeClass('active');
                $('#step' + step + '-indicator').addClass('active');
            }

            // Date selection
            $(document).on('click', '.date-cell', function() {
                $('.date-cell').removeClass('selected');
                $(this).addClass('selected');
                selectedDate = $(this).data('date');
                $('#step1Next').prop('disabled', false);
            });

            $('#step1Next').on('click', function() {
                if (selectedDate) {
                    const dateObj = new Date(selectedDate);
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    $('#selectedDateDisplay').text(dateObj.toLocaleDateString('en-US', options));
                    loadTimeSlots(selectedDate);
                    showStep(2);
                }
            });

            // Time selection
            $(document).on('click', '.time-slot', function() {
                if ($(this).hasClass('disabled')) return;
                
                $('.time-slot').removeClass('selected');
                $(this).addClass('selected');
                selectedTime = $(this).data('time');
                $('#step2Next').prop('disabled', false);
            });

            $('#step2Back').on('click', function() {
                showStep(1);
            });

            $('#step2Next').on('click', function() {
                if (selectedDate && selectedTime) {
                    const dateObj = new Date(selectedDate);
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    $('#selectedDateTimeDisplay').text(dateObj.toLocaleDateString('en-US', options) + ' at ' + selectedTime);
                    $('#selectedDate').val(selectedDate);
                    $('#selectedTime').val(selectedTime);
                    showStep(3);
                }
            });

            $('#step3Back').on('click', function() {
                showStep(2);
            });

            // Form submission
            $('#bookingForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                $('#submitBooking').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Booking...');
                
                $.post('<?= site_url("calendar/create_booking"); ?>', formData, function(response) {
                    if (response.success) {
                        $('#step1, #step2, #step3').hide();
                        $('.step-indicator').hide();
                        $('#successMessage').show();
                        $('#confirmationCode').text(response.confirmation_code);
                        $('#confirmDate').text(selectedDate);
                        $('#confirmTime').text(selectedTime);
                    } else {
                        alert(response.message || 'Error creating booking');
                        $('#submitBooking').prop('disabled', false).html('Confirm Booking');
                    }
                }, 'json').fail(function() {
                    alert('Error creating booking');
                    $('#submitBooking').prop('disabled', false).html('Confirm Booking');
                });
            });

            generateDates();
        });
    </script>
</body>
</html>
