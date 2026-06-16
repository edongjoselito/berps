<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BERPS Mobile — Privacy Policy</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #334155;
            line-height: 1.7;
            padding: 16px;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            padding: 40px 0 28px;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .header p {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }
        .badge {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 100px;
            margin-bottom: 16px;
        }
        .section {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
        }
        .section-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title svg {
            width: 22px;
            height: 22px;
            color: #1d4ed8;
        }
        .section p, .section li {
            font-size: 14px;
            color: #475569;
            font-weight: 500;
        }
        .section ul {
            margin-top: 10px;
            margin-left: 0;
            list-style: none;
        }
        .section ul li {
            position: relative;
            padding-left: 20px;
            margin-bottom: 12px;
        }
        .section ul li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            width: 6px;
            height: 6px;
            background: #1d4ed8;
            border-radius: 50%;
        }
        .section ul li strong {
            color: #0f172a;
            display: block;
            margin-bottom: 2px;
        }
        .meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .meta span {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 600;
        }
        .footer {
            text-align: center;
            padding: 32px 0;
            font-size: 13px;
            color: #94a3b8;
            font-weight: 600;
        }
        .footer a {
            color: #1d4ed8;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            body { padding: 12px; }
            .header { padding: 24px 0 20px; }
            .header h1 { font-size: 22px; }
            .section { padding: 18px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">Privacy Policy</div>
            <h1>Your Data in BERPS</h1>
            <p>This Privacy Policy explains how the BERPS system collects, uses, and protects your personal data within your organization.</p>
            <div class="meta">
                <span>Last updated: June 13, 2026</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                What Data BERPS Stores
            </div>
            <ul>
                <li>
                    <strong>Account Profile</strong>
                    Your user ID, username, password (encrypted), position/role, first name, middle name, last name, email address, and profile photo. This is managed by your workspace administrator in the BERPS users table.
                </li>
                <li>
                    <strong>Authentication Session</strong>
                    When you sign in, the server issues a secure bearer token (JWT) that expires after 30 days. The token contains your user ID, username, role, and workspace ID for authorization purposes.
                </li>
                <li>
                    <strong>Work Records</strong>
                    Tasks and assignments, attendance time-in/time-out entries, DTR (Daily Time Record) data, support tickets, calendar events, and reminders created or assigned to you within the workspace.
                </li>
                <li>
                    <strong>Device-Only Preferences</strong>
                    Your optional biometric login preference and cached session are stored locally on this device only. These are never sent to the server.
                </li>
            </ul>
        </div>

        <div class="section">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                How Your Data Is Used
            </div>
            <ul>
                <li>
                    <strong>Authentication &amp; Access</strong>
                    Your username and encrypted password verify your identity. Your role/position determines which features you can access in the mobile app.
                </li>
                <li>
                    <strong>Work Data Sync</strong>
                    Tasks, attendance, DTR, support tickets, and calendar events are fetched from the workspace server so you can view and manage them in the app.
                </li>
                <li>
                    <strong>Password Recovery</strong>
                    If you request a password reset, a one-time code is sent to your registered email address. Reset tokens expire automatically after a short time.
                </li>
                <li>
                    <strong>Profile Photo</strong>
                    Your uploaded avatar is stored on the workspace server and displayed in the app for identification by your team.
                </li>
            </ul>
        </div>

        <div class="section">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Who Can See Your Data
            </div>
            <p>BERPS is a self-hosted system. Your data stays within your organization.</p>
            <ul>
                <li>
                    <strong>Within Your Workspace</strong>
                    Your profile, tasks, attendance, and support tickets are visible to workspace administrators and team members based on the permissions set in your BERPS system.
                </li>
                <li>
                    <strong>No External Sharing</strong>
                    BERPS does not sell, share, or transmit your personal data to external companies, advertisers, or analytics services.
                </li>
                <li>
                    <strong>Server Location</strong>
                    All data is stored in the MySQL database on the BERPS server managed by your organization.
                </li>
            </ul>
        </div>

        <div class="section">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Your Rights
            </div>
            <ul>
                <li>
                    <strong>Access</strong>
                    You can view your profile, tasks, attendance, and support tickets at any time through the app.
                </li>
                <li>
                    <strong>Correction</strong>
                    Contact your workspace administrator to update inaccurate profile information (name, email, position, or avatar).
                </li>
                <li>
                    <strong>Deletion</strong>
                    Request your workspace administrator to deactivate or delete your account. This removes your profile and work records from the server database.
                </li>
                <li>
                    <strong>Withdraw Consent</strong>
                    Sign out and clear the app data from your device settings to remove all locally stored sessions and preferences.
                </li>
            </ul>
        </div>

        <div class="section">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Data Retention
            </div>
            <p>Your account and work data remain in the BERPS database as long as your workspace administrator keeps your account active. When an admin deactivates or deletes your account, your records are removed from the system. Session tokens expire automatically after 30 days. Password reset codes expire after 15 minutes. All data cached on this device is removed when you sign out or uninstall the app.</p>
        </div>

        <div class="section">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Contact
            </div>
            <p>For questions about your data, or to request access, correction, or deletion, contact your workspace administrator. They manage the BERPS server and your account settings.</p>
        </div>

        <div class="footer">
            <p>BERPS Mobile Privacy Policy</p>
        </div>
    </div>
</body>
</html>
