<?php
$footerLevel = strtolower(trim((string) $this->session->userdata('level')));
$footerHomeUrl = base_url();

switch ($footerLevel) {
    case 'system administrator':
    case 'super admin':
        $footerHomeUrl = base_url('Page/superAdmin');
        break;
    case 'admin':
        $footerHomeUrl = base_url('Page/admin');
        break;
    case 'manager':
    case 'pos admin':
        $footerHomeUrl = base_url('Pos/posAdmin');
        break;
    case 'encoder':
    case 'staff':
    case 'account':
        $footerHomeUrl = base_url('Page/staff');
        break;
    case 'cashier':
    case 'pos staff':
        $footerHomeUrl = base_url('Pos/posStaff');
        break;
    case 'client':
        $footerHomeUrl = base_url('Page/clientDashboard');
        break;
    case 'student':
        $footerHomeUrl = base_url('Page/studentsprofile');
        break;
}
?>

<link rel="stylesheet" href="<?= base_url('assets/css/request-bell.css'); ?>">

<style>
    .berps-app-footer__brand a strong {
        color: #4338ca;
    }
    .berps-app-footer__links a[href^="tel:"] i { color: #059669; }
    .berps-app-footer__links a[href^="mailto:"] i { color: #dc2626; }
    .berps-app-footer__links span i.fa-location-dot { color: #2563eb; }
    .berps-app-footer__social a[aria-label*="Facebook"] i { color: #1877f2; }
    .berps-app-footer__social a[aria-label*="YouTube"] i { color: #ff0000; }
    .berps-app-footer__social a[aria-label*="Facebook"]:hover { border-color: #1877f2; background: rgba(24,119,242,.08); }
    .berps-app-footer__social a[aria-label*="YouTube"]:hover { border-color: #ff0000; background: rgba(255,0,0,.08); }
    .berps-app-footer__social a:hover i { color: inherit; }
    .berps-app-footer__social a[aria-label*="Facebook"]:hover i { color: #1877f2; }
    .berps-app-footer__social a[aria-label*="YouTube"]:hover i { color: #ff0000; }
</style>

<footer class="footer berps-app-footer">
    <div class="container-fluid">
        <div class="berps-app-footer__inner">
            <div class="berps-app-footer__brand">
                <a href="<?= htmlspecialchars($footerHomeUrl, ENT_QUOTES, 'UTF-8'); ?>" aria-label="BERPS Home">
                    <strong>BERPS</strong>
                </a>
                <span>&copy; <?= date('Y'); ?> SoftTech Solutions</span>
            </div>
            <div class="berps-app-footer__links" role="group" aria-label="Contact information">
                <a href="tel:+6391232350149"><i class="fa-solid fa-phone" aria-hidden="true"></i><span>+639 123 235 0149</span></a>
                <a href="mailto:admin@softtechservices.net"><i class="fa-solid fa-envelope" aria-hidden="true"></i><span>admin@softtechservices.net</span></a>
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>Lower Salazar, Mati City, Davao Oriental</span>
            </div>
            <div class="berps-app-footer__social" role="group" aria-label="Social links">
                <a href="https://www.facebook.com/SoftTechMati" target="_blank" rel="noopener noreferrer" aria-label="BERPS on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                <a href="https://www.youtube.com/@SoftTechSolutions" target="_blank" rel="noopener noreferrer" aria-label="SoftTech Solutions on YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= base_url('assets/js/req-bell.js'); ?>"></script>
