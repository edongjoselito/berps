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

                <style>
                    /* Hero Banner */
                    .client-profile-page .ri-hero {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 16px;
                        padding: 28px 24px;
                        margin: 0 0 22px;
                        border-radius: 16px;
                        background: #0e7490;
                        box-shadow: 0 8px 32px rgba(14, 116, 144, 0.25);
                        position: relative;
                        overflow: hidden;
                    }

                    .client-profile-page .ri-hero::before {
                        content: '';
                        position: absolute;
                        top: -50%;
                        right: -10%;
                        width: 400px;
                        height: 400px;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.06);
                        pointer-events: none;
                    }

                    .client-profile-page .ri-hero::after {
                        content: '';
                        position: absolute;
                        bottom: -60%;
                        right: 15%;
                        width: 300px;
                        height: 300px;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.04);
                        pointer-events: none;
                    }

                    .client-profile-page .ri-hero__content {
                        position: relative;
                        z-index: 1;
                    }

                    .client-profile-page .ri-hero__eyebrow {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        margin-bottom: 8px;
                        color: rgba(255, 255, 255, 0.85);
                        font-size: 0.78rem;
                        font-weight: 600;
                        letter-spacing: 0.04em;
                    }

                    .client-profile-page .ri-hero__eyebrow i {
                        font-size: 1rem;
                    }

                    .client-profile-page .ri-hero__title {
                        margin: 0 0 4px 0;
                        color: #fff;
                        font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                        font-weight: 800;
                        line-height: 1.15;
                        letter-spacing: -0.02em;
                        font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                    }

                    .client-profile-page .ri-hero__subtitle {
                        margin: 0;
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 0.88rem;
                        max-width: 520px;
                    }

                    .client-profile-page .ri-hero__actions {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 10px;
                        position: relative;
                        z-index: 1;
                    }

                    .client-profile-page .ri-hero-btn {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 8px 16px;
                        border-radius: 10px;
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        background: rgba(255, 255, 255, 0.15);
                        color: #fff;
                        font-size: 0.82rem;
                        font-weight: 600;
                        text-decoration: none;
                        cursor: pointer;
                        transition: all 0.18s ease;
                    }

                    .client-profile-page .ri-hero-btn:hover,
                    .client-profile-page .ri-hero-btn:focus {
                        background: rgba(255, 255, 255, 0.25);
                        border-color: rgba(255, 255, 255, 0.5);
                        color: #fff;
                        text-decoration: none;
                        transform: translateY(-1px);
                    }

                    .client-profile-page .memo-bounce {
                        display: inline-block;
                        animation: memo-bounce 2s ease-in-out infinite;
                    }

                    @keyframes memo-bounce {
                        0%, 70%, 100% { transform: translateY(0); }
                        15% { transform: translateY(-10px); }
                        30% { transform: translateY(0); }
                        45% { transform: translateY(-5px); }
                        60% { transform: translateY(0); }
                    }

                    .client-profile-page .berps-form-card {
                        border-top: 3px solid #0e7490;
                    }

                    @media (max-width: 767px) {
                        .client-profile-page .ri-hero,
                        .client-profile-page .ri-hero__actions {
                            flex-direction: column;
                            align-items: stretch;
                        }

                        .client-profile-page .ri-hero {
                            padding: 20px;
                        }

                        .client-profile-page .ri-hero-btn {
                            flex: 1 1 auto;
                            justify-content: center;
                        }
                    }
                </style>

                <div class="ri-hero">
                    <div class="ri-hero__content">
                        <div class="ri-hero__eyebrow">
                            <i class="mdi mdi-lifebuoy"></i>
                            Client Portal
                        </div>
                        <h1 class="ri-hero__title">Report an Issue <span class="memo-bounce">📝</span></h1>
                        <p class="ri-hero__subtitle">Give the support team enough context to investigate and respond quickly.</p>
                    </div>
                    <div class="ri-hero__actions">
                        <a class="ri-hero-btn" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-arrow-left"></i>
                            <span>Back to My Tickets</span>
                        </a>
                    </div>
                </div>

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
