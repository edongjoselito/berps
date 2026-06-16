<?php
$result = isset($result) ? $result : 'success';
$row = isset($row) ? $row : null;
$isPaid = $row && $row->status === 'paid';
$invoiceUrl = $row
    ? base_url('Page/invoice?id=' . rawurlencode((string) $row->orderID))
    : base_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= $isPaid ? 'Payment Received' : 'Payment Status'; ?> | BERPS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: "Segoe UI", system-ui, sans-serif; background: #eef2f7; margin: 0; padding: 60px 20px; color: #0f172a; }
.card { max-width: 520px; margin: 0 auto; background: #fff; border-radius: 14px; padding: 40px; box-shadow: 0 10px 30px rgba(15,23,42,.08); text-align: center; }
.icon { width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 18px; display: flex; align-items: center; justify-content: center; font-size: 38px; color: #fff; }
.icon.ok { background: #059669; }
.icon.wait { background: #d97706; }
.icon.bad { background: #dc2626; }
h1 { margin: 0 0 8px; font-size: 1.4rem; }
p { color: #475569; line-height: 1.6; margin: 4px 0; }
.amount { font-size: 1.6rem; font-weight: 700; margin: 14px 0; color: #0f172a; }
.btn { display: inline-block; margin-top: 22px; background: #1e40af; color: #fff; padding: 12px 22px; border-radius: 8px; text-decoration: none; font-weight: 600; }
.btn:hover { background: #1d3fa5; }
.muted { font-size: 0.8rem; color: #94a3b8; margin-top: 20px; }
</style>
</head>
<body>
<div class="card">
<?php if ($result === 'cancel'): ?>
    <div class="icon bad">&times;</div>
    <h1>Payment Cancelled</h1>
    <p>You cancelled the checkout. No charge was made.</p>
<?php elseif ($isPaid): ?>
    <div class="icon ok">&#10003;</div>
    <h1>Payment Received</h1>
    <p>Thank you! Your payment has been confirmed.</p>
    <div class="amount">PHP <?= number_format((float) $row->amount, 2); ?></div>
    <p>Invoice <strong>#<?= htmlspecialchars((string) $row->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?></strong></p>
<?php else: ?>
    <div class="icon wait">&#8987;</div>
    <h1>Processing Payment</h1>
    <p>We've received your checkout. PayMongo is still confirming the charge &mdash; you'll see the invoice marked as paid as soon as it clears.</p>
    <?php if ($row): ?>
        <div class="amount">PHP <?= number_format((float) $row->amount, 2); ?></div>
        <p>Invoice <strong>#<?= htmlspecialchars((string) $row->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?></strong></p>
    <?php endif; ?>
<?php endif; ?>

    <a class="btn" href="<?= htmlspecialchars($invoiceUrl, ENT_QUOTES, 'UTF-8'); ?>">Back to Invoice</a>
    <?php if (!$isPaid && $result !== 'cancel' && $row): ?>
        <p class="muted">This page will refresh automatically.</p>
        <script>setTimeout(function(){ location.reload(); }, 5000);</script>
    <?php endif; ?>
</div>
</body>
</html>
