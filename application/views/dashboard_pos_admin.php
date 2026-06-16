<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<style>
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

    .kpi.clickable {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .kpi.clickable:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(36, 59, 83, .2);
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
        margin-bottom: 18px;
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

<body>
    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title mb-1">
                                    POS Admin Dashboard
                                </h4>
                                <div class="clearfix"></div>
                                <hr style="border:0;height:2px;background:linear-gradient(to right,#4285F4 60%,#FBBC05 80%,#34A853 100%);border-radius:1px;margin:12px 0;">
                            </div>
                        </div>
                    </div>

                    <div class="kpi-grid mb-3">
                        <div class="card kpi blue clickable" data-type="sales-today" data-title="Sales Today">
                            <div class="card-body">
                                <div>
                                    <h2 class="count mb-1"><?= isset($kpi_new_transactions) ? (int)$kpi_new_transactions : 0; ?></h2>
                                    <p class="label mb-0">Sales Today</p>
                                </div>
                                <div class="icon"><i class="mdi mdi-cart-outline"></i></div>
                            </div>
                        </div>
                        <div class="card kpi pink clickable" data-type="sales-week" data-title="Sales This Week">
                            <div class="card-body">
                                <div>
                                    <h2 class="count mb-1"><?= isset($kpi_completed_sales) ? (int)$kpi_completed_sales : 0; ?></h2>
                                    <p class="label mb-0">Sales This Week</p>
                                </div>
                                <div class="icon"><i class="mdi mdi-check-all"></i></div>
                            </div>
                        </div>
                        <div class="card kpi purple clickable" data-type="low-stock" data-title="Low Stock Items">
                            <div class="card-body">
                                <div>
                                    <h2 class="count mb-1"><?= isset($summary_low_stock) ? (int)$summary_low_stock : 0; ?></h2>
                                    <p class="label mb-0">Low Stock (<=5)</p>
                                </div>
                                <div class="icon"><i class="mdi mdi-alert"></i></div>
                            </div>
                        </div>
                        <div class="card kpi cyan clickable" data-type="expiring-soon" data-title="Expiring Soon Products">
                            <div class="card-body">
                                <div>
                                    <h2 class="count mb-1"><?= isset($summary_expiring) ? (int)$summary_expiring : 0; ?></h2>
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
                            <h5>Finance Snapshot</h5>
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
                                        <small class="text-light">Live counts</small>
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
                                                            <button type="button" class="btn btn-primary btn-xs waves-effect waves-light inventory-btn" data-type="active-products" data-title="Active Products">
                                                                <?= number_format((int)($summary_active_products ?? 0)); ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align:left;">Expired Products</td>
                                                        <td style="text-align:center;">
                                                            <button type="button" class="btn btn-danger btn-xs waves-effect waves-light inventory-btn" data-type="expired-products" data-title="Expired Products">
                                                                <?= number_format((int)($summary_expired_products ?? 0)); ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align:left;">Low Stock (≤5)</td>
                                                        <td style="text-align:center;">
                                                            <button type="button" class="btn btn-warning btn-xs waves-effect waves-light inventory-btn" data-type="low-stock" data-title="Low Stock Items">
                                                                <?= number_format((int)($summary_low_stock ?? 0)); ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align:left;">Expiring Soon (≤30 days)</td>
                                                        <td style="text-align:center;">
                                                            <button type="button" class="btn btn-info btn-xs waves-effect waves-light inventory-btn" data-type="expiring-soon" data-title="Expiring Soon Products">
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

    <!-- Dashboard Details Modal -->
    <div class="modal fade" id="dashboardDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Dashboard Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">Loading details...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="exportData">Export Data</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle KPI card clicks
            document.querySelectorAll('.kpi.clickable').forEach(function(card) {
                card.addEventListener('click', function() {
                    var type = this.dataset.type;
                    var title = this.dataset.title;
                    loadDashboardDetails(type, title);
                });
            });

            // Handle inventory button clicks
            document.querySelectorAll('.inventory-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var type = this.dataset.type;
                    var title = this.dataset.title;
                    loadDashboardDetails(type, title);
                });
            });

            // Load dashboard details function
            function loadDashboardDetails(type, title) {
                // Set modal title
                document.getElementById('modalTitle').textContent = title;
                
                // Show loading state
                document.getElementById('modalContent').innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading details...</p>
                    </div>
                `;
                
                // Show modal
                $('#dashboardDetailsModal').modal('show');
                
                // Fetch data via AJAX
                $.ajax({
                    url: '<?= base_url(); ?>Pos/getDashboardDetails',
                    method: 'POST',
                    data: {
                        type: type
                    },
                    success: function(response) {
                        if (response.success) {
                            updateModalContent(response.data, type);
                        } else {
                            document.getElementById('modalContent').innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="mdi mdi-alert-circle"></i>
                                    Failed to load data: ${response.message || 'Unknown error'}
                                </div>
                            `;
                        }
                    },
                    error: function() {
                        document.getElementById('modalContent').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="mdi mdi-alert-circle"></i>
                                Failed to load data. Please try again.
                            </div>
                        `;
                    }
                });
            }

            // Update modal content
            function updateModalContent(data, type) {
                var html = '';
                
                if (type === 'sales-today' || type === 'sales-week') {
                    html = generateSalesTable(data);
                } else {
                    html = generateProductTable(data);
                }
                
                document.getElementById('modalContent').innerHTML = html;
            }

            // Generate sales table HTML
            function generateSalesTable(data) {
                var html = `
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        html += `
                            <tr>
                                <td>${item.id || '-'}</td>
                                <td>${item.date || '-'}</td>
                                <td>${item.customer || '-'}</td>
                                <td class="text-right">${item.amount || '0.00'}</td>
                                <td><span class="badge badge-success">${item.status || 'Completed'}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="window.open('${item.view_url || '#'}', '_blank')">
                                        <i class="mdi mdi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html += `<tr><td colspan="6" class="text-center text-muted">No sales data available</td></tr>`;
                }
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                return html;
            }

            // Generate product table HTML
            function generateProductTable(data) {
                var html = `
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Stock Quantity</th>
                                    <th>Reorder Level</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        var statusClass = item.status === 'Active' ? 'success' : 
                                        item.status === 'Low Stock' ? 'warning' : 
                                        item.status === 'Expired' ? 'danger' : 'info';
                        
                        html += `
                            <tr>
                                <td>${item.name || '-'}</td>
                                <td>${item.category || '-'}</td>
                                <td class="text-center">${item.stock || 0}</td>
                                <td class="text-center">${item.reorder_level || 0}</td>
                                <td>${item.expiry_date || '-'}</td>
                                <td><span class="badge badge-${statusClass}">${item.status || 'Unknown'}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="window.open('${item.edit_url || '#'}', '_blank')">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html += `<tr><td colspan="7" class="text-center text-muted">No product data available</td></tr>`;
                }
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                return html;
            }

            // Export data functionality
            document.getElementById('exportData').addEventListener('click', function() {
                // Get current type from modal title or store it globally
                var currentType = document.getElementById('modalTitle').textContent.toLowerCase().replace(/\s+/g, '-');
                window.open('<?= base_url(); ?>Page/exportDashboardData?type=' + currentType, '_blank');
            });
        });
    </script>
</body>

</html>
