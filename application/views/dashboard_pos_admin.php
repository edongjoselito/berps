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
                    <header class="berps-page-header">
                        <div class="berps-page-header__content">
                            <span class="berps-page-header__eyebrow">Point of sale</span>
                            <h1 class="berps-page-title">POS Admin Dashboard</h1>
                            <p class="berps-page-subtitle">Monitor sales, inventory risk, and outstanding balances from one view.</p>
                        </div>
                        <div class="berps-page-header__actions">
                            <a href="<?= base_url(); ?>Pos/posNewTransaction" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-cart-plus mr-1" aria-hidden="true"></i>New sale
                            </a>
                        </div>
                    </header>

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
