<head>
    <meta charset="utf-8" />
    <title>BERPS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="BERPS business operations and resource planning platform" name="description" />
    <meta content="BERPS" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php include_once __DIR__ . '/page-experience.php'; ?>
    <?php
    $pwaUserId = trim((string) $this->session->userdata('user_id'));
    if ($pwaUserId === '' || $pwaUserId === '0') {
        $pwaUserId = trim((string) $this->session->userdata('username'));
    }
    $pwaUserKey = $this->session->userdata('logged_in') === TRUE && $pwaUserId !== ''
        ? (int) $this->session->userdata('settingsID') . '-' . $pwaUserId
        : '';
    ?>
    <meta name="berps-base-url" content="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="berps-offline-user" content="<?= htmlspecialchars($pwaUserKey, ENT_QUOTES, 'UTF-8'); ?>">
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url(); ?>assets/images/favicon.png">
    <meta name="theme-color" content="#1f5bc6">
    <link rel="manifest" href="<?= base_url('manifest.webmanifest?v=2'); ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/icons/berps-192.png'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/pwa.css?v=1'); ?>">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Plugins css-->
    <link href="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/libs/select2/select2.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="<?= base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
    <link href="<?= base_url(); ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-stylesheet" />

    <!-- third party css -->
    <link href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/libs/datatables/select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="<?= base_url(); ?>assets/css/fonts.css?v=20260722-8" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/css/berps-design-system.css?v=20261005-1" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/css/phosphor-icons-custom.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/css/account-modals.css?v=20260722-1" rel="stylesheet" type="text/css" />
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="<?= base_url(); ?>assets/js/berps-ui.js?v=20260722-7" defer></script>
    <script src="<?= base_url(); ?>assets/js/account-modals.js?v=20260722-2" defer></script>
    <script src="<?= base_url('assets/js/pwa.js?v=1'); ?>" defer></script>
</head>
