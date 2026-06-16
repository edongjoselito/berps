<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid add-project-page">
                    <style>
                        .content-page {
                            display: flex;
                            flex-direction: column;
                            min-height: 100vh;
                        }

                        .content-page .content {
                            flex: 1 0 auto;
                            padding-bottom: 1.25rem;
                        }

                        .footer {
                            flex: 0 0 auto;
                            margin-top: 0 !important;
                        }

                        .add-project-page .title-wrap {
                            padding: 1.25rem 1.25rem 0;
                        }

                        .add-project-page .title-wrap h4 {
                            margin: 0;
                            line-height: 1.1;
                        }

                        .add-project-page .title-sub {
                            display: inline-block;
                            margin-top: 4px;
                        }

                        .add-project-page .title-divider {
                            border: 0;
                            height: 2px;
                            border-radius: 1px;
                            background: linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%);
                            margin: 10px 1.25rem 16px;
                        }

                        .add-project-page .card {
                            border: 1px solid rgba(15, 23, 42, .08);
                            border-radius: 14px;
                        }

                        .add-project-page .card-header {
                            border-bottom: 1px solid rgba(15, 23, 42, .08);
                            background: rgba(248, 250, 252, .65);
                            padding: .85rem 1.25rem;
                        }

                        .add-project-page .card-body {
                            padding: 1.25rem;
                        }

                        .add-project-page .form-label {
                            font-weight: 600;
                            color: #5c6b7a;
                            margin-bottom: .25rem;
                        }

                        .add-project-page .req::after {
                            content: " *";
                            color: #dc3545;
                        }

                        .add-project-page .form-row {
                            margin-left: -8px;
                            margin-right: -8px;
                        }

                        .add-project-page .form-row>[class^="col"] {
                            padding-left: 8px;
                            padding-right: 8px;
                        }

                        .form-text {
                            font-size: .8rem;
                            color: #6c757d;
                        }

                        .add-project-page .form-actions {
                            display: flex;
                            gap: .5rem;
                            justify-content: flex-end;
                        }

                        #otherDetails {
                            min-height: 110px;
                        }

                        .was-validated .form-control:invalid,
                        .form-control.is-invalid,
                        .was-validated .custom-select:invalid,
                        .custom-select.is-invalid {
                            border-color: #dc3545;
                        }

                        .was-validated .form-control:valid,
                        .form-control.is-valid,
                        .was-validated .custom-select:valid,
                        .custom-select.is-valid {
                            border-color: #198754;
                        }

                        .select2-container--default .select2-selection--single {
                            height: calc(2.25rem + 2px);
                            border: 1px solid #ced4da;
                            border-radius: .25rem;
                            padding: .375rem .75rem;
                        }

                        .select2-container--default .select2-selection--single .select2-selection__rendered {
                            line-height: 1.4rem;
                            padding-left: 0;
                            color: #495057;
                        }

                        .select2-container--default .select2-selection--single .select2-selection__arrow {
                            height: calc(2.25rem + 2px);
                            right: 6px;
                        }

                        @media (max-width:576px) {
                            .add-project-page .title-wrap {
                                padding: 1rem 1rem 0;
                            }

                            .add-project-page .title-divider {
                                margin: 8px 1rem 12px;
                            }
                        }
                    </style>

                    <div class="row">
                        <div class="col-12">
                            <div class="title-wrap">
                                <h4 class="page-title">
                                    Add Project<br>
                                    <span class="badge badge-purple title-sub">Create a new project record</span>
                                </h4>
                            </div>
                            <hr class="title-divider">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">Project Information</h5>
                                    <div class="d-flex align-items-center" style="gap:.5rem;">
                                        <a href="<?= base_url(); ?>Page/projectList" class="btn btn-outline-secondary btn-sm">
                                            <i class="mdi mdi-briefcase-outline mr-1"></i> View Project List
                                        </a>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <form class="needs-validation" method="post" novalidate>
                                        <div class="form-group">
                                            <label class="form-label req" for="projectDescription">Project</label>
                                            <input type="text" class="form-control" id="projectDescription" name="projectDescription" required>
                                            <div class="invalid-feedback">Please enter the project name.</div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label class="form-label req" for="projectCategory">Category</label>
                                                <select class="form-control" id="projectCategory" name="projectCategory" required>
                                                    <option value="" disabled selected>— Select a category —</option>
                                                    <?php if (!empty($data)): ?>
                                                        <?php foreach ($data as $row): ?>
                                                            <option value="<?= htmlspecialchars($row->Category, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars($row->Category, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                                <div class="invalid-feedback">Select a category.</div>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label class="form-label req" for="contractDate">Contract Date</label>
                                                <input type="date" class="form-control" id="contractDate" name="contractDate" value="<?= date('Y-m-d'); ?>" required>
                                                <div class="invalid-feedback">Provide a contract date.</div>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label class="form-label" for="projectCost">Project Cost</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">₱</span>
                                                    </div>
                                                    <input type="text" class="form-control" id="projectCost" name="projectCost" inputmode="decimal" placeholder="0.00">
                                                </div>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label class="form-label" for="contactPerson">Contact Person / Contact Nos.</label>
                                                <input type="text" class="form-control" id="contactPerson" name="contactPerson">
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <input type="hidden" name="CustID" id="CustID" value="">
                                                <label class="form-label" for="clientSelect">Client</label>
                                                <select class="form-control" id="clientSelect">
                                                    <option value="">— Search and select client —</option>
                                                    <?php if (!empty($clients)): ?>
                                                        <?php foreach ($clients as $client): ?>
                                                            <option
                                                                value="<?= htmlspecialchars($client->CustID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-customer="<?= htmlspecialchars($client->Customer, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-address="<?= htmlspecialchars($client->Address, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-contactperson="<?= htmlspecialchars($client->ContactPerson, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars($client->Customer, ENT_QUOTES, 'UTF-8'); ?>
                                                                <?php if (!empty($client->Address)): ?>
                                                                    — <?= htmlspecialchars($client->Address, ENT_QUOTES, 'UTF-8'); ?>
                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                                <input type="hidden" name="Customer" id="Customer" value="">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label class="form-label" for="Address">Address</label>
                                                <input type="text" class="form-control" id="Address" name="Address" placeholder="Street, Barangay, City/Province, ZIP">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="otherDetails">Other Details</label>
                                            <textarea class="form-control" id="otherDetails" name="otherDetails" rows="3" placeholder="Key contract terms, deliverables, notes, etc."></textarea>
                                        </div>

                                        <div class="form-actions">
                                            <button class="btn btn-warning" type="reset" id="btnResetForm">
                                                <i class="mdi mdi-refresh mr-1"></i>Reset
                                            </button>
                                            <input type="submit" name="submit" class="btn btn-primary" value="Save Project">
                                        </div>
                                    </form>
                                </div>
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
    <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/calendar.init.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/jquery.chat.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/jquery.todo.js"></script>
    <script src="<?= base_url(); ?>assets/libs/morris-js/morris.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/raphael/raphael.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>

    <script>
        (function() {
            'use strict';

            var form = document.querySelector('.needs-validation');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            }

            var cost = document.getElementById('projectCost');
            if (cost) {
                cost.addEventListener('blur', function() {
                    var v = (cost.value || '').toString().replace(/[^\d.]/g, '');
                    if (v) {
                        var num = parseFloat(v);
                        if (!isNaN(num)) {
                            cost.value = num.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                });

                cost.addEventListener('input', function() {
                    this.value = this.value.replace(/[^\d.]/g, '');
                });
            }

            if (window.jQuery && jQuery.fn && typeof jQuery.fn.select2 === 'function') {
                jQuery('#clientSelect').select2({
                    width: '100%',
                    placeholder: '— Search and select client —',
                    allowClear: true
                });

                jQuery('#clientSelect').on('change', function() {
                    var selected = jQuery(this).find(':selected');

                    var custId = selected.val() || '';
                    var customer = selected.data('customer') || '';
                    var address = selected.data('address') || '';
                    var contactPerson = selected.data('contactperson') || '';

                    jQuery('#CustID').val(custId);
                    jQuery('#Customer').val(customer);
                    jQuery('#Address').val(address);

                    if (contactPerson && !jQuery('#contactPerson').val()) {
                        jQuery('#contactPerson').val(contactPerson);
                    }
                });

                jQuery('#btnResetForm').on('click', function() {
                    setTimeout(function() {
                        jQuery('#clientSelect').val('').trigger('change');
                        jQuery('#CustID').val('');
                        jQuery('#Customer').val('');
                        jQuery('#Address').val('');
                    }, 0);
                });
            }
        })();
    </script>
</body>

</html>