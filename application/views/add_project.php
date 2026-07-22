<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid add-project-page berps-form-page berps-page">
                    <header class="berps-page-header">
                        <div class="berps-page-header__content">
                            <span class="berps-page-header__eyebrow">Projects</span>
                            <h1 class="berps-page-title">Add Project</h1>
                            <p class="berps-page-subtitle">Create a project record and connect it to the correct client and contract details.</p>
                        </div>
                        <div class="berps-page-header__actions">
                            <a href="<?= base_url(); ?>Page/projectList" class="btn btn-outline-secondary">
                                <i class="mdi mdi-arrow-left mr-1" aria-hidden="true"></i>Back to Project List
                            </a>
                        </div>
                    </header>

                    <div class="berps-form-card">
                        <div class="berps-form-card__header">
                            <div>
                                <h2 class="berps-form-card__title">Project information</h2>
                                <p class="berps-form-card__copy">Fields marked with an asterisk are required.</p>
                            </div>
                        </div>

                        <div class="berps-form-card__body">
                            <form class="needs-validation" method="post" novalidate>
                                <section class="berps-form-section">
                                    <div class="berps-form-section__header">
                                        <h3 class="berps-form-section__title">Contract details</h3>
                                    </div>
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
                                </section>

                                <section class="berps-form-section">
                                    <div class="berps-form-section__header">
                                        <h3 class="berps-form-section__title">Client information</h3>
                                        <p class="berps-form-section__copy">Selecting a client fills the stored address and contact information.</p>
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
                                </section>

                                <section class="berps-form-section">
                                        <div class="form-group">
                                            <label class="form-label" for="otherDetails">Other Details</label>
                                            <textarea class="form-control" id="otherDetails" name="otherDetails" rows="3" placeholder="Key contract terms, deliverables, notes, etc."></textarea>
                                        </div>
                                </section>

                                <div class="berps-form-actions">
                                    <a href="<?= base_url(); ?>Page/projectList" class="btn btn-outline-secondary">Cancel</a>
                                    <button class="btn btn-outline-secondary" type="reset" id="btnResetForm">
                                        <i class="mdi mdi-refresh mr-1" aria-hidden="true"></i>Reset
                                    </button>
                                    <button type="submit" name="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save-outline mr-1" aria-hidden="true"></i>Save Project
                                    </button>
                                </div>
                            </form>
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
