<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Database Error</title>
    <style>
        body { margin: 30px; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        .box { border: 1px solid #e0e0e0; border-radius: 8px; padding: 18px; background:#fff; max-width: 640px; }
        h1 { margin-top: 0; font-size: 20px; }
        .muted { color: #6c757d; font-size: 14px; }
        code { background:#f8f9fa; padding:2px 4px; border-radius:4px; }
    </style>
</head>
<body>
<div class="box">
    <h1>Database Error</h1>
    <p class="muted">A database error occurred. Please contact support.</p>
    <?php if (ENVIRONMENT !== 'production'): ?>
        <p><strong><?= htmlspecialchars($heading ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <div><?= $message; ?></div>
    <?php endif; ?>
</div>
</body>
</html>
