<?php
$data = isset($data) ? $data : array();
$data2 = isset($data2) ? $data2 : array();
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid invoice-list-page">

                    <style>
                         @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                         .invoice-list-page {
                              --bg: #f5f7fb;
                              --surface: rgba(255, 255, 255, 0.96);
                              --surface-strong: #ffffff;
                              --surface-soft: #f8fbff;
                              --line: #e4ebf4;
                              --line-strong: #cfdbea;
                              --text: #142235;
                              --text-soft: #617489;
                              --text-faint: #8ea0b5;
                              --primary: #2563eb;
                              --primary-2: #1d4ed8;
                              --primary-soft: #eaf2ff;
                              --success: #059669;
                              --warning: #d97706;
                              --danger: #dc2626;
                              --font-head: 'DM Sans', system-ui, -apple-system, sans-serif;
                              --font-body: 'DM Sans', system-ui, -apple-system, sans-serif;
                         }

                         .invoice-list-page * {
                              box-sizing: border-box;
                         }

                         .invoice-list-page .content {
                              margin-bottom: 40px;
                         }

                         .invoice-list-page .page-header {
                              display: flex;
                              justify-content: space-between;
                              align-items: flex-end;
                              gap: 20px;
                              flex-wrap: wrap;
                              margin-bottom: 24px;
                              margin-top: 20px;
                         }

                         .invoice-list-page .page-eyebrow {
                              display: inline-flex;
                              align-items: center;
                              gap: 8px;
                              font-size: 0.875rem;
                              font-weight: 600;
                              color: var(--primary);
                              text-transform: uppercase;
                              letter-spacing: 0.05em;
                              margin-bottom: 12px;
                         }

                         .invoice-list-page .page-eyebrow::before {
                              content: '';
                              width: 8px;
                              height: 8px;
                              background: var(--primary);
                              border-radius: 50%;
                              box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                         }

                         .invoice-list-page .page-title {
                              margin: 0;
                              font-family: var(--font-head);
                              font-size: 1.5rem;
                              font-weight: 700;
                              color: var(--text);
                              letter-spacing: -0.02em;
                         }

                         .invoice-list-page .page-subtitle {
                              margin-top: 6px;
                              color: var(--text-soft);
                              font-size: 0.9rem;
                              max-width: 760px;
                         }

                         .invoice-list-page .page-actions {
                              display: flex;
                              gap: 12px;
                              flex-wrap: wrap;
                         }

                         .invoice-list-page .stats-grid {
                              display: grid;
                              grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                              gap: 20px;
                              margin-bottom: 28px;
                              margin-top: 8px;
                         }

                         .invoice-list-page .stat-card {
                              background: var(--surface);
                              border: 1px solid rgba(255, 255, 255, 0.72);
                              border-radius: 16px;
                              padding: 20px;
                              box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                         }

                         .invoice-list-page .stat-label {
                              font-size: 0.875rem;
                              color: var(--text-soft);
                              margin-bottom: 8px;
                              font-weight: 500;
                         }

                         .invoice-list-page .stat-value {
                              font-size: 1.75rem;
                              font-weight: 700;
                              color: var(--text);
                         }

                         .invoice-list-page .stat-meta {
                              font-size: 0.75rem;
                              color: var(--text-faint);
                              margin-top: 4px;
                         }

                         .invoice-list-page .card-stack {
                              display: grid;
                              gap: 16px;
                              margin-top: 4px;
                         }

                         .invoice-list-page .theme-card {
                              background: var(--surface);
                              border: 1px solid rgba(255, 255, 255, 0.72);
                              border-radius: 16px;
                              box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                              overflow: hidden;
                         }

                         .invoice-list-page .theme-card-head {
                              padding: 14px 18px;
                              border-bottom: 1px solid var(--line);
                              background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                         }

                         .invoice-list-page .theme-card-title {
                              margin: 0;
                              color: var(--text);
                              font-size: 0.95rem;
                              font-weight: 700;
                              letter-spacing: -0.01em;
                         }

                         .invoice-list-page .theme-card-body,
                         .modal-body.inv-modal-body {
                              padding-left: 16px;
                              padding-right: 16px;
                         }

                         .invoice-list-page #delivery-table tbody td {
                              padding: 13px 12px !important;
                         }

                         .inv-modal-footer {
                              flex-direction: column;
                              align-items: stretch;
                         }

                         .inv-modal-footer .text-right {
                              width: 100%;
                              display: flex;
                              gap: 10px;
                         }

                         .inv-modal-footer .text-right button {
                              width: 100%;
                         }

                         .invoice-list-page .item-builder-head {
                              flex-direction: column;
                              align-items: stretch;
                         }

                         .invoice-list-page .btn-add-entry {
                              width: 100%;
                         }

                         .invoice-list-page .btn-action,
                         .invoice-list-page .btn-submit {
                              display: inline-flex;
                              align-items: center;
                              justify-content: center;
                              gap: 8px;
                              border-radius: 12px;
                              font-size: 0.92rem;
                              font-weight: 700;
                              padding: 11px 18px;
                              transition: all 0.16s ease;
                              text-decoration: none;
                         }

                         .invoice-list-page .btn-action {
                              border: 1px solid var(--line-strong);
                              color: var(--text);
                              background: #fff;
                         }

                         .invoice-list-page .btn-action:hover {
                              color: var(--primary);
                              border-color: #bfd3ef;
                              background: #f9fbff;
                              transform: translateY(-1px);
                              box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
                         }

                         .invoice-list-page .btn-submit {
                              background: var(--primary);
                              color: #fff;
                              border: 1px solid var(--primary);
                         }

                         .invoice-list-page .btn-submit:hover {
                              background: var(--primary-2);
                              border-color: var(--primary-2);
                              transform: translateY(-1px);
                              box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
                         }

                         .invoice-list-page .tbl-btn {
                              display: inline-flex;
                              align-items: center;
                              justify-content: center;
                              gap: 6px;
                              padding: 6px 12px;
                              border-radius: 8px;
                              font-size: 0.85rem;
                              font-weight: 600;
                              transition: all 0.16s ease;
                              border: none;
                              cursor: pointer;
                              text-decoration: none;
                         }

                         .invoice-list-page .tbl-btn-print {
                              background: var(--primary);
                              color: #fff;
                         }

                         .invoice-list-page .tbl-btn-print:hover {
                              background: var(--primary-2);
                              transform: translateY(-1px);
                              box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
                         }

                         .invoice-list-page .tbl-actions {
                              display: flex;
                              align-items: center;
                              justify-content: center;
                              gap: 6px;
                              flex-wrap: wrap;
                         }

                         .invoice-list-page .tbl-btn {
                              display: inline-flex;
                              align-items: center;
                              justify-content: center;
                              gap: 4px;
                              padding: 6px 10px;
                              border-radius: 6px;
                              font-size: 0.8rem;
                              font-weight: 600;
                              transition: all 0.16s ease;
                              border: none;
                              cursor: pointer;
                              text-decoration: none;
                              min-width: 32px;
                              height: 32px;
                              background: var(--primary);
                              color: #fff;
                         }

                         .invoice-list-page .tbl-btn:hover {
                              transform: translateY(-1px);
                              box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                         }

                         .invoice-list-page .tbl-btn-success {
                              background: var(--success);
                              color: #fff;
                         }

                         .invoice-list-page .tbl-btn-success:hover {
                              background: #047857;
                         }

                         .invoice-list-page .tbl-btn-warning {
                              background: var(--warning);
                              color: #fff;
                         }

                         .invoice-list-page .tbl-btn-warning:hover {
                              background: #b45309;
                         }

                         .invoice-list-page .tbl-btn-danger {
                              background: var(--danger);
                              color: #fff;
                         }

                         .invoice-list-page .tbl-btn-danger:hover {
                              background: #b91c1c;
                         }

                         .invoice-list-page .badge {
                              padding: 4px 10px;
                              border-radius: 6px;
                              font-weight: 500;
                              font-size: 0.75rem;
                         }

                         .invoice-list-page .badge-success {
                              background: #d1fae5;
                              color: var(--success);
                         }

                         .invoice-list-page .badge-warning {
                              background: #fef3c7;
                              color: var(--warning);
                         }

                         .invoice-list-page .badge-danger {
                              background: #fee2e2;
                              color: var(--danger);
                         }

                         .invoice-list-page .badge-secondary {
                              background: #f1f5f9;
                              color: var(--text-soft);
                         }

                         .invoice-list-page .table-responsive {
                              border-radius: 12px;
                              overflow: hidden;
                              box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                         }

                         .invoice-list-page #delivery-table {
                              background: var(--surface-strong);
                              border: 1px solid var(--line);
                              border-radius: 12px;
                              overflow: hidden;
                         }

                         .invoice-list-page #delivery-table thead th {
                              background: var(--surface-soft);
                              color: var(--text);
                              font-weight: 600;
                              font-size: 0.875rem;
                              padding: 14px 16px;
                              border-bottom: 2px solid var(--line);
                              text-transform: uppercase;
                              letter-spacing: 0.05em;
                         }

                         .invoice-list-page #delivery-table tbody td {
                              padding: 13px 12px !important;
                              border-bottom: 1px solid var(--line);
                              vertical-align: middle;
                              color: var(--text);
                         }

                         .invoice-list-page #delivery-table tbody tr:hover {
                              background: rgba(37, 99, 235, 0.02);
                         }

                         /* Minimal sidebar fix - only for this page */
                         .left-side-menu .metismenu li a {
                              pointer-events: auto !important;
                         }

                         /* Fix dropdown visibility on all screens */
                         .dropdown-menu {
                              position: absolute !important;
                              z-index: 9999 !important;
                              background: white !important;
                              border: 1px solid #dee2e6 !important;
                              border-radius: 0.375rem !important;
                              box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175) !important;
                         }

                         .dropdown-menu.show {
                              display: block !important;
                         }

                         .dropdown-menu-right {
                              right: 0 !important;
                              left: auto !important;
                         }

                         @media (max-width: 768px) {
                              .invoice-list-page .page-header {
                                   flex-direction: column;
                                   align-items: stretch;
                              }

                              .invoice-list-page .page-actions {
                                   justify-content: stretch;
                              }

                              .invoice-list-page .btn-action,
                              .invoice-list-page .btn-submit {
                                   width: 100%;
                                   justify-content: center;
                              }

                              .invoice-list-page .stats-grid {
                                   grid-template-columns: 1fr;
                              }

                              /* Ensure dropdowns are visible on mobile */
                              .dropdown-menu {
                                   position: fixed !important;
                                   top: auto !important;
                                   bottom: 0 !important;
                                   left: 0 !important;
                                   right: 0 !important;
                                   width: 100% !important;
                                   max-height: 50vh !important;
                                   overflow-y: auto !important;
                                   border-radius: 0 !important;
                                   border-top: 1px solid #dee2e6 !important;
                                   box-shadow: 0 -0.5rem 1rem rgba(0, 0, 0, 0.175) !important;
                                   z-index: 9999 !important;
                              }

                              .dropdown-menu-right {
                                   right: 0 !important;
                                   left: 0 !important;
                              }

                              .dropdown-item {
                                   z-index: 10000 !important;
                                   position: relative !important;
                              }
                         }
                    </style>

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Delivery Management</div>
                            <h4 class="page-title">Customer Deliveries</h4>
                        </div>
                        <div class="page-actions">
                            <div class="filter-buttons" style="display: flex; gap: 8px; margin-right: 12px;">
                                <a href="<?= base_url(); ?>Page/customerDeliveryList" class="btn-action <?= !isset($_GET['status']) ? 'active' : ''; ?>">
                                    <i class="mdi mdi-format-list-bulleted"></i> All
                                </a>
                                <a href="<?= base_url(); ?>Page/customerDeliveryList?status=delivered" class="btn-action <?= isset($_GET['status']) && $_GET['status'] == 'delivered' ? 'active' : ''; ?>">
                                    <i class="mdi mdi-truck-check"></i> Delivered
                                </a>
                                <a href="<?= base_url(); ?>Page/customerDeliveryList?status=pending" class="btn-action <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'active' : ''; ?>">
                                    <i class="mdi mdi-clock"></i> Not Yet
                                </a>
                            </div>
                            <a href="<?= base_url(); ?>Page/newCustomerDelivery" class="btn-submit">
                                <i class="mdi mdi-plus-circle-outline"></i>
                                Add New Delivery
                            </a>
                        </div>
                    </div>

                    <?php
                    // Calculate statistics
                    $totalCount   = !empty($data) ? count($data) : 0;
                    $deliveredCount = 0;
                    $pendingCount = 0;
                    $cancelledCount = 0;
                    $totalAmount = 0;
                    
                    if (!empty($data)) {
                         foreach ($data as $d) {
                              if ($d->deliveryStatus === 'delivered') $deliveredCount++;
                              elseif ($d->deliveryStatus === 'pending') $pendingCount++;
                              elseif ($d->deliveryStatus === 'cancelled') $cancelledCount++;
                              $totalAmount += (float)$d->total_amount;
                         }
                    }
                    ?>

                    <div class="stats-grid">
                         <div class="stat-card stat-total">
                              <div class="stat-label">Total</div>
                              <div class="stat-value"><?= $totalCount; ?></div>
                              <div class="stat-meta">All deliveries</div>
                         </div>
                         <div class="stat-card stat-paid">
                              <div class="stat-label">Delivered</div>
                              <div class="stat-value"><?= $deliveredCount; ?></div>
                              <div class="stat-meta">Completed deliveries</div>
                         </div>
                         <div class="stat-card stat-partial">
                              <div class="stat-label">Pending</div>
                              <div class="stat-value"><?= $pendingCount; ?></div>
                              <div class="stat-meta">Awaiting delivery</div>
                         </div>
                         <div class="stat-card stat-unpaid">
                              <div class="stat-label">Total Value</div>
                              <div class="stat-value"><?= number_format($totalAmount, 2); ?></div>
                              <div class="stat-meta">Delivery amount</div>
                         </div>
                    </div>

                    <div class="card-stack">
                        <div class="theme-card">
                            <div class="theme-card-head">
                                <h5 class="theme-card-title">Delivery List</h5>
                            </div>
                            <div class="theme-card-body">

                                <?php if ($this->session->flashdata('success')): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <?php if ($this->session->flashdata('error')): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table id="delivery-table" class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Delivery No</th>
                                                <th>Customer</th>
                                                <th>Delivery Date</th>
                                                <th>Total Amount</th>
                                                <th>Paid</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($data)): ?>
                                                <?php foreach ($data as $delivery): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= htmlspecialchars($delivery->deliveryNo, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                            <?php if ($delivery->primary_invoice_no): ?>
                                                                <br><small class="text-muted">Inv: <?= htmlspecialchars($delivery->primary_invoice_no, ENT_QUOTES, 'UTF-8'); ?></small>
                                                            <?php endif; ?>
                                                                                                                    </td>
                                                        <td>
                                                            <strong><?= htmlspecialchars($delivery->customerName, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                            <br><small class="text-muted"><?= htmlspecialchars(substr($delivery->customerAddress, 0, 50), ENT_QUOTES, 'UTF-8'); ?>...</small>
                                                        </td>
                                                        <td>
                                                            <?php if ($delivery->delivery_count > 1): ?>
                                                                <?= date('M d, Y', strtotime($delivery->first_delivery_date)); ?> - <?= date('M d, Y', strtotime($delivery->last_delivery_date)); ?>
                                                            <?php else: ?>
                                                                <?= date('M d, Y', strtotime($delivery->first_delivery_date)); ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?= number_format($delivery->total_amount, 2); ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php 
                                                            // Debug: Show actual value
                                                            $totalPaid = $delivery->total_paid ?? 0;
                                                            if ($totalPaid > 0) {
                                                                echo number_format($totalPaid, 2);
                                                            } else {
                                                                echo '<span style="color: #999;">0.00</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?= number_format($delivery->total_balance, 2); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-<?= $delivery->deliveryStatus === 'delivered' ? 'success' : ($delivery->deliveryStatus === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                                <?= ucfirst($delivery->deliveryStatus); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="tbl-actions">
                                                                <a href="<?= base_url(); ?>Page/viewCustomerDelivery?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>" class="tbl-btn" title="View Details">
                                                                    <i class="mdi mdi-eye"></i>
                                                                </a>

                                                                <a href="<?= base_url(); ?>Page/printDeliveryReceipt?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>" target="_blank" rel="noopener" class="tbl-btn" title="Print Receipt">
                                                                    <i class="mdi mdi-printer"></i>
                                                                </a>

                                                                <a href="<?= base_url(); ?>Page/editCustomerDelivery?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>" class="tbl-btn" title="Edit Delivery">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </a>

                                                                <?php if ($delivery->deliveryStatus === 'delivered'): ?>
                                                                    <a href="javascript:void(0);" class="tbl-btn tbl-btn-success" title="Mark as Undelivered">
                                                                        <i class="mdi mdi-check-circle"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <a href="<?= base_url(); ?>Page/updateDeliveryStatus?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>&status=delivered" class="tbl-btn tbl-btn-warning" title="Mark as Delivered">
                                                                        <i class="mdi mdi-truck-delivery"></i>
                                                                    </a>
                                                                <?php endif; ?>

                                                                
                                                                <?php if ($delivery->deliveryStatus !== 'delivered'): ?>
                                                                <a href="<?= base_url(); ?>Page/deleteCustomerDelivery?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>" onclick="return confirm('Are you sure you want to delete this delivery?');" class="tbl-btn tbl-btn-danger" title="Delete Delivery">
                                                                    <i class="mdi mdi-delete"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="tbl-btn tbl-btn-secondary" disabled title="Cannot delete delivered delivery">
                                                                    <i class="mdi mdi-delete-off"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">
                                                        <div class="py-5">
                                                            <i class="mdi mdi-truck d-block font-size-48 text-muted mb-3"></i>
                                                            <h5 class="text-muted">No deliveries found</h5>
                                                            <p class="text-muted">Create your first delivery to get started</p>
                                                            <a href="<?= base_url('Page/newCustomerDelivery'); ?>" class="btn btn-submit">
                                                                <i class="mdi mdi-plus"></i> Add New Delivery
                                                            </a>
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

    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTables
        $('#delivery-table').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: 7 }
            ]
        });

        // Initialize Bootstrap dropdowns
        $('.dropdown-toggle').dropdown();
        
        // Fix dropdown positioning in DataTables
        $('#delivery-table').on('draw.dt', function() {
            $('.dropdown-toggle').dropdown();
        });
    });
    </script>

</body>
</html>
