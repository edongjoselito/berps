<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your BERPS Account</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1b5ed6, #114cb3);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #0b1d3d;
            font-size: 22px;
            margin-top: 0;
        }
        .content p {
            color: #617489;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #1b5ed6, #114cb3);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
        }
        .button:hover {
            box-shadow: 0 4px 12px rgba(27, 94, 214, 0.3);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #617489;
            font-size: 14px;
        }
        .footer a {
            color: #1b5ed6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BERPS</h1>
            <p>Business Resource Planning System</p>
        </div>
        <div class="content">
            <h2>Welcome to BERPS!</h2>
            <p>Hi <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>,</p>
            <p>Thank you for signing up for BERPS. We're excited to have you on board!</p>
            <p>To complete your registration and activate your account, please click the button below to confirm your email address:</p>
            <p style="text-align: center;">
                <a href="<?= $confirmation_link; ?>" class="button">Confirm My Account</a>
            </p>
            <p>Or copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #1b5ed6; font-size: 14px;"><?= $confirmation_link; ?></p>
            <p><strong>This link will expire in 24 hours.</strong></p>
            <p>If you didn't create an account with BERPS, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y'); ?> BERPS. All rights reserved.</p>
            <p>Need help? <a href="#">Contact Support</a></p>
        </div>
    </div>
</body>
</html>
