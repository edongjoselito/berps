<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Password Reset</title>
</head>

<body style="font-family: Arial, sans-serif; color: #0f172a; background-color: #f6f8fc; margin:0; padding:24px;">
  <?php
  $name = trim(($user->fName ?? '') . ' ' . ($user->lName ?? ''));
  $name = $name !== '' ? $name : ($user->email ?? 'there');
  $safeLink = htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8');
  $expires = !empty($expires_at) ? date('M j, Y g:i A', strtotime($expires_at)) : '';
  ?>
  <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 10px 30px rgba(15,23,42,0.08);">
    <tr>
      <td style="padding:24px;">
        <h2 style="margin:0 0 12px 0;color:#0f172a;">Reset your BERPS password</h2>
        <p style="margin:0 0 16px 0;">Hi <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>,</p>
        <p style="margin:0 0 16px 0;">We received a request to reset your BERPS account password. Click the button below to create a new one.</p>
        <p style="margin:0 0 24px 0; text-align:center;">
          <a href="<?= $safeLink; ?>" style="display:inline-block;padding:12px 18px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:10px;font-weight:700;">Reset Password</a>
        </p>
        <p style="margin:0 0 16px 0; word-break:break-all;">If the button does not work, copy and paste this link into your browser:<br><?= $safeLink; ?></p>
        <?php if ($expires): ?>
          <p style="margin:0 0 16px 0;color:#6b7280;">This link expires on <?= htmlspecialchars($expires, ENT_QUOTES, 'UTF-8'); ?>.</p>
        <?php endif; ?>
        <p style="margin:0 0 16px 0;">If you did not request a reset, you can safely ignore this email.</p>
        <p style="margin:0;color:#6b7280;">Thanks,<br>The BERPS Team</p>
      </td>
    </tr>
  </table>
</body>

</html>