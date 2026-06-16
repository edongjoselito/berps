<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($page_title ?? 'Forgot Password - BERPS', ENT_QUOTES, 'UTF-8') ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="shortcut icon" href="<?= base_url('assets/images/logo-sm1.png') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/icons.min.css') ?>">

  <style>
    :root {
      --ink: #0f172a;
      --muted: #5b6475;
      --border: #d9deeb;
      --card: #ffffff;
      --accent: #2563eb;
      --accent-2: #7c3aed;
      --bg: radial-gradient(circle at 20% 20%, rgba(124, 58, 237, .12), transparent 35%),
        radial-gradient(circle at 80% 0%, rgba(37, 99, 235, .16), transparent 30%),
        linear-gradient(135deg, #f7f9ff, #eef2f7);
      --shadow: 0 30px 70px rgba(15, 23, 42, 0.15);
      --radius: 20px;
    }

    * {
      box-sizing: border-box
    }

    html,
    body {
      height: 100%
    }

    body {
      margin: 0;
      font-family: "Manrope", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: var(--bg);
      color: var(--ink);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .shell {
      width: min(900px, 100%);
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 28px;
      align-items: center;
    }

    .hero {
      padding: 18px;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 999px;
      background: rgba(37, 99, 235, 0.12);
      color: var(--accent);
      font-weight: 700;
      font-size: 14px;
      border: 1px solid rgba(37, 99, 235, 0.22);
    }

    .hero h1 {
      margin: 14px 0 8px;
      font-size: 32px;
      line-height: 1.2;
      letter-spacing: -0.02em;
    }

    .hero p {
      margin: 0;
      color: var(--muted);
      line-height: 1.6;
      max-width: 460px;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow), 0 0 0 0 rgba(37, 99, 235, 0.12);
      padding: 26px;
      position: relative;
      overflow: hidden;
      animation: pulseGlow 8s ease-in-out infinite;
    }
    .card::after {
      content: '';
      position: absolute;
      inset: -2px;
      border-radius: calc(var(--radius) + 2px);
      padding: 2px;
      background: linear-gradient(120deg, rgba(37, 99, 235, 0.9), rgba(124, 58, 237, 0.8), rgba(37, 99, 235, 0.9));
      background-size: 320% 320%;
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
    .card:hover::after { opacity: 1; filter: brightness(1.05); animation-duration: 3s; }

    .card h2 {
      margin: 0 0 6px;
      font-size: 22px;
      letter-spacing: -0.01em;
    }

    .card small {
      color: var(--muted);
    }

    form {
      margin-top: 18px;
      display: flex;
      flex-direction: column;
      gap: 16px
    }

    label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: var(--muted);
      margin-bottom: 6px;
    }

    .input {
      width: 100%;
      padding: 13px 14px;
      border-radius: 12px;
      border: 1px solid var(--border);
      background: #f8faff;
      font-size: 15px;
      transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .input:focus {
      outline: none;
      border-color: rgba(37, 99, 235, 0.7);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
      background: #fff;
    }

    .btn {
      width: 100%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      border: none;
      border-radius: 12px;
      padding: 13px 14px;
      font-weight: 700;
      font-size: 15px;
      color: #fff;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      cursor: pointer;
      box-shadow: 0 16px 35px rgba(37, 99, 235, 0.35);
      transition: transform .15s ease, box-shadow .15s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 40px rgba(37, 99, 235, 0.4)
    }

    .btn:active {
      transform: translateY(0);
      box-shadow: 0 12px 24px rgba(37, 99, 235, 0.3)
    }

    .link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: var(--accent);
      font-weight: 700;
      text-decoration: none;
      margin-top: 10px;
    }

    .alert {
      border-radius: 12px;
      padding: 11px 12px;
      margin: 6px 0;
      font-size: 13px;
      font-weight: 600;
      border: 1px solid;
    }

    .alert--ok {
      background: #f0fdf4;
      border-color: #a3e635;
      color: #365314
    }

    .alert--err {
      background: #fef2f2;
      border-color: #fecaca;
      color: #991b1b
    }

    .alert--info {
      background: #eff6ff;
      border-color: #bfdbfe;
      color: #1d4ed8
    }

    @media(max-width:700px) {
      body {
        padding: 18px
      }

      .card {
        padding: 22px
      }
    }
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
  <div class="shell">
    <div class="hero">
      <div class="badge"><i class="mdi mdi-shield-key-outline"></i> Secure reset</div>
      <h1>Send a reset link to your inbox</h1>
      <p>Enter your username and the email you want us to use. We’ll send a one-time password reset link to that address if the account exists.</p>
    </div>

    <div class="card">
      <h2>Forgot password</h2>
      <small>Reset access in under a minute.</small>

      <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert--ok"><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert--err"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <?php if ($this->session->flashdata('info')): ?>
        <div class="alert alert--info"><?= htmlspecialchars($this->session->flashdata('info'), ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <?php if (validation_errors()): ?>
        <div class="alert alert--err"><?= validation_errors(); ?></div>
      <?php endif; ?>

      <?= form_open('login/forgot'); ?>
      <?php if (isset($this->security)): ?>
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <?php endif; ?>

      <div>
        <label>Username</label>
        <input class="input" type="text" name="username" value="<?= set_value('username'); ?>" autocomplete="username" required>
        <?= form_error('username', '<div class="alert alert--err" style="margin-top:8px">', '</div>'); ?>
      </div>

      <div>
        <label>Email </label>
        <input class="input" type="email" name="email" value="<?= set_value('email'); ?>" required>
        <?= form_error('email', '<div class="alert alert--err" style="margin-top:8px">', '</div>'); ?>
      </div>

      <button class="btn" type="submit">
        <i class="mdi mdi-send"></i> Send reset link
      </button>
      <?= form_close(); ?>

      <a class="link" href="<?= site_url('login'); ?>"><i class="mdi mdi-arrow-left"></i> Back to login</a>
    </div>
  </div>
</body>

</html>
