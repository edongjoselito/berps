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
                    <h4 class="m-0"><i class="mdi mdi-pencil-plus"></i> Compose Message
                        <small class="text-muted ml-2">from <?= htmlspecialchars($account->primary_email ?? ''); ?></small>
                    </h4>
                    <a href="<?= base_url('ZohoMail/inbox'); ?>" class="btn btn-sm btn-outline-secondary"><i class="mdi mdi-arrow-left"></i> Back</a>
                </div>

                <?php if ($msg = $this->session->flashdata('danger')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($msg); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" action="<?= base_url('ZohoMail/send'); ?>" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>To</label>
                                <input type="text" name="toAddress" class="form-control" required value="<?= htmlspecialchars($prefill['to'] ?? ''); ?>" placeholder="recipient@domain.com (comma-separated)">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Cc</label>
                                    <input type="text" name="ccAddress" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Bcc</label>
                                    <input type="text" name="bccAddress" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" class="form-control" required value="<?= htmlspecialchars($prefill['subject'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Message (HTML)</label>
                                <textarea name="content" class="form-control" rows="12" required><?= htmlspecialchars($prefill['body'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Attachments</label>
                                <input type="file" name="attachments[]" class="form-control-file" multiple>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-send"></i> Send</button>
                        </form>
                    </div>
                </div>

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
