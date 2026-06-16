<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($page_title ?? 'Reset Password - BERPS', ENT_QUOTES, 'UTF-8') ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="shortcut icon" href="<?= base_url('assets/images/logo-sm1.png') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/icons.min.css') ?>">

  <style>
    :root{
      --ink:#0f172a;
      --muted:#5b6475;
      --border:#d9deeb;
      --card:#ffffff;
      --accent:#2563eb;
      --accent-2:#7c3aed;
      --bg:radial-gradient(circle at 15% 20%, rgba(37,99,235,.14), transparent 35%),
           radial-gradient(circle at 85% 0%, rgba(124,58,237,.16), transparent 32%),
           linear-gradient(150deg,#f7f9ff,#eef2f7);
      --shadow:0 30px 70px rgba(15,23,42,0.15);
      --radius:20px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family:"Manrope",ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;
      background:var(--bg);
      color:var(--ink);
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
    }
    .wrap{
      width:min(520px,100%);
      background:var(--card);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow), 0 0 0 0 rgba(37,99,235,0.12);
      padding:26px;
      position:relative;
      overflow:hidden;
      animation:pulseGlow 8s ease-in-out infinite;
    }
    .wrap::after{
      content:'';
      position:absolute;
      inset:-2px;
      border-radius:calc(var(--radius) + 2px);
      padding:2px;
      background:linear-gradient(120deg, rgba(37,99,235,0.9), rgba(124,58,237,0.8), rgba(37,99,235,0.9));
      background-size:320% 320%;
      animation:borderflow 4s linear infinite;
      mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
      mask-composite: exclude;
      -webkit-mask-composite: xor;
      pointer-events:none;
      opacity:0.78;
      transition:opacity .25s ease, filter .25s ease;
    }
    .wrap:hover::after{opacity:1;filter:brightness(1.05);animation-duration:3s;}
    .heading{
      display:flex;
      align-items:center;
      gap:12px;
      margin-bottom:6px;
    }
    .heading .icon{
      width:42px;height:42px;border-radius:14px;
      background:linear-gradient(135deg,var(--accent),var(--accent-2));
      color:#fff;
      display:grid;place-items:center;
      box-shadow:0 12px 28px rgba(37,99,235,0.35);
    }
    .heading h1{margin:0;font-size:22px;letter-spacing:-0.01em}
    .sub{margin:4px 0 16px;color:var(--muted);line-height:1.5}
    form{display:flex;flex-direction:column;gap:16px;margin-top:10px}
    label{
      display:block;
      font-size:14px;
      font-weight:600;
      color:var(--muted);
      margin-bottom:6px;
    }
    .field{position:relative}
    .input{
      width:100%;
      padding:13px 46px 13px 14px;
      border-radius:12px;
      border:1px solid var(--border);
      background:#f8faff;
      font-size:15px;
      transition:border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .input:focus{
      outline:none;
      border-color:rgba(37,99,235,0.7);
      box-shadow:0 0 0 3px rgba(37,99,235,0.18);
      background:#fff;
    }
    .toggle{
      position:absolute;
      right:10px;
      top:50%;
      transform:translateY(-50%);
      border:none;
      background:transparent;
      padding:6px;
      color:#6b7280;
      cursor:pointer;
    }
    .btn{
      width:100%;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      border:none;
      border-radius:12px;
      padding:13px 14px;
      font-weight:700;
      font-size:15px;
      color:#fff;
      background:linear-gradient(135deg,var(--accent),var(--accent-2));
      cursor:pointer;
      box-shadow:0 16px 35px rgba(37,99,235,0.35);
      transition:transform .15s ease, box-shadow .15s ease;
    }
    .btn:hover{transform:translateY(-1px);box-shadow:0 18px 40px rgba(37,99,235,0.4)}
    .btn:active{transform:translateY(0);box-shadow:0 12px 24px rgba(37,99,235,0.3)}
    .link{
      display:inline-flex;
      align-items:center;
      gap:6px;
      color:var(--accent);
      font-weight:700;
      text-decoration:none;
      margin-top:12px;
    }
    .alert{
      border-radius:12px;
      padding:11px 12px;
      margin:6px 0;
      font-size:13px;
      font-weight:600;
      border:1px solid;
    }
    .alert--err{background:#fef2f2;border-color:#fecaca;color:#991b1b}
    @keyframes borderflow{
      0%{background-position:0% 50%}
      50%{background-position:100% 50%}
      100%{background-position:0% 50%}
    }
    @keyframes pulseGlow{
      0%,100%{box-shadow:var(--shadow),0 0 0 0 rgba(37,99,235,0.12)}
      50%{box-shadow:var(--shadow),0 0 0 12px rgba(37,99,235,0.06)}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="heading">
      <div class="icon"><i class="mdi mdi-lock-reset" style="font-size:22px"></i></div>
      <div>
        <h1>Reset password</h1>
        <div class="sub">Create a new password you haven’t used before.</div>
      </div>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
      <div class="alert alert--err"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('msg')): ?>
      <div class="alert alert--err"><?= htmlspecialchars($this->session->flashdata('msg'), ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (validation_errors()): ?>
      <div class="alert alert--err"><?= validation_errors(); ?></div>
    <?php endif; ?>

    <?= form_open('login/reset'); ?>
      <?php if (isset($this->security)): ?>
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <?php endif; ?>
      <input type="hidden" name="selector"  value="<?= htmlspecialchars($selector ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="validator" value="<?= htmlspecialchars($validator ?? '', ENT_QUOTES, 'UTF-8') ?>">

      <div>
        <label for="password">New password</label>
        <div class="field">
          <input id="password" class="input" type="password" name="password" minlength="8" required>
          <button type="button" class="toggle" data-toggle="#password" aria-label="Show password">
            <i class="mdi mdi-eye-outline"></i>
          </button>
        </div>
        <?= form_error('password', '<div class="alert alert--err" style="margin-top:8px">', '</div>'); ?>
      </div>

      <div>
        <label for="password2">Confirm password</label>
        <div class="field">
          <input id="password2" class="input" type="password" name="password2" required>
          <button type="button" class="toggle" data-toggle="#password2" aria-label="Show password">
            <i class="mdi mdi-eye-outline"></i>
          </button>
        </div>
        <?= form_error('password2', '<div class="alert alert--err" style="margin-top:8px">', '</div>'); ?>
      </div>

      <button class="btn" type="submit">
        <i class="mdi mdi-check-circle-outline"></i> Update password
      </button>
    <?= form_close(); ?>

    <a class="link" href="<?= site_url('login'); ?>"><i class="mdi mdi-arrow-left"></i> Back to login</a>
  </div>

  <script>
    document.querySelectorAll('.toggle').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var sel = btn.getAttribute('data-toggle');
        var input = document.querySelector(sel);
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show
          ? '<i class="mdi mdi-eye-off-outline"></i>'
          : '<i class="mdi mdi-eye-outline"></i>';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      });
    });
  </script>
</body>
</html>
