<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e8f1fb">

    <title>BERPS</title>

    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-sm1.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/images/logo-sm1.png'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">

    <style>
        :root {
            --bg: #e8f1fb;
            --panel: rgba(255, 255, 255, 0.96);
            --bg-card: #ffffff;
            --border: rgba(15, 54, 111, 0.16);
            --primary: #1b5ed6;
            --primary-dark: #114cb3;
            --text: #0b1d3d;
            --muted: rgba(11, 29, 61, 0.65);
            --error: #c62828;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            background: linear-gradient(135deg, #fefefe, var(--bg));
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
            cursor: default;
        }

        /* animated background orbs */
        .bg-orbs {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .orb {
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.55;
            animation: float 16s ease-in-out infinite;
            transition: transform 0.3s ease;
        }

        .orb.one {
            background: rgba(27, 94, 214, 0.35);
            top: -40px;
            left: -60px;
            animation-delay: 0s;
        }

        .orb.two {
            background: rgba(17, 76, 179, 0.25);
            bottom: -80px;
            right: -40px;
            animation-delay: 3s;
        }

        .orb.three {
            background: rgba(27, 94, 214, 0.22);
            top: 40%;
            left: 60%;
            animation-delay: 6s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(30px, -20px, 0) scale(1.06);
            }
        }

        .auth-layout {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 360px));
            gap: 48px;
            width: min(980px, 100%);
            align-items: center;
            justify-content: center;
            justify-items: center;
            padding: clamp(24px, 5vw, 48px);
            border-radius: 28px;
            border: 1px solid var(--border);
            background: var(--panel);
            box-shadow: 0 25px 70px rgba(17, 52, 108, 0.2);
            backdrop-filter: blur(8px);
            transition: transform 0.1s ease;
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 18px;
            align-items: center;
            text-align: center;
        }

        .brand-mark {
            width: 88px;
            height: 88px;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid var(--border);
            padding: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 14px 35px rgba(32, 94, 214, 0.18);
            animation: pop 1.6s ease-in-out infinite alternate;
            cursor: default;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            user-select: none;
        }

        .brand-mark:hover {
            transform: scale(1.1) rotate(-5deg);
            box-shadow: 0 18px 45px rgba(32, 94, 214, 0.3);
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            pointer-events: none;
        }

        .brand h1 {
            margin: 0;
            font-size: clamp(32px, 4vw, 40px);
            font-weight: 700;
            letter-spacing: -0.01em;
            transition: transform 0.3s ease;
        }

        .brand h1:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand p {
            margin: 0;
            color: var(--muted);
            max-width: 420px;
            line-height: 1.5;
            margin-left: auto;
            margin-right: auto;
        }

        .brand-features {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 12px;
        }

        .brand-feature {
            border-radius: 14px;
            padding: 10px 14px;
            background: rgba(27, 94, 214, 0.08);
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.4;
            min-width: 130px;
            text-align: center;
            transition: all .3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            cursor: pointer;
            user-select: none;
        }

        .brand-feature:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 12px 28px rgba(27, 94, 214, 0.2);
            background: rgba(27, 94, 214, 0.15);
        }

        .brand-feature:active {
            transform: translateY(-2px) scale(0.98);
        }

        .brand-feature strong {
            display: block;
            color: var(--text);
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        .auth-card {
            background: var(--bg-card);
            border: 1px solid rgba(15, 54, 111, 0.2);
            border-radius: 22px;
            padding: 32px;
            box-shadow: 0 20px 55px rgba(20, 44, 90, 0.15), 0 0 0 0 rgba(27, 94, 214, 0.12);
            width: min(440px, 100%);
            position: relative;
            overflow: visible;
            animation: pulseGlow 8s ease-in-out infinite;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            inset: -40% auto auto -40%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(27, 94, 214, 0.16) 0%, transparent 70%);
            filter: blur(6px);
            animation: drift 12s ease-in-out infinite;
        }

        .auth-card::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 24px;
            padding: 2px;
            background: linear-gradient(120deg, rgba(27, 94, 214, 0.9), rgba(90, 168, 255, 0.9), rgba(17, 76, 179, 0.85), rgba(27, 94, 214, 0.9));
            background-size: 300% 300%;
            animation: borderflow 4s linear infinite;
            mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            mask-composite: exclude;
            -webkit-mask-composite: xor;
            pointer-events: none;
            opacity: 0.78;
            transition: opacity .25s ease, filter .25s ease;
        }

        .auth-card:hover::after {
            opacity: 1;
            filter: brightness(1.05);
            animation-duration: 3s;
        }

        @keyframes drift {

            0%,
            100% {
                transform: translate3d(0, 0, 0) rotate(0deg);
            }

            50% {
                transform: translate3d(16px, -12px, 0) rotate(5deg);
            }
        }

        @keyframes borderflow {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes pulseGlow {

            0%,
            100% {
                box-shadow: 0 20px 55px rgba(20, 44, 90, 0.15), 0 0 0 0 rgba(27, 94, 214, 0.12);
            }

            50% {
                box-shadow: 0 20px 55px rgba(20, 44, 90, 0.2), 0 0 0 12px rgba(27, 94, 214, 0.06);
            }
        }

        .status {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid rgba(198, 40, 40, 0.2);
            font-size: 14px;
            background: rgba(198, 40, 40, 0.08);
            color: var(--error);
            animation: shake 0.5s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
            position: relative;
            z-index: 1;
        }

        .input-with-toggle {
            position: relative;
        }

        label {
            display: block;
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 6px;
            transition: color 0.2s ease;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #f7f9fc;
            color: var(--text);
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .input-with-toggle input {
            padding-right: 48px;
        }

        input:focus {
            outline: none;
            border-color: rgba(27, 94, 214, 0.8);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(27, 94, 214, 0.15);
            transform: translateY(-2px) scale(1.01);
        }

        input:focus+label,
        input:focus~label {
            color: var(--primary);
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: var(--muted);
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .remember:hover {
            transform: scale(1.05);
        }

        .remember input {
            accent-color: var(--primary);
            cursor: pointer;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            padding: 6px;
            cursor: pointer;
            color: var(--muted);
            border-radius: 10px;
            transition: all .3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .toggle-password:hover {
            background: rgba(27, 94, 214, 0.08);
            color: var(--primary);
            transform: translateY(-50%) scale(1.2) rotate(10deg);
        }

        .toggle-password:active {
            transform: translateY(-50%) scale(0.9);
        }

        .toggle-password:focus-visible {
            outline: 2px solid rgba(27, 94, 214, 0.4);
            outline-offset: 2px;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
            display: block;
        }

        .toggle-password .eye-closed {
            display: none;
        }

        .toggle-password[aria-pressed="true"] {
            color: var(--primary);
        }

        .toggle-password[aria-pressed="true"] .eye-open {
            display: none;
        }

        .toggle-password[aria-pressed="true"] .eye-closed {
            display: block;
        }

        .link {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: transform 0.2s ease;
            display: inline-block;
        }

        .link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .2s ease;
        }

        .link:hover {
            color: var(--primary);
            transform: translateX(4px);
        }

        .link:hover::after {
            transform: scaleX(1);
        }

        button[type="submit"] {
            border: none;
            border-radius: 14px;
            padding: 14px 18px;
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            cursor: pointer;
            box-shadow: 0 14px 32px rgba(17, 76, 179, 0.35);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
        }

        button[type="submit"]::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        button[type="submit"]:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 20px 50px rgba(17, 76, 179, 0.5);
        }

        button[type="submit"]:hover::before {
            width: 300px;
            height: 300px;
        }

        button[type="submit"]:active {
            transform: translateY(0) scale(0.98);
            box-shadow: 0 10px 24px rgba(17, 76, 179, 0.35);
            filter: brightness(.98);
        }

        @media (max-width: 640px) {
            body {
                padding: 16px;
            }

            .auth-layout {
                grid-template-columns: 1fr;
            }

            .auth-card {
                padding: 26px;
                width: 100%;
            }

            .form-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        @keyframes pop {
            from {
                transform: translateY(0) scale(1);
            }

            to {
                transform: translateY(-4px) scale(1.02);
            }
        }

        /* Ripple effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s ease-out;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <div class="bg-orbs">
        <div class="orb one"></div>
        <div class="orb two"></div>
        <div class="orb three"></div>
    </div>
    <div class="auth-layout">
        <section class="brand">
            <span class="brand-mark" id="draggableLogo">
                <img src="<?= base_url('assets/images/logo-sm1.png'); ?>" alt="BERPS logo">
            </span>
            <div>
                <h1>Business ERPS</h1>
                <p>A Business Resource Planning System built for task management, invoicing, and job order processing so every team stays aligned from request to delivery.</p>
            </div>
        </section>

        <section class="auth-card">
            <?php if ($this->session->flashdata('msg')): ?>
                <div class="status">
                    <?= $this->session->flashdata('msg'); ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('Login/auth'); ?>" method="post">
                <div>
                    <label for="username">Email</label>
                    <input id="username" type="text" name="username" autocomplete="username" required>
                </div>

                <div>
                    <label for="password">Account Password</label>
                    <div class="input-with-toggle">
                        <input id="password" type="password" name="password" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" aria-label="Show password" aria-pressed="false">
                            <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2.5 12c2.1-4 5.4-6.5 9.5-6.5s7.4 2.5 9.5 6.5c-2.1 4-5.4 6.5-9.5 6.5S4.6 16 2.5 12z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="12" cy="12" r="2.8" fill="none" stroke="currentColor" stroke-width="1.5" />
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 3l18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M5 8.5C6.8 6.3 9.2 5 12 5s5.2 1.3 7 3.5c.8 1 1.5 2.1 2 3.5-.5 1.4-1.2 2.5-2 3.5-.6.8-1.3 1.5-2.1 2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.5 9.5a3.5 3.5 0 0 1 4.9 4.9" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-footer">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a class="link" href="<?= site_url('login/forgot'); ?>">Forgot Password?</a>
                </div>

                <!-- <p style="margin:-6px 0 0;color:var(--muted);font-size:13px;line-height:1.5;">
                    Clients can sign in using their portal email or client ID assigned to their company account.
                </p> -->

                <button type="submit">Sign in</button>
            </form>
        </section>
    </div>
    <script>
        (function() {
            // Password toggle functionality
            var passwordInput = document.getElementById('password');
            var toggleBtn = document.querySelector('.toggle-password');
            if (passwordInput && toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    var isVisible = passwordInput.type === 'text';
                    passwordInput.type = isVisible ? 'password' : 'text';
                    toggleBtn.setAttribute('aria-pressed', String(!isVisible));
                    toggleBtn.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
                });
            }

            // Ripple effect on button
            var submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    var ripple = document.createElement('span');
                    ripple.className = 'ripple';
                    this.appendChild(ripple);

                    var rect = this.getBoundingClientRect();
                    var size = Math.max(rect.width, rect.height);
                    var x = e.clientX - rect.left - size / 2;
                    var y = e.clientY - rect.top - size / 2;

                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';

                    setTimeout(function() {
                        ripple.remove();
                    }, 600);
                });
            }

            // Mouse move parallax effect
            document.addEventListener('mousemove', function(e) {
                var x = (e.clientX / window.innerWidth - 0.5) * 20;
                var y = (e.clientY / window.innerHeight - 0.5) * 20;

                var orbs = document.querySelectorAll('.orb');
                orbs.forEach(function(orb, index) {
                    var speed = (index + 1) * 0.5;
                    orb.style.transform = 'translate3d(' + (x * speed) + 'px, ' + (y * speed) + 'px, 0)';
                });

                var layout = document.querySelector('.auth-layout');
                layout.style.transform = 'translate3d(' + (x * 0.3) + 'px, ' + (y * 0.3) + 'px, 0)';
            });

            // Interactive brand features
            var features = document.querySelectorAll('.brand-feature');
            features.forEach(function(feature) {
                feature.addEventListener('click', function() {
                    this.style.animation = 'none';
                    setTimeout(() => {
                        this.style.animation = '';
                    }, 10);
                });
            });

            // Input animation effects
            var inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.01)';
                });

                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        })();
    </script>
</body>

</html>
