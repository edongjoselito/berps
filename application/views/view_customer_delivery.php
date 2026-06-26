<?php include('includes/head.php'); ?>
<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid admin-dashboard-page">

                    <style>
                        
                        * {
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .admin-dashboard-page {
                            --bg: #f8fafc;
                            --surface: #ffffff;
                            --surface-strong: #ffffff;
                            --surface-soft: #f1f5f9;
                            --surface-hover: #f8fafc;
                            --line: #e2e8f0;
                            --line-strong: #cbd5e1;
                            --text: #0f172a;
                            --text-soft: #64748b;
                            --text-faint: #94a3b8;
                            --primary: #3b82f6;
                            --primary-2: #2563eb;
                            --primary-soft: #dbeafe;
                            --primary-light: #eff6ff;
                            --success: #10b981;
                            --success-soft: #d1fae5;
                            --warning: #f59e0b;
                            --warning-soft: #fef3c7;
                            --danger: #ef4444;
                            --danger-soft: #fee2e2;
                            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
                            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                            --radius: 12px;
                            --radius-lg: 16px;
                        }

                        .admin-dashboard-page .card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            box-shadow: var(--shadow-sm);
                            transition: all 0.2s ease;
                        }

                        .admin-dashboard-page .card:hover {
                            box-shadow: var(--shadow);
                            transform: translateY(-1px);
                        }

                        .admin-dashboard-page .card-body {
                            padding: 28px;
                        }

                        .admin-dashboard-page .card-title {
                            font-weight: 600;
                            font-size: 1.125rem;
                            color: var(--text);
                            margin-bottom: 1.5rem;
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        }

                        .admin-dashboard-page .table {
                            color: var(--text);
                            border-collapse: separate;
                            border-spacing: 0;
                        }

                        .admin-dashboard-page .table thead th {
                            background: var(--surface-soft);
                            color: var(--text);
                            font-weight: 600;
                            font-size: 0.875rem;
                            padding: 16px;
                            border-bottom: 2px solid var(--line);
                            text-transform: uppercase;
                            letter-spacing: 0.025em;
                        }

                        .admin-dashboard-page .table tbody td {
                            padding: 16px;
                            border-bottom: 1px solid var(--line);
                            vertical-align: middle;
                            transition: background-color 0.15s ease;
                        }

                        .admin-dashboard-page .table tbody tr:hover td {
                            background-color: var(--surface-hover);
                        }

                        .admin-dashboard-page .btn {
                            border-radius: var(--radius);
                            font-weight: 500;
                            padding: 10px 20px;
                            transition: all 0.2s ease;
                            border: none;
                            display: inline-flex;
                            align-items: center;
                            gap: 0.5rem;
                        }

                        .admin-dashboard-page .btn:hover {
                            transform: translateY(-1px);
                            box-shadow: var(--shadow);
                        }

                        .admin-dashboard-page .btn-primary {
                            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-2) 100%);
                            color: white;
                        }

                        .admin-dashboard-page .btn-secondary {
                            background: var(--surface);
                            color: var(--text-soft);
                            border: 1px solid var(--line);
                        }

                        .admin-dashboard-page .btn-secondary:hover {
                            background: var(--surface-hover);
                            color: var(--text);
                        }

                        .admin-dashboard-page .btn-success {
                            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
                            color: white;
                        }

                        .admin-dashboard-page .badge {
                            padding: 6px 12px;
                            border-radius: 20px;
                            font-weight: 500;
                            font-size: 0.75rem;
                            text-transform: uppercase;
                            letter-spacing: 0.025em;
                            border: none;
                        }

                        .admin-dashboard-page .badge-primary {
                            background: var(--primary-soft);
                            color: var(--primary);
                        }

                        .admin-dashboard-page .badge-success {
                            background: var(--success-soft);
                            color: var(--success);
                        }

                        .admin-dashboard-page .badge-warning {
                            background: var(--warning-soft);
                            color: var(--warning);
                        }

                        .admin-dashboard-page .badge-secondary {
                            background: var(--surface-soft);
                            color: var(--text-soft);
                        }

                        .admin-dashboard-page .badge-danger {
                            background: var(--danger-soft);
                            color: var(--danger);
                        }

                        .admin-dashboard-page .text-muted {
                            color: var(--text-soft);
                        }

                        .delivery-header {
                            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-soft) 100%);
                            padding: 32px;
                            border-radius: var(--radius-lg);
                            margin-bottom: 32px;
                            border: 1px solid var(--primary-soft);
                            position: relative;
                            overflow: hidden;
                        }

                        .delivery-header::before {
                            content: '';
                            position: absolute;
                            top: 0;
                            right: 0;
                            width: 200px;
                            height: 200px;
                            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
                            border-radius: 50%;
                        }

                        .delivery-header h5 {
                            font-weight: 700;
                            font-size: 1.5rem;
                            color: var(--primary);
                            margin-bottom: 1rem;
                        }

                        .delivery-header p {
                            margin-bottom: 0.5rem;
                            font-weight: 500;
                        }

                        .delivery-header strong {
                            color: var(--text-soft);
                        }

                        .total-amount {
                            font-size: 2rem;
                            font-weight: 700;
                            color: var(--primary);
                            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-2) 100%);
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                            background-clip: text;
                        }

                        .page-title-box {
                            background: var(--surface);
                            padding: 24px 32px;
                            border-radius: var(--radius-lg);
                            margin-bottom: 32px;
                            border: 1px solid var(--line);
                            box-shadow: var(--shadow-sm);
                        }

                        .page-title {
                            font-weight: 700;
                            font-size: 1.875rem;
                            color: var(--text);
                            margin-bottom: 0.5rem;
                        }

                        .breadcrumb {
                            background: none;
                            padding: 0;
                            margin: 0;
                        }

                        .breadcrumb-item {
                            color: var(--text-soft);
                            font-weight: 500;
                        }

                        .breadcrumb-item a {
                            color: var(--primary);
                            text-decoration: none;
                            transition: color 0.2s ease;
                        }

                        .breadcrumb-item a:hover {
                            color: var(--primary-2);
                        }

                        .breadcrumb-item.active {
                            color: var(--text);
                        }

                        .table-responsive {
                            border-radius: var(--radius);
                            overflow: hidden;
                            border: 1px solid var(--line);
                        }

                        .status-indicator {
                            display: inline-flex;
                            align-items: center;
                            gap: 0.5rem;
                        }

                        .status-dot {
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            animation: pulse 2s infinite;
                        }

                        @keyframes pulse {
                            0%, 100% { opacity: 1; }
                            50% { opacity: 0.5; }
                        }

                        .status-success .status-dot {
                            background: var(--success);
                        }

                        .status-warning .status-dot {
                            background: var(--warning);
                        }

                        .status-danger .status-dot {
                            background: var(--danger);
                        }

                        .info-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                            gap: 1rem;
                        }

                        .info-item {
                            padding: 1rem;
                            background: var(--surface-soft);
                            border-radius: var(--radius);
                            border-left: 4px solid var(--primary);
                        }

                        .info-label {
                            font-size: 0.875rem;
                            color: var(--text-soft);
                            margin-bottom: 0.25rem;
                        }

                        .info-value {
                            font-weight: 600;
                            color: var(--text);
                        }
                    </style>

                    <!-- Page Title -->
                    <div class="page-title-box">
                        <div class="row align-items-center">
                            <div class="col-sm-6">
                                <h4 class="page-title">View Delivery</h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= base_url('Page/admin'); ?>">Home</a></li>
                                    <li class="breadcrumb-item"><a href="<?= base_url('Page/customerDeliveryList'); ?>">Deliveries</a></li>
                                    <li class="breadcrumb-item active">View Delivery</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <?php if ($delivery): ?>
                        <!-- Delivery Header -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="delivery-header">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <div class="status-indicator status-<?= $delivery->deliveryStatus === 'delivered' ? 'success' : ($delivery->deliveryStatus === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                    <span class="status-dot"></span>
                                                    <span class="badge badge-<?= $delivery->deliveryStatus === 'delivered' ? 'success' : ($delivery->deliveryStatus === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                        <?= ucfirst($delivery->deliveryStatus); ?>
                                                    </span>
                                                </div>
                                                <div class="status-indicator">
                                                    <span class="badge badge-<?= $delivery->paymentStatus === 'paid' ? 'success' : ($delivery->paymentStatus === 'partial' ? 'warning' : 'secondary'); ?>">
                                                        <?= ucfirst($delivery->paymentStatus); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <h5><strong>Delivery No:</strong> <?= htmlspecialchars($delivery->deliveryNo, ENT_QUOTES, 'UTF-8'); ?></h5>
                                            <?php if ($delivery->invoiceNo): ?>
                                                <p class="mb-1"><strong>Invoice:</strong> <?= htmlspecialchars($delivery->invoiceNo, ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php endif; ?>
                                            <p class="mb-1"><strong>Delivery Date:</strong> <?= date('F d, Y', strtotime($delivery->deliveryDate)); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-end">
                                                <div class="d-flex flex-column align-items-end gap-2">
                                                    <div class="badge badge-primary">
                                                        <i class="mdi mdi-calendar me-1"></i>
                                                        <?= date('M j, Y', strtotime($delivery->createdAt)); ?>
                                                    </div>
                                                    <div class="badge badge-secondary">
                                                        <i class="mdi mdi-account me-1"></i>
                                                        <?= htmlspecialchars($delivery->deliveredBy, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">
                                            <i class="mdi mdi-account-circle me-2"></i>
                                            Customer Information
                                        </h5>
                                        <div class="info-grid">
                                            <div class="info-item">
                                                <div class="info-label">Customer Name</div>
                                                <div class="info-value"><?= htmlspecialchars($delivery->customerName, ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-label">Address</div>
                                                <div class="info-value"><?= htmlspecialchars($delivery->customerAddress, ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-label">Received By</div>
                                                <div class="info-value"><?= htmlspecialchars($delivery->receivedBy, ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-label">Delivered By</div>
                                                <div class="info-value"><?= htmlspecialchars($delivery->deliveredBy, ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">
                                            <i class="mdi mdi-cash me-2"></i>
                                            Payment Summary
                                        </h5>
                                        <div class="info-grid">
                                            <div class="info-item">
                                                <div class="info-label">Total Amount</div>
                                                <div class="info-value"><?= number_format($delivery->totalAmount, 2); ?></div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-label">Amount Paid</div>
                                                <div class="info-value text-success"><?= number_format($delivery->amountPaid, 2); ?></div>
                                            </div>
                                            <div class="info-item" style="border-left-color: var(--primary);">
                                                <div class="info-label">Balance</div>
                                                <div class="info-value total-amount"><?= number_format($delivery->balance, 2); ?></div>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-label">Payment Status</div>
                                                <div class="info-value">
                                                    <span class="badge badge-<?= $delivery->paymentStatus === 'paid' ? 'success' : ($delivery->paymentStatus === 'partial' ? 'warning' : 'secondary'); ?>">
                                                        <?= ucfirst($delivery->paymentStatus); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Items -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">
                                            <i class="mdi mdi-package-variant me-2"></i>
                                            Delivery Items
                                        </h5>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th><i class="mdi mdi-text me-1"></i> Item Description</th>
                                                        <th><i class="mdi mdi-counter me-1"></i> Quantity</th>
                                                        <th><i class="mdi mdi-ruler me-1"></i> Unit</th>
                                                        <th><i class="mdi mdi-currency-usd me-1"></i> Unit Price</th>
                                                        <th><i class="mdi mdi-cash me-1"></i> Total</th>
                                                        <th><i class="mdi mdi-information me-1"></i> Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($items)): ?>
                                                        <?php foreach ($items as $item): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-2">
                                                                            <div class="badge badge-primary">
                                                                                <i class="mdi mdi-package"></i>
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <strong><?= htmlspecialchars($item->itemDescription, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-secondary"><?= $item->itemQuantity; ?></span>
                                                                </td>
                                                                <td><?= htmlspecialchars($item->itemUnit, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?= number_format($item->itemUnitPrice, 2); ?></td>
                                                                <td>
                                                                    <strong class="text-primary"><?= number_format($item->lineTotal, 2); ?></strong>
                                                                </td>
                                                                <td>
                                                                    <?php if ($item->serialNo || $item->model || $item->brand): ?>
                                                                        <div class="d-flex flex-column gap-1">
                                                                            <?php if ($item->brand): ?>
                                                                                <small class="badge badge-light">
                                                                                    <i class="mdi mdi-domain me-1"></i><?= htmlspecialchars($item->brand, ENT_QUOTES, 'UTF-8'); ?>
                                                                                </small>
                                                                            <?php endif; ?>
                                                                            <?php if ($item->model): ?>
                                                                                <small class="badge badge-light">
                                                                                    <i class="mdi mdi-cube-outline me-1"></i><?= htmlspecialchars($item->model, ENT_QUOTES, 'UTF-8'); ?>
                                                                                </small>
                                                                            <?php endif; ?>
                                                                            <?php if ($item->serialNo): ?>
                                                                                <small class="badge badge-light">
                                                                                    <i class="mdi mdi-barcode me-1"></i>SN: <?= htmlspecialchars($item->serialNo, ENT_QUOTES, 'UTF-8'); ?>
                                                                                </small>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">
                                                                            <i class="mdi mdi-minus-circle-outline"></i> No details
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <i class="mdi mdi-package-variant-closed font-size-48 mb-2"></i>
                                                                    <span>No items found</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <?php if ($delivery->notes): ?>
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">
                                                <i class="mdi mdi-note-text me-2"></i>
                                                Notes
                                            </h5>
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information me-2"></i>
                                                <?= htmlspecialchars($delivery->notes, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="d-flex gap-3 flex-wrap">
                                    <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn btn-secondary">
                                        <i class="mdi mdi-arrow-left"></i> Back to List
                                    </a>
                                    <a href="javascript:window.print()" class="btn btn-primary">
                                        <i class="mdi mdi-printer"></i> Print Delivery
                                    </a>
                                    <a href="<?= base_url('Page/editCustomerDelivery?id=' . $delivery->deliveryID); ?>" class="btn btn-success">
                                        <i class="mdi mdi-pencil"></i> Edit Delivery
                                    </a>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="text-center py-5">
                                            <i class="mdi mdi-alert-circle d-block font-size-48 text-muted mb-3"></i>
                                            <h5 class="text-muted">Delivery not found</h5>
                                            <a href="<?= base_url('Page/deliveryList'); ?>" class="btn btn-primary">Back to List</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>

    <?php include('includes/footer.php'); ?>

</body>
</html>
