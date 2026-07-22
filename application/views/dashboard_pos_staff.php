<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid pos-staff-dashboard pb-4">
                    <style>
                        .pos-staff-dashboard .page-title-box {
                            padding: 6px 0 8px;
                            margin: 0 0 12px;
                        }

                        .pos-staff-dashboard .page-title {
                            font-weight: 700;
                            letter-spacing: -0.2px;
                            margin-bottom: 4px;
                        }

                        a.text-decoration-none:hover {
                            text-decoration: none;
                        }

                        .kpi-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                            gap: 16px;
                        }

                        .card.kpi {
                            border: 0;
                            border-radius: 14px;
                            background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
                            box-shadow: 0 6px 18px rgba(36, 59, 83, .08);
                            transition: transform .22s ease, box-shadow .22s ease;
                        }

                        .card.kpi:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 10px 26px rgba(36, 59, 83, .14);
                        }

                        .card.kpi .card-body {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            padding: 1.1rem 1.25rem;
                        }

                        .card.kpi .count {
                            font-size: 2.0rem;
                            font-weight: 800;
                            color: #1f2d3d;
                            margin: 0;
                            line-height: 1;
                        }

                        .card.kpi .label {
                            margin: .15rem 0 0;
                            color: #546e7a;
                            font-weight: 700;
                            letter-spacing: .2px;
                        }

                        .card.kpi .icon {
                            width: 56px;
                            height: 56px;
                            border-radius: 14px;
                            display: grid;
                            place-items: center;
                            font-size: 28px;
                        }

                        .kpi.blue .icon {
                            background: rgba(37, 99, 235, .08);
                            color: #2563eb;
                        }

                        .kpi.pink .icon {
                            background: rgba(236, 72, 153, .10);
                            color: #ec4899;
                        }

                        .kpi.purple .icon {
                            background: rgba(139, 92, 246, .10);
                            color: #8b5cf6;
                        }

                        .kpi.cyan .icon {
                            background: rgba(6, 182, 212, .10);
                            color: #06b6d4;
                        }

                        .kpi.primary .icon {
                            background: rgba(59, 130, 246, .10);
                            color: #3b82f6;
                        }

                        @media (max-width: 767.98px) {
                            .kpi-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                                gap: 12px;
                            }

                            .card.kpi .card-body {
                                padding: .9rem;
                            }

                            .card.kpi .count {
                                font-size: 1.6rem;
                            }

                            .card.kpi .icon {
                                width: 44px;
                                height: 44px;
                                font-size: 22px;
                                border-radius: 12px;
                            }
                        }

                        .inv-card {
                            border: 1px solid #dee2e6;
                            border-radius: 10px;
                            box-shadow: 0 6px 18px rgba(36, 59, 83, .08);
                        }

                        .inv-card .card-header {
                            background: #6f42c1;
                            color: #fff;
                            padding: .9rem 1rem;
                            border-top-left-radius: 10px;
                            border-top-right-radius: 10px;
                        }

                        .inv-card .card-title {
                            margin: 0;
                            font-weight: 600;
                        }

                        .inv-card .table thead th {
                            background: #f8f9fc;
                            font-weight: 700;
                        }

                        .inv-card .table td,
                        .inv-card .table th {
                            vertical-align: middle;
                        }

                        .quick-strip {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 16px;
                            margin: 18px 0;
                        }

                        .quick-card {
                            background: #fff;
                            border: 1px solid #dee2e6;
                            border-radius: 14px;
                            box-shadow: 0 6px 18px rgba(36, 59, 83, .08);
                            padding: 18px 20px;
                        }

                        .quick-card h5 {
                            margin-bottom: 12px;
                            font-weight: 700;
                            color: #1f2d3d;
                        }

                        .quick-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .mini-stats {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 12px;
                        }

                        .mini-stat {
                            padding: 12px 14px;
                            border-radius: 12px;
                            background: #f8fbff;
                            border: 1px solid #e2ebf6;
                        }

                        .mini-stat-label {
                            color: #60758a;
                            font-size: .75rem;
                            text-transform: uppercase;
                            letter-spacing: .08em;
                            font-weight: 700;
                        }

                        .mini-stat-value {
                            margin-top: 6px;
                            font-size: 1.1rem;
                            font-weight: 800;
                            color: #1f2d3d;
                        }

                        @media (max-width: 991.98px) {
                            .quick-strip {
                                grid-template-columns: minmax(0, 1fr);
                            }
                        }
                    </style>

                    <style>
                        /* Hero Banner */
                        .pos-staff-dashboard .ps-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 0 0 22px;
                            border-radius: 16px;
                            background: #4338ca;
                            box-shadow: 0 8px 32px rgba(67, 56, 202, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .pos-staff-dashboard .ps-hero::before {
                            content: '';
                            position: absolute;
                            top: -50%;
                            right: -10%;
                            width: 400px;
                            height: 400px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.06);
                            pointer-events: none;
                        }

                        .pos-staff-dashboard .ps-hero::after {
                            content: '';
                            position: absolute;
                            bottom: -60%;
                            right: 15%;
                            width: 300px;
                            height: 300px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.04);
                            pointer-events: none;
                        }

                        .pos-staff-dashboard .ps-hero__content {
                            position: relative;
                            z-index: 1;
                        }

                        .pos-staff-dashboard .ps-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .pos-staff-dashboard .ps-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .pos-staff-dashboard .ps-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.02em;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                        }

                        .pos-staff-dashboard .ps-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                            max-width: 520px;
                        }

                        .pos-staff-dashboard .ps-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .pos-staff-dashboard .ps-hero-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 8px 16px;
                            border-radius: 10px;
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            background: rgba(255, 255, 255, 0.15);
                            color: #fff;
                            font-size: 0.82rem;
                            font-weight: 600;
                            text-decoration: none;
                            cursor: pointer;
                            transition: all 0.18s ease;
                        }

                        .pos-staff-dashboard .ps-hero-btn:hover,
                        .pos-staff-dashboard .ps-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .pos-staff-dashboard .ps-hero-btn--solid {
                            border-color: rgba(255, 255, 255, 0.6);
                            background: rgba(255, 255, 255, 0.95);
                            color: #4338ca;
                            font-weight: 700;
                        }

                        .pos-staff-dashboard .ps-hero-btn--solid:hover,
                        .pos-staff-dashboard .ps-hero-btn--solid:focus {
                            background: #fff;
                            color: #3730a3;
                        }

                        .pos-staff-dashboard .cart-bounce {
                            display: inline-block;
                            animation: cart-bounce 2s ease-in-out infinite;
                        }

                        @keyframes cart-bounce {
                            0%, 70%, 100% { transform: translateY(0); }
                            15% { transform: translateY(-10px); }
                            30% { transform: translateY(0); }
                            45% { transform: translateY(-5px); }
                            60% { transform: translateY(0); }
                        }

                        .pos-staff-dashboard .card.kpi {
                            border-top: 3px solid #4338ca;
                        }

                        .pos-staff-dashboard .quick-card {
                            border-top: 3px solid #4338ca;
                        }

                        @media (max-width: 767px) {
                            .pos-staff-dashboard .ps-hero,
                            .pos-staff-dashboard .ps-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .pos-staff-dashboard .ps-hero {
                                padding: 20px;
                            }

                            .pos-staff-dashboard .ps-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }
                    </style>

                    <div class="ps-hero">
                        <div class="ps-hero__content">
                            <div class="ps-hero__eyebrow">
                                <i class="mdi mdi-store-outline"></i>
                                POS Panel
                            </div>
                            <h1 class="ps-hero__title"><?= htmlspecialchars($page_title ?? 'POS Staff Panel', ENT_QUOTES, 'UTF-8'); ?> <span class="cart-bounce">🛒</span></h1>
                            <p class="ps-hero__subtitle">Track sales, monitor inventory, and manage transactions from your point-of-sale dashboard.</p>
                        </div>
                        <div class="ps-hero__actions">
                            <a class="ps-hero-btn ps-hero-btn--solid" href="<?= base_url(); ?>Pos/posNewTransaction">
                                <i class="mdi mdi-plus-circle-outline"></i>
                                <span>New Sale</span>
                            </a>
                            <a class="ps-hero-btn" href="<?= base_url(); ?>Pos/posTransactionHistory">
                                <i class="mdi mdi-history"></i>
                                <span>Sales History</span>
                            </a>
                            <a class="ps-hero-btn" href="<?= base_url(); ?>Pos/posReports">
                                <i class="mdi mdi-chart-box-outline"></i>
                                <span>Reports</span>
                            </a>
                            <a class="ps-hero-btn" href="<?= base_url(); ?>Pos/posProductList">
                                <i class="mdi mdi-package-variant-closed"></i>
                                <span>Products</span>
                            </a>
                        </div>
                    </div>

                    <div class="kpi-grid">
                        <div class="card kpi blue">
                            <div class="card-body">
                                <div>
                                    <h2 class="count mb-1"><?= isset($kpi_new_transactions) ? (int)$kpi_new_transactions : 0; ?></h2>
                                    <p class="label mb-0">Sales Today</p>
                                </div>
                                <div class="icon"><i class="mdi mdi-cart-outline"></i></div>
                            </div>
                        </div>
                        <div class="card kpi pink">
                            <div class="card-body">
                                <div>
                                    <h2 class="count mb-1"><?= isset($kpi_completed_sales) ? (int)$kpi_completed_sales : 0; ?></h2>
                                    <p class="label mb-0">Sales This Week</p>
                                </div>
                                <div class="icon"><i class="mdi mdi-check-all"></i></div>
                            </div>
                        </div>
                        <div class="card kpi purple">
                            <div class="card-body">
                                <div>
                                    <h2 class="count mb-1"><?= isset($kpi_low_stock) ? (int)$kpi_low_stock : 0; ?></h2>
                                    <p class="label mb-0">Low Stock (<=5)</p>
                                </div>
                                <div class="icon"><i class="mdi mdi-alert"></i></div>
                            </div>
                        </div>
                        <div class="card kpi cyan">
                            <div class="card-body">
                                <div>
                                    <h2 class="count mb-1"><?= isset($kpi_expiring) ? (int)$kpi_expiring : 0; ?></h2>
                                    <p class="label mb-0">Expiring Soon (<=30d)</p>
                                </div>
                                <div class="icon"><i class="mdi mdi-timer-sand"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="quick-strip">
                        <div class="quick-card">
                            <h5>Quick Actions</h5>
                            <div class="quick-actions">
                                <a href="<?= base_url(); ?>Pos/posNewTransaction" class="btn btn-primary btn-sm">New Sale</a>
                                <a href="<?= base_url(); ?>Pos/posTransactionHistory" class="btn btn-outline-primary btn-sm">Sales History</a>
                                <a href="<?= base_url(); ?>Pos/posReports" class="btn btn-outline-info btn-sm">Reports</a>
                                <a href="<?= base_url(); ?>Pos/posProductList" class="btn btn-outline-secondary btn-sm">Products</a>
                            </div>
                        </div>
                        <div class="quick-card">
                            <h5>Collection Snapshot</h5>
                            <div class="mini-stats">
                                <div class="mini-stat">
                                    <div class="mini-stat-label">Sales Today</div>
                                    <div class="mini-stat-value"><?= number_format((float)($summary_sales_today_amount ?? 0), 2); ?></div>
                                </div>
                                <div class="mini-stat">
                                    <div class="mini-stat-label">Month to Date</div>
                                    <div class="mini-stat-value"><?= number_format((float)($summary_sales_month_amount ?? 0), 2); ?></div>
                                </div>
                                <div class="mini-stat">
                                    <div class="mini-stat-label">Outstanding</div>
                                    <div class="mini-stat-value"><?= number_format((float)($summary_outstanding_balance ?? 0), 2); ?></div>
                                </div>
                                <div class="mini-stat">
                                    <div class="mini-stat-label">Open Installments</div>
                                    <div class="mini-stat-value"><?= number_format((int)($summary_open_installments ?? 0)); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card inv-card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="card-title mb-0 text-white">Inventory Summary</h5>
                                    </div>
                                    <div class="card-widgets">
                                        <a data-toggle="collapse" href="#invSummary" role="button" aria-expanded="true" aria-controls="invSummary">
                                            <i class="mdi mdi-minus text-white"></i>
                                        </a>
                                    </div>
                                </div>
                                <div id="invSummary" class="collapse show">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align:left;">Category</th>
                                                        <th style="text-align:center;">Count</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td style="text-align:left;">Active Products</td>
                                                        <td style="text-align:center;">
                                                            <button type="button" class="btn btn-primary btn-xs waves-effect waves-light">
                                                                <?= number_format((int)($summary_active_products ?? 0)); ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align:left;">Expired Products</td>
                                                        <td style="text-align:center;">
                                                            <button type="button" class="btn btn-danger btn-xs waves-effect waves-light">
                                                                <?= number_format((int)($summary_expired_products ?? 0)); ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align:left;">Low Stock (≤5)</td>
                                                        <td style="text-align:center;">
                                                            <button type="button" class="btn btn-warning btn-xs waves-effect waves-light">
                                                                <?= number_format((int)($summary_low_stock ?? 0)); ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align:left;">Expiring Soon (≤30 days)</td>
                                                        <td style="text-align:center;">
                                                            <button type="button" class="btn btn-info btn-xs waves-effect waves-light">
                                                                <?= number_format((int)($summary_expiring ?? 0)); ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>
