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
    :root {
        --page-content-top-gap: 22px;
    }

    /* keep page content off the nav + footer across screens */
    .content-page .content {
        padding-top: var(--page-content-top-gap);
    }

    @media (max-width: 576px) {
        :root {
            --page-content-top-gap: 18px;
        }
    }

    .footer {
        background: #f8f9fa;
        border-top: 1px solid #e0e0e0;
        font-size: 0.9rem;
    }

    .footer .footer-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem 1rem;
        padding: 0.85rem 0;
    }

    .footer .footer-brand {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        color: #212529;
    }

    .footer .footer-brand a {
        color: inherit;
        text-decoration: none;
    }

    .footer .footer-brand a:hover {
        text-decoration: none;
    }

    .footer .footer-brand span {
        color: #6c757d;
        font-size: 0.85rem;
    }

    .footer .footer-contact {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        color: #495057;
    }

    .footer .footer-contact span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .footer .social-links {
        display: flex;
        gap: 0.4rem;
    }

    .footer .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        font-size: 0.85rem;
        transition: opacity 0.2s ease-in-out;
        position: relative;
    }

    .footer .social-links a:hover {
        opacity: 0.8;
        text-decoration: none;
    }

    .footer .social-links a::after {
        content: attr(data-label);
        position: absolute;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #212529;
        color: #fff;
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 0.7rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.15s ease, transform 0.15s ease;
    }

    .footer .social-links a:hover::after {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    @media (max-width: 576px) {
        .footer .footer-inner {
            justify-content: center;
            text-align: center;
        }

        .footer .footer-contact {
            justify-content: center;
        }
    }
</style>

<footer class="footer mt-3">
    <div class="container-fluid">
        <div class="footer-inner">
            <div class="footer-brand">
                <a href="<?= htmlspecialchars($footerHomeUrl, ENT_QUOTES, 'UTF-8'); ?>" aria-label="BERPS Home">
                    <strong>BERPS</strong>
                </a>
                <span>&copy; <?= date('Y'); ?> All rights reserved.</span>
            </div>
            <div class="footer-contact">
                <span><i class="fa-solid fa-phone"></i>+639 123 235 0149</span>
                <span><i class="fa-solid fa-envelope"></i>admin@softtechservices.net</span>
                <span><i class="fa-solid fa-location-dot"></i>Lower Salazar, Mati City, Davao Oriental</span>
            </div>
            <div class="social-links">
                <a href="https://www.facebook.com/SoftTechMati" target="_blank" rel="noopener" aria-label="Facebook" data-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="mailto:admin@softtechservices.net" target="_blank" rel="noopener" aria-label="Gmail" data-label="Email us"><i class="fa-solid fa-envelope"></i></a>
                <a href="https://www.youtube.com/@SoftTechSolutions" target="_blank" rel="noopener" aria-label="YouTube Channel" data-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= base_url('assets/js/req-bell.js'); ?>"></script>
