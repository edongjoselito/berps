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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg: #e8f1fb;
            --panel: rgba(255, 255, 255, 0.96);
            --bg-card: #ffffff;
            --border: rgba(15, 54, 111, 0.16);
            --primary: #1b5ed6;
            --primary-dark: #114cb3;
            --primary-light: #4a8dff;
            --text: #0b1d3d;
            --muted: rgba(11, 29, 61, 0.65);
            --error: #c62828;
            --success: #2e7d32;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-primary, 'Montserrat', 'Poppins', 'Segoe UI', 'Helvetica Neue', Arial, sans-serif);
            background: linear-gradient(135deg, #fefefe, var(--bg));
            color: var(--text);
            line-height: 1.6;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
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
            width: 48px;
            height: 48px;
            border-radius: 12px;
        }

        .navbar-brand span {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .navbar-nav {
            display: flex;
            gap: 24px;
            align-items: center;
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
            padding: 10px 24px;
            border-radius: 10px;
            background: var(--primary);
            color: white !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-nav:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(27, 94, 214, 0.3);
        }

        .btn-nav-secondary {
            padding: 10px 24px;
            border-radius: 10px;
            background: transparent;
            color: var(--primary) !important;
            font-weight: 600;
            border: 2px solid var(--primary);
            transition: all 0.3s ease;
        }

        .btn-nav-secondary:hover {
            background: var(--primary);
            color: white !important;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            padding: 160px 32px 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: clamp(40px, 5vw, 64px);
            font-weight: 800;
            margin-bottom: 24px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: clamp(18px, 2vw, 22px);
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
            padding: 16px 40px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 12px 30px rgba(27, 94, 214, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 40px rgba(27, 94, 214, 0.5);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        /* Features Section */
        .features {
            padding: 100px 32px;
            background: white;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: clamp(32px, 4vw, 48px);
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: var(--bg);
            border-radius: 20px;
            padding: 32px;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(27, 94, 214, 0.15);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text);
        }

        .feature-card p {
            color: var(--muted);
            font-size: 15px;
        }

        /* CTA Section */
        .cta {
            padding: 100px 32px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
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
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(27, 94, 214, 0.2);
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
            border-radius: 10px;
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
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(27, 94, 214, 0.3);
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
            border-radius: 10px;
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
            background: var(--text);
            color: white;
            padding: 60px 32px 32px;
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
                gap: 12px;
            }

            .navbar-nav a:not(.btn-nav) {
                display: none;
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
        <div class="navbar-nav">
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
            
            <h1>Transform Your Business with BERPS</h1>
            <p>A comprehensive Business Resource Planning System designed to streamline your operations, from invoicing and job orders to inventory management and sales tracking.</p>
            <div class="hero-buttons">
                <a href="<?= site_url('Login/signup_page'); ?>" class="btn-hero btn-primary">Get Started Free</a>
                <a href="#features" class="btn-hero btn-secondary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-container">
            <div class="section-title">
                <h2>Powerful Features for Your Business</h2>
                <p>Everything you need to manage your business efficiently in one integrated platform.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3>Invoicing</h3>
                    <p>Create professional invoices, track payments, and manage billing cycles effortlessly.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Job Order Processing & Tracking</h3>
                    <p>Process and track job orders from creation to completion. Monitor progress, assign tasks, and manage workflows efficiently.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Customer Support</h3>
                    <p>Provide excellent customer service with integrated support tools. Track inquiries, manage tickets, and resolve issues promptly.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Calendar Management</h3>
                    <p>Organize your schedule with a built-in calendar. Track appointments, set reminders, and manage your time effectively.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>Task Management</h3>
                    <p>Create, assign, and track tasks efficiently. Set priorities, deadlines, and monitor task completion across your team.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <h3>Payroll</h3>
                    <p>Manage employee payroll efficiently. Calculate salaries, deductions, and generate payslips with automated processing.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-id-badge"></i>
                    </div>
                    <h3>HRIS</h3>
                    <p>Comprehensive Human Resource Information System. Manage employee records, attendance, leave, and performance evaluations.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>Business Settings</h3>
                    <p>Customize the system to match your business needs. Configure company details, pricing, and more.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3>User Management</h3>
                    <p>Manage user roles and permissions. Control access to different features based on user hierarchy.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <h2>Ready to Transform Your Business?</h2>
        <p>Join thousands of businesses already using BERPS to streamline their operations and boost productivity.</p>
        <a href="<?= site_url('Login/signup_page'); ?>" class="btn-hero btn-secondary">Sign Up Now</a>
    </section>

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <div class="login-modal-content">
            <button class="login-modal-close" id="closeLoginModal">&times;</button>
            <h3>Login to Your Account</h3>
            
            <?php if ($this->session->flashdata('msg')): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($this->session->flashdata('msg'), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('Login/auth'); ?>" method="post">
                <div class="form-group">
                    <label for="loginUsername">Username / Email</label>
                    <input type="text" id="loginUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
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

        if (loginBtn && loginModal) {
            loginBtn.addEventListener('click', function(e) {
                e.preventDefault();
                loginModal.classList.add('active');
            });
        }

        if (closeLoginModal && loginModal) {
            closeLoginModal.addEventListener('click', function() {
                loginModal.classList.remove('active');
            });
        }

        // Close modal when clicking outside
        if (loginModal) {
            loginModal.addEventListener('click', function(e) {
                if (e.target === loginModal) {
                    loginModal.classList.remove('active');
                }
            });
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && loginModal && loginModal.classList.contains('active')) {
                loginModal.classList.remove('active');
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
