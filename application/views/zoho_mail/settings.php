<!DOCTYPE html>
<html lang="en">
<?php include(APPPATH.'views/includes/head.php'); ?>
<body>
<div id="wrapper">
    <?php include(APPPATH.'views/includes/top-nav-bar.php'); ?>
    <?php include(APPPATH.'views/includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="d-flex align-items-center justify-content-between mt-3 mb-3">
                    <h4 class="m-0"><i class="mdi mdi-email-outline mr-1"></i> Zoho Mail Settings</h4>
                    <a href="<?= base_url('ZohoMail/inbox'); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="mdi mdi-inbox-arrow-down"></i> Open Inbox
                    </a>
                </div>

                <?php if ($msg = $this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($msg); ?></div>
                <?php endif; ?>
                <?php if ($msg = $this->session->flashdata('danger')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($msg); ?></div>
                <?php endif; ?>

                <?php
                $a = $account;
                $authType    = $a->auth_type    ?? 'oauth';
                $dc          = $a->data_center  ?? 'com';
                $clientId    = $a->client_id    ?? '';
                $clientSecret= $a->client_secret?? '';
                $redirect    = $a->redirect_uri ?? $default_redirect;
                $scope       = $a->scope        ?? 'ZohoMail.messages.ALL,ZohoMail.accounts.READ,ZohoMail.folders.ALL,ZohoMail.attachments.ALL';
                $status      = $a->status       ?? 'disconnected';
                $primary     = $a->primary_email?? '';
                $accountId   = $a->account_id   ?? '';
                $lastError   = $a->last_error   ?? '';
                ?>

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="header-title">Connection Status</h5>
                        <p class="mb-1">
                            <strong>Status:</strong>
                            <?php if ($status === 'connected'): ?>
                                <span class="badge badge-success">Connected</span>
                            <?php elseif ($status === 'error'): ?>
                                <span class="badge badge-danger">Error</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Disconnected</span>
                            <?php endif; ?>
                        </p>
                        <?php if ($primary): ?>
                            <p class="mb-1"><strong>Account:</strong> <?= htmlspecialchars($primary); ?> <small class="text-muted">(<?= htmlspecialchars($accountId); ?>)</small></p>
                        <?php endif; ?>
                        <?php if ($lastError): ?>
                            <div class="alert alert-warning mt-2"><strong>Last error:</strong> <?= htmlspecialchars($lastError); ?></div>
                        <?php endif; ?>

                        <?php if ($status === 'connected'): ?>
                            <a href="<?= base_url('ZohoMail/disconnect'); ?>" class="btn btn-outline-danger btn-sm mt-2"
                               onclick="return confirm('Disconnect Zoho Mail account?');">
                               <i class="mdi mdi-link-off"></i> Disconnect
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="header-title">Credentials</h5>
                        <p class="text-muted">
                            Get these from <a href="https://api-console.zoho.com" target="_blank">Zoho API Console</a>.
                            Use a <em>Server-based Application</em> for OAuth, or a <em>Self Client</em> if you prefer a permanent token.
                        </p>

                        <form method="post" action="<?= base_url('ZohoMail/saveSettings'); ?>">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Auth Type</label>
                                    <select name="auth_type" class="form-control">
                                        <option value="oauth"       <?= $authType==='oauth'?'selected':''; ?>>OAuth 2.0</option>
                                        <option value="self_client" <?= $authType==='self_client'?'selected':''; ?>>Self-Client (paste token)</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Data Center</label>
                                    <select name="data_center" class="form-control">
                                        <?php foreach (['com'=>'.com (US)','eu'=>'.eu (EU)','in'=>'.in (India)','com.au'=>'.com.au (AU)','com.cn'=>'.com.cn (CN)','jp'=>'.jp (JP)'] as $k=>$lbl): ?>
                                            <option value="<?= $k ?>" <?= $dc===$k?'selected':''; ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Redirect URI <small class="text-muted">(register this in Zoho)</small></label>
                                    <input type="text" name="redirect_uri" class="form-control" value="<?= htmlspecialchars($redirect); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Client ID</label>
                                    <input type="text" name="client_id" class="form-control" value="<?= htmlspecialchars($clientId); ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Client Secret</label>
                                    <input type="text" name="client_secret" class="form-control" value="<?= htmlspecialchars($clientSecret); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Scopes <small class="text-muted">(comma-separated)</small></label>
                                <input type="text" name="scope" class="form-control" value="<?= htmlspecialchars($scope); ?>">
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save Credentials</button>
                            <?php if ($authType === 'oauth' && $clientId): ?>
                                <a href="<?= base_url('ZohoMail/connect'); ?>" class="btn btn-success ml-1"><i class="mdi mdi-link-variant"></i> Connect Zoho</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <?php if ($authType === 'self_client'): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="header-title">Self-Client Refresh Token</h5>
                        <p class="text-muted">
                            From the Zoho API Console &rarr; Self Client &rarr; Generate Code with the same scopes,
                            then exchange the grant code for a refresh token via <code>/oauth/v2/token</code>. Paste the
                            <strong>refresh_token</strong> here.
                        </p>
                        <form method="post" action="<?= base_url('ZohoMail/saveSelfClient'); ?>">
                            <div class="form-group">
                                <label>Refresh Token</label>
                                <input type="text" name="refresh_token" class="form-control" placeholder="1000.xxxxxxxxxxxx.xxxxxxxxxxxx">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-key"></i> Save & Verify</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php include(APPPATH.'views/includes/footer.php'); ?>
    </div>
</div>
<?php include(APPPATH.'views/includes/themecustomizer.php'); ?>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
