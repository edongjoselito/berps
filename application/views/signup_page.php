<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e8f1fb">

    <title>Sign Up - BERPS</title>

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
            min-height: 100vh;
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
            transition: color 0.3s ease;
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
            box-shadow: 0 8px 20px rgba(27, 94, 214, 0.3);
        }

        /* Signup Section */
        .signup-section {
            padding: 160px 32px 100px;
            background: var(--bg);
            min-height: 100vh;
        }

        .signup-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .signup-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 50px rgba(27, 94, 214, 0.1);
            border: 1px solid var(--border);
        }

        .signup-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .signup-header h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text);
        }

        .signup-header p {
            color: var(--muted);
            font-size: 16px;
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

        .form-section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(27, 94, 214, 0.3);
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

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary-dark);
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

            .signup-section {
                padding: 120px 20px 60px;
            }

            .signup-card {
                padding: 30px;
            }

            .signup-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="<?= site_url('login'); ?>" class="navbar-brand">
            <img src="<?= base_url('assets/images/logo-sm1.png'); ?>" alt="BERPS">
            <span>BERPS</span>
        </a>
        <div class="navbar-nav">
            <a href="<?= site_url('login'); ?>">Back to Home</a>
            <a href="#" class="btn-nav" id="loginBtn">Login</a>
        </div>
    </nav>

    <!-- Signup Section -->
    <section class="signup-section">
        <div class="signup-container">
            <a href="<?= site_url('login'); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>

            <div class="signup-card">
                <div class="signup-header">
                    <h1>Create Your Account</h1>
                    <p>Fill in your business details to get started with BERPS.</p>
                </div>
                
                <?php if ($this->session->flashdata('signup_error')): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($this->session->flashdata('signup_error'), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('signup_success')): ?>
                    <div class="alert alert-success">
                        <?= $this->session->flashdata('signup_success'); ?>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('Login/signup'); ?>" method="post" id="signupForm">
                    <div class="form-section-title">Account Information</div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label for="fName">First Name *</label>
                            <input type="text" id="fName" name="fName" required value="<?= isset($form_data['fName']) ? htmlspecialchars($form_data['fName']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="lName">Last Name *</label>
                            <input type="text" id="lName" name="lName" required value="<?= isset($form_data['lName']) ? htmlspecialchars($form_data['lName']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required value="<?= isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" required style="padding-right: 45px;">
                            <button type="button" id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); font-size: 18px; padding: 0;">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <input type="hidden" name="accountType" value="company">
                    <input type="hidden" name="compName" value="">
                    <input type="hidden" name="compTin" value="">
                    <input type="hidden" name="compType" value="">
                    <input type="hidden" name="compAddress" value="">
                    <input type="hidden" name="proprietor" value="">
                    <input type="hidden" name="businessLines" value="">
                    
                    <input type="hidden" name="package" value="2">
                    
                    <div id="packageDetails" style="margin-top: 15px; padding: 15px; background: #f7f9fc; border-radius: 10px; border: 1px solid var(--border); display: none;">
                        <h4 id="packageTitle" style="margin-bottom: 10px; color: var(--primary);"></h4>
                        <p id="packageDescription" style="margin-bottom: 10px; color: var(--muted); font-size: 14px;"></p>
                        <div id="packageFeatures" style="margin-top: 10px;"></div>
                    </div>
                    
                    <?php if (isset($recaptchaEnabled) && $recaptchaEnabled): ?>
                        <div class="form-group" style="margin-top: 20px;">
                            <label>Security Verification</label>
                            <div id="recaptcha-container"></div>
                            <div id="recaptcha-error" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: none;">Please complete the reCAPTCHA verification.</div>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn-submit">Create Account</button>
                </form>
            </div>
        </div>
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

    <style>
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
    </style>

    <script>
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

        // Company fields are hidden

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

        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (togglePassword && passwordInput && eyeIcon) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        }

        // Package details display
        const packageSelect = document.getElementById('package');
        const packageDetails = document.getElementById('packageDetails');
        const packageTitle = document.getElementById('packageTitle');
        const packageDescription = document.getElementById('packageDescription');
        const packageFeatures = document.getElementById('packageFeatures');

        const packages = {
            'all': {
                title: 'All Packages',
                description: 'Complete access to all features from all packages.',
                features: ['Invoice Management', 'Deliveries Tracking', 'Expenses Management', 'Job Order System', 'Projects Management', 'Support System', 'Tasks Management', 'Notes', 'Calendar', 'Payroll', 'Employee Payroll', 'Salary Computation', 'Payroll Reports', 'Point of Sale']
            },
            '1': {
                title: 'Package 1: Business Operations Suite',
                description: 'A complete solution for managing operations, projects, and customer transactions.',
                features: ['Invoice Management', 'Deliveries Tracking', 'Expenses Management', 'Job Order System', 'Projects Management', 'Support System', 'Tasks Management', 'Notes', 'Calendar']
            },
            '2': {
                title: 'Package 2: Task Management Suite',
                description: 'Designed for teams that need task tracking and scheduling.',
                features: ['Tasks Management', 'Notes', 'Calendar']
            },
            '3': {
                title: 'Package 3: Payroll Management Suite',
                description: 'Designed to streamline employee compensation and payroll processing.',
                features: ['Payroll', 'Employee Payroll', 'Salary Computation', 'Payroll Reports', 'Notes']
            },
            '4': {
                title: 'Package 4: POS',
                description: 'Designed for retail, cashiering, and point-of-sale account access.',
                features: ['Point of Sale', 'Notes']
            }
        };

        if (packageSelect && packageDetails && packageTitle && packageDescription && packageFeatures) {
            packageSelect.addEventListener('change', function() {
                const selectedPackage = this.value;
                if (selectedPackage && packages[selectedPackage]) {
                    const pkg = packages[selectedPackage];
                    packageTitle.textContent = pkg.title;
                    packageDescription.textContent = pkg.description;
                    
                    let featuresHtml = '<strong>Includes:</strong><ul style="margin-top: 8px; padding-left: 20px;">';
                    pkg.features.forEach(function(feature) {
                        featuresHtml += '<li style="margin-bottom: 4px;">' + feature + '</li>';
                    });
                    featuresHtml += '</ul>';
                    packageFeatures.innerHTML = featuresHtml;
                    
                    packageDetails.style.display = 'block';
                } else {
                    packageDetails.style.display = 'none';
                }
            });

            // Initialize on page load if a package is pre-selected
            if (packageSelect.value) {
                packageSelect.dispatchEvent(new Event('change'));
            }
        }

        <?php if (isset($recaptchaEnabled) && $recaptchaEnabled): ?>
            // Load reCAPTCHA
            var recaptchaSiteKey = '<?= isset($recaptchaSiteKey) ? htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8') : ''; ?>';
            var recaptchaVersion = '<?= isset($recaptchaVersion) ? htmlspecialchars($recaptchaVersion, ENT_QUOTES, 'UTF-8') : 'v2'; ?>';
            
            if (recaptchaVersion === 'v2') {
                // Load reCAPTCHA v2
                var script = document.createElement('script');
                script.src = 'https://www.google.com/recaptcha/api.js';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
                
                // Render reCAPTCHA when script loads
                script.onload = function() {
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.ready(function() {
                            grecaptcha.render('recaptcha-container', {
                                'sitekey': recaptchaSiteKey
                            });
                        });
                    }
                };
            } else {
                // Load reCAPTCHA v3
                var script = document.createElement('script');
                script.src = 'https://www.google.com/recaptcha/api.js?render=' + recaptchaSiteKey;
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
                
                // Execute reCAPTCHA v3 on form submit
                script.onload = function() {
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.ready(function() {
                            var form = document.getElementById('signupForm');
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                grecaptcha.execute(recaptchaSiteKey, {action: 'signup'}).then(function(token) {
                                    // Add token to form
                                    var tokenInput = document.createElement('input');
                                    tokenInput.type = 'hidden';
                                    tokenInput.name = 'g-recaptcha-response';
                                    tokenInput.value = token;
                                    form.appendChild(tokenInput);
                                    form.submit();
                                });
                            });
                        });
                    }
                };
            }
        <?php endif; ?>
    </script>
</body>

</html>
