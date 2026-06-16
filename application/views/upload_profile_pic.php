<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <!-- Begin page -->
    <div id="wrapper">

        <!-- Topbar Start -->
        <?php include('includes/top-nav-bar.php'); ?>
        <!-- end Topbar -->
        <!-- ========== Left Sidebar Start ========== -->

        <!-- Lef Side bar -->
        <?php include('includes/sidebar.php'); ?>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <div class="container-fluid upload-profile-page">
                    <style>
                        .upload-profile-page .breadcrumb {
                            background: transparent;
                            padding: 0;
                            margin-bottom: 1.5rem;
                        }

                        .upload-profile-page .header-card {
                            border: none;
                            border-radius: 18px;
                            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
                            overflow: hidden;
                            margin-bottom: 1.5rem;
                        }

                        .upload-profile-page .header-card .card-body {
                            /* Removed gradient */
                            background: #4c6ef5;
                            /* flat primary color */
                            padding: 32px 32px;
                        }

                        .upload-profile-page .header-card h3 {
                            margin: 0;
                            font-size: 1.6rem;
                            font-weight: 600;
                        }

                        .upload-profile-page .upload-card {
                            border: none;
                            border-radius: 16px;
                            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.07);
                        }

                        .upload-profile-page .upload-card .card-body {
                            padding: 30px 34px;
                        }

                        .upload-profile-page .guidelines {
                            font-size: 0.9rem;
                            color: #6c757d;
                            margin-top: 8px;
                        }

                        .upload-profile-page .guidelines span {
                            color: #e03131;
                            font-weight: 600;
                        }
                    </style>

                    <div class="row">
                        <div class="col-12">
                            <!-- You can put breadcrumb or title here if needed -->
                        </div>
                    </div>

                    <div class="card header-card">

                    </div>

                    <?php if ($this->session->flashdata('msg')): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('msg'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="card upload-card">
                        <div class="card-body">
                            <form action="<?= site_url('Page/uploadProfPic'); ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden"
                                    name="StudentNumber"
                                    value="<?= htmlspecialchars($this->session->userdata('username'), ENT_QUOTES, 'UTF-8'); ?>"
                                    readonly>
                                <div class="form-group">
                                    <label for="profile_picture" class="font-weight-semibold">Profile Picture</label>
                                    <input type="file" class="form-control" id="profile_picture" name="nonoy" accept="image/*" required>
                                    <p class="guidelines mb-0">
                                        Limit the file size to <span>2MB</span>. Recommended dimensions are <span>215px × 215px</span>.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" name="submit" class="btn btn-primary">
                                        <i class="mdi mdi-upload mr-1"></i> Upload
                                    </button>

                                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php include('includes/footer.php'); ?>
                </div>
            </div>
        </div>
        <!-- END wrapper -->


        <!-- Right Sidebar -->
        <?php include('includes/themecustomizer.php'); ?>
        <!-- /Right-bar -->

        <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.js"></script>
        <script src="<?= base_url(); ?>assets/js/pages/calendar.init.js"></script>
        <script src="<?= base_url(); ?>assets/js/pages/jquery.chat.js"></script>
        <script src="<?= base_url(); ?>assets/js/pages/jquery.todo.js"></script>
        <script src="<?= base_url(); ?>assets/libs/morris-js/morris.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/raphael/raphael.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
        <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>
        <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>

    </div>
</body>

</html>