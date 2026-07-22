<?php
$jobOrders = isset($data) && is_array($data) ? $data : array();
$clients = isset($data2) && is_array($data2) ? $data2 : array();
$nextInvoiceNo = (!empty($data1) && isset($data1[0]->InvoiceNo)) ? $data1[0]->InvoiceNo + 1 : 100001;
$userLevel = strtolower(trim((string) $this->session->userdata('level')));
$isAdmin = $userLevel === 'admin';
$canAcceptPayment = in_array($userLevel, array('admin', 'staff'), true);

$jobOrderCount = count($jobOrders);
$totalDueAmount = 0.0;
$totalPaidAmount = 0.0;
$totalBalanceAmount = 0.0;
$paidCount = 0;
$unpaidCount = 0;

foreach ($jobOrders as $jobOrder) {
    $totalDueAmount += (float) ($jobOrder->TotalDue ?? 0);
    $totalPaidAmount += (float) ($jobOrder->AmountPaid ?? 0);
    $totalBalanceAmount += (float) ($jobOrder->Balance ?? 0);
    
    $balance = (float) ($jobOrder->Balance ?? 0);
    if ($balance <= 0.00001) {
        $paidCount++;
    } else {
        $unpaidCount++;
    }
}
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
                <div class="container-fluid job-order-page berps-page">


                    <header class="berps-page-header">
                        <div class="berps-page-header__content">
                            <span class="berps-page-header__eyebrow">Job Orders</span>
                            <h1 class="berps-page-title">Job Order Management</h1>
                            <p class="berps-page-subtitle">Track open work, collect payments, and open the generated invoice.</p>
                        </div>
                        <div class="berps-page-header__actions">
                            <a href="<?= base_url(); ?>Page/jobOrderEntry" class="btn btn-primary">
                                <i class="mdi mdi-plus-circle-outline mr-1" aria-hidden="true"></i>Add New Job Order
                            </a>
                        </div>
                    </header>

                    <div class="berps-stat-grid">
                        <div class="berps-stat-card">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($jobOrderCount); ?></p>
                                <p class="berps-stat-card__label">Open Job Orders</p>
                                <p class="berps-stat-card__meta">Active records currently visible in this list</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-clipboard-text-outline"></i></span>
                        </div>
                        <div class="berps-stat-card berps-tone-info">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($totalDueAmount, 2); ?></p>
                                <p class="berps-stat-card__label">Total Due</p>
                                <p class="berps-stat-card__meta">Combined billed amount for open job orders</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-file-document-outline"></i></span>
                        </div>
                        <div class="berps-stat-card berps-tone-success">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($totalPaidAmount, 2); ?></p>
                                <p class="berps-stat-card__label">Collected</p>
                                <p class="berps-stat-card__meta">Payments already posted against these job orders</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-cash-check"></i></span>
                        </div>
                        <div class="berps-stat-card berps-tone-danger">
                            <div>
                                <p class="berps-stat-card__value"><?= number_format($totalBalanceAmount, 2); ?></p>
                                <p class="berps-stat-card__label">Outstanding</p>
                                <p class="berps-stat-card__meta">Remaining balance still to be paid</p>
                            </div>
                            <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-alert-circle-outline"></i></span>
                        </div>
                    </div>

                    <section class="berps-table-card">
                        <div class="berps-table-card__header">
                            <div>
                                <h2 class="berps-section-title">Job Order List</h2>
                                <p class="berps-section-copy">Each saved job order creates its invoice record immediately.</p>
                            </div>

                            <div class="berps-choice-pills">
                                <button class="berps-choice-pill filter-btn active" data-filter="all" onclick="filterJobOrders('all')">
                                    All
                                    <span class="filter-badge"><?= $jobOrderCount; ?></span>
                                </button>
                                <button class="berps-choice-pill filter-btn" data-filter="paid" onclick="filterJobOrders('paid')">
                                    Paid
                                    <span class="filter-badge"><?= $paidCount; ?></span>
                                </button>
                                <button class="berps-choice-pill filter-btn" data-filter="unpaid" onclick="filterJobOrders('unpaid')">
                                    Unpaid
                                    <span class="filter-badge"><?= $unpaidCount; ?></span>
                                </button>
                            </div>
                        </div>

                        <div class="berps-table-card__body">
                            <div class="table-responsive">
                                <table id="jo-table" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Invoice No.</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th class="text-right">Total Due</th>
                                            <th class="text-right">Amount Paid</th>
                                            <th class="text-right">Balance</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($jobOrders)): ?>
                                            <?php foreach ($jobOrders as $row): ?>
                                                <?php
                                                $balance = (float) ($row->Balance ?? 0);
                                                $amountPaid = (float) ($row->AmountPaid ?? 0);
                                                $isFullyPaid = $balance <= 0.00001;
                                                $hasPayment = $amountPaid > 0;
                                                $paymentStateClass = 'berps-status--danger';
                                                $paymentStateLabel = 'Unpaid';

                                                if ($isFullyPaid) {
                                                    $paymentStateClass = 'berps-status--success';
                                                    $paymentStateLabel = 'Fully Paid';
                                                } elseif ($hasPayment) {
                                                    $paymentStateClass = 'berps-status--warning';
                                                    $paymentStateLabel = 'Partially Paid';
                                                }

                                                $invoiceHref = base_url() . 'Page/invoice?id=' . rawurlencode((string) $row->orderID);
                                                $paymentHistoryHref = base_url() . 'Page/paymentHistory?id=' . rawurlencode((string) $row->orderID);
                                                $paymentStatus = $isFullyPaid ? 'paid' : 'unpaid';
                                                ?>
                                                <tr data-payment-status="<?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <td>
                                                        <a class="inv-no-link" href="<?= htmlspecialchars($invoiceHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                            #<?= htmlspecialchars((string) $row->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="customer-line"><?= htmlspecialchars((string) $row->Customer, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="address-line"><?= htmlspecialchars((string) ($row->CustAddress ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td class="num-cell" style="font-size:0.8rem;color:var(--text-soft);"><?= htmlspecialchars((string) $row->TransDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td style="max-width:240px;">
                                                        <div class="desc-main"><?= htmlspecialchars((string) $row->JobDescription, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="desc-meta"><?= htmlspecialchars((string) ($row->Notes ?? 'No notes added'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td class="text-right num-cell"><?= number_format((float) $row->TotalDue, 2); ?></td>
                                                    <td class="text-right">
                                                        <?php if ($hasPayment): ?>
                                                            <a class="action-link num-cell" href="<?= htmlspecialchars($paymentHistoryHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= number_format($amountPaid, 2); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="num-cell"><?= number_format($amountPaid, 2); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                                                            <span class="num-cell"><?= number_format(max($balance, 0), 2); ?></span>
                                                            <span class="berps-status <?= $paymentStateClass; ?>"><?= $paymentStateLabel; ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenu<?= (int) $row->orderID; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                Actions
                                                            </button>

                                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu<?= (int) $row->orderID; ?>">
                                                                <a class="dropdown-item" href="<?= htmlspecialchars($invoiceHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <i class="fa fa-file-invoice"></i> View Invoice
                                                                </a>
                                                                <a class="dropdown-item" href="<?= htmlspecialchars($invoiceHref . '&print=1', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                                                    <i class="fa fa-print"></i> Print Invoice
                                                                </a>
                                                                <a class="dropdown-item" href="<?= base_url(); ?>Page/printJobOrderForm?id=<?= (int) $row->orderID; ?>" target="_blank" rel="noopener">
                                                                    <i class="fa fa-file-alt"></i> Print Job Order Form
                                                                </a>

                                                                <?php if ($hasPayment): ?>
                                                                    <a class="dropdown-item" href="<?= htmlspecialchars($paymentHistoryHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <i class="fa fa-credit-card"></i> View Payment Details
                                                                    </a>
                                                                <?php endif; ?>

                                                                <?php if ($canAcceptPayment && !$isFullyPaid): ?>
                                                                    <a class="dropdown-item" href="<?= base_url(); ?>Page/addPaymentJO?id=<?= (int) $row->orderID; ?>&InvoiceNo=<?= rawurlencode((string) $row->InvoiceNo); ?>&PaymentSource=Job Order&return_to=joList">
                                                                        <i class="fa fa-credit-card"></i> Accept Payment
                                                                    </a>
                                                                <?php elseif (!$isFullyPaid): ?>
                                                                    <span class="dropdown-item disabled text-muted">Payment access unavailable</span>
                                                                <?php else: ?>
                                                                    <div class="dropdown-divider"></div>
                                                                    <span class="dropdown-item disabled text-success">Paid</span>
                                                                <?php endif; ?>

                                                                <?php if ($isAdmin): ?>
                                                                    <div class="dropdown-divider"></div>
                                                                    <a class="dropdown-item" href="<?= base_url(); ?>Page/jobOrderEntry?id=<?= (int) $row->orderID; ?>">
                                                                        <i class="fa fa-edit"></i> Edit Record
                                                                    </a>
                                                                    <?php if (!$isFullyPaid): ?>
                                                                        <a class="dropdown-item text-danger" href="<?= base_url(); ?>Page/deleteJO?id=<?= (int) $row->orderID; ?>&return_to=joList" onclick="return confirm('Are you sure you want to delete this record?');">
                                                                            <i class="fa fa-trash"></i> Delete
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="dropdown-item disabled text-muted">
                                                                            <i class="fa fa-trash"></i> Delete (Paid)
                                                                        </span>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-empty">No job orders found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script>
        (function($) {
            'use strict';

            $(function() {
                var table = $('#jo-table').DataTable({
                    responsive: true,
                    autoWidth: false,
                    stateSave: true,
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    order: [],
                    dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6 text-sm-right"f>>' +
                         'rt' +
                         '<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6"p>>',
                    language: {
                        emptyTable: 'No job orders found.',
                        search: 'Search:',
                        searchPlaceholder: 'Invoice no, customer, description...',
                        lengthMenu: 'Show _MENU_ entries',
                        info: 'Showing _START_ to _END_ of _TOTAL_ job orders'
                    },
                    columnDefs: [{
                        targets: [4, 5, 6],
                        className: 'text-right'
                    }, {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }]
                });

                // Custom filter function
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        var activeFilter = $('.filter-btn.active').data('filter');
                        var row = table.row(dataIndex);
                        var paymentStatus = row.nodes().to$().data('payment-status');

                        if (activeFilter === 'all') {
                            return true;
                        } else if (activeFilter === 'paid') {
                            return paymentStatus === 'paid';
                        } else if (activeFilter === 'unpaid') {
                            return paymentStatus === 'unpaid';
                        }
                        return true;
                    }
                );
            });

            // Global filter function
            window.filterJobOrders = function(filterType) {
                $('.filter-btn').removeClass('active');
                $('.filter-btn[data-filter="' + filterType + '"]').addClass('active');
                $('#jo-table').DataTable().draw();
            };
        })(jQuery);
    </script>

</body>

</html>