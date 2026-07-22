<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e8f1fb">

    <title>BERPS - Business Resource Planning System</title>

    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-sm1.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/images/logo-sm1.png'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/berps-design-system.css'); ?>?v=20260722-5">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome-free/css/all.min.css'); ?>">

    <style>
        :root {
            --bg: var(--berps-bg);
            --panel: var(--berps-surface);
            --bg-card: var(--berps-surface);
            --border: var(--berps-border);
            --primary: var(--berps-primary);
            --primary-dark: var(--berps-primary-hover);
            --primary-light: #4b7fd7;
            --text: var(--berps-text);
            --muted: var(--berps-text-muted);
            --error: var(--berps-danger);
            --success: var(--berps-success);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            background: var(--berps-surface);
            color: var(--text);
            line-height: 1.6;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            border-bottom: 1px solid var(--border);
            padding: 12px clamp(20px, 4vw, 56px);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text);
        }

        .navbar-brand img {
            width: 44px;
            height: 44px;
        }

        .navbar-brand span {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .navbar-nav {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .navbar-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            padding: 0;
            border: 1px solid var(--border);
            border-radius: var(--berps-radius-md);
            background: white;
            color: var(--text);
            font-size: 20px;
            cursor: pointer;
        }

        .navbar-nav a {
            text-decoration: none;
            color: var(--muted);
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-nav a:hover {
            color: var(--primary);
        }

        .btn-nav {
            padding: 9px 18px;
            border-radius: var(--berps-radius-md);
            background: var(--primary);
            color: white !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-nav:hover {
            background: var(--primary-dark);
        }

        .btn-nav-secondary {
            padding: 9px 18px;
            border-radius: var(--berps-radius-md);
            background: transparent;
            color: var(--primary) !important;
            font-weight: 600;
            border: 1px solid var(--primary);
            transition: all 0.3s ease;
        }

        .btn-nav-secondary:hover {
            background: var(--primary-soft);
            color: var(--primary-dark) !important;
        }

        /* Hero Section */
        .hero {
            padding: 156px 32px 96px;
            text-align: center;
            position: relative;
            overflow: hidden;
            background: var(--berps-surface-soft);
            border-bottom: 1px solid var(--border);
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: clamp(38px, 5vw, 60px);
            font-weight: 800;
            margin-bottom: 24px;
            color: var(--text);
            letter-spacing: -0.045em;
            line-height: 1.08;
        }

        .hero-label {
            margin-bottom: 18px;
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .hero p {
            font-size: clamp(17px, 2vw, 20px);
            color: var(--muted);
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 14px 26px;
            border-radius: var(--berps-radius-md);
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary-soft);
            color: var(--primary-dark);
        }

        /* Features Section */
        .features {
            padding: 88px 32px;
            background: white;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 44px;
        }

        .section-title h2 {
            font-size: clamp(30px, 4vw, 42px);
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text);
        }

        .section-title p {
            font-size: 18px;
            color: var(--muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: var(--bg-card);
            border-radius: var(--berps-radius-lg);
            padding: 28px;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
        }

        .feature-card:hover {
            border-color: var(--berps-primary-border);
            box-shadow: var(--berps-shadow-sm);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--berps-radius-md);
            background: var(--berps-primary-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text);
        }

        .feature-card p {
            color: var(--muted);
            font-size: 15px;
        }

        .feature-list {
            display: grid;
            gap: 10px;
            margin: 20px 0 0;
            padding: 18px 0 0;
            border-top: 1px solid var(--border);
            list-style: none;
            color: var(--text);
            font-size: 14px;
            font-weight: 600;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .feature-list i {
            color: var(--success);
        }

        /* CTA Section */
        .cta {
            padding: 80px 32px;
            background: #17243a;
            text-align: center;
            color: white;
        }

        .cta h2 {
            font-size: clamp(32px, 4vw, 48px);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 18px;
            margin-bottom: 32px;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Login Modal */
        .login-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-modal.active {
            display: flex;
        }

        .login-modal-content {
            background: white;
            border-radius: var(--berps-radius-lg);
            padding: 36px;
            max-width: 450px;
            width: 100%;
            box-shadow: var(--berps-shadow-lg);
            border: 1px solid var(--border);
            position: relative;
        }

        .login-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--muted);
            transition: color 0.3s ease;
        }

        .login-modal-close:hover {
            color: var(--primary);
        }

        .auth-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
            color: var(--text);
        }

        .auth-card p {
            color: var(--muted);
            margin-bottom: 24px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border-radius: var(--berps-radius-md);
            border: 1px solid var(--border);
            background: #f7f9fc;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(27, 94, 214, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            border-radius: var(--berps-radius-md);
            border: none;
            background: var(--primary);
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        .auth-divider {
            text-align: center;
            margin: 32px 0;
            position: relative;
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            right: 0;
            height: 1px;
            background: var(--border);
        }

        .auth-divider span {
            background: white;
            padding: 0 16px;
            color: var(--muted);
            font-size: 14px;
            position: relative;
        }

        .alert {
            padding: 16px;
            border-radius: var(--berps-radius-md);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: rgba(198, 40, 40, 0.1);
            border: 1px solid rgba(198, 40, 40, 0.2);
            color: var(--error);
        }

        .alert-success {
            background: rgba(46, 125, 50, 0.1);
            border: 1px solid rgba(46, 125, 50, 0.2);
            color: var(--success);
        }

        /* Footer */
        .footer {
            background: #101a2a;
            color: white;
            padding: 26px 32px;
            text-align: center;
        }

        .footer p {
            opacity: 0.7;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 12px 20px;
            }

            .navbar-nav {
                display: none;
                position: absolute;
                top: calc(100% + 1px);
                right: 0;
                left: 0;
                align-items: stretch;
                flex-direction: column;
                gap: 8px;
                padding: 16px 20px 20px;
                border-bottom: 1px solid var(--border);
                background: white;
                box-shadow: var(--berps-shadow-md);
            }

            .navbar-nav.is-open {
                display: flex;
            }

            .navbar-nav a {
                justify-content: center;
                min-height: 42px;
                padding: 9px 16px;
            }

            .navbar-toggle {
                display: inline-flex;
            }

            .hero {
                padding: 120px 20px 60px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-hero {
                width: 100%;
            }

            .auth-container {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="#" class="navbar-brand">
            <img src="<?= base_url('assets/images/logo-sm1.png'); ?>" alt="BERPS">
            <span>BERPS</span>
        </a>
        <button class="navbar-toggle" type="button" id="navbarToggle" aria-controls="publicNavigation" aria-expanded="false" aria-label="Open navigation">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
        <div class="navbar-nav" id="publicNavigation">
            <a href="#features"><i class="fas fa-star"></i> Features</a>
            <a href="<?= site_url('Login/signup_page'); ?>" class="btn-nav-secondary"><i class="fas fa-user-plus"></i> Sign Up</a>
            <a href="#" class="btn-nav" id="loginBtn"><i class="fas fa-sign-in-alt"></i> Login</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <?php if ($this->session->flashdata('msg')): ?>
                <div class="alert alert-error" style="max-width: 600px; margin: 0 auto 30px;">
                    <?= htmlspecialchars($this->session->flashdata('msg'), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="hero-label">Business operations, one workspace</div>
            <h1>Run billing, projects, support, and people operations in one place.</h1>
            <p>BERPS brings the everyday work of your team into a clear, connected system—from invoices and job orders to tasks, payroll, and customer support.</p>
            <div class="hero-buttons">
                <a href="<?= site_url('Login/signup_page'); ?>" class="btn-hero btn-primary">Create an account</a>
                <a href="#features" class="btn-hero btn-secondary">Explore the modules</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-container">
            <div class="section-title">
                <h2>Built around the work your team does</h2>
                <p>Keep related workflows together, reduce repeated encoding, and give every role a clearer view of what needs attention.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3>Sales and finance</h3>
                    <p>Move from billing to collection with the transaction history and reports your team needs.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check" aria-hidden="true"></i> Invoices and recurring billing</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i> Payments and expenses</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i> Deliveries and sales reports</li>
                    </ul>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Projects and customers</h3>
                    <p>Keep client work, responsibilities, progress, and support conversations connected.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check" aria-hidden="true"></i> Job orders and projects</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i> Tasks and team assignments</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i> Clients and support tickets</li>
                    </ul>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>People and planning</h3>
                    <p>Give managers and employees one place for records, schedules, attendance, and payroll.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check" aria-hidden="true"></i> Employee records and attendance</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i> Payroll and payslips</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i> Calendar, goals, and reminders</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <h2>A clearer way to run daily operations</h2>
        <p>Start with the modules your team needs and keep one dependable source of information as your work grows.</p>
        <a href="<?= site_url('Login/signup_page'); ?>" class="btn-hero btn-secondary">Create an account</a>
    </section>

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="loginModalTitle">
        <div class="login-modal-content" tabindex="-1">
            <button class="login-modal-close" id="closeLoginModal" type="button" aria-label="Close login dialog">&times;</button>
            <h3 id="loginModalTitle">Login to your account</h3>
            
            <?php if ($this->session->flashdata('msg')): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($this->session->flashdata('msg'), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('Login/auth'); ?>" method="post">
                <div class="form-group">
                    <label for="loginUsername">Username / Email</label>
                    <input type="text" id="loginUsername" name="username" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" autocomplete="current-password" required>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px;">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="<?= site_url('login/forgot'); ?>" style="color: var(--primary); text-decoration: none; font-size: 14px;">Forgot Password?</a>
                </div>
                <button type="submit" class="btn-submit">Login</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> BERPS - Business Resource Planning System. All rights reserved.</p>
    </footer>

    <script>
        const navbarToggle = document.getElementById('navbarToggle');
        const publicNavigation = document.getElementById('publicNavigation');

        if (navbarToggle && publicNavigation) {
            navbarToggle.addEventListener('click', function() {
                const isOpen = publicNavigation.classList.toggle('is-open');
                navbarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                navbarToggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
            });

            publicNavigation.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    publicNavigation.classList.remove('is-open');
                    navbarToggle.setAttribute('aria-expanded', 'false');
                    navbarToggle.setAttribute('aria-label', 'Open navigation');
                });
            });
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Login modal functionality
        const loginBtn = document.getElementById('loginBtn');
        const loginModal = document.getElementById('loginModal');
        const closeLoginModal = document.getElementById('closeLoginModal');
        const loginModalContent = loginModal ? loginModal.querySelector('.login-modal-content') : null;
        let loginModalTrigger = null;

        function openLoginModal(trigger) {
            if (!loginModal) return;
            loginModalTrigger = trigger || document.activeElement;
            loginModal.classList.add('active');
            loginModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            window.requestAnimationFrame(function() {
                const usernameField = document.getElementById('loginUsername');
                if (usernameField) usernameField.focus();
            });
        }

        function closeLoginDialog() {
            if (!loginModal) return;
            loginModal.classList.remove('active');
            loginModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (loginModalTrigger && typeof loginModalTrigger.focus === 'function') {
                loginModalTrigger.focus();
            }
        }

        if (loginBtn && loginModal) {
            loginBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openLoginModal(loginBtn);
            });
        }

        if (closeLoginModal && loginModal) {
            closeLoginModal.addEventListener('click', closeLoginDialog);
        }

        // Close modal when clicking outside
        if (loginModal) {
            loginModal.addEventListener('click', function(e) {
                if (e.target === loginModal) {
                    closeLoginDialog();
                }
            });
        }

        // Keep keyboard focus inside the login dialog while it is open.
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && loginModal && loginModal.classList.contains('active')) {
                closeLoginDialog();
                return;
            }

            if (e.key === 'Tab' && loginModal && loginModal.classList.contains('active') && loginModalContent) {
                const focusable = Array.from(loginModalContent.querySelectorAll(
                    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
                )).filter(function(element) {
                    return element.offsetParent !== null;
                });

                if (!focusable.length) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });

        // Form validation
        const signupForm = document.getElementById('signupForm');
        if (signupForm) {
            signupForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long.');
                    return false;
                }
            });
        }
    </script>
</body>

</html>
