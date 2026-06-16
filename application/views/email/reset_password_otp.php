<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BERPS password reset code</title>
</head>

<body style="font-family: Arial, sans-serif; color: #0f172a; background-color: #f6f8fc; margin:0; padding:24px;">
  <?php
  $name = trim(($user->fName ?? '') . ' ' . ($user->lName ?? ''));
  $name = $name !== '' ? $name : ($user->email ?? 'there');
  $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
  $safeOtp  = htmlspecialchars((string) $otp, ENT_QUOTES, 'UTF-8');
  $minutes  = (int) ($ttl_minutes ?? 15);
  ?>
  <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 10px 30px rgba(15,23,42,0.08);">
    <tr>
      <td style="padding:24px;">
        <h2 style="margin:0 0 12px 0;color:#0f172a;">Reset your BERPS password</h2>
        <p style="margin:0 0 16px 0;">Hi <?= $safeName; ?>,</p>
        <p style="margin:0 0 16px 0;">Use the verification code below in the BERPS mobile app to reset your password.</p>
        <p style="margin:0 0 24px 0; text-align:center;">
          <span style="display:inline-block;padding:14px 22px;background:#0B1D3D;color:#ffffff;letter-spacing:6px;font-weight:800;font-size:26px;border-radius:12px;font-family:Menlo, Consolas, monospace;">
            <?= $safeOtp; ?>
          </span>
        </p>
        <p style="margin:0 0 16px 0;color:#6b7280;">This code expires in <?= $minutes; ?> minute<?= $minutes === 1 ? '' : 's'; ?>. If you did not request a reset, you can safely ignore this email.</p>
        <p style="margin:0;color:#6b7280;">Thanks,<br>The BERPS Team</p>
      </td>
    </tr>
  </table>
</body>

</html>
