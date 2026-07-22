<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid berps-page pos-admin-dashboard-page">
                    <style>
                        .pos-admin-dashboard-page .pa-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 24px 0 22px;
                            border-radius: 16px;
                            background: #c2410c;
                            box-shadow: 0 8px 32px rgba(194, 65, 12, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .pos-admin-dashboard-page .pa-hero::before {
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

                        .pos-admin-dashboard-page .pa-hero::after {
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

                        .pos-admin-dashboard-page .pa-hero__content {
                            position: relative;
                            z-index: 1;
                        }

                        .pos-admin-dashboard-page .pa-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .pos-admin-dashboard-page .pa-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .pos-admin-dashboard-page .pa-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.02em;
                        }

                        .pos-admin-dashboard-page .pa-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                            max-width: 520px;
                        }

                        .pos-admin-dashboard-page .pa-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .pos-admin-dashboard-page .pa-hero-btn {
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

                        .pos-admin-dashboard-page .pa-hero-btn:hover,
                        .pos-admin-dashboard-page .pa-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .pos-admin-dashboard-page .pa-hero-btn--solid {
                            border-color: rgba(255, 255, 255, 0.6);
                            background: rgba(255, 255, 255, 0.95);
                            color: #c2410c;
                            font-weight: 700;
                        }

                        .pos-admin-dashboard-page .pa-hero-btn--solid:hover,
                        .pos-admin-dashboard-page .pa-hero-btn--solid:focus {
                            background: #fff;
                            color: #9a3412;
                        }

                        /* Cart bounce animation */
                        .pos-admin-dashboard-page .cart-bounce {
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

                        /* Deep orange accent on cards */
                        .pos-admin-dashboard-page .berps-section-card,
                        .pos-admin-dashboard-page .berps-table-card {
                            border-top: 3px solid #c2410c;
                        }

                        .pos-admin-dashboard-page .berps-section-card__header,
                        .pos-admin-dashboard-page .berps-table-card__header {
                            border-bottom: 2px solid #c2410c;
                        }

                        .pos-admin-dashboard-page .berps-section-title {
                            color: #c2410c;
                        }

                        /* Responsive hero */
                        @media (max-width: 767px) {
                            .pos-admin-dashboard-page .pa-hero,
                            .pos-admin-dashboard-page .pa-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .pos-admin-dashboard-page .pa-hero {
                                padding: 20px;
                            }

                            .pos-admin-dashboard-page .pa-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }
                    </style>

                    <div class="pa-hero">
                        <div class="pa-hero__content">
                            <div class="pa-hero__eyebrow">
                                <i class="mdi mdi-store-outline"></i>
                                Point of Sale
                            </div>
                            <h1 class="pa-hero__title">POS Admin Dashboard <span class="cart-bounce">🛒</span></h1>
                            <p class="pa-hero__subtitle">Monitor sales, inventory risk, and outstanding balances from one view.</p>
                        </div>
                        <div class="pa-hero__actions">
                            <a class="pa-hero-btn pa-hero-btn--solid" href="<?= base_url(); ?>Pos/posNewTransaction">
                                <i class="mdi mdi-cart-plus"></i>
                                <span>New Sale</span>
                            </a>
                        </div>
                    </div>

                    <section aria-labelledby="pos-summary-heading">
                        <h2 id="pos-summary-heading" class="sr-only">Sales and inventory summary</h2>
                        <div class="berps-stat-grid">
                            <div class="berps-stat-card berps-tone-info is-interactive kpi clickable" role="button" tabindex="0" data-type="sales-today" data-title="Sales Today" aria-label="View sales today details">
                                <div>
                                    <p class="berps-stat-card__value"><?= isset($kpi_new_transactions) ? (int) $kpi_new_transactions : 0; ?></p>
                                    <p class="berps-stat-card__label">Sales today</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-cart-outline"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-success is-interactive kpi clickable" role="button" tabindex="0" data-type="sales-week" data-title="Sales This Week" aria-label="View sales this week details">
                                <div>
                                    <p class="berps-stat-card__value"><?= isset($kpi_completed_sales) ? (int) $kpi_completed_sales : 0; ?></p>
                                    <p class="berps-stat-card__label">Sales this week</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-check-all"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-danger is-interactive kpi clickable" role="button" tabindex="0" data-type="low-stock" data-title="Low Stock Items" aria-label="View low-stock item details">
                                <div>
                                    <p class="berps-stat-card__value"><?= isset($summary_low_stock) ? (int) $summary_low_stock : 0; ?></p>
                                    <p class="berps-stat-card__label">Low stock (5 or fewer)</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-alert-outline"></i></span>
                            </div>
                            <div class="berps-stat-card berps-tone-warning is-interactive kpi clickable" role="button" tabindex="0" data-type="expiring-soon" data-title="Expiring Soon Products" aria-label="View products expiring within 30 days">
                                <div>
                                    <p class="berps-stat-card__value"><?= isset($summary_expiring) ? (int) $summary_expiring : 0; ?></p>
                                    <p class="berps-stat-card__label">Expiring within 30 days</p>
                                </div>
                                <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-timer-sand"></i></span>
                            </div>
                        </div>
                    </section>

                    <div class="berps-layout-grid">
                        <section class="berps-section-card" aria-labelledby="pos-actions-heading">
                            <div class="berps-section-card__header">
                                <div>
                                    <h2 id="pos-actions-heading" class="berps-section-title">Quick actions</h2>
                                    <p class="berps-section-copy">Start a sale or open a frequently used POS area.</p>
                                </div>
                            </div>
                            <div class="berps-section-card__body">
                                <div class="berps-section-nav">
                                    <a href="<?= base_url(); ?>Pos/posNewTransaction" class="btn btn-primary btn-sm">New sale</a>
                                    <a href="<?= base_url(); ?>Pos/posTransactionHistory" class="btn btn-outline-primary btn-sm">Sales history</a>
                                    <a href="<?= base_url(); ?>Pos/posReports" class="btn btn-outline-primary btn-sm">Reports</a>
                                    <a href="<?= base_url(); ?>Pos/posProductList" class="btn btn-outline-primary btn-sm">Products</a>
                                </div>
                            </div>
                        </section>

                        <section class="berps-section-card" aria-labelledby="finance-snapshot-heading">
                            <div class="berps-section-card__header">
                                <div>
                                    <h2 id="finance-snapshot-heading" class="berps-section-title">Finance snapshot</h2>
                                    <p class="berps-section-copy">Current sales and collection totals.</p>
                                </div>
                            </div>
                            <div class="berps-section-card__body">
                                <div class="berps-summary-grid">
                                    <div class="berps-summary-item">
                                        <div><span class="berps-summary-item__label">Sales today</span><span class="berps-summary-item__value"><?= number_format((float) ($summary_sales_today_amount ?? 0), 2); ?></span></div>
                                    </div>
                                    <div class="berps-summary-item">
                                        <div><span class="berps-summary-item__label">Month to date</span><span class="berps-summary-item__value"><?= number_format((float) ($summary_sales_month_amount ?? 0), 2); ?></span></div>
                                    </div>
                                    <div class="berps-summary-item">
                                        <div><span class="berps-summary-item__label">Outstanding</span><span class="berps-summary-item__value"><?= number_format((float) ($summary_outstanding_balance ?? 0), 2); ?></span></div>
                                    </div>
                                    <div class="berps-summary-item">
                                        <div><span class="berps-summary-item__label">Open installments</span><span class="berps-summary-item__value"><?= number_format((int) ($summary_open_installments ?? 0)); ?></span></div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section class="berps-table-card" aria-labelledby="inventory-summary-heading">
                        <div class="berps-table-card__header">
                            <div>
                                <h2 id="inventory-summary-heading" class="berps-section-title">Inventory summary</h2>
                                <p class="berps-section-copy">Live product counts and inventory warnings.</p>
                            </div>
                            <a class="btn btn-light btn-sm" data-toggle="collapse" href="#invSummary" role="button" aria-expanded="true" aria-controls="invSummary">
                                <i class="mdi mdi-minus" aria-hidden="true"></i><span class="sr-only">Toggle inventory summary</span>
                            </a>
                        </div>
                        <div id="invSummary" class="collapse show">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th class="text-center">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Active products</td>
                                            <td class="text-center"><button type="button" class="btn btn-primary btn-sm inventory-btn" data-type="active-products" data-title="Active Products"><?= number_format((int) ($summary_active_products ?? 0)); ?></button></td>
                                        </tr>
                                        <tr>
                                            <td>Expired products</td>
                                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm inventory-btn" data-type="expired-products" data-title="Expired Products"><?= number_format((int) ($summary_expired_products ?? 0)); ?></button></td>
                                        </tr>
                                        <tr>
                                            <td>Low stock (5 or fewer)</td>
                                            <td class="text-center"><button type="button" class="btn btn-warning btn-sm inventory-btn" data-type="low-stock" data-title="Low Stock Items"><?= number_format((int) ($summary_low_stock ?? 0)); ?></button></td>
                                        </tr>
                                        <tr>
                                            <td>Expiring within 30 days</td>
                                            <td class="text-center"><button type="button" class="btn btn-info btn-sm inventory-btn" data-type="expiring-soon" data-title="Expiring Soon Products"><?= number_format((int) ($summary_expiring ?? 0)); ?></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
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
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Dashboard Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                function openCardDetails() {
                    var type = this.dataset.type;
                    var title = this.dataset.title;
                    loadDashboardDetails(type, title);
                }

                card.addEventListener('click', openCardDetails);
                card.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openCardDetails.call(this);
                    }
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
