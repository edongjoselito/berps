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

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box mb-2">
                                <h4 class="page-title"><?= htmlspecialchars($page_title ?? 'POS Staff Panel', ENT_QUOTES, 'UTF-8'); ?></h4>
                                <div class="clearfix"></div>
                                <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:12px 0;" />
                            </div>
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
