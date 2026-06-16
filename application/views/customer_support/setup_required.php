<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h4 class="page-title">Customer Support Setup Required</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard'); ?>">Home</a></li>
                            <li class="breadcrumb-item active">Customer Support</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Setup Required Alert -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center py-5">
                                <i class="mdi mdi-database-alert d-block font-size-64 text-warning mb-4"></i>
                                <h4 class="text-warning mb-3">Customer Support Database Setup Required</h4>
                                
                                <div class="alert alert-warning text-start">
                                    <h5><i class="mdi mdi-alert"></i> Issue Detected</h5>
                                    <p class="mb-2"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="mb-0"><?= htmlspecialchars($solution, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>

                                <div class="alert alert-info text-start">
                                    <h5><i class="mdi mdi-information"></i> Required Actions</h5>
                                    <p>To fix this issue, you need to install the Customer Support database tables:</p>
                                    <ol class="text-start">
                                        <li><strong>Method 1: Use phpMyAdmin</strong>
                                            <ul>
                                                <li>Open phpMyAdmin</li>
                                                <li>Select your database (usually "berps")</li>
                                                <li>Click "Import" tab</li>
                                                <li>Choose the file: <code>database/customer_support_schema.sql</code></li>
                                                <li>Click "Go" to execute</li>
                                            </ul>
                                        </li>
                                        <li><strong>Method 2: Use MySQL Command Line</strong>
                                            <ul>
                                                <li>Open command prompt/terminal</li>
                                                <li>Navigate to your project directory: <code>cd f:\xampp\htdocs\berps</code></li>
                                                <li>Run: <code>mysql -u root -p berps < database/customer_support_schema.sql</code></li>
                                                <li>Enter your MySQL password when prompted</li>
                                            </ul>
                                        </li>
                                        <li><strong>Method 3: Use the Diagnostic Tool</strong>
                                            <ul>
                                                <li>Visit: <a href="<?= base_url('check_support_tables.php'); ?>" target="_blank"><?= base_url('check_support_tables.php'); ?></a></li>
                                                <li>This will show you exactly which tables are missing</li>
                                                <li>Follow the instructions provided</li>
                                            </ul>
                                        </li>
                                    </ol>
                                </div>

                                <div class="alert alert-success text-start">
                                    <h5><i class="mdi mdi-check-circle"></i> What Will Be Created</h5>
                                    <p>The database script will create the following tables:</p>
                                    <ul>
                                        <li><code>support_departments</code> - Department information</li>
                                        <li><code>employee_departments</code> - Employee assignments</li>
                                        <li><code>support_issues</code> - Support tickets/issues</li>
                                        <li><code>support_issue_comments</code> - Issue comments</li>
                                        <li><code>support_notifications</code> - System notifications</li>
                                        <li><code>support_issue_history</code> - Status change tracking</li>
                                        <li><code>support_knowledge_base</code> - Knowledge base articles</li>
                                        <li><code>support_satisfaction_ratings</code> - Customer ratings</li>
                                        <li><code>support_sla_tracking</code> - Service level agreements</li>
                                        <li><code>support_issue_attachments</code> - File attachments</li>
                                        <li><code>support_settings</code> - System settings</li>
                                        <li><code>support_templates</code> - Email templates</li>
                                        <li><code>support_escalation_rules</code> - Escalation rules</li>
                                        <li><code>support_time_tracking</code> - Time tracking</li>
                                        <li><code>support_customer_feedback</code> - Customer feedback</li>
                                        <li><code>support_reports</code> - Saved reports</li>
                                        <li><code>support_audit_log</code> - Audit trail</li>
                                    </ul>
                                    <p class="mb-0 mt-2"><strong>Plus:</strong> Default departments, settings, templates, and escalation rules will be automatically populated.</p>
                                </div>

                                <div class="d-flex justify-content-center gap-3 mt-4">
                                    <a href="<?= base_url('check_support_tables.php'); ?>" target="_blank" class="btn btn-info">
                                        <i class="mdi mdi-database-search"></i> Check Database Status
                                    </a>
                                    <a href="<?= base_url('Page/admin'); ?>" class="btn btn-secondary">
                                        <i class="mdi mdi-arrow-left"></i> Back to Admin Dashboard
                                    </a>
                                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                                        <i class="mdi mdi-refresh"></i> Refresh After Setup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Setup Guide -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="mdi mdi-lightbulb"></i> Quick Setup Guide</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Step 1: Backup Database (Recommended)</h6>
                                    <p>Before running any SQL scripts, it's recommended to backup your current database.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Step 2: Run Schema Script</h6>
                                    <p>Execute the <code>customer_support_schema.sql</code> file to create all required tables.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Step 3: Verify Installation</h6>
                                    <p>Use the diagnostic tool to verify all tables were created successfully.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Step 4: Test System</h6>
                                    <p>Refresh this page to access the Customer Support dashboard.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 30 seconds to check if setup is complete
setInterval(function() {
    // This will automatically refresh the page every 30 seconds
    // Users can also manually click the refresh button
}, 30000);

// Add keyboard shortcut for refresh (Ctrl+R or F5)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
        e.preventDefault();
        location.reload();
    }
});
</script>
