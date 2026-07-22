<?php
$values = isset($formValues) && is_array($formValues) ? $formValues : array();
$backUrl = isset($backUrl) && trim((string) $backUrl) !== '' ? (string) $backUrl : base_url() . 'Page/clientList';

$clientId = trim((string) ($values['CustID'] ?? ''));
$customer = trim((string) ($values['Customer'] ?? ''));
$address = trim((string) ($values['Address'] ?? ''));
$contact = trim((string) ($values['Contact'] ?? ''));
$contactPerson = trim((string) ($values['ContactPerson'] ?? ''));
$companyEmail = trim((string) ($values['CompanyEmail'] ?? ''));
$clientStat = trim((string) ($values['ClientStat'] ?? 'Active'));
$clientSource = trim((string) ($values['client_source'] ?? ''));
$facebookLink = trim((string) ($values['facebook_link'] ?? ''));
$clientEmail = trim((string) ($values['client_email'] ?? ''));
$notes = trim((string) ($values['notes'] ?? ''));
$salesAgent = trim((string) ($values['sales_agent'] ?? ''));
$portalEnabled = trim((string) ($values['portal_enabled'] ?? '0'));
$portalPassword = trim((string) ($values['portal_password'] ?? ''));
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
                <div class="container-fluid client-entry-page berps-form-page berps-page">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="client-entry-hero">
                        <div class="client-entry-hero__content">
                            <div class="client-entry-hero__eyebrow">
                                <i class="mdi mdi-account-plus-outline"></i>
                                Clients
                            </div>
                            <h1 class="client-entry-hero__title">Add Client <span class="client-wave">🤝</span></h1>
                            <p class="client-entry-hero__subtitle">Create the company profile, primary contact details, and optional portal access.</p>
                        </div>
                        <div class="client-entry-hero__actions">
                            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="client-entry-hero-btn">
                                <i class="mdi mdi-arrow-left"></i>
                                <span>Back to Client List</span>
                            </a>
                        </div>
                    </div>

                    <div class="berps-form-card">
                        <div class="berps-form-card__header">
                            <div>
                                <h2 class="berps-form-card__title">Client profile details</h2>
                                <p class="berps-form-card__copy">Required fields must be completed before the client can be saved.</p>
                            </div>
                        </div>
                        <div class="berps-form-card__body">
                            <form method="post" action="<?= base_url(); ?>Page/clientEntry">
                                <section class="berps-form-section">
                                    <div class="berps-form-section__header">
                                        <h3 class="berps-form-section__title">Primary details</h3>
                                    </div>
                                    <input type="hidden" id="client_id" name="CustID" value="<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label class="berps-required" for="client_name">Client</label>
                                            <input type="text" class="form-control" id="client_name" name="Customer" value="<?= htmlspecialchars($customer, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="berps-required" for="client_address">Address</label>
                                        <input type="text" class="form-control" id="client_address" name="Address" value="<?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="contact_person">Contact Person</label>
                                            <input type="text" class="form-control" id="contact_person" name="ContactPerson" value="<?= htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="contact_number">Contact Nos.</label>
                                            <input type="text" class="form-control" id="contact_number" name="Contact" value="<?= htmlspecialchars($contact, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="company_email">Company E-mail</label>
                                            <input type="email" class="form-control" id="company_email" name="CompanyEmail" value="<?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>
                                </section>

                                <section class="berps-form-section">
                                    <div class="berps-form-section__header">
                                        <h3 class="berps-form-section__title">Sales and lead information</h3>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="client_source">Client Source</label>
                                            <select class="custom-select" id="client_source" name="client_source">
                                                <option value="">Select Source</option>
                                                <option value="Facebook Ads" <?= strcasecmp($clientSource, 'Facebook Ads') === 0 ? 'selected' : ''; ?>>Facebook Ads</option>
                                                <option value="E-mail Marketing" <?= strcasecmp($clientSource, 'E-mail Marketing') === 0 ? 'selected' : ''; ?>>E-mail Marketing</option>
                                                <option value="Referral" <?= strcasecmp($clientSource, 'Referral') === 0 ? 'selected' : ''; ?>>Referral</option>
                                                <option value="Others" <?= strcasecmp($clientSource, 'Others') === 0 ? 'selected' : ''; ?>>Others</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="sales_agent">Sales Agent</label>
                                            <input type="text" class="form-control" id="sales_agent" name="sales_agent" value="<?= htmlspecialchars($salesAgent, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="facebook_link">Facebook Link</label>
                                            <input type="text" class="form-control" id="facebook_link" name="facebook_link" value="<?= htmlspecialchars($facebookLink, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="berps-required" for="client_status">Status</label>
                                            <select class="custom-select" id="client_status" name="ClientStat" required>
                                                <option value="Active" <?= strcasecmp($clientStat, 'Active') === 0 ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?= strcasecmp($clientStat, 'Inactive') === 0 ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="Prospect" <?= strcasecmp($clientStat, 'Prospect') === 0 ? 'selected' : ''; ?>>Prospect</option>
                                                <option value="Donation" <?= strcasecmp($clientStat, 'Donation') === 0 ? 'selected' : ''; ?>>Donation</option>
                                            </select>
                                        </div>
                                    </div>
                                </section>

                                <section class="berps-form-section">
                                    <div class="berps-form-section__header">
                                        <h3 class="berps-form-section__title">Client portal access</h3>
                                        <p class="berps-form-section__copy">Portal credentials become required only when access is enabled.</p>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="client_email">Client Email</label>
                                            <input type="email" class="form-control" id="client_email" name="client_email" value="<?= htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="portal_enabled">Portal Access</label>
                                            <select class="custom-select" id="portal_enabled" name="portal_enabled">
                                                <option value="0" <?= $portalEnabled !== '1' ? 'selected' : ''; ?>>Disabled</option>
                                                <option value="1" <?= $portalEnabled === '1' ? 'selected' : ''; ?>>Enabled</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="portal_password">Portal Password</label>
                                        <input type="password" class="form-control" id="portal_password" name="portal_password" value="<?= htmlspecialchars($portalPassword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Required when portal access is enabled">
                                        <small class="form-text text-muted">Clients sign in using their Client Email and this portal password.</small>
                                    </div>

                                    <div class="berps-conditional-note" id="portal-note">
                                        Enable portal access only when the client email and initial portal password are both ready.
                                    </div>
                                </section>

                                <section class="berps-form-section">
                                    <div class="berps-form-section__header">
                                        <h3 class="berps-form-section__title">Internal notes</h3>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="notes">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="5"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </section>

                                <div class="berps-form-note">
                                    <i class="mdi mdi-information-outline berps-form-note__icon" aria-hidden="true"></i>
                                    <div><strong>Before saving:</strong> If portal access is enabled, use a unique client email and provide an initial password. Clients still under follow-up can be saved as prospects.</div>
                                </div>

                                <div class="berps-form-actions">
                                    <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                                    <button type="submit" name="addclient" value="1" class="btn btn-primary">Add Client</button>
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
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        (function() {
            var portalToggle = document.getElementById('portal_enabled');
            var clientEmailField = document.getElementById('client_email');
            var portalPasswordField = document.getElementById('portal_password');
            var portalNote = document.getElementById('portal-note');

            function syncPortalState() {
                var enabled = portalToggle && portalToggle.value === '1';
                if (portalNote) {
                    portalNote.classList.toggle('is-visible', enabled);
                }
                if (clientEmailField) {
                    clientEmailField.required = enabled;
                }
                if (portalPasswordField) {
                    portalPasswordField.required = enabled;
                }
            }

            if (portalToggle) {
                portalToggle.addEventListener('change', syncPortalState);
            }

            syncPortalState();
        })();
    </script>

</body>

</html>
