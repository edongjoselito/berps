<?php
$companyRow = !empty($data) && isset($data[0]) ? $data[0] : null;
$companyName = $companyRow ? trim((string) ($companyRow->CompName ?? '')) : '';
$companyAddress = $companyRow ? trim((string) ($companyRow->CompAddress ?? '')) : '';
$companyTin = $companyRow ? trim((string) ($companyRow->CompTin ?? '')) : '';
$proprietor = $companyRow ? trim((string) ($companyRow->Proprietor ?? '')) : '';
$companyType = $companyRow ? trim((string) ($companyRow->CompType ?? '')) : '';
$enabledBusinessLines = [];
$rawBusinessLines = $companyRow ? (string) ($companyRow->BusinessLines ?? '') : '';
if ($rawBusinessLines !== '') {
    $decodedBusinessLines = json_decode($rawBusinessLines, true);
    if (is_array($decodedBusinessLines)) {
        $enabledBusinessLines = array_values(array_filter(array_map('strval', $decodedBusinessLines)));
    }
}

// Invoice footer settings
$invoiceFooter = isset($invoiceFooter) && is_array($invoiceFooter) && !empty($invoiceFooter) ? $invoiceFooter[0] : null;
$bankName1 = $invoiceFooter ? trim((string) ($invoiceFooter->bank_name_1 ?? '')) : '';
$bankAccountName1 = $invoiceFooter ? trim((string) ($invoiceFooter->bank_account_name_1 ?? '')) : '';
$bankAccountNo1 = $invoiceFooter ? trim((string) ($invoiceFooter->bank_account_no_1 ?? '')) : '';
$bankName2 = $invoiceFooter ? trim((string) ($invoiceFooter->bank_name_2 ?? '')) : '';
$bankAccountName2 = $invoiceFooter ? trim((string) ($invoiceFooter->bank_account_name_2 ?? '')) : '';
$bankAccountNo2 = $invoiceFooter ? trim((string) ($invoiceFooter->bank_account_no_2 ?? '')) : '';
$contactEmail = $invoiceFooter ? trim((string) ($invoiceFooter->contact_email ?? '')) : '';
$contactPhone = $invoiceFooter ? trim((string) ($invoiceFooter->contact_phone ?? '')) : '';
$footerDisclaimer = $invoiceFooter ? trim((string) ($invoiceFooter->footer_disclaimer ?? '')) : '';
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
                <div class="container-fluid business-details-page">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= htmlspecialchars($this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <style>

                        .business-details-page {
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
                            --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                            --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                            --radius-xl: 16px;
                            --radius-lg: 12px;
                            --radius-md: 10px;
                            --radius-sm: 8px;
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            --font-mono: var(--font-primary);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 100px;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .business-details-page .content {
                            margin-bottom: 40px;
                        }

                        .business-details-page * {
                            box-sizing: border-box;
                        }

                        .business-details-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .business-details-page .page-eyebrow {
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

                        .business-details-page .page-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .business-details-page .page-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2.1rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .business-details-page .page-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .business-details-page .page-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .business-details-page .btn-action,
                        .business-details-page .btn-submit {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border-radius: 12px;
                            font-size: 0.92rem;
                            font-weight: 700;
                            padding: 11px 18px;
                            text-decoration: none;
                            transition: all 0.16s ease;
                        }

                        .business-details-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .business-details-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .business-details-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                            min-width: 180px;
                        }

                        .business-details-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .business-details-page .stat-grid {
                            display: grid;
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                            gap: 16px;
                            margin-bottom: 20px;
                        }

                        .business-details-page .stat-card {
                            position: relative;
                            overflow: hidden;
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 18px 20px;
                        }

                        .business-details-page .stat-card::before {
                            content: '';
                            position: absolute;
                            inset: 0 0 auto 0;
                            height: 4px;
                            background: linear-gradient(90deg, #3b82f6, #60a5fa);
                        }

                        .business-details-page .stat-label {
                            color: var(--text-faint);
                            font-size: 0.74rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            margin-bottom: 12px;
                        }

                        .business-details-page .stat-value {
                            color: var(--text);
                            font-size: 1.35rem;
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.04em;
                        }

                        .business-details-page .stat-meta {
                            color: var(--text-soft);
                            font-size: 0.84rem;
                            margin-top: 7px;
                        }

                        .business-details-page .content-grid {
                            display: grid;
                            grid-template-columns: minmax(0, 1.5fr) minmax(320px, 0.95fr);
                            gap: 20px;
                            align-items: start;
                        }

                        .business-details-page .card-stack,
                        .business-details-page .side-stack {
                            display: grid;
                            gap: 20px;
                        }

                        .business-details-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.72);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow);
                            overflow: hidden;
                        }

                        .business-details-page .theme-card-head {
                            padding: 18px 22px;
                            border-bottom: 1px solid var(--line);
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                        }

                        .business-details-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1.02rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                        }

                        .business-details-page .theme-card-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.86rem;
                        }

                        .business-details-page .theme-card-body {
                            padding: 22px;
                        }

                        .business-details-page .side-stack .theme-card {
                            position: sticky;
                            top: 88px;
                        }

                        .business-details-page label {
                            color: var(--text);
                            font-size: 0.82rem;
                            font-weight: 700;
                            letter-spacing: 0.02em;
                            margin-bottom: 8px;
                        }

                        .business-details-page .form-control {
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            min-height: 46px;
                            color: var(--text);
                            box-shadow: none;
                        }

                        .business-details-page .form-control:focus {
                            border-color: #9cc0f5;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
                        }

                        .business-details-page textarea.form-control {
                            min-height: 110px;
                            resize: vertical;
                        }

                        .business-details-page .field-note {
                            display: block;
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.82rem;
                            line-height: 1.55;
                        }

                        .business-details-page .preview-shell {
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                            padding: 18px;
                            box-shadow: var(--shadow-soft);
                        }

                        .business-details-page .preview-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: var(--primary-2);
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 14px;
                        }

                        .business-details-page .preview-name {
                            color: var(--text);
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 1.5rem;
                            line-height: 1.1;
                            letter-spacing: -0.04em;
                            font-weight: 800;
                            margin-bottom: 10px;
                        }

                        .business-details-page .preview-line {
                            color: var(--text-soft);
                            font-size: 0.92rem;
                            line-height: 1.65;
                            margin-bottom: 8px;
                            white-space: pre-line;
                        }

                        .business-details-page .preview-kicker {
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-top: 18px;
                            margin-bottom: 10px;
                        }

                        .business-details-page .preview-grid {
                            display: grid;
                            gap: 12px;
                        }

                        .business-details-page .preview-box {
                            border: 1px solid var(--line);
                            border-radius: 14px;
                            background: #fff;
                            padding: 14px;
                        }

                        .business-details-page .preview-box-label {
                            color: var(--text-faint);
                            font-size: 0.72rem;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 7px;
                        }

                        .business-details-page .preview-box-value {
                            color: var(--text);
                            font-size: 0.97rem;
                            font-weight: 700;
                            line-height: 1.45;
                            word-break: break-word;
                        }

                        .business-details-page .tips-list {
                            display: grid;
                            gap: 10px;
                        }

                        .business-details-page .tips-item {
                            display: flex;
                            gap: 10px;
                            align-items: flex-start;
                            color: var(--text-soft);
                            font-size: 0.88rem;
                            line-height: 1.55;
                        }

                        .business-details-page .tips-item i {
                            color: var(--primary);
                            margin-top: 3px;
                        }

                        @media (max-width: 1199px) {

                            .business-details-page .stat-grid,
                            .business-details-page .content-grid {
                                grid-template-columns: 1fr;
                            }

                            .business-details-page .side-stack .theme-card {
                                position: static;
                            }
                        }

                        @media (max-width: 767px) {
                            .business-details-page .page-title {
                                font-size: 1.75rem;
                            }

                            .business-details-page .theme-card-head,
                            .business-details-page .theme-card-body {
                                padding-left: 16px;
                                padding-right: 16px;
                            }
                        }
                    </style>

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">System Configuration</div>
                            <h4 class="page-title">Business Details</h4>
                            <div class="page-subtitle">Keep the company profile current so invoices, printouts, and reports show the right business name, address, TIN, and tax setup.</div>
                        </div>
                        <div class="page-actions">
                            <a href="<?= base_url(); ?>Page/admin" class="btn-action">Back to Dashboard</a>
                            <button type="submit" form="businessDetailsForm" class="btn-submit">Save Company Profile</button>
                        </div>
                    </div>

                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">Company Name</div>
                            <div class="stat-value" id="stat-company-name"><?= htmlspecialchars($companyName !== '' ? $companyName : 'Not Set', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="stat-meta">Primary identity shown on invoices and reports.</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">TIN</div>
                            <div class="stat-value" id="stat-company-tin"><?= htmlspecialchars($companyTin !== '' ? $companyTin : 'Not Set', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="stat-meta">Printed in the invoice footer and tax-related summaries.</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Company Type</div>
                            <div class="stat-value" id="stat-company-type"><?= htmlspecialchars($companyType !== '' ? $companyType : 'Not Set', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="stat-meta">Use values like VAT or Non-VAT to match your billing setup.</div>
                        </div>
                    </div>

                    <form id="businessDetailsForm" method="post" novalidate>
                        <div class="content-grid">
                            <div class="card-stack">
                                <div class="theme-card">
                                    <div class="theme-card-head">
                                        <h5 class="theme-card-title">Company Profile</h5>
                                        <div class="theme-card-subtitle">These details are used throughout BERPS whenever the company appears on billing documents.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="form-group">
                                            <label for="company-name">Company Name</label>
                                            <input type="text" class="form-control" id="company-name" name="CompName" value="<?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            <small class="field-note">This is the main name that appears on the invoice header and printed reports.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="company-address">Company Address</label>
                                            <textarea class="form-control" id="company-address" name="CompAddress" placeholder="Street, barangay, city, province"><?= htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            <small class="field-note">Use the full mailing or billing address you want reflected in invoice printouts.</small>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="company-tin">TIN</label>
                                                <input type="text" class="form-control" id="company-tin" name="CompTin" value="<?= htmlspecialchars($companyTin, ENT_QUOTES, 'UTF-8'); ?>" placeholder="000-000-000-000">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="company-type">Company Type</label>
                                                <input type="text" class="form-control" id="company-type" name="CompType" value="<?= htmlspecialchars($companyType, ENT_QUOTES, 'UTF-8'); ?>" placeholder="VAT / Non-VAT">
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="company-proprietor">Proprietor / Authorized Representative</label>
                                            <input type="text" class="form-control" id="company-proprietor" name="Proprietor" value="<?= htmlspecialchars($proprietor, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Owner or signatory name">
                                            <small class="field-note">Useful when the company is a sole proprietorship or when you want a named representative visible in business documents.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="theme-card" style="margin-top: 20px;">
                                    <div class="theme-card-head">
                                        <h5 class="theme-card-title">Invoice Footer / Payment Details</h5>
                                        <div class="theme-card-subtitle">These details appear at the bottom of printed invoices, including bank accounts for payment and contact information.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="form-group">
                                            <label for="bank-name-1">Bank Account 1</label>
                                            <div class="form-row">
                                                <div class="form-group col-md-4 mb-0">
                                                    <input type="text" class="form-control" id="bank-name-1" name="bank_name_1" value="<?= htmlspecialchars($bankName1, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Bank Name (e.g., PNB)">
                                                </div>
                                                <div class="form-group col-md-4 mb-0">
                                                    <input type="text" class="form-control" id="bank-account-name-1" name="bank_account_name_1" value="<?= htmlspecialchars($bankAccountName1, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Account Name">
                                                </div>
                                                <div class="form-group col-md-4 mb-0">
                                                    <input type="text" class="form-control" id="bank-account-no-1" name="bank_account_no_1" value="<?= htmlspecialchars($bankAccountNo1, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Account No.">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="bank-name-2">Bank Account 2</label>
                                            <div class="form-row">
                                                <div class="form-group col-md-4 mb-0">
                                                    <input type="text" class="form-control" id="bank-name-2" name="bank_name_2" value="<?= htmlspecialchars($bankName2, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Bank Name (e.g., BDO)">
                                                </div>
                                                <div class="form-group col-md-4 mb-0">
                                                    <input type="text" class="form-control" id="bank-account-name-2" name="bank_account_name_2" value="<?= htmlspecialchars($bankAccountName2, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Account Name">
                                                </div>
                                                <div class="form-group col-md-4 mb-0">
                                                    <input type="text" class="form-control" id="bank-account-no-2" name="bank_account_no_2" value="<?= htmlspecialchars($bankAccountNo2, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Account No.">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="contact-email">Contact Email</label>
                                                <input type="email" class="form-control" id="contact-email" name="contact_email" value="<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g., sales@company.com">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="contact-phone">Contact Phone</label>
                                                <input type="text" class="form-control" id="contact-phone" name="contact_phone" value="<?= htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g., 0912 345 6789">
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="footer-disclaimer">Footer Disclaimer / Legal Notice</label>
                                            <textarea class="form-control" id="footer-disclaimer" name="footer_disclaimer" rows="4" placeholder="Enter legal disclaimer or communication notice to appear at the bottom of invoices"><?= htmlspecialchars($footerDisclaimer, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            <small class="field-note">This text appears at the bottom of printed invoices. Include confidentiality notices, payment terms, or legal disclaimers.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="theme-card" style="margin-top: 20px;">
                                    <div class="theme-card-head">
                                        <h5 class="theme-card-title">Company Business Lines</h5>
                                        <div class="theme-card-subtitle">Enable the business lines your company operates. POS screens can use this as the source of truth for available workflows and reporting.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="form-group mb-0">
                                            <label class="d-block">Enabled Business Lines</label>
                                            <?php
                                            $businessLineChoices = [
                                                'pharmacy' => 'Pharmacy',
                                                'grocery' => 'Grocery',
                                                'restaurant' => 'Restaurant',
                                                'electronics' => 'Electronics',
                                                'clothing' => 'Clothing',
                                                'general' => 'General',
                                            ];
                                            ?>
                                            <div class="form-row">
                                                <?php foreach ($businessLineChoices as $lineKey => $lineLabel): ?>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input
                                                                type="checkbox"
                                                                class="custom-control-input"
                                                                id="business-line-<?= htmlspecialchars($lineKey, ENT_QUOTES, 'UTF-8'); ?>"
                                                                name="business_lines[]"
                                                                value="<?= htmlspecialchars($lineKey, ENT_QUOTES, 'UTF-8'); ?>"
                                                                <?= in_array($lineKey, $enabledBusinessLines, true) ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label" for="business-line-<?= htmlspecialchars($lineKey, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($lineLabel, ENT_QUOTES, 'UTF-8'); ?></label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <small class="field-note">These settings are saved per company (per settingsID).</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="side-stack">
                                <div class="theme-card">
                                    <div class="theme-card-head">
                                        <h5 class="theme-card-title">Invoice Preview</h5>
                                        <div class="theme-card-subtitle">A quick snapshot of how the company information will read inside BERPS invoice pages.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="preview-shell">
                                            <div class="preview-badge">Live Preview</div>
                                            <div class="preview-name" id="preview-company-name"><?= htmlspecialchars($companyName !== '' ? $companyName : 'Your Company Name', ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="preview-line" id="preview-company-address"><?= htmlspecialchars($companyAddress !== '' ? $companyAddress : 'Company address will appear here.', ENT_QUOTES, 'UTF-8'); ?></div>

                                            <div class="preview-kicker">Billing Details</div>
                                            <div class="preview-grid">
                                                <div class="preview-box">
                                                    <div class="preview-box-label">TIN</div>
                                                    <div class="preview-box-value" id="preview-company-tin"><?= htmlspecialchars($companyTin !== '' ? $companyTin : 'Not set', ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                                <div class="preview-box">
                                                    <div class="preview-box-label">Company Type</div>
                                                    <div class="preview-box-value" id="preview-company-type"><?= htmlspecialchars($companyType !== '' ? $companyType : 'Not set', ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                                <div class="preview-box">
                                                    <div class="preview-box-label">Proprietor / Representative</div>
                                                    <div class="preview-box-value" id="preview-company-proprietor"><?= htmlspecialchars($proprietor !== '' ? $proprietor : 'Not set', ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="theme-card">
                                    <div class="theme-card-head">
                                        <h5 class="theme-card-title">Setup Notes</h5>
                                        <div class="theme-card-subtitle">A few quick reminders to keep billing details consistent.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="tips-list">
                                            <div class="tips-item">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Keep the company name exactly as you want it to appear in official invoices and printouts.</span>
                                            </div>
                                            <div class="tips-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>Use a complete business address so printed invoices remain ready for delivery or filing.</span>
                                            </div>
                                            <div class="tips-item">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                                <span>Set the TIN and company type carefully because they affect invoice presentation and tax reporting screens.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

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
            var bindings = [{
                    inputId: 'company-name',
                    targetIds: ['preview-company-name', 'stat-company-name'],
                    emptyText: 'Your Company Name'
                },
                {
                    inputId: 'company-address',
                    targetIds: ['preview-company-address'],
                    emptyText: 'Company address will appear here.'
                },
                {
                    inputId: 'company-tin',
                    targetIds: ['preview-company-tin', 'stat-company-tin'],
                    emptyText: 'Not set'
                },
                {
                    inputId: 'company-type',
                    targetIds: ['preview-company-type', 'stat-company-type'],
                    emptyText: 'Not set'
                },
                {
                    inputId: 'company-proprietor',
                    targetIds: ['preview-company-proprietor'],
                    emptyText: 'Not set'
                }
            ];

            bindings.forEach(function(binding) {
                var input = document.getElementById(binding.inputId);
                if (!input) {
                    return;
                }

                var targets = binding.targetIds
                    .map(function(id) {
                        return document.getElementById(id);
                    })
                    .filter(Boolean);

                var sync = function() {
                    var value = (input.value || '').trim();
                    var displayValue = value !== '' ? value : binding.emptyText;
                    targets.forEach(function(target) {
                        target.textContent = displayValue;
                    });
                };

                input.addEventListener('input', sync);
                sync();
            });
        })();
    </script>

</body>

</html>