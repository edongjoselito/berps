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
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h4 class="card-title mb-2"><?= htmlspecialchars($page_title ?? 'Settings', ENT_QUOTES, 'UTF-8'); ?></h4>
                                    <div class="alert alert-warning mb-0" role="alert">
                                        <?= htmlspecialchars($message ?? 'This settings area is not available.', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include('includes/footer.php'); ?>
            </div>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
