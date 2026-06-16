<?php
$supportProjects = isset($supportProjects) && is_array($supportProjects) ? $supportProjects : array();
$supportDepartmentOptions = isset($supportDepartmentOptions) && is_array($supportDepartmentOptions) ? $supportDepartmentOptions : array();
$backUrl = base_url('Page/clientMyTickets');
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
            <div class="container-fluid client-profile-page">
                <style>
                    .client-profile-page {
                        --bg: #f5f7fb;
                        --surface: rgba(255, 255, 255, 0.92);
                        --surface-2: #ffffff;
                        --line: #e7ecf3;
                        --text: #122033;
                        --text-soft: #5e7188;
                        --text-faint: #8ea0b5;
                        --primary: #2563eb;
                        --primary-soft: #eaf2ff;
                        --danger: #e11d48;
                        --danger-soft: #fff1f2;
                        --shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
                        --radius-xl: 22px;
                        --radius-lg: 18px;
                        font-family: 'Inter', 'Poppins', 'Segoe UI', Arial, sans-serif;
                        background:
                            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                            radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                            linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                        min-height: 100vh;
                        padding-bottom: 24px;
                    }
                    .client-profile-page .cp-header { margin: 24px 0 22px; display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; }
                    .client-profile-page .cp-eyebrow { display:inline-flex; align-items:center; padding:7px 12px; border-radius:999px; background:rgba(37,99,235,.08); color:#1d4ed8; font-size:.76rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:10px; }
                    .client-profile-page .cp-title { margin:0; color:var(--text); font-size:clamp(2rem,3vw,2.7rem); line-height:1.05; font-weight:800; }
                    .client-profile-page .cp-subtitle { margin:12px 0 0; color:var(--text-soft); font-size:1rem; max-width:780px; }
                    .client-profile-page .btn-soft, .client-profile-page .btn-solid {
                        display:inline-flex; align-items:center; gap:10px; border-radius:18px; padding:12px 20px; font-weight:700; text-decoration:none;
                        box-shadow:0 10px 26px rgba(15,23,42,.04); border:1px solid var(--line); background:rgba(255,255,255,.9); color:var(--text);
                    }
                    .client-profile-page .btn-solid { background:linear-gradient(135deg,var(--primary),#1d4ed8); color:#fff; border-color:transparent; }
                    .client-profile-page .panel-card { background:var(--surface); border:1px solid rgba(255,255,255,.75); border-radius:var(--radius-xl); box-shadow:var(--shadow); overflow:hidden; }
                    .client-profile-page .panel-header { padding:24px 28px 18px; border-bottom:1px solid var(--line); }
                    .client-profile-page .panel-title { margin:0; color:var(--text); font-size:1.45rem; font-weight:800; }
                    .client-profile-page .panel-subtitle { margin-top:8px; color:var(--text-soft); font-size:.98rem; }
                    .client-profile-page .panel-body { padding:22px 28px 28px; }
                    .client-profile-page .form-grid { display:grid; grid-template-columns:repeat(12, minmax(0, 1fr)); gap:18px; }
                    .client-profile-page .col-4 { grid-column:span 4; }
                    .client-profile-page .col-6 { grid-column:span 6; }
                    .client-profile-page .col-8 { grid-column:span 8; }
                    .client-profile-page .col-12 { grid-column:span 12; }
                    .client-profile-page label { display:block; margin-bottom:8px; color:var(--text); font-weight:700; }
                    .client-profile-page .form-control, .client-profile-page .form-control-file {
                        width:100%;
                        min-height: 56px;
                        border:1px solid var(--line);
                        border-radius:16px;
                        padding:16px 18px;
                        background:#fff;
                        color:var(--text);
                        line-height:1.2;
                    }
                    .client-profile-page select.form-control {
                        height: 56px;
                        padding-top: 0;
                        padding-bottom: 0;
                        padding-right: 48px;
                    }
                    .client-profile-page input.form-control {
                        height: 56px;
                    }
                    .client-profile-page textarea.form-control { min-height:160px; resize:vertical; }
                    .client-profile-page .form-hint { margin-top:8px; color:var(--text-soft); font-size:.88rem; }
                    .client-profile-page .action-row { display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; margin-top:20px; }
                    .client-profile-page .alert { border:none; border-radius:18px; box-shadow:0 10px 26px rgba(15,23,42,.04); }
                    @media (max-width: 991px) {
                        .client-profile-page .col-4,
                        .client-profile-page .col-6,
                        .client-profile-page .col-8 { grid-column:span 12; }
                    }
                </style>

                <div class="cp-header">
                    <div>
                        <div class="cp-eyebrow">Client Portal</div>
                        <h1 class="cp-title">Report an Issue</h1>
                        <!-- <p class="cp-subtitle">Submit a complete support request with project reference, images, and supporting links so the team can respond faster.</p> -->
                    </div>
                    <a class="btn-soft" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-arrow-left"></i>Back to My Tickets</a>
                </div>

                <?php if ($this->session->flashdata('danger')): ?>
                    <div class="alert alert-danger"><?= $this->session->flashdata('danger'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning"><?= $this->session->flashdata('warning'); ?></div>
                <?php endif; ?>

                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Ticket Submission Form</h2>
                        <div class="panel-subtitle">Complete the details below and the support team will review your concern shortly.</div>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="<?= base_url('Page/submitClientSupportIssue'); ?>" enctype="multipart/form-data">
                            <div class="form-grid">
                                <div class="col-12">
                                    <label for="department">Department</label>
                                    <select class="form-control" id="department" name="department" required>
                                        <option value="">Select department</option>
                                        <?php foreach ($supportDepartmentOptions as $value => $label): ?>
                                            <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="project_id">Related Project</label>
                                    <select class="form-control" id="project_id" name="project_id">
                                        <?php if (empty($supportProjects)): ?>
                                            <option value="0">General</option>
                                        <?php endif; ?>
                                        <?php foreach ($supportProjects as $project): ?>
                                            <option value="<?= (int) ($project->projectID ?? 0); ?>"><?= htmlspecialchars((string) ($project->projectDescription ?? 'Project'), ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="title">Subject</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="col-12">
                                    <label for="description">Report Details</label>
                                    <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="attachments">Attachment</label>
                                    <input type="file" class="form-control-file" id="attachments" name="attachments[]" multiple accept="image/*">
                                    <div class="form-hint">You may upload multiple images. Maximum of 2 MB per file.</div>
                                </div>
                                <div class="col-12">
                                    <label for="reference_link">Reference Link</label>
                                    <input type="url" class="form-control" id="reference_link" name="reference_link" placeholder="https://drive.google.com/...">
                                    <div class="form-hint">Use this for Google Drive or other external reference files.</div>
                                </div>
                            </div>

                            <div class="action-row">
                                <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn-soft">Cancel</a>
                                <button type="submit" class="btn-solid" id="submitBtn"><i class="fas fa-paper-plane"></i>Submit Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</div>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
(function() {
    const form = document.querySelector('form[method="post"]');
    const submitBtn = document.getElementById('submitBtn');
    let isSubmitting = false;
    const SESSION_KEY = 'lastTicketSubmission';

    function getFormFingerprint() {
        const dept = document.getElementById('department')?.value || '';
        const project = document.getElementById('project_id')?.value || '';
        const title = document.getElementById('title')?.value?.trim() || '';
        const desc = document.getElementById('description')?.value?.trim().substring(0, 100) || '';
        return dept + '|' + project + '|' + title + '|' + desc;
    }

    function checkRecentDuplicate() {
        const lastSubmission = sessionStorage.getItem(SESSION_KEY);
        if (!lastSubmission) return false;
        try {
            const data = JSON.parse(lastSubmission);
            const now = Date.now();
            const fingerprint = getFormFingerprint();
            // Check if same form data submitted within last 30 seconds
            if (data.fingerprint === fingerprint && (now - data.timestamp) < 30000) {
                return true;
            }
        } catch (e) {}
        return false;
    }

    function recordSubmission() {
        sessionStorage.setItem(SESSION_KEY, JSON.stringify({
            fingerprint: getFormFingerprint(),
            timestamp: Date.now()
        }));
    }

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }

            if (checkRecentDuplicate()) {
                e.preventDefault();
                alert('Please wait a moment before submitting another ticket with the same details.');
                return false;
            }

            isSubmitting = true;
            recordSubmission();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Submitting...';
        });
    }
})();
</script>
</body>
</html>
