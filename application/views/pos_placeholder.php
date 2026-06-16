<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid pos-staff-dashboard pb-4">
                    <style>
                        .pos-staff-dashboard .page-title-box {
                            padding: 6px 0 8px;
                            margin: 0 0 12px;
                        }

                        .pos-staff-dashboard .page-title {
                            font-weight: 700;
                            letter-spacing: -0.2px;
                            margin-bottom: 4px;
                        }

                        .pos-staff-dashboard .page-subtitle {
                            margin-top: 2px;
                        }

                        .pos-staff-dashboard .card-placeholder {
                            border: 1px solid #e5e7eb;
                            border-radius: 12px;
                            background: #fff;
                            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
                            padding: 20px;
                        }

                        .pos-staff-dashboard .card-placeholder p {
                            margin-bottom: 0;
                            color: #4b5563;
                        }
                    </style>

                    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <h4 class="page-title mb-0"><?= htmlspecialchars($page_title ?? 'POS', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <?php if (!empty($page_subtitle)): ?>
                                <p class="text-muted page-subtitle mb-0"><?= htmlspecialchars($page_subtitle, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-placeholder">
                        <p>This section is ready for its POS implementation.</p>
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
