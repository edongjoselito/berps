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
            <div class="container-fluid client-profile-page berps-form-page berps-page">

                <header class="berps-page-header">
                    <div class="berps-page-header__content">
                        <span class="berps-page-header__eyebrow">Client portal</span>
                        <h1 class="berps-page-title">Report an Issue</h1>
                        <p class="berps-page-subtitle">Give the support team enough context to investigate and respond quickly.</p>
                    </div>
                    <div class="berps-page-header__actions">
                        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>Back to My Tickets</a>
                    </div>
                </header>

                <?php if ($this->session->flashdata('danger')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning"><?= htmlspecialchars((string) $this->session->flashdata('warning'), ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <div class="berps-form-card">
                    <div class="berps-form-card__header">
                        <div>
                            <h2 class="berps-form-card__title">Ticket submission form</h2>
                            <p class="berps-form-card__copy">Complete the details below and the support team will review your concern shortly.</p>
                        </div>
                    </div>
                    <div class="berps-form-card__body">
                        <form method="post" action="<?= base_url('Page/submitClientSupportIssue'); ?>" enctype="multipart/form-data">
                            <div class="form-grid">
                                <div class="col-12">
                                    <label class="berps-required" for="department">Department</label>
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
                                    <label class="berps-required" for="title">Subject</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="col-12">
                                    <label class="berps-required" for="description">Report Details</label>
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

                            <div class="berps-form-actions">
                                <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-paper-plane mr-1" aria-hidden="true"></i>Submit Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php include('includes/footer.php'); ?>
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
