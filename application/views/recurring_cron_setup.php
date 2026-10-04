<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-2">Recurring Invoice Cron Job</h4>
                                    <p class="text-muted">
                                        The recurring invoice generator also runs automatically when staff use the system.
                                        Add the cron job below on your production server so it keeps running on schedule even
                                        when nobody is logged in. Once a day is enough (the command below runs at 6:00 AM);
                                        running it more often is harmless.
                                    </p>

                                    <h5 class="mt-4">Endpoint</h5>
                                    <p>The URL the cron job calls:</p>
                                    <pre class="bg-light p-2 border rounded"><code><?= htmlspecialchars($endpoint_url, ENT_QUOTES, 'UTF-8'); ?></code></pre>

                                    <h5 class="mt-4">Cron commands (production)</h5>

                                    <p class="mb-1"><strong>Option 1 — URL cron</strong> (works on any host):</p>
                                    <?php if (!empty($cron_command)): ?>
                                        <pre class="bg-light p-2 border rounded"><code><?= htmlspecialchars($cron_command, ENT_QUOTES, 'UTF-8'); ?></code></pre>
                                        <p class="text-danger small mb-0">
                                            Keep this command private — the key authorizes invoice generation.
                                        </p>
                                    <?php else: ?>
                                        <p class="mb-0">
                                            <a class="btn btn-sm btn-outline-primary"
                                               href="<?= site_url('Page/recurringCronSetup'); ?>?show_cron=1">
                                                Show cron command (includes secret key)
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <p class="mb-1 mt-4"><strong>Option 2 — CLI cron</strong> (no key needed; use your server's PHP path):</p>
                                    <pre class="bg-light p-2 border rounded"><code>0 6 * * * <?= htmlspecialchars($cli_command, ENT_QUOTES, 'UTF-8'); ?></code></pre>

                                    <div class="alert alert-info mt-4 mb-0">
                                        <strong>Notes for production:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>The key is generated per-installation, so visit this same page on the production server to get its own command — the local key will not work there.</li>
                                            <li>The endpoint returns a JSON summary of what it generated, e.g. <code>{"status":"ok","generatedCount":2}</code>.</li>
                                            <li>Invoices are dated on their schedule date; running the cron early in the morning prepares everything due within each template's configured window.</li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

</body>
</html>
