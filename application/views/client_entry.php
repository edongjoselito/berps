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
                <div class="container-fluid client-entry-page">

                    <style>
                        .client-entry-page {
                            --surface: rgba(255, 255, 255, 0.96);
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
                            --info: #0f766e;
                            --info-soft: #ecfeff;
                            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
                            --shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.05);
                            --radius-xl: 24px;
                            --radius-lg: 18px;
                            --radius-md: 14px;
                            --radius-sm: 10px;
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 28px;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .client-entry-page * {
                            box-sizing: border-box;
                        }

                        .client-entry-page .alert {
                            border: none;
                            border-radius: 16px;
                            box-shadow: var(--shadow-soft);
                        }

                        .client-entry-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .client-entry-page .page-eyebrow {
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

                        .client-entry-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .client-entry-page .page-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2.1rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .client-entry-page .page-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .client-entry-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .client-entry-page .btn-action,
                        .client-entry-page .btn-submit {
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

                        .client-entry-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .client-entry-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .client-entry-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .client-entry-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                            color: #fff;
                        }

                        .client-entry-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .client-entry-page .theme-card-head {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .client-entry-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .client-entry-page .theme-card-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .client-entry-page .theme-card-body {
                            padding: 22px;
                        }

                        .client-entry-page .form-section+.form-section {
                            margin-top: 24px;
                            padding-top: 24px;
                            border-top: 1px solid var(--line);
                        }

                        .client-entry-page .section-title {
                            margin: 0 0 14px;
                            color: var(--text);
                            font-size: 0.92rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .client-entry-page label {
                            color: var(--text);
                            font-size: 0.82rem;
                            font-weight: 700;
                            letter-spacing: 0.02em;
                            margin-bottom: 8px;
                        }

                        .client-entry-page .form-control,
                        .client-entry-page .custom-select {
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            min-height: 46px;
                            color: var(--text);
                            box-shadow: none;
                        }

                        .client-entry-page textarea.form-control {
                            min-height: 120px;
                        }

                        .client-entry-page .form-control:focus,
                        .client-entry-page .custom-select:focus {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .client-entry-page .helper-card {
                            margin-top: 18px;
                            padding: 16px 18px;
                            border-radius: var(--radius-lg);
                            background: var(--info-soft);
                            border: 1px solid rgba(15, 118, 110, 0.14);
                            box-shadow: var(--shadow-soft);
                        }

                        .client-entry-page .helper-title {
                            color: var(--text);
                            font-size: 0.9rem;
                            font-weight: 800;
                            margin-bottom: 6px;
                        }

                        .client-entry-page .helper-copy {
                            color: var(--text-soft);
                            font-size: 0.85rem;
                            line-height: 1.5;
                        }

                        .client-entry-page .portal-note {
                            display: none;
                            margin-top: 12px;
                            padding: 12px 14px;
                            border-radius: 12px;
                            background: var(--warning-soft);
                            color: var(--warning);
                            font-size: 0.83rem;
                            font-weight: 700;
                        }

                        .client-entry-page .portal-note.is-visible {
                            display: block;
                        }

                        .client-entry-page .form-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                            margin-top: 24px;
                        }

                        .client-entry-page .btn {
                            border-radius: 12px;
                            font-weight: 700;
                            padding: 10px 16px;
                        }

                        .client-entry-page .btn-primary {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);
                        }

                        .client-entry-page .btn-light {
                            border: 1px solid var(--line-strong);
                            background: #fff;
                            color: var(--text);
                        }

                        .client-entry-page .btn-warning {
                            border: none;
                            background: linear-gradient(135deg, #f59e0b, #f97316);
                            color: #fff;
                        }

                        @media (max-width: 767px) {
                            .client-entry-page .page-title {
                                font-size: 1.75rem;
                            }

                            .client-entry-page .theme-card-head,
                            .client-entry-page .theme-card-body {
                                padding-left: 16px;
                                padding-right: 16px;
                            }
                        }
                    </style>

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

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Clients</div>
                            <h4 class="page-title">Add Client</h4>
                            <!-- <div class="page-subtitle">Create a new client profile in a full-page workspace instead of a modal, with room for portal access, lead source, and contact details.</div> -->
                        </div>
                        <div class="page-actions">
                            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn-action">
                                <i class="mdi mdi-arrow-left"></i>
                                Back to Client List
                            </a>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="theme-card-head">
                            <h5 class="theme-card-title">Client Profile Details</h5>
                            <!-- <div class="theme-card-subtitle">Encode the company profile, contact information, status, and optional portal access in one page.</div> -->
                        </div>
                        <div class="theme-card-body">
                            <form method="post" action="<?= base_url(); ?>Page/clientEntry">
                                <div class="form-section">
                                    <div class="section-title">Primary Details</div>
                                    <div class="form-row">
                                        <div class="form-group col-md-3" style="display: none;">
                                            <label for="client_id">Client ID</label>
                                            <input type="text" class="form-control" id="client_id" name="CustID" value="<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>" readonly required>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label for="client_name">Client</label>
                                            <input type="text" class="form-control" id="client_name" name="Customer" value="<?= htmlspecialchars($customer, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="client_address">Address</label>
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
                                </div>

                                <div class="form-section">
                                    <div class="section-title">Sales and Lead Information</div>
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
                                            <label for="client_status">Status</label>
                                            <select class="custom-select" id="client_status" name="ClientStat" required>
                                                <option value="Active" <?= strcasecmp($clientStat, 'Active') === 0 ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?= strcasecmp($clientStat, 'Inactive') === 0 ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="Prospect" <?= strcasecmp($clientStat, 'Prospect') === 0 ? 'selected' : ''; ?>>Prospect</option>
                                                <option value="Donation" <?= strcasecmp($clientStat, 'Donation') === 0 ? 'selected' : ''; ?>>Donation</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="section-title">Client Portal Access</div>
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

                                    <div class="portal-note" id="portal-note">
                                        Enable portal access only when the client email and initial portal password are both ready.
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="section-title">Internal Notes</div>
                                    <div class="form-group mb-0">
                                        <label for="notes">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="5"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>

                                <div class="helper-card">
                                    <div class="helper-title">Before saving</div>
                                    <div class="helper-copy">If you enable portal access, make sure `Client Email` is unique and set an initial portal password. If the client is still under follow-up, you can save the profile as `Prospect` first.</div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="addclient" value="1" class="btn btn-primary">Add Client</button>
                                    <button type="reset" class="btn btn-warning text-white">Reset</button>
                                    <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light">Cancel</a>
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