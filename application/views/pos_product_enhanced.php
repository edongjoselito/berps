<?php
$products = is_array($products ?? null) ? $products : [];
$edit_product = isset($edit_product) ? $edit_product : null;
$open_edit_modal = !empty($open_edit_modal) && $edit_product;
$next_sku = $next_sku ?? '';
$pos_categories = is_array($pos_categories ?? null) ? $pos_categories : [];
$notice = $notice ?? '';
$notice_type = $notice_type ?? '';

function getBusinessTypeBadgeClass($type) {
    $classes = [
        'pharmacy' => 'success',
        'grocery' => 'warning',
        'restaurant' => 'danger',
        'electronics' => 'info',
        'clothing' => 'primary',
        'general' => 'secondary'
    ];
    return $classes[$type] ?? 'secondary';
}

function getIndustryInfo($product) {
    $type = $product->business_type ?? 'general';
    $info = '';

    switch ($type) {
        case 'pharmacy':
            $info = $product->generic_name ?? '';
            break;
        case 'grocery':
            $info = $product->product_type ?? '';
            break;
        case 'restaurant':
            $info = $product->menu_category ?? '';
            break;
        case 'electronics':
            $info = $product->model_number ?? '';
            break;
        case 'clothing':
            $info = $product->material ?? '';
            break;
    }

    return $info ?: 'Standard';
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<style>
    :root{
        --primary:#3b82f6;
        --primary-dark:#2563eb;
        --success:#16a34a;
        --warning:#f59e0b;
        --danger:#dc2626;
        --info:#06b6d4;
        --secondary:#64748b;
        --bg:#f4f7fb;
        --card:#ffffff;
        --text:#0f172a;
        --muted:#64748b;
        --border:#e2e8f0;
        --shadow:0 10px 30px rgba(15, 23, 42, 0.08);
        --radius:16px;
    }

    body {
        background: var(--bg);
    }

    .content-page,
    .content,
    .container-fluid {
        background: transparent !important;
    }

    .page-shell {
        padding-top: 0;
        padding-bottom: 24px;
    }

    .hero-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #38bdf8 100%);
        border-radius: 22px;
        padding: 28px 30px;
        color: #fff;
        box-shadow: 0 18px 40px rgba(37, 99, 235, 0.20);
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
    }

    .hero-card::before,
    .hero-card::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        background: rgba(255,255,255,0.10);
        pointer-events: none;
    }

    .hero-card::before {
        width: 180px;
        height: 180px;
        top: -60px;
        right: -40px;
    }

    .hero-card::after {
        width: 130px;
        height: 130px;
        bottom: -40px;
        right: 140px;
    }

    .hero-card__inner {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
    }

    .hero-card__title {
        font-size: 30px;
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: 6px;
        color: #fff;
    }

    .hero-card__subtitle {
        font-size: 14px;
        color: rgba(255,255,255,0.88);
        margin: 0;
        max-width: 700px;
    }

    .hero-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn-modern {
        border-radius: 12px;
        min-height: 44px;
        padding: 0.65rem 1.1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.10);
    }

    .btn-modern.btn-light {
        color: #0f172a;
        border: none;
    }

    .btn-modern.btn-primary {
        background: #fff;
        color: var(--primary-dark);
        border: none;
    }

    .btn-modern.btn-outline-light {
        border: 1px solid rgba(255,255,255,0.35);
        color: #fff;
        background: rgba(255,255,255,0.08);
    }

    .stats-row {
        margin-bottom: 20px;
    }

    .mini-stat {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow);
        padding: 18px 20px;
        height: 100%;
    }

    .mini-stat__label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .mini-stat__value {
        font-size: 28px;
        font-weight: 800;
        color: var(--text);
        line-height: 1;
    }

    .mini-stat__sub {
        margin-top: 8px;
        font-size: 13px;
        color: var(--muted);
    }

    .info-panel {
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        border: 1px solid #dbeafe;
        border-left: 5px solid var(--primary);
        border-radius: 18px;
        box-shadow: var(--shadow);
        padding: 22px 24px;
        margin-bottom: 22px;
    }

    .info-panel h5 {
        font-weight: 800;
        color: #1e3a8a;
        margin-bottom: 14px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .info-item {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 14px;
    }

    .info-item strong {
        display: block;
        margin-bottom: 5px;
        color: var(--text);
        font-size: 14px;
    }

    .info-item span {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.45;
    }

    .main-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .main-card__header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .main-card__title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text);
        margin: 0;
    }

    .main-card__subtitle {
        font-size: 13px;
        color: var(--muted);
        margin: 3px 0 0;
    }

    .table-wrap {
        padding: 18px;
    }

    #datatable {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 10px !important;
    }

    #datatable thead th {
        border: none !important;
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 14px 14px !important;
        white-space: nowrap;
    }

    #datatable tbody tr {
        background: #fff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    }

    #datatable tbody td {
        vertical-align: middle !important;
        border-top: 1px solid var(--border) !important;
        border-bottom: 1px solid var(--border) !important;
        border-left: none !important;
        border-right: none !important;
        padding: 15px 14px !important;
    }

    #datatable tbody td:first-child {
        border-left: 1px solid var(--border) !important;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    #datatable tbody td:last-child {
        border-right: 1px solid var(--border) !important;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .product-name {
        font-weight: 700;
        color: var(--text);
    }

    .sku-chip {
        display: inline-flex;
        align-items: center;
        background: #eef2ff;
        color: #3730a3;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .stock-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .stock-pill.in {
        background: #dcfce7;
        color: #166534;
    }

    .stock-pill.low {
        background: #fef3c7;
        color: #92400e;
    }

    .stock-pill.out {
        background: #fee2e2;
        color: #991b1b;
    }

    .price-text {
        font-weight: 800;
        color: #0f172a;
    }

    .industry-note {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.35;
    }

    .action-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: .2s ease;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .action-btn.edit {
        background: #e0f2fe;
        color: #0369a1;
    }

    .action-btn.delete {
        background: #fee2e2;
        color: #b91c1c;
    }

    .badge {
        padding: .48rem .68rem;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .enhanced-modal .modal-dialog {
        max-width: 1180px;
    }

    .enhanced-modal .modal-content {
        border: none;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.18);
    }

    .enhanced-modal .modal-header {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        color: #fff;
        border: none;
        padding: 18px 24px;
    }

    .enhanced-modal .modal-title {
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .enhanced-modal .close {
        color: #fff;
        opacity: 1;
        text-shadow: none;
    }

    .enhanced-modal .modal-body {
        background: #f8fafc;
        padding: 0;
    }

    .enhanced-modal .modal-footer {
        background: #fff;
        border-top: 1px solid var(--border);
        padding: 16px 22px;
    }

    .step-indicator {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        padding: 20px 22px 0;
        background: transparent;
        margin-bottom: 0;
    }

    .step {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 74px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }

    .step-number {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #e2e8f0;
        color: #334155;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .step-text {
        font-size: 13px;
        font-weight: 800;
        color: var(--muted);
        line-height: 1.3;
    }

    .step.active {
        border-color: #bfdbfe;
        background: #eff6ff;
    }

    .step.active .step-number {
        background: var(--primary);
        color: #fff;
    }

    .step.active .step-text {
        color: #1d4ed8;
    }

    .step.completed {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .step.completed .step-number {
        background: var(--success);
        color: #fff;
    }

    .step.completed .step-text {
        color: #15803d;
    }

    .step-content {
        padding: 22px;
    }

    .business-type-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }

    .business-type-card {
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 18px;
        padding: 22px 18px;
        cursor: pointer;
        transition: .25s ease;
        text-align: left;
        color: var(--text);
        min-height: 165px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }

    .business-type-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 28px rgba(15, 23, 42, 0.10);
        border-color: #93c5fd;
    }

    .business-type-card.selected {
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        border-color: var(--primary);
        box-shadow: 0 16px 32px rgba(37, 99, 235, 0.14);
    }

    .business-type-card i {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 14px;
        background: #eff6ff;
        color: var(--primary-dark);
    }

    .business-type-card h4 {
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 800;
        color: var(--text);
    }

    .business-type-card p {
        margin: 0;
        font-size: 13px;
        color: var(--muted);
        line-height: 1.5;
        opacity: 1;
    }

    .industry-fields {
        background: #fff;
        border-radius: 18px;
        padding: 24px;
        border: 1px solid var(--border);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        margin-bottom: 0;
    }

    .industry-fields h5 {
        color: var(--text);
        font-weight: 800;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
        font-size: 17px;
    }

    .industry-specific-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
    }

    .field-group {
        margin-bottom: 0;
    }

    .field-group label {
        display: block;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 7px;
        font-size: 13px;
    }

    .field-group .form-control,
    .field-group .form-control-sm,
    .field-group textarea,
    .field-group select {
        border: 1px solid #dbe2ea;
        border-radius: 12px;
        min-height: 46px;
        padding: 10px 14px;
        font-size: 14px;
        box-shadow: none !important;
        transition: .2s ease;
    }

    .field-group textarea.form-control {
        min-height: 100px;
    }

    .field-group .form-control:focus,
    .field-group .form-control-sm:focus,
    .field-group textarea:focus,
    .field-group select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12) !important;
    }

    .field-help {
        font-size: 12px;
        color: var(--muted);
        margin-top: 6px;
        line-height: 1.4;
    }

    .required-indicator {
        color: var(--danger);
        font-weight: 800;
    }

    .pharmacy-fields {
        border-left: 5px solid var(--success);
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
    }

    .grocery-fields {
        border-left: 5px solid var(--warning);
        background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
    }

    .restaurant-fields {
        border-left: 5px solid var(--danger);
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
    }

    .electronics-fields {
        border-left: 5px solid var(--info);
        background: linear-gradient(135deg, #ecfeff 0%, #ffffff 100%);
    }

    .clothing-fields {
        border-left: 5px solid #7c3aed;
        background: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);
    }

    .general-fields {
        border-left: 5px solid var(--secondary);
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    }

    .alert-industry {
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 18px;
        border: none;
        font-size: 13px;
        line-height: 1.5;
    }

    .alert-industry.pharmacy {
        background: #dcfce7;
        color: #166534;
    }

    .alert-industry.grocery {
        background: #fef3c7;
        color: #92400e;
    }

    .alert-industry.restaurant {
        background: #fee2e2;
        color: #991b1b;
    }

    .alert-industry.electronics {
        background: #cffafe;
        color: #155e75;
    }

    .alert-industry.clothing {
        background: #ede9fe;
        color: #5b21b6;
    }

    .alert-industry.general {
        background: #e2e8f0;
        color: #334155;
    }

    .check-grid-2,
    .check-grid-4 {
        display: grid;
        gap: 8px;
    }

    .check-grid-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .check-grid-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .check-option {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.7);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 9px 10px;
        font-size: 13px;
        color: #334155;
        min-height: 42px;
    }

    .check-option input {
        margin: 0;
    }

    .footer-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        min-height: 38px;
        padding: 6px 10px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 10px !important;
        margin: 0 2px;
    }

    .empty-state {
        text-align: center;
        padding: 42px 20px;
        color: var(--muted);
    }

    .empty-state i {
        font-size: 42px;
        margin-bottom: 10px;
        display: block;
        color: #94a3b8;
    }

    @media (max-width: 991px) {
        .step-indicator {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .hero-card {
            padding: 22px 18px;
            border-radius: 18px;
        }

        .hero-card__title {
            font-size: 24px;
        }

        .step-indicator {
            grid-template-columns: 1fr;
        }

        .industry-specific-grid,
        .business-type-selector {
            grid-template-columns: 1fr;
        }

        .check-grid-2,
        .check-grid-4 {
            grid-template-columns: 1fr 1fr;
        }

        .table-wrap {
            padding: 12px;
        }

        .step-content {
            padding: 16px;
        }

        .industry-fields {
            padding: 18px;
        }
    }

    @media (max-width: 480px) {
        .check-grid-2,
        .check-grid-4 {
            grid-template-columns: 1fr;
        }

        .hero-card__actions,
        .footer-actions {
            width: 100%;
        }

        .hero-card__actions .btn-modern,
        .footer-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<body>
<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="page-shell">

                <div class="hero-card">
                    <div class="hero-card__inner">
                        <div>
                            <div class="hero-card__title">Product Management</div>
                            <p class="hero-card__subtitle">
                                Manage products with a cleaner and more modern industry-based workflow for pharmacy, grocery, restaurant, electronics, clothing, and general inventory.
                            </p>
                        </div>

                        <div class="hero-card__actions">
                            <button type="button" class="btn btn-modern btn-primary" data-toggle="modal" data-target="#addProductModal">
                                <i class="mdi mdi-plus-circle"></i> Add Product
                            </button>
                            <a href="<?= base_url('Pos/posProductList'); ?>" class="btn btn-modern btn-outline-light">
                                <i class="mdi mdi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row stats-row">
                    <div class="col-md-4 mb-3">
                        <div class="mini-stat">
                            <div class="mini-stat__label">Total Products</div>
                            <div class="mini-stat__value"><?= count($products); ?></div>
                            <div class="mini-stat__sub">All products currently in the list</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="mini-stat">
                            <div class="mini-stat__label">Next SKU</div>
                            <div class="mini-stat__value" style="font-size:20px;"><?= htmlspecialchars($next_sku, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="mini-stat__sub">Auto-generated serial number</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="mini-stat">
                            <div class="mini-stat__label">Supported Types</div>
                            <div class="mini-stat__value">6</div>
                            <div class="mini-stat__sub">Business-specific product categories</div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($notice)): ?>
                    <div class="alert alert-<?= ($notice_type === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert" style="border-radius:14px; box-shadow: var(--shadow);">
                        <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="info-panel">
                    <h5><i class="mdi mdi-information-outline"></i> Industry-Specific Product Entry</h5>
                    <p class="mb-0" style="color:#475569;">
                        The form adapts depending on the business type so you can capture cleaner, more relevant product data.
                    </p>

                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Pharmacy</strong>
                            <span>Generic name, dosage form, strength, FDA registration, prescription settings</span>
                        </div>
                        <div class="info-item">
                            <strong>Grocery</strong>
                            <span>Product type, allergens, nutritional settings, shelf life, origin</span>
                        </div>
                        <div class="info-item">
                            <strong>Restaurant</strong>
                            <span>Preparation time, dietary options, cooking method, allergen warnings</span>
                        </div>
                        <div class="info-item">
                            <strong>Electronics</strong>
                            <span>Model number, warranty, compatibility, specs, serial tracking</span>
                        </div>
                        <div class="info-item">
                            <strong>Clothing</strong>
                            <span>Material, sizes, colors, fit type, care instructions</span>
                        </div>
                        <div class="info-item">
                            <strong>General</strong>
                            <span>Standard description, specifications, usage instructions, safety information</span>
                        </div>
                    </div>
                </div>

                <div class="main-card">
                    <div class="main-card__header">
                        <div>
                            <h4 class="main-card__title">Product List</h4>
                            <p class="main-card__subtitle">Browse, edit, and maintain your product catalog</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table id="datatable" class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Serial Number</th>
                                        <th>Product</th>
                                        <th>Business Type</th>
                                        <th>Category</th>
                                        <th>Unit Price</th>
                                        <th>Stock</th>
                                        <th>Industry Info</th>
                                        <th class="no-details">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($products)): ?>
                                    <?php $row = 1; foreach ($products as $p): ?>
                                        <?php
                                            $stockQty = (int) $p->stock_qty;
                                            $stockClass = 'in';
                                            $stockLabel = 'In Stock';

                                            if ($stockQty <= 0) {
                                                $stockClass = 'out';
                                                $stockLabel = 'Out of Stock';
                                            } elseif ($stockQty <= 5) {
                                                $stockClass = 'low';
                                                $stockLabel = 'Low Stock';
                                            }
                                        ?>
                                        <tr>
                                            <td><strong><?= $row++; ?></strong></td>
                                            <td>
                                                <span class="sku-chip"><?= htmlspecialchars($p->sku, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td>
                                                <div class="product-name"><?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= getBusinessTypeBadgeClass($p->business_type ?? 'general'); ?>">
                                                    <?= ucfirst($p->business_type ?? 'General'); ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($p->category, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="price-text"><?= number_format((float)$p->unit_price, 2); ?></span></td>
                                            <td>
                                                <span class="stock-pill <?= $stockClass; ?>">
                                                    <?= $stockQty; ?> • <?= $stockLabel; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="industry-note"><?= htmlspecialchars(getIndustryInfo($p), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td class="no-details">
                                                <div class="action-group">
                                                    <a href="<?= base_url('Pos/posEditProduct/' . $p->id) ?>" class="action-btn edit" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= base_url('Pos/posDeleteProduct/' . $p->id) ?>"
                                                       class="action-btn delete"
                                                       title="Delete"
                                                       onclick="return confirm('Delete this product?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <i class="mdi mdi-package-variant-closed"></i>
                                                No products yet.
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
    </div>
</div>

<div class="modal fade enhanced-modal" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="post" action="<?= base_url('Pos/posCreateProduct'); ?>" id="enhancedProductForm">
            <input type="hidden" name="business_type" value="general">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mb-0">
                        <i class="mdi mdi-plus-circle"></i>
                        Add Product
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="step-indicator">
                        <div class="step active" id="step2">
                            <div class="step-number">2</div>
                            <div class="step-text">Basic Product Info</div>
                        </div>
                        <div class="step" id="step4">
                            <div class="step-number">4</div>
                            <div class="step-text">Pricing and Inventory</div>
                        </div>
                    </div>

                    <div id="step2Content" class="step-content">
                        <div class="industry-fields">
                            <h5>Basic Product Information</h5>
                            <div class="industry-specific-grid">
                                <div class="field-group">
                                    <label>Serial Number <span class="required-indicator">*</span></label>
                                    <input type="text" name="sku" class="form-control"
                                           value="<?= htmlspecialchars($next_sku, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    <div class="field-help">Auto-generated unique identifier</div>
                                </div>

                                <div class="field-group">
                                    <label>Product Name <span class="required-indicator">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                <div class="field-group">
                                    <label>Category <span class="required-indicator">*</span></label>
                                    <select name="category" class="form-control" required>
                                        <option value="">Select category</option>
                                        <?php foreach ($pos_categories as $cat): ?>
                                            <?php $catName = trim((string) ($cat->name ?? '')); ?>
                                            <?php if ($catName !== ''): ?>
                                                <option value="<?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="field-group">
                                    <label>Barcode / UPC</label>
                                    <input type="text" name="barcode" class="form-control">
                                    <div class="field-help">Useful for barcode scanning in POS</div>
                                </div>

                                <div class="field-group">
                                    <label>Unit of Measurement <span class="required-indicator">*</span></label>
                                    <select name="unit" class="form-control" required>
                                        <option value="pcs">Pieces (pcs)</option>
                                        <option value="kg">Kilograms (kg)</option>
                                        <option value="lb">Pounds (lb)</option>
                                        <option value="l">Liters (l)</option>
                                        <option value="ml">Milliliters (ml)</option>
                                        <option value="box">Box</option>
                                        <option value="bottle">Bottle</option>
                                        <option value="pack">Pack</option>
                                        <option value="dozen">Dozen</option>
                                        <option value="meter">Meter</option>
                                        <option value="set">Set</option>
                                    </select>
                                </div>

                                <div class="field-group">
                                    <label>Brand / Manufacturer</label>
                                    <input type="text" name="brand" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="step4Content" class="step-content" style="display:none;">
                        <div class="industry-fields">
                            <h5>Pricing and Inventory Management</h5>
                            <div class="industry-specific-grid">
                                <div class="field-group">
                                    <label>Unit Cost <span class="required-indicator">*</span></label>
                                    <input type="number" step="0.01" name="unit_cost" class="form-control" value="0" required>
                                    <div class="field-help">Cost price of the product</div>
                                </div>
                                <div class="field-group">
                                    <label>Unit Price <span class="required-indicator">*</span></label>
                                    <input type="number" step="0.01" name="unit_price" class="form-control" value="0" required>
                                    <div class="field-help">Selling price to customers</div>
                                </div>
                                <div class="field-group">
                                    <label>Tax Type <span class="required-indicator">*</span></label>
                                    <select name="tax_type" class="form-control" required>
                                        <option value="vatable">Vatable (12%)</option>
                                        <option value="vat_exempt">VAT Exempt</option>
                                        <option value="zero_rated">Zero Rated</option>
                                    </select>
                                </div>
                                <div class="field-group">
                                    <label>Initial Stock Quantity <span class="required-indicator">*</span></label>
                                    <input type="number" name="stock_qty" class="form-control" value="0" required>
                                </div>
                                <div class="field-group">
                                    <label>Reorder Level <span class="required-indicator">*</span></label>
                                    <input type="number" name="reorder_level" class="form-control" value="5" required>
                                    <div class="field-help">Low stock warning threshold</div>
                                </div>
                                <div class="field-group">
                                    <label>Discount Eligible</label>
                                    <select name="discount_eligible" class="form-control">
                                        <option value="1">Yes - Can be discounted</option>
                                        <option value="0">No - Fixed price only</option>
                                    </select>
                                </div>
                                <div class="field-group">
                                    <label>Expiry Date</label>
                                    <input type="date" name="expiry_date" class="form-control">
                                    <div class="field-help">For products with expiration dates</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="footer-actions ml-auto">
                        <button type="button" class="btn btn-light" id="prevBtn" onclick="previousStep()" style="display:none;">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()" disabled>Next</button>
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">
                            <i class="mdi mdi-content-save"></i> Save Product
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>
</div>

<?php include('includes/themecustomizer.php'); ?>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script>
    let currentStep = 2;

    function nextStep() {
        if (!validateStep(currentStep)) {
            return;
        }

        document.getElementById(`step${currentStep}Content`).style.display = 'none';
        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.getElementById(`step${currentStep}`).classList.add('completed');

        currentStep = (currentStep === 2) ? 4 : (currentStep + 1);

        document.getElementById(`step${currentStep}Content`).style.display = 'block';
        document.getElementById(`step${currentStep}`).classList.add('active');

        updateStepButtons();
    }

    function previousStep() {
        document.getElementById(`step${currentStep}Content`).style.display = 'none';
        document.getElementById(`step${currentStep}`).classList.remove('active');

        currentStep = (currentStep === 4) ? 2 : (currentStep - 1);

        document.getElementById(`step${currentStep}Content`).style.display = 'block';
        document.getElementById(`step${currentStep}`).classList.remove('completed');
        document.getElementById(`step${currentStep}`).classList.add('active');

        updateStepButtons();
    }

    function updateStepButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        if (currentStep === 2) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
            nextBtn.disabled = false;
        } else if (currentStep === 4) {
            prevBtn.style.display = 'inline-block';
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
            nextBtn.disabled = false;
        } else {
            prevBtn.style.display = 'inline-block';
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
            nextBtn.disabled = false;
        }
    }

    function validateStep(step) {
        if (step === 2) {
            const name = document.querySelector('input[name="name"]').value.trim();
            const category = (document.querySelector('select[name="category"]').value || '').trim();
            const unit = document.querySelector('select[name="unit"]').value;

            if (!name || !category || !unit) {
                alert('Please fill in all required fields in Basic Product Information.');
                return false;
            }
        }

        if (step === 4) {
            const unitCost = document.querySelector('input[name="unit_cost"]').value;
            const unitPrice = document.querySelector('input[name="unit_price"]').value;
            const stockQty = document.querySelector('input[name="stock_qty"]').value;
            const reorderLevel = document.querySelector('input[name="reorder_level"]').value;

            if (!unitCost || !unitPrice || !stockQty || !reorderLevel) {
                alert('Please fill in all required pricing and stock fields.');
                return false;
            }

            if (parseFloat(unitPrice) <= 0) {
                alert('Unit price must be greater than 0.');
                return false;
            }
        }

        return true;
    }

    document.getElementById('enhancedProductForm').addEventListener('submit', function() {
        const existingField = this.querySelector('input[name="business_type"]');
        if (existingField) {
            existingField.value = 'general';
        }
    });

    $(document).ready(function () {
        $('#datatable').DataTable({
            responsive: true,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: 'no-details' }
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                zeroRecords: "No matching products found",
                info: "Showing _START_ to _END_ of _TOTAL_ products",
                infoEmpty: "No products available",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            }
        });

        $('#addProductModal').on('hidden.bs.modal', function () {
            currentStep = 2;

            $('#enhancedProductForm')[0].reset();
            $('.step').removeClass('active completed');
            $('#step2').addClass('active');

            $('.step-content').hide();
            $('#step2Content').show();

            $('#step3Content .industry-fields').hide();

            document.getElementById('nextBtn').style.display = 'inline-block';
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('prevBtn').style.display = 'none';
            document.getElementById('nextBtn').disabled = false;

            const skuInput = document.querySelector('input[name="sku"]');
            if (skuInput) {
                skuInput.value = <?= json_encode($next_sku) ?>;
            }
        });

        updateStepButtons();
    });
</script>
</div>
</body>
</html>
