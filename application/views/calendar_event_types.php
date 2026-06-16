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

    .event-type-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        background: #fff;
        transition: all 0.2s;
    }

    .event-type-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
    }

    .event-type-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .event-type-title {
        font-weight: 700;
        font-size: 16px;
        color: #111827;
    }

    .event-type-duration {
        background: #f3f4f6;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
    }

    .event-type-link {
        background: #eff6ff;
        color: #2563eb;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
        display: inline-block;
    }

    .modal-content {
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, .06);
        box-shadow: 0 18px 50px rgba(18, 38, 63, .18);
    }

    .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    .form-help {
        color: #6b7280;
        font-size: 12px;
        margin-top: 4px;
    }

    .event-fieldset {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 14px;
        background: #fbfdff;
    }

    .event-fieldset legend {
        width: auto;
        padding: 0 8px;
        margin-bottom: 0;
        font-size: 12px;
        font-weight: 800;
        color: #2563eb;
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
                                    <h4 class="mb-1">Event Types</h4>
                                    <p class="text-muted mb-0">Create different meeting types for scheduling (e.g., 15-min, 30-min, 1-hour meetings)</p>
                                </div>
                                <button class="btn btn-info" id="addEventTypeBtn">
                                    <i class="mdi mdi-plus mr-1"></i> Add Event Type
                                </button>
                            </div>

                            <div id="eventTypesList">
                                <!-- Event types will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <div class="modal fade" id="eventTypeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="modalTitle">Add Event Type</h5>
                        <div class="small text-muted">Configure your meeting type settings</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <form id="eventTypeForm">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold">Event Name *</label>
                                <input type="text" class="form-control" name="name" id="eventTypeName" required>
                                <div class="form-help">e.g., "15-Minute Meeting", "30-Minute Consultation"</div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold">Description</label>
                                <textarea class="form-control" name="description" id="eventTypeDescription" rows="2"></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Duration *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="duration" id="eventTypeDuration" value="30" min="5" required>
                                    <select class="form-control" name="duration_unit" id="eventTypeDurationUnit">
                                        <option value="minute">Minutes</option>
                                        <option value="hour">Hours</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Color</label>
                                <input type="color" class="form-control" name="color" id="eventTypeColor" value="#3788d8">
                            </div>

                            <div class="col-md-12">
                                <fieldset class="event-fieldset">
                                    <legend>Location</legend>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Location Type</label>
                                            <select class="form-control" name="location_type" id="eventTypeLocationType">
                                                <option value="in_person">In Person</option>
                                                <option value="online">Online / Video Call</option>
                                                <option value="phone">Phone Call</option>
                                                <option value="custom">Custom</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Location Details</label>
                                            <input type="text" class="form-control" name="location_details" id="eventTypeLocationDetails" placeholder="e.g., Zoom link, office address">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="col-md-12">
                                <fieldset class="event-fieldset">
                                    <legend>Buffer Time</legend>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Buffer Before (minutes)</label>
                                            <input type="number" class="form-control" name="buffer_before" id="eventTypeBufferBefore" value="0" min="0">
                                            <div class="form-help">Time to add before each meeting</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Buffer After (minutes)</label>
                                            <input type="number" class="form-control" name="buffer_after" id="eventTypeBufferAfter" value="0" min="0">
                                            <div class="form-help">Time to add after each meeting</div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="col-md-12">
                                <fieldset class="event-fieldset mb-0">
                                    <legend>Booking Notice</legend>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Min Notice (hours)</label>
                                            <input type="number" class="form-control" name="min_booking_notice" id="eventTypeMinNotice" value="0" min="0">
                                            <div class="form-help">Minimum hours before booking</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Max Notice (days)</label>
                                            <input type="number" class="form-control" name="max_booking_notice" id="eventTypeMaxNotice" value="90" min="1">
                                            <div class="form-help">Maximum days in advance for booking</div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold">Booking Link Slug (optional)</label>
                                <input type="text" class="form-control" name="booking_link_slug" id="eventTypeSlug" placeholder="e.g., 15min-meeting">
                                <div class="form-help">Custom URL slug for your booking page. Leave blank to auto-generate.</div>
                            </div>

                            <input type="hidden" name="event_type_id" id="eventTypeId">
                        </div>
                    </form>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <div>
                        <button id="deleteEventTypeBtn" class="btn btn-outline-danger d-none mr-2">
                            <i class="mdi mdi-delete-outline"></i> Delete
                        </button>
                        <button id="saveEventTypeBtn" class="btn btn-info">
                            <i class="mdi mdi-content-save-outline mr-1"></i> Save Event Type
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const $eventTypesList = $('#eventTypesList');
            const $eventTypeForm = $('#eventTypeForm');
            const $modal = $('#eventTypeModal');
            const $modalTitle = $('#modalTitle');
            const $deleteBtn = $('#deleteEventTypeBtn');

            function loadEventTypes() {
                $.get('<?= site_url("calendar/get_event_types") ?>', function(response) {
                    if (response.success) {
                        renderEventTypes(response.data);
                    }
                }, 'json');
            }

            function renderEventTypes(eventTypes) {
                if (eventTypes.length === 0) {
                    $eventTypesList.html('<div class="text-center py-5 text-muted">No event types created yet. Click "Add Event Type" to create one.</div>');
                    return;
                }

                let html = '';
                eventTypes.forEach(function(et) {
                    const durationUnit = et.duration_unit === 'hour' ? 'hr' : 'min';
                    const bookingLink = et.booking_link_slug ? 
                        '<div class="event-type-link"><i class="mdi mdi-link-variant mr-1"></i>' + base_url + 'book/' + et.booking_link_slug + '</div>' : '';

                    html += `
                        <div class="event-type-card" data-id="${et.id}">
                            <div class="event-type-header">
                                <div>
                                    <div class="event-type-title">${et.name}</div>
                                    <div class="event-type-duration">${et.duration} ${durationUnit}</div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-info edit-event-type" data-id="${et.id}">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-event-type" data-id="${et.id}">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                            ${et.description ? '<div class="text-muted small mb-2">' + et.description + '</div>' : ''}
                            ${bookingLink}
                        </div>
                    `;
                });

                $eventTypesList.html(html);
            }

            function resetForm() {
                $eventTypeForm[0].reset();
                $('#eventTypeId').val('');
                $('#eventTypeDuration').val('30');
                $('#eventTypeColor').val('#3788d8');
                $('#eventTypeBufferBefore').val('0');
                $('#eventTypeBufferAfter').val('0');
                $('#eventTypeMinNotice').val('0');
                $('#eventTypeMaxNotice').val('90');
                $modalTitle.text('Add Event Type');
                $deleteBtn.addClass('d-none');
            }

            function loadEventType(id) {
                $.get('<?= site_url("calendar/get_event_types") ?>', function(response) {
                    if (response.success) {
                        const et = response.data.find(e => e.id == id);
                        if (et) {
                            $('#eventTypeId').val(et.id);
                            $('#eventTypeName').val(et.name);
                            $('#eventTypeDescription').val(et.description || '');
                            $('#eventTypeDuration').val(et.duration);
                            $('#eventTypeDurationUnit').val(et.duration_unit);
                            $('#eventTypeColor').val(et.color);
                            $('#eventTypeLocationType').val(et.location_type);
                            $('#eventTypeLocationDetails').val(et.location_details || '');
                            $('#eventTypeBufferBefore').val(et.buffer_before);
                            $('#eventTypeBufferAfter').val(et.buffer_after);
                            $('#eventTypeMinNotice').val(et.min_booking_notice);
                            $('#eventTypeMaxNotice').val(et.max_booking_notice);
                            $('#eventTypeSlug').val(et.booking_link_slug || '');
                            $modalTitle.text('Edit Event Type');
                            $deleteBtn.removeClass('d-none');
                            $modal.modal('show');
                        }
                    }
                }, 'json');
            }

            $('#addEventTypeBtn').on('click', function() {
                resetForm();
                $modal.modal('show');
            });

            $(document).on('click', '.edit-event-type', function() {
                const id = $(this).data('id');
                loadEventType(id);
            });

            $(document).on('click', '.delete-event-type', function() {
                if (!confirm('Delete this event type?')) return;
                const id = $(this).data('id');
                
                $.post('<?= site_url("calendar/delete_event_type") ?>', { event_type_id: id }, function(response) {
                    if (response.success) {
                        loadEventTypes();
                    } else {
                        alert(response.message || 'Error deleting event type');
                    }
                }, 'json').fail(function() {
                    alert('Error deleting event type');
                });
            });

            $('#saveEventTypeBtn').on('click', function() {
                const formData = $eventTypeForm.serialize();
                
                $.post('<?= site_url("calendar/save_event_type") ?>', formData, function(response) {
                    if (response.success) {
                        $modal.modal('hide');
                        loadEventTypes();
                    } else {
                        alert(response.message || 'Error saving event type');
                    }
                }, 'json').fail(function() {
                    alert('Error saving event type');
                });
            });

            $deleteBtn.on('click', function() {
                if (!confirm('Delete this event type?')) return;
                const id = $('#eventTypeId').val();
                
                $.post('<?= site_url("calendar/delete_event_type") ?>', { event_type_id: id }, function(response) {
                    if (response.success) {
                        $modal.modal('hide');
                        loadEventTypes();
                    } else {
                        alert(response.message || 'Error deleting event type');
                    }
                }, 'json').fail(function() {
                    alert('Error deleting event type');
                });
            });

            $modal.on('hidden.bs.modal', resetForm);

            loadEventTypes();
        });
    </script>
</body>
</html>
