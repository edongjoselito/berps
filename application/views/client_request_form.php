<?php
$client = isset($client) ? $client : null;
$projects = isset($projects) && is_array($projects) ? $projects : [];
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
                <div class="container-fluid client-request-page">

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
                        .client-request-page {
                            max-width: 900px;
                            margin: 0 auto;
                            padding: 30px 20px;
                        }

                        .client-request-page .entry-header {
                            margin-bottom: 28px;
                        }

                        .client-request-page .entry-title {
                            font-size: 1.75rem;
                            font-weight: 800;
                            color: var(--text);
                            margin-bottom: 8px;
                        }

                        .client-request-page .entry-breadcrumb {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            font-size: 0.85rem;
                            color: var(--text-soft);
                        }

                        .client-request-page .entry-breadcrumb a {
                            color: var(--primary);
                            text-decoration: none;
                            font-weight: 600;
                        }

                        .client-request-page .entry-breadcrumb a:hover {
                            text-decoration: underline;
                        }

                        .client-request-page .entry-breadcrumb .separator {
                            color: var(--text-faint);
                        }

                        .client-request-page .entry-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-xl);
                            box-shadow: var(--shadow-soft);
                            padding: 32px 36px;
                        }

                        .client-request-page .form-group {
                            margin-bottom: 22px;
                        }

                        .client-request-page .form-label {
                            font-size: 0.82rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            color: var(--text-faint);
                            margin-bottom: 8px;
                        }

                        .client-request-page .form-control {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-md);
                            padding: 12px 16px;
                            font-size: 0.95rem;
                            background: var(--surface-soft);
                            transition: all 0.18s ease;
                        }

                        .client-request-page .form-control:focus {
                            border-color: var(--primary);
                            background: var(--surface);
                            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                        }

                        .client-request-page textarea.form-control {
                            resize: vertical;
                            min-height: 120px;
                        }

                        .client-request-page .btn-primary {
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            border: none;
                            color: #fff;
                            font-weight: 700;
                            padding: 12px 28px;
                            border-radius: var(--radius-md);
                            transition: all 0.18s ease;
                        }

                        .client-request-page .btn-primary:hover {
                            transform: translateY(-1px);
                            box-shadow: var(--shadow);
                        }

                        .client-request-page .btn-secondary {
                            background: var(--surface-soft);
                            border: 1px solid var(--line);
                            color: var(--text);
                            font-weight: 600;
                            padding: 12px 24px;
                            border-radius: var(--radius-md);
                            transition: all 0.18s ease;
                        }

                        .client-request-page .btn-secondary:hover {
                            background: var(--surface);
                            border-color: var(--text-soft);
                        }

                        .client-request-page .form-actions {
                            display: flex;
                            gap: 12px;
                            margin-top: 28px;
                        }

                        .client-request-page .info-box {
                            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(99, 102, 241, 0.06));
                            border: 1px solid rgba(59, 130, 246, 0.2);
                            border-radius: var(--radius-lg);
                            padding: 18px 22px;
                            margin-bottom: 26px;
                        }

                        .client-request-page .info-box-title {
                            font-size: 0.9rem;
                            font-weight: 700;
                            color: var(--primary);
                            margin-bottom: 6px;
                        }

                        .client-request-page .info-box-text {
                            font-size: 0.85rem;
                            color: var(--text-soft);
                            line-height: 1.5;
                        }
                    </style>

                    <div class="entry-header">
                        <h1 class="entry-title">Submit Request</h1>
                        <div class="entry-breadcrumb">
                            <a href="<?= base_url('Page/clientDashboard'); ?>">Dashboard</a>
                            <span class="separator">/</span>
                            <span>Submit Request</span>
                        </div>
                    </div>

                    <div class="entry-card">
                        <div class="info-box">
                            <div class="info-box-title">Submit an Issue or Request</div>
                            <div class="info-box-text">
                                Use this form to submit issues, requests, or feedback related to your projects. Your request will be assigned to our team for review and action.
                            </div>
                        </div>

                        <form method="POST" action="<?= base_url('Page/saveClientRequest'); ?>">
                            <div class="form-group">
                                <label class="form-label" for="projectID">Project *</label>
                                <select class="form-control" id="projectID" name="projectID" required>
                                    <option value="">Select a project</option>
                                    <?php if (!empty($projects)): ?>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?= (int) $project->projectID; ?>">
                                                <?= htmlspecialchars($project->projectDescription ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No projects available</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="priority">Priority</label>
                                <select class="form-control" id="priority" name="priority">
                                    <option value="Normal" selected>Normal</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="task">Request Details *</label>
                                <textarea class="form-control" id="task" name="task" rows="6" required placeholder="Describe your request, issue, or feedback in detail..."></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Submit Request
                                </button>
                                <a href="<?= base_url('Page/clientDashboard'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

<?php include('includes/themecustomizer.php'); ?>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>

</html>
