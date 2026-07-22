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
                <div class="container-fluid customer-delivery-page berps-page">


                    <div class="cd-hero">
                        <div class="cd-hero__content">
                            <div class="cd-hero__eyebrow">
                                <i class="mdi mdi-truck-fast-outline"></i>
                                Delivery Management
                            </div>
                            <h1 class="cd-hero__title">Customer Deliveries <span class="truck-move">🚚</span></h1>
                            <p class="cd-hero__subtitle">Monitor delivery schedules, payment status, and fulfillment progress.</p>
                        </div>
                        <div class="cd-hero__actions">
                            <div class="berps-choice-pills">
                                <a href="<?= base_url(); ?>Page/customerDeliveryList" class="berps-choice-pill <?= !isset($_GET['status']) ? 'is-active' : ''; ?>">
                                    <i class="mdi mdi-format-list-bulleted"></i> All
                                </a>
                                <a href="<?= base_url(); ?>Page/customerDeliveryList?status=delivered" class="berps-choice-pill <?= isset($_GET['status']) && $_GET['status'] == 'delivered' ? 'is-active' : ''; ?>">
                                    <i class="mdi mdi-truck-check"></i> Delivered
                                </a>
                                <a href="<?= base_url(); ?>Page/customerDeliveryList?status=pending" class="berps-choice-pill <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'is-active' : ''; ?>">
                                    <i class="mdi mdi-clock"></i> Not Yet
                                </a>
                            </div>
                            <a href="<?= base_url(); ?>Page/newCustomerDelivery" class="cd-hero-btn cd-hero-btn--solid">
                                <i class="mdi mdi-plus-circle-outline"></i>
                                <span>Add New Delivery</span>
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

                    <div class="berps-stat-grid">
                         <div class="berps-stat-card">
                              <div>
                                   <p class="berps-stat-card__value"><?= $totalCount; ?></p>
                                   <p class="berps-stat-card__label">Total</p>
                                   <p class="berps-stat-card__meta">All deliveries</p>
                              </div>
                              <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-truck-outline"></i></span>
                         </div>
                         <div class="berps-stat-card berps-tone-success">
                              <div>
                                   <p class="berps-stat-card__value"><?= $deliveredCount; ?></p>
                                   <p class="berps-stat-card__label">Delivered</p>
                                   <p class="berps-stat-card__meta">Completed deliveries</p>
                              </div>
                              <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-truck-check-outline"></i></span>
                         </div>
                         <div class="berps-stat-card berps-tone-warning">
                              <div>
                                   <p class="berps-stat-card__value"><?= $pendingCount; ?></p>
                                   <p class="berps-stat-card__label">Pending</p>
                                   <p class="berps-stat-card__meta">Awaiting delivery</p>
                              </div>
                              <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-clock-outline"></i></span>
                         </div>
                         <div class="berps-stat-card berps-tone-info">
                              <div>
                                   <p class="berps-stat-card__value"><?= number_format($totalAmount, 2); ?></p>
                                   <p class="berps-stat-card__label">Total Value</p>
                                   <p class="berps-stat-card__meta">Delivery amount</p>
                              </div>
                              <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-cash-multiple"></i></span>
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
                                                            <span class="berps-status berps-status--<?= $delivery->deliveryStatus === 'delivered' ? 'success' : ($delivery->deliveryStatus === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                                <?= ucfirst($delivery->deliveryStatus); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="berps-row-actions">
                                                                <a href="<?= base_url(); ?>Page/viewCustomerDelivery?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>" class="berps-icon-action" title="View Details" aria-label="View Details">
                                                                    <i class="mdi mdi-eye"></i>
                                                                </a>

                                                                <a href="<?= base_url(); ?>Page/printDeliveryReceipt?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>" target="_blank" rel="noopener" class="berps-icon-action" title="Print Receipt" aria-label="Print Receipt">
                                                                    <i class="mdi mdi-printer"></i>
                                                                </a>

                                                                <a href="<?= base_url(); ?>Page/editCustomerDelivery?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>" class="berps-icon-action" title="Edit Delivery" aria-label="Edit Delivery">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </a>

                                                                <?php if ($delivery->deliveryStatus === 'delivered'): ?>
                                                                    <a href="javascript:void(0);" class="berps-icon-action" title="Mark as Undelivered" aria-label="Mark as Undelivered">
                                                                        <i class="mdi mdi-check-circle"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <a href="<?= base_url(); ?>Page/updateDeliveryStatus?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>&status=delivered" class="berps-icon-action" title="Mark as Delivered" aria-label="Mark as Delivered">
                                                                        <i class="mdi mdi-truck-delivery"></i>
                                                                    </a>
                                                                <?php endif; ?>


                                                                <?php if ($delivery->deliveryStatus !== 'delivered'): ?>
                                                                <a href="<?= base_url(); ?>Page/deleteCustomerDelivery?deliveryNo=<?= urlencode($delivery->deliveryNo); ?>&customer=<?= urlencode($delivery->customerName); ?>" onclick="return confirm('Are you sure you want to delete this delivery?');" class="berps-icon-action berps-icon-action--danger" title="Delete Delivery" aria-label="Delete Delivery">
                                                                    <i class="mdi mdi-delete"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="berps-icon-action" disabled title="Cannot delete delivered delivery">
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
            autoWidth: false,
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6 text-sm-right"f>>' +
                 'rt' +
                 '<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6"p>>',
            language: {
                emptyTable: 'No deliveries found.',
                search: 'Search:',
                searchPlaceholder: 'Delivery no, customer...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ deliveries'
            },
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
