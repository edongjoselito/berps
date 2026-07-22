<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid invoice-list-page berps-page">

                         <?php if ($this->session->flashdata('success')): ?>
                              <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                   <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <?php if ($this->session->flashdata('danger')): ?>
                              <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                   <?= htmlspecialchars($this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <?php
                         $selectedCustomerId = isset($selectedCustomerId) ? trim((string) $selectedCustomerId) : '';
                         $clientOptions = isset($data2) && is_array($data2) ? $data2 : array();
                         $selectedCustomerName = 'All customers';
                         foreach ($clientOptions as $clientOption) {
                              $optionCustId = trim((string) ($clientOption->CustID ?? ''));
                              if ($optionCustId !== '' && $optionCustId === $selectedCustomerId) {
                                   $selectedCustomerName = trim((string) ($clientOption->Customer ?? ''));
                                   break;
                              }
                         }
                         ?>


                         <div class="inv-hero">
                              <div class="inv-hero__content">
                                   <div class="inv-hero__eyebrow">
                                        <i class="mdi mdi-file-document-multiple-outline"></i>
                                        Invoice Management
                                   </div>
                                   <h1 class="inv-hero__title">Invoices <span class="bill-flip">🧾</span></h1>
                                   <p class="inv-hero__subtitle">Manage billing records, monitor payments, and track outstanding balances.</p>
                              </div>
                              <div class="inv-hero__actions">
                                   <a href="<?= base_url(); ?>Page/invoiceEntry" class="inv-hero-btn inv-hero-btn--solid">
                                        <i class="mdi mdi-plus-circle-outline"></i>
                                        <span>Add New Invoice</span>
                                   </a>
                              </div>
                         </div>

                         <?php
                         // Helper function to calculate covered period for recurring invoices
                         function getCoveredMonths($invoice)
                         {
                              $frequency = $invoice->recurringFrequency ?? 'none';
                              $scheduleDate = $invoice->recurringScheduleDate ?? '';

                              if ($frequency === 'none' || empty($scheduleDate)) {
                                   return '';
                              }

                              $startDate = new DateTime($scheduleDate);
                              $endDate = clone $startDate;

                              // Calculate the covered period based on frequency
                              switch ($frequency) {
                                   case 'daily':
                                        // Daily: just the single day (schedule date)
                                        $endDate = $startDate;
                                        break;

                                   case 'weekly':
                                        // Weekly: 7 days from schedule date
                                        $endDate->modify('+6 days');
                                        break;

                                   case 'monthly':
                                        // Monthly: from schedule date to schedule date + 1 month - 1 day
                                        $endDate->modify('+1 month');
                                        $endDate->modify('-1 day');
                                        break;

                                   case 'quarterly':
                                        // Quarterly: from schedule date to schedule date + 3 months - 1 day
                                        $endDate->modify('+3 months');
                                        $endDate->modify('-1 day');
                                        break;

                                   case 'yearly':
                                        // Yearly: from schedule date to schedule date + 1 year - 1 day
                                        $endDate->modify('+1 year');
                                        $endDate->modify('-1 day');
                                        break;

                                   default:
                                        return '';
                              }

                              return 'From ' . date('M d, Y', $startDate->getTimestamp()) . ' To ' . date('M d, Y', $endDate->getTimestamp());
                         }

                         // Safely calculate next invoice number, handling non-numeric formats
                         $nextInvoiceNo = 100001;
                         if (!empty($data1) && isset($data1[0]->InvoiceNo)) {
                              $lastInvoiceNo = $data1[0]->InvoiceNo;
                              // Extract numeric portion if it ends with digits
                              if (preg_match('/(\d+)$/', $lastInvoiceNo, $matches)) {
                                   $nextInvoiceNo = (int)$matches[1] + 1;
                              } else {
                                   // If no numeric ending, use timestamp-based number
                                   $nextInvoiceNo = (int)date('Ymd') . '001';
                              }
                         }

                         $totalCount   = !empty($data) ? count($data) : 0;
                         $paidCount    = 0;
                         $partialCount = 0;
                         $unpaidCount = 0;
                         if (!empty($data)) {
                              foreach ($data as $r) {
                                   $b = (float)$r->Balance;
                                   $p = (float)$r->AmountPaid;
                                   if ($b <= 0.00001)      $paidCount++;
                                   elseif ($p > 0)          $partialCount++;
                                   else                     $unpaidCount++;
                              }
                         }
                         ?>

                         <div class="berps-stat-grid">
                              <div class="berps-stat-card">
                                   <div>
                                        <p class="berps-stat-card__value"><?= $totalCount; ?></p>
                                        <p class="berps-stat-card__label">Total</p>
                                        <p class="berps-stat-card__meta">All invoices</p>
                                   </div>
                                   <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-file-document-outline"></i></span>
                              </div>
                              <div class="berps-stat-card berps-tone-success">
                                   <div>
                                        <p class="berps-stat-card__value"><?= $paidCount; ?></p>
                                        <p class="berps-stat-card__label">Fully Paid</p>
                                        <p class="berps-stat-card__meta">Completed payments</p>
                                   </div>
                                   <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-check-circle-outline"></i></span>
                              </div>
                              <div class="berps-stat-card berps-tone-warning">
                                   <div>
                                        <p class="berps-stat-card__value"><?= $partialCount; ?></p>
                                        <p class="berps-stat-card__label">Partial</p>
                                        <p class="berps-stat-card__meta">Partially paid</p>
                                   </div>
                                   <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-clock-outline"></i></span>
                              </div>
                              <div class="berps-stat-card berps-tone-danger">
                                   <div>
                                        <p class="berps-stat-card__value"><?= $unpaidCount; ?></p>
                                        <p class="berps-stat-card__label">Unpaid</p>
                                        <p class="berps-stat-card__meta">Awaiting payment</p>
                                   </div>
                                   <span class="berps-stat-card__icon" aria-hidden="true"><i class="mdi mdi-alert-circle-outline"></i></span>
                              </div>
                         </div>

                         <section class="berps-section-card">
                              <div class="berps-section-card__header">
                                   <div>
                                        <h2 class="berps-section-title">Filter By Customer</h2>
                                        <p class="berps-section-copy">Search the company name and narrow the invoice list before reviewing records.</p>
                                   </div>
                                   <div class="filter-active-chip">
                                        <i class="mdi mdi-domain"></i>
                                        <?= htmlspecialchars($selectedCustomerName !== '' ? $selectedCustomerName : 'All customers', ENT_QUOTES, 'UTF-8'); ?>
                                   </div>
                              </div>

                              <div class="berps-section-card__body">
                              <form method="get" action="<?= base_url('Page/invList'); ?>" class="filter-form">
                                   <div class="filter-field">
                                        <label for="invoice-customer-filter">Customer</label>
                                        <select name="customer" id="invoice-customer-filter" class="form-control customer-filter-select">
                                             <option value="">All customers</option>
                                             <?php foreach ($clientOptions as $clientOption): ?>
                                                  <?php
                                                  $optionCustId = trim((string) ($clientOption->CustID ?? ''));
                                                  $optionCustomer = trim((string) ($clientOption->Customer ?? ''));
                                                  if ($optionCustId === '' && $optionCustomer === '') {
                                                       continue;
                                                  }
                                                  ?>
                                                  <option value="<?= htmlspecialchars($optionCustId, ENT_QUOTES, 'UTF-8'); ?>" <?= $optionCustId === $selectedCustomerId ? 'selected' : ''; ?>>
                                                       <?= htmlspecialchars($optionCustomer !== '' ? $optionCustomer : $optionCustId, ENT_QUOTES, 'UTF-8'); ?>
                                                  </option>
                                             <?php endforeach; ?>
                                        </select>
                                   </div>

                                   <div class="filter-actions">
                                        <button type="submit" class="btn btn-primary">
                                             <i class="mdi mdi-filter-variant mr-1" aria-hidden="true"></i>Apply Filter
                                        </button>
                                   </div>

                                   <div class="filter-actions">
                                        <a href="<?= base_url('Page/invList'); ?>" class="btn btn-outline-secondary">
                                             <i class="mdi mdi-refresh mr-1" aria-hidden="true"></i>Clear
                                        </a>
                                   </div>
                              </form>
                              </div>
                         </section>

                         <div class="card-stack">
                              <div class="theme-card">

                                   <div class="theme-card-head">
                                        <h5 class="theme-card-title">Invoice List</h5>
                                   </div>

                                   <div class="theme-card-body">
                                        <div class="table-responsive">
                                             <table id="invoice-table" class="table table-hover mb-0">
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
                                                       <?php if (!empty($data)): ?>
                                                            <?php foreach ($data as $row): ?>
                                                                 <?php
                                                                 $balance = (float) $row->Balance;
                                                                 $amountPaid = (float) $row->AmountPaid;
                                                                 $isFullyPaid = $balance <= 0.00001;
                                                                 $hasPayment = $amountPaid > 0;
                                                                 $paymentHistoryHref = base_url() . 'Page/paymentHistory?id=' . rawurlencode((string) $row->orderID);
                                                                 $paymentStateClass = 'berps-status--danger';
                                                                 $paymentStateLabel = 'Unpaid';
                                                                 $invoiceItems = isset($row->invoiceItems) && is_array($row->invoiceItems) ? $row->invoiceItems : array();
                                                                 $primaryItem = !empty($invoiceItems) ? $invoiceItems[0] : array(
                                                                      'itemDescription' => (string) ($row->JobDescription ?? ''),
                                                                      'itemQuantity' => (isset($row->itemQuantity) && is_numeric($row->itemQuantity) && (int) $row->itemQuantity > 0) ? (int) $row->itemQuantity : 1,
                                                                      'itemDurationUnit' => (string) ($row->itemDurationUnit ?? 'each'),
                                                                      'itemUnitPrice' => (isset($row->itemUnitPrice) && is_numeric($row->itemUnitPrice)) ? (float) $row->itemUnitPrice : ((float) $row->TotalDue),
                                                                      'lineTotal' => (float) $row->TotalDue,
                                                                 );
                                                                 $itemQuantity = (int) ($primaryItem['itemQuantity'] ?? 1);
                                                                 $itemDurationUnit = trim((string) ($primaryItem['itemDurationUnit'] ?? ''));
                                                                 $itemUnitPrice = (float) ($primaryItem['itemUnitPrice'] ?? 0);
                                                                 $showItemBreakdown = !empty($primaryItem);
                                                                 $unitLabel = $itemDurationUnit;
                                                                 if ($unitLabel !== '' && $unitLabel !== 'each' && $itemQuantity !== 1 && !preg_match('/s$/i', $unitLabel)) {
                                                                      $unitLabel .= 's';
                                                                 }
                                                                 $rateUnitLabel = $itemDurationUnit !== '' ? $itemDurationUnit : 'each';
                                                                 if ($unitLabel === '' || $unitLabel === 'each') {
                                                                      $itemBreakdown = $itemQuantity . ' x PHP ' . number_format($itemUnitPrice, 2) . ' / ' . $rateUnitLabel;
                                                                 } else {
                                                                      $itemBreakdown = $itemQuantity . ' ' . $unitLabel . ' x PHP ' . number_format($itemUnitPrice, 2) . ' / ' . $rateUnitLabel;
                                                                 }

                                                                 $descriptionLabel = trim((string) ($primaryItem['itemDescription'] ?? $row->JobDescription ?? ''));
                                                                 if ($descriptionLabel === '') {
                                                                      $summaryText = trim((string) ($row->invoiceSummary ?? 'Invoice item'));
                                                                      // Take only the first line if summary contains multiple lines
                                                                      $descriptionLabel = explode("\n", $summaryText)[0];
                                                                 }
                                                                 $extraItemCount = max(count($invoiceItems) - 1, 0);
                                                                 $itemJsonPayload = json_encode($invoiceItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                                                 $itemJson = htmlspecialchars($itemJsonPayload !== false ? $itemJsonPayload : '[]', ENT_QUOTES, 'UTF-8');

                                                                 if ($isFullyPaid) {
                                                                      $paymentStateClass = 'berps-status--success';
                                                                      $paymentStateLabel = 'Fully Paid';
                                                                 } elseif ($amountPaid > 0) {
                                                                      $paymentStateClass = 'berps-status--warning';
                                                                      $paymentStateLabel = 'Partially Paid';
                                                                 }
                                                                 ?>
                                                                 <tr>
                                                                      <td>
                                                                           <a class="inv-no-link" href="<?= base_url(); ?>Page/invoice?id=<?= $row->orderID; ?>">
                                                                                #<?= $row->InvoiceNo; ?>
                                                                           </a>
                                                                      </td>
                                                                      <td><?= $row->Customer; ?></td>
                                                                      <td style="font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);font-size:0.8rem;color:var(--text-soft);"><?= $row->TransDate; ?></td>
                                                                      <td style="max-width:260px;">
                                                                           <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($descriptionLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                           <?php if ($showItemBreakdown): ?>
                                                                                <small class="item-breakdown"><?= htmlspecialchars($itemBreakdown, ENT_QUOTES, 'UTF-8'); ?></small>
                                                                           <?php endif; ?>
                                                                           <?php if ($extraItemCount > 0): ?>
                                                                                <small class="item-extra-summary">+<?= $extraItemCount; ?> more entr<?= $extraItemCount === 1 ? 'y' : 'ies'; ?></small>
                                                                           <?php endif; ?>
                                                                           <?php if (($row->recurringFrequency ?? 'none') !== 'none'): ?>
                                                                                <small class="text-muted">
                                                                                     <?= ucfirst((string) $row->recurringFrequency); ?> recurring
                                                                                     <?php if (!empty($row->recurringScheduleDate)): ?>
                                                                                          · Schedule <?= date('M d, Y', strtotime($row->recurringScheduleDate)); ?>
                                                                                     <?php endif; ?>
                                                                                     <?php
                                                                                     $coveredMonths = getCoveredMonths($row);
                                                                                     if (!empty($coveredMonths)): ?>
                                                                                          · Covers <?= htmlspecialchars($coveredMonths, ENT_QUOTES, 'UTF-8'); ?>
                                                                                     <?php endif; ?>
                                                                                </small>
                                                                           <?php endif; ?>
                                                                      </td>
                                                                      <td class="text-right num-cell"><?= number_format($row->TotalDue, 2); ?></td>
                                                                      <td class="text-right">
                                                                           <?php if ($hasPayment): ?>
                                                                                <a class="action-link num-cell" href="<?= htmlspecialchars($paymentHistoryHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                     <?= number_format($row->AmountPaid, 2); ?>
                                                                                </a>
                                                                           <?php else: ?>
                                                                                <span class="num-cell"><?= number_format($row->AmountPaid, 2); ?></span>
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
                                                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenu<?= $row->orderID; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                     Actions
                                                                                </button>

                                                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu<?= $row->orderID; ?>">
                                                                                     <a class="dropdown-item" href="<?= base_url(); ?>Page/invoice?id=<?= $row->orderID; ?>&print=1" target="_blank" rel="noopener">
                                                                                          <i class="fa fa-print"></i> Print Invoice
                                                                                     </a>

                                                                                     <a class="dropdown-item"
                                                                                          href="javascript:void(0);"
                                                                                          data-toggle="modal"
                                                                                          data-target="#emailInvoiceModal"
                                                                                          data-orderid="<?= $row->orderID; ?>"
                                                                                          data-invoiceno="<?= $row->InvoiceNo; ?>"
                                                                                          data-client="<?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                          data-email="<?= htmlspecialchars($row->client_email ?? $row->customer_email ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                                          onclick="prepareEmailModal(this)">
                                                                                          <i class="fa fa-envelope"></i> Send via Email
                                                                                     </a>

                                                                                     <?php if ($hasPayment): ?>
                                                                                          <a class="dropdown-item" href="<?= htmlspecialchars($paymentHistoryHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                               <i class="fa fa-credit-card"></i> View Payment Details
                                                                                          </a>
                                                                                     <?php endif; ?>

                                                                                     <?php if (!$isFullyPaid): ?>
                                                                                          <a class="dropdown-item" href="<?= base_url(); ?>Page/addPaymentJO?id=<?= $row->orderID; ?>&InvoiceNo=<?= $row->InvoiceNo; ?>&PaymentSource=Others">
                                                                                               <i class="fa fa-plus"></i> Add Payment
                                                                                          </a>
                                                                                     <?php endif; ?>

                                                                                     <?php if (in_array($this->session->userdata('level'), ['Admin', 'Staff', 'Encoder'], true)): ?>
                                                                                          <div class="dropdown-divider"></div>

                                                                                          <a class="dropdown-item"
                                                                                               href="<?= base_url(); ?>Page/invoiceEntry?id=<?= (int) $row->orderID; ?>">
                                                                                               <i class="fa fa-edit"></i> Edit Record
                                                                                          </a>

                                                                                          <a class="dropdown-item"
                                                                                               href="<?= base_url(); ?>Page/duplicateInvoice?id=<?= (int) $row->orderID; ?>"
                                                                                               onclick="return confirm('Create a duplicate copy of this invoice?');">
                                                                                               <i class="fa fa-copy"></i> Duplicate Invoice
                                                                                          </a>

                                                                                          <a class="dropdown-item text-warning"
                                                                                               href="javascript:void(0);"
                                                                                               data-toggle="modal"
                                                                                               data-target="#voidInvoiceModal"
                                                                                               data-orderid="<?= $row->orderID; ?>"
                                                                                               data-invoiceno="<?= $row->InvoiceNo; ?>"
                                                                                               onclick="prepareVoidModal(this)">
                                                                                               <i class="fa fa-ban"></i> Void Invoice
                                                                                          </a>

                                                                                          <a class="dropdown-item text-danger"
                                                                                               href="<?= base_url(); ?>Page/deleteJO?id=<?= $row->orderID; ?>&return_to=invList"
                                                                                               onclick="return confirm('Are you sure you want to delete this record?');">
                                                                                               <i class="fa fa-trash"></i> Delete
                                                                                          </a>
                                                                                     <?php endif; ?>
                                                                                </div>
                                                                           </div>
                                                                      </td>
                                                                 </tr>
                                                            <?php endforeach; ?>
                                                       <?php endif; ?>
                                                  </tbody>
                                             </table>
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

     <div class="modal fade berps-form-modal" id="invoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header inv-modal-header">
                         <h5 class="modal-title">Create New Invoice</h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>

                    <div class="modal-body inv-modal-body">
                         <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addInvoice" novalidate id="invoiceForm" data-balance-form data-item-form>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Customer Information
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-3">
                                             <label for="invoice-number">Invoice No.</label>
                                             <input type="text" class="form-control" id="invoice-number" name="InvoiceNo" value="<?= $nextInvoiceNo; ?>" readonly required>
                                        </div>
                                        <div class="form-group col-md-9">
                                             <label for="invoice-customer">Customer</label>
                                             <select class="form-control" id="invoice-customer" name="CustID" required>
                                                  <option value="" data-address=""></option>
                                                  <?php if (!empty($data2)): ?>
                                                       <?php foreach ($data2 as $row): ?>
                                                            <option
                                                                 value="<?= htmlspecialchars($row->CustID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                 data-address="<?= htmlspecialchars($row->Address ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                 data-name="<?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?>">
                                                                 <?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($row->CustID, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                       <?php endforeach; ?>
                                                  <?php endif; ?>
                                             </select>
                                        </div>
                                   </div>

                                   <div class="form-group mb-0">
                                        <label for="invoice-customer-address">Customer Address</label>
                                        <input type="text" class="form-control" id="invoice-customer-address" name="CustAddress" placeholder="Customer address will populate automatically" readonly>
                                        <small class="inv-helper">This field is automatically filled once a customer is selected.</small>
                                   </div>
                              </div>

                              <div class="item-builder" data-item-builder>
                                   <div class="item-builder-head">
                                        <div>
                                             <div class="item-builder-title">Invoice Entries</div>
                                             <div class="item-builder-subtitle">Add one or more billable entries. The total invoice amount updates automatically.</div>
                                        </div>
                                        <button type="button" class="btn-add-entry" data-add-item-row>+ Add Entry</button>
                                   </div>

                                   <div data-item-rows></div>
                                   <small class="item-total-warning" data-total-warning></small>
                              </div>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Billing Schedule
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-6">
                                             <label for="invoice-recurring-frequency">Recurring</label>
                                             <select class="form-control" id="invoice-recurring-frequency" name="recurringFrequency">
                                                  <option value="none" selected>No</option>
                                                  <option value="daily">Daily</option>
                                                  <option value="weekly">Weekly</option>
                                                  <option value="monthly">Monthly</option>
                                                  <option value="quarterly">Quarterly</option>
                                                  <option value="yearly">Yearly</option>
                                             </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                             <label for="invoice-recurring-schedule-date">Schedule Date</label>
                                             <input type="date" class="form-control" id="invoice-recurring-schedule-date" name="recurringScheduleDate">
                                        </div>
                                   </div>
                              </div>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Invoice Summary
                                   </div>

                                   <div class="form-row align-items-end">
                                        <div class="form-group col-md-7">
                                             <label for="invoice-total-due">Invoice Amount</label>
                                             <input type="number" class="form-control" id="invoice-total-due" name="TotalDue" min="0" step="0.01" required readonly>
                                             <small class="inv-helper">This is the sum of all invoice entries.</small>
                                        </div>
                                        <div class="form-group col-md-5">
                                             <div class="invoice-summary-box">
                                                  <div class="invoice-summary-label">Total Due</div>
                                                  <div class="invoice-summary-value" id="invoice-total-preview">₱0.00</div>
                                             </div>
                                        </div>
                                   </div>

                                   <input type="hidden" name="AmountPaid" id="invoice-amount-paid" value="0.00">
                                   <input type="hidden" name="Balance" id="invoice-balance" value="0.00">
                                   <input type="hidden" name="PaymentReference" id="invoice-payment-reference" value="">

                                   <div class="form-group mb-0">
                                        <label for="invoice-notes">Notes</label>
                                        <textarea class="form-control invoice-notes" id="invoice-notes" name="Notes" placeholder="Write additional billing notes here..."></textarea>
                                   </div>
                              </div>

                              <div class="inv-modal-footer">
                                   <div class="inv-footer-note">
                                        Review the invoice details before saving.
                                   </div>

                                   <div class="text-right">
                                        <button type="button" class="btn btn-invoice-cancel" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="submit" class="btn btn-invoice-save ml-2">Save Invoice</button>
                                   </div>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <div class="modal fade berps-form-modal" id="invoiceEditModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header inv-modal-header">
                         <h5 class="modal-title">Update Invoice</h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>

                    <div class="modal-body inv-modal-body">
                         <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateJO" novalidate data-balance-form data-edit-form data-item-form>
                              <input type="hidden" name="id" value="">
                              <input type="hidden" name="return_to" value="invList">

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Customer Information
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-3">
                                             <label for="invoice-edit-number">Invoice No.</label>
                                             <input type="text" class="form-control" id="invoice-edit-number" name="InvoiceNo" value="" readonly required>
                                        </div>
                                        <div class="form-group col-md-9">
                                             <label for="invoice-edit-customer">Customer</label>
                                             <select class="form-control" id="invoice-edit-customer" name="CustID" required>
                                                  <option value="" data-address=""></option>
                                                  <?php if (!empty($data2)): ?>
                                                       <?php foreach ($data2 as $row): ?>
                                                            <option
                                                                 value="<?= htmlspecialchars($row->CustID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                 data-address="<?= htmlspecialchars($row->Address ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                 data-name="<?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?>">
                                                                 <?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($row->CustID, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                       <?php endforeach; ?>
                                                  <?php endif; ?>
                                             </select>
                                        </div>
                                   </div>

                                   <div class="form-group mb-0">
                                        <label for="invoice-edit-customer-address">Customer Address</label>
                                        <input type="text" class="form-control" id="invoice-edit-customer-address" name="CustAddress" value="" readonly>
                                        <small class="inv-helper">This field updates automatically based on the selected customer.</small>
                                   </div>
                              </div>

                              <div class="item-builder" data-item-builder>
                                   <div class="item-builder-head">
                                        <div>
                                             <div class="item-builder-title">Invoice Entries</div>
                                             <div class="item-builder-subtitle">Update the encoded line items below. The invoice total recalculates automatically.</div>
                                        </div>
                                        <button type="button" class="btn-add-entry" data-add-item-row>+ Add Entry</button>
                                   </div>
                                   <div data-item-rows></div>
                                   <small class="item-total-warning" data-total-warning></small>
                              </div>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Billing Schedule
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-6">
                                             <label for="invoice-edit-recurring-frequency">Recurring</label>
                                             <select class="form-control" id="invoice-edit-recurring-frequency" name="recurringFrequency">
                                                  <option value="none">No</option>
                                                  <option value="daily">Daily</option>
                                                  <option value="weekly">Weekly</option>
                                                  <option value="monthly">Monthly</option>
                                                  <option value="quarterly">Quarterly</option>
                                                  <option value="yearly">Yearly</option>
                                             </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                             <label for="invoice-edit-recurring-schedule-date">Schedule Date</label>
                                             <input type="date" class="form-control" id="invoice-edit-recurring-schedule-date" name="recurringScheduleDate" value="">
                                             <small class="inv-helper" id="invoice-edit-recurring-help">
                                                  Recurring invoices generate 10 days before the schedule date.
                                             </small>
                                        </div>
                                   </div>
                              </div>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Invoice Summary
                                   </div>

                                   <div class="form-row align-items-end">
                                        <div class="form-group col-md-4">
                                             <label for="invoice-edit-total-due">Total Due</label>
                                             <input type="number" class="form-control" id="invoice-edit-total-due" name="TotalDue" min="0" step="0.01" value="" required readonly>
                                             <small class="inv-helper">This is the sum of all invoice entries and cannot be lower than the amount already paid.</small>
                                        </div>
                                        <div class="form-group col-md-4">
                                             <label for="invoice-edit-amount-paid">Amount Paid</label>
                                             <input type="number" class="form-control" id="invoice-edit-amount-paid" name="AmountPaid" value="" step="0.01" readonly required>
                                        </div>
                                        <div class="form-group col-md-4">
                                             <label for="invoice-edit-balance">Balance</label>
                                             <input type="text" class="form-control" id="invoice-edit-balance" name="Balance" value="" readonly required>
                                        </div>
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-7">
                                             <label for="invoice-edit-notes">Notes</label>
                                             <textarea class="form-control invoice-notes" id="invoice-edit-notes" name="Notes" placeholder="Write additional billing notes here..."></textarea>
                                        </div>
                                        <div class="form-group col-md-5">
                                             <div class="invoice-summary-box">
                                                  <div class="invoice-summary-label">Updated Total Due</div>
                                                  <div class="invoice-summary-value" id="invoice-edit-total-preview">₱0.00</div>
                                             </div>
                                        </div>
                                   </div>
                              </div>

                              <div class="inv-modal-footer">
                                   <div class="inv-footer-note">
                                        Make sure all invoice entries and billing details are correct before updating.
                                   </div>

                                   <div class="text-right">
                                        <button type="button" class="btn btn-invoice-cancel" data-dismiss="modal">Close</button>
                                        <button type="submit" name="submit" class="btn btn-invoice-save ml-2">Update Invoice</button>
                                   </div>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <div class="modal fade berps-form-modal" id="addpayment" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header inv-modal-header">
                         <h5 class="modal-title">New Payment</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body inv-modal-body">
                         <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addJO" novalidate data-balance-form>
                              <input type="hidden" name="dataid" id="dataid" value="">
                              <div class="form-row">
                                   <div class="form-group col-md-3">
                                        <label for="payment-invoice-number">Invoice No.</label>
                                        <input type="text" class="form-control" id="payment-invoice-number" name="InvoiceNo" value="<?= $nextInvoiceNo; ?>" readonly required>
                                   </div>
                                   <div class="form-group col-md-9">
                                        <label for="payment-customer">Customer</label>
                                        <input type="text" class="form-control" id="payment-customer" name="Customer" required>
                                   </div>
                              </div>

                              <div class="form-group">
                                   <label for="payment-address">Customer Address</label>
                                   <input type="text" class="form-control" id="payment-address" name="CustAddress">
                              </div>

                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="payment-description">Job Description</label>
                                        <input type="text" class="form-control" id="payment-description" name="JobDescription" required>
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="payment-notes">Notes</label>
                                        <input type="text" class="form-control" id="payment-notes" name="Notes">
                                   </div>
                              </div>

                              <div class="form-row">
                                   <div class="form-group col-md-4">
                                        <label>Total Due</label>
                                        <input type="number" class="form-control" name="TotalDue" min="0" step="0.01" required>
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label>Amount Paid</label>
                                        <input type="number" class="form-control" name="AmountPaid" min="0" step="0.01" required>
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label>Balance</label>
                                        <input type="text" class="form-control" name="Balance" readonly required>
                                   </div>
                              </div>

                              <div class="text-right">
                                   <button type="submit" name="submit" class="btn btn-invoice-save">Save Job Order</button>
                                   <button type="reset" class="btn btn-invoice-cancel ml-2">Reset</button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <!-- Void Invoice Modal -->
     <div class="modal fade berps-form-modal" id="voidInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
               <div class="modal-content" style="border-radius: 22px; overflow: hidden; box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #dc2626, #ef4444); border: none; padding: 22px 24px;">
                         <h5 class="modal-title" style="color: #fff; font-size: 1.08rem; font-weight: 800; letter-spacing: -0.02em;">
                              <i class="fa fa-ban mr-2"></i>Void Invoice
                         </h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; text-shadow: none; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); width: 38px; height: 38px; border-radius: 50%; padding: 0; margin: 0; line-height: 1;">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body" style="background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%); padding: 24px;">
                         <form id="voidInvoiceForm" method="post" action="<?= base_url(); ?>Page/voidInvoice">
                              <input type="hidden" name="orderID" id="voidOrderID" value="">
                              <input type="hidden" name="return_to" value="invList">

                              <div class="alert alert-warning" style="border: none; border-radius: 14px; background: #fffbeb; color: #92400e; font-size: 0.9rem;">
                                   <i class="fa fa-exclamation-triangle mr-2"></i>
                                   <strong>Warning:</strong> Voiding an invoice will permanently cancel it and set the balance to zero. This action cannot be undone.
                              </div>

                              <div class="form-group" style="margin-top: 20px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Invoice Number
                                   </label>
                                   <input type="text" id="voidInvoiceNo" class="form-control" readonly style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);">
                              </div>

                              <div class="form-group" style="margin-top: 16px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Reason for Voiding <span style="color: #dc2626;">*</span>
                                   </label>
                                   <textarea name="voidReason" id="voidReason" class="form-control" rows="3" required placeholder="Enter reason for voiding this invoice..." style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; resize: vertical;"></textarea>
                              </div>

                              <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                                   <button type="button" class="btn" data-dismiss="modal" style="background: #fff; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 20px; font-weight: 600; margin-right: 10px;">
                                        Cancel
                                   </button>
                                   <button type="submit" class="btn" style="background: linear-gradient(135deg, #dc2626, #ef4444); color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-weight: 700; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);">
                                        <i class="fa fa-ban mr-1"></i>Void Invoice
                                   </button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <!-- Email Invoice Modal -->
     <div class="modal fade berps-form-modal" id="emailInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
               <div class="modal-content" style="border-radius: 22px; overflow: hidden; box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #2563eb, #3b82f6); border: none; padding: 22px 24px;">
                         <h5 class="modal-title" style="color: #fff; font-size: 1.08rem; font-weight: 800; letter-spacing: -0.02em;">
                              <i class="fa fa-envelope mr-2"></i>Send Invoice via Email
                         </h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; text-shadow: none; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); width: 38px; height: 38px; border-radius: 50%; padding: 0; margin: 0; line-height: 1;">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body" style="background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%); padding: 24px;">
                         <form id="emailInvoiceForm" method="post" action="<?= base_url(); ?>Page/emailInvoicePDF">
                              <input type="hidden" name="orderID" id="emailOrderID" value="">

                              <div class="form-group">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Invoice Number
                                   </label>
                                   <input type="text" id="emailInvoiceNo" class="form-control" readonly style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);">
                              </div>

                              <div class="form-group" style="margin-top: 16px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Client Name
                                   </label>
                                   <input type="text" id="emailClientName" class="form-control" readonly style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                              </div>

                              <div class="form-group" style="margin-top: 16px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Recipient Email <span style="color: #dc2626;">*</span>
                                   </label>
                                   <input type="email" name="recipientEmail" id="recipientEmail" class="form-control" required placeholder="Enter email address..." style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                                   <small class="form-text text-muted" style="margin-top: 6px;">The invoice will be sent in the email.</small>
                              </div>

                              <div class="form-group" style="margin-top: 16px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Additional Message (Optional)
                                   </label>
                                   <textarea name="emailMessage" id="emailMessage" class="form-control" rows="3" placeholder="Enter a custom message to include in the email..." style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; resize: vertical;"></textarea>
                              </div>

                              <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                                   <button type="button" class="btn" data-dismiss="modal" style="background: #fff; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 20px; font-weight: 600; margin-right: 10px;">
                                        Cancel
                                   </button>
                                   <button type="submit" class="btn" style="background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-weight: 700; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);">
                                        <i class="fa fa-paper-plane mr-1"></i>Send Email
                                   </button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/calendar.init.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/jquery.chat.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/jquery.todo.js"></script>
     <script src="<?= base_url(); ?>assets/libs/morris-js/morris.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/raphael/raphael.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>

     <script>
          (function($) {
               'use strict';

               function attachBalanceCalculator(form) {
                    var $form = $(form);
                    var $total = $form.find('input[name="TotalDue"]');
                    var $paid = $form.find('input[name="AmountPaid"]');
                    var $balance = $form.find('input[name="Balance"]');

                    function computeBalance() {
                         var total = parseFloat($total.val()) || 0;
                         var paid = parseFloat($paid.val()) || 0;
                         var balance = total - paid;
                         $balance.val(balance.toFixed(2));
                    }

                    $total.off('input.invoiceBalance').on('input.invoiceBalance', computeBalance);
                    $paid.off('input.invoiceBalance').on('input.invoiceBalance', computeBalance);
                    computeBalance();
               }

               function attachCustomerAddressSync(form) {
                    var $form = $(form);
                    var $customer = $form.find('select[name="CustID"]');
                    var $address = $form.find('input[name="CustAddress"]');

                    if (!$customer.length || !$address.length) {
                         return;
                    }

                    function syncAddress() {
                         var selectedValue = $customer.val() || '';
                         var selectedAddress = $customer.find('option:selected').data('address') || '';
                         if (selectedValue || !$address.val()) {
                              $address.val(selectedAddress);
                         }
                    }

                    $customer.off('change.invoiceAddress').on('change.invoiceAddress', syncAddress);
                    $form.off('reset.invoiceAddress').on('reset.invoiceAddress', function() {
                         window.setTimeout(function() {
                              $customer.trigger('change');
                         }, 0);
                    });
                    syncAddress();
               }

               function initializeInvoiceCustomerSelect() {
                    initializeCustomerSelect('#invoice-customer', '#invoiceModal');
               }

               function initializeEditCustomerSelect() {
                    initializeCustomerSelect('#invoice-edit-customer', '#invoiceEditModal');
               }

               function initializeInvoiceCustomerFilter() {
                    var $filter = $('#invoice-customer-filter');

                    if (!$filter.length || !$.fn || typeof $.fn.select2 !== 'function') {
                         return false;
                    }

                    if ($filter.hasClass('select2-hidden-accessible')) {
                         $filter.select2('destroy');
                    }

                    $filter.select2({
                         width: '100%',
                         placeholder: 'All customers',
                         allowClear: true,
                         dropdownParent: $filter.closest('.filter-field')
                    });

                    $filter.off('change.autoFilter').on('change.autoFilter', function() {
                         $(this).closest('form').trigger('submit');
                    });

                    return true;
                }

               function initializeCustomerSelect(selector, modalSelector) {
                    var $customer = $(selector);

                    if (!$customer.length || !$.fn || typeof $.fn.select2 !== 'function') {
                         return false;
                    }

                    if ($customer.hasClass('select2-hidden-accessible')) {
                         $customer.select2('destroy');
                    }

                    $customer.select2({
                         width: '100%',
                         dropdownParent: $(modalSelector),
                         placeholder: 'Select customer'
                    });

                    return true;
               }

               function initializeInvoiceSelect2(attempt) {
                    attempt = attempt || 0;

                    if (!window.jQuery || !$.fn || typeof $.fn.select2 !== 'function') {
                         if (attempt < 20) {
                              window.setTimeout(function() {
                                   initializeInvoiceSelect2(attempt + 1);
                              }, 100);
                         }
                         return;
                    }

                    initializeInvoiceCustomerSelect();
                    initializeEditCustomerSelect();
                    initializeInvoiceCustomerFilter();
                }

               function normalizeAmount(value) {
                    var parsed = parseFloat(value);
                    return isFinite(parsed) ? parsed.toFixed(2) : '0.00';
               }

               function escapeHtml(value) {
                    return $('<div>').text(value == null ? '' : String(value)).html();
               }

               function defaultInvoiceItem() {
                    return {
                         itemDescription: '',
                         itemQuantity: 1,
                         itemDurationUnit: 'each',
                         itemUnitPrice: '0.00'
                    };
               }

               function currency(value) {
                    var parsed = parseFloat(value) || 0;
                    return new Intl.NumberFormat('en-PH', {
                         style: 'currency',
                         currency: 'PHP'
                    }).format(parsed);
               }

               function formatQuantity(value) {
                    var parsed = parseFloat(value) || 0;
                    if (Math.round(parsed) === parsed) {
                         return String(Math.round(parsed));
                    }

                    return parsed.toFixed(2);
               }

               function formatDurationLabel(quantity, unitValue) {
                    if (!unitValue || unitValue === 'each') {
                         return '';
                    }

                    return quantity === 1 || /s$/i.test(unitValue) ? unitValue : unitValue + 's';
               }

               function formatRateUnit(unitValue) {
                    return unitValue ? String(unitValue) : 'each';
               }

               function normalizeInvoiceItem(item) {
                    var normalized = $.extend({}, defaultInvoiceItem(), item || {});
                    var quantity = parseFloat(normalized.itemQuantity);
                    var unitPrice = parseFloat(normalized.itemUnitPrice);

                    normalized.itemQuantity = isFinite(quantity) && quantity > 0 ? String(Math.round(quantity)) : '1';
                    normalized.itemDurationUnit = normalized.itemDurationUnit ? String(normalized.itemDurationUnit) : 'each';
                    normalized.itemUnitPrice = isFinite(unitPrice) && unitPrice >= 0 ? unitPrice.toFixed(2) : '0.00';
                    normalized.itemDescription = normalized.itemDescription ? String(normalized.itemDescription) : '';

                    return normalized;
               }

               function parseInvoiceItems(rawItems) {
                    var items = rawItems;

                    if (typeof items === 'string') {
                         try {
                              items = JSON.parse(items);
                         } catch (error) {
                              items = [];
                         }
                    }

                    if (!Array.isArray(items)) {
                         items = [];
                    }

                    return items.map(normalizeInvoiceItem);
               }

               function buildInvoiceItemRow(item, index) {
                    var normalized = normalizeInvoiceItem(item);

                    return '' +
                         '<div class="item-row" data-item-row>' +
                         '  <div class="item-row-head">' +
                         '    <div class="item-row-title">Entry ' + (index + 1) + '</div>' +
                         '    <div class="item-row-total" data-item-line-total>PHP 0.00</div>' +
                         '  </div>' +
                         '  <div class="form-row">' +
                         '    <div class="form-group col-md-12">' +
                         '      <label>Description</label>' +
                         '      <input type="text" class="form-control" name="itemDescription[]" value="' + escapeHtml(normalized.itemDescription) + '" required>' +
                         '    </div>' +
                         '  </div>' +
                         '  <div class="form-row">' +
                         '    <div class="form-group col-md-3">' +
                         '      <label>Rate</label>' +
                         '      <input type="number" class="form-control" name="itemUnitPrice[]" min="0" step="0.01" value="' + escapeHtml(normalized.itemUnitPrice) + '">' +
                         '    </div>' +
                         '    <div class="form-group col-md-3">' +
                         '      <label>Qty</label>' +
                         '      <input type="number" class="form-control" name="itemQuantity[]" min="1" step="1" value="' + escapeHtml(normalized.itemQuantity) + '">' +
                         '    </div>' +
                         '    <div class="form-group col-md-3">' +
                         '      <label>Unit</label>' +
                         '      <select class="form-control" name="itemDurationUnit[]">' +
                         '        <option value="each"' + (normalized.itemDurationUnit === 'each' ? ' selected' : '') + '>Each</option>' +
                         '        <option value="day"' + (normalized.itemDurationUnit === 'day' ? ' selected' : '') + '>Day</option>' +
                         '        <option value="week"' + (normalized.itemDurationUnit === 'week' ? ' selected' : '') + '>Week</option>' +
                         '        <option value="month"' + (normalized.itemDurationUnit === 'month' ? ' selected' : '') + '>Month</option>' +
                         '        <option value="year"' + (normalized.itemDurationUnit === 'year' ? ' selected' : '') + '>Year</option>' +
                         '      </select>' +
                         '    </div>' +
                         '    <div class="form-group col-md-3">' +
                         '      <label>&nbsp;</label>' +
                         '      <button type="button" class="btn-remove-entry" data-remove-item-row>Remove</button>' +
                         '    </div>' +
                         '  </div>' +
                         '  <small class="item-breakdown-inline" data-item-breakdown></small>' +
                         '</div>';
               }

               function attachInvoiceItemBuilder(form, initialItems) {
                    var $form = $(form);
                    var $rows = $form.find('[data-item-rows]');
                    var $total = $form.find('input[name="TotalDue"]');
                    var $warning = $form.find('[data-total-warning]');

                    if (!$rows.length || !$total.length) {
                         return;
                    }

                    function updatePreviewBox() {
                         var totalValue = parseFloat($total.val()) || 0;
                         var previewId = $form.closest('.modal').attr('id') === 'invoiceEditModal' ?
                              '#invoice-edit-total-preview' :
                              '#invoice-total-preview';
                         $(previewId).text('₱' + totalValue.toLocaleString('en-PH', {
                              minimumFractionDigits: 2,
                              maximumFractionDigits: 2
                         }));
                    }

                    function collectItems() {
                         var items = [];

                         $rows.find('[data-item-row]').each(function() {
                              var $row = $(this);
                              items.push(normalizeInvoiceItem({
                                   itemDescription: $row.find('input[name="itemDescription[]"]').val() || '',
                                   itemQuantity: $row.find('input[name="itemQuantity[]"]').val() || '1',
                                   itemDurationUnit: $row.find('select[name="itemDurationUnit[]"]').val() || 'each',
                                   itemUnitPrice: $row.find('input[name="itemUnitPrice[]"]').val() || '0.00'
                              }));
                         });

                         return items;
                    }

                    function renderRows(items) {
                         var normalizedItems = Array.isArray(items) && items.length ? items.map(normalizeInvoiceItem) : [defaultInvoiceItem()];
                         var markup = normalizedItems.map(function(item, index) {
                              return buildInvoiceItemRow(item, index);
                         }).join('');

                         $rows.html(markup);
                         refreshTotals();
                    }

                    function refreshTotals() {
                         var totalAmount = 0;
                         var rowCount = 0;

                         $rows.find('[data-item-row]').each(function(index) {
                              rowCount++;

                              var $row = $(this);
                              var quantity = parseFloat($row.find('input[name="itemQuantity[]"]').val()) || 0;
                              var unitPrice = parseFloat($row.find('input[name="itemUnitPrice[]"]').val()) || 0;
                              var unitValue = ($row.find('select[name="itemDurationUnit[]"]').val() || 'each').toString();
                              var lineTotal = Math.max(0, quantity * unitPrice);
                              var durationLabel = formatDurationLabel(quantity, unitValue);
                              var rateUnitLabel = formatRateUnit(unitValue);
                              var breakdownPrefix = durationLabel ?
                                   formatQuantity(quantity) + ' ' + durationLabel + ' x ' + currency(unitPrice) + ' / ' + rateUnitLabel :
                                   formatQuantity(quantity) + ' x ' + currency(unitPrice) + ' / ' + rateUnitLabel;

                              $row.find('.item-row-title').text('Entry ' + (index + 1));
                              $row.find('[data-item-line-total]').text(currency(lineTotal));
                              $row.find('[data-item-breakdown]').text(quantity > 0 ? (breakdownPrefix + ' = ' + currency(lineTotal)) : '');
                              totalAmount += lineTotal;
                         });

                         if (rowCount === 0) {
                              renderRows([defaultInvoiceItem()]);
                              return;
                         }

                         $rows.find('[data-remove-item-row]').prop('disabled', rowCount === 1);
                         $total.val(totalAmount.toFixed(2)).trigger('input');
                         updatePreviewBox();

                         var amountPaid = parseFloat($form.find('input[name="AmountPaid"]').val()) || 0;
                         if ($total.length && $total.get(0)) {
                              if (totalAmount + 0.00001 < amountPaid) {
                                   $total.get(0).setCustomValidity('Total due cannot be lower than the amount already paid.');
                                   $warning.text('Total due cannot be lower than the amount already paid.');
                              } else {
                                   $total.get(0).setCustomValidity('');
                                   $warning.text('');
                              }
                         }
                    }

                    $form.off('.invoiceItems');
                    $form.on('click.invoiceItems', '[data-add-item-row]', function() {
                         var items = collectItems();
                         items.push(defaultInvoiceItem());
                         renderRows(items);
                    });

                    $form.on('click.invoiceItems', '[data-remove-item-row]', function() {
                         var items = collectItems();
                         var rowIndex = $(this).closest('[data-item-row]').index();

                         if (items.length <= 1) {
                              renderRows([defaultInvoiceItem()]);
                              return;
                         }

                         items.splice(rowIndex, 1);
                         renderRows(items);
                    });

                    $form.on('input.invoiceItems change.invoiceItems', 'input[name="itemDescription[]"], input[name="itemQuantity[]"], select[name="itemDurationUnit[]"], input[name="itemUnitPrice[]"]', function() {
                         refreshTotals();
                    });

                    $form.on('reset.invoiceItems', function() {
                         window.setTimeout(function() {
                              renderRows([defaultInvoiceItem()]);
                         }, 0);
                    });

                    $form.on('submit.invoiceItems', function() {
                         refreshTotals();
                    });

                    renderRows(Array.isArray(initialItems) && initialItems.length ? initialItems : collectItems());
               }

               function populateInvoiceEditModal(trigger) {
                    var $trigger = $(trigger);
                    var $modal = $('#invoiceEditModal');
                    var $form = $modal.find('form[data-edit-form]');
                    var amountPaid = parseFloat($trigger.data('paid')) || 0;
                    var invoiceItems = parseInvoiceItems($trigger.attr('data-items'));

                    if (!$form.length) {
                         return;
                    }

                    $form.find('input[name="id"]').val($trigger.data('id') || '');
                    $form.find('input[name="InvoiceNo"]').val($trigger.data('invoiceNo') || '');
                    $form.find('select[name="CustID"]').val($trigger.data('custId') || '').trigger('change');
                    $form.find('input[name="CustAddress"]').val($trigger.data('custAddress') || '');
                    $form.find('textarea[name="Notes"]').val($trigger.data('notes') || '');
                    $form.find('input[name="TotalDue"]')
                         .attr('min', normalizeAmount(amountPaid))
                         .val(normalizeAmount($trigger.data('totalDue')));
                    $form.find('input[name="AmountPaid"]').val(normalizeAmount(amountPaid));
                    $form.find('input[name="Balance"]').val(normalizeAmount($trigger.data('balance')));
                    $form.find('select[name="recurringFrequency"]').val($trigger.data('recurringFrequency') || 'none');
                    $form.find('input[name="recurringScheduleDate"]').val($trigger.data('recurringScheduleDate') || '');

                    var isGeneratedOccurrence = parseInt($trigger.data('recurringTemplateId'), 10) > 0;
                    $form.find('select[name="recurringFrequency"], input[name="recurringScheduleDate"]').prop('disabled', isGeneratedOccurrence);
                    $('#invoice-edit-recurring-help').text(
                         isGeneratedOccurrence ?
                         'This invoice was generated from a recurring template. Edit the original template to change the recurrence.' :
                         'Recurring invoices generate 10 days before the schedule date for daily, weekly, monthly, quarterly, or yearly schedules.'
                    );

                    attachBalanceCalculator($form.get(0));
                    attachCustomerAddressSync($form.get(0));
                    attachInvoiceItemBuilder($form.get(0), invoiceItems);
                    $form.removeClass('was-validated');
               }

               $(function() {
                    initializeInvoiceSelect2();

                    if ($.fn.DataTable.isDataTable('#invoice-table')) {
                         $('#invoice-table').DataTable().destroy();
                    }
                    $('#invoice-table').DataTable({
                         responsive: true,
                         autoWidth: false,
                         order: [],
                         pageLength: 10,
                         lengthMenu: [10, 25, 50, 100],
                         dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6 text-sm-right"f>>' +
                              'rt' +
                              '<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6"p>>',
                         language: {
                              emptyTable: 'No invoices found.',
                              search: 'Search:',
                              searchPlaceholder: 'Invoice number or description...',
                              lengthMenu: 'Show _MENU_ entries',
                              info: 'Showing _START_ to _END_ of _TOTAL_ invoices'
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

                    $('[data-balance-form]').each(function() {
                         attachBalanceCalculator(this);
                         attachCustomerAddressSync(this);
                    });

                    $('[data-item-form]').each(function() {
                         attachInvoiceItemBuilder(this);
                    });

                    $('#invoiceModal, #addpayment, #invoiceEditModal').on('shown.bs.modal', function() {
                         var form = $(this).find('form[data-balance-form]');
                         if (form.length) {
                              attachBalanceCalculator(form.get(0));
                              attachCustomerAddressSync(form.get(0));
                         }

                         var itemForm = $(this).find('form[data-item-form]');
                         if (itemForm.length) {
                              attachInvoiceItemBuilder(itemForm.get(0));
                         }

                         if ($(this).attr('id') === 'invoiceModal') {
                              initializeInvoiceCustomerSelect();
                         }

                         if ($(this).attr('id') === 'invoiceEditModal') {
                              initializeEditCustomerSelect();
                         }
                    });

                    $('#invoiceEditModal').on('show.bs.modal', function(event) {
                         if (event.relatedTarget) {
                              populateInvoiceEditModal(event.relatedTarget);
                         }
                    });

                    $('#invoice-customer').on('change', function() {
                         var selected = this.options[this.selectedIndex];
                         $('#invoice-customer-address').val(selected.getAttribute('data-address') || '');
                    });

                    $('#invoice-edit-customer').on('change', function() {
                         var selected = this.options[this.selectedIndex];
                         $('#invoice-edit-customer-address').val(selected.getAttribute('data-address') || '');
                    });
               });

               // Void Invoice Modal Handler
               window.prepareVoidModal = function(element) {
                    var orderID = $(element).data('orderid');
                    var invoiceNo = $(element).data('invoiceno');
                    $('#voidOrderID').val(orderID);
                    $('#voidInvoiceNo').val('#' + invoiceNo);
                    $('#voidReason').val('');
               };

               // Email Invoice Modal Handler
               window.prepareEmailModal = function(element) {
                    var orderID = $(element).data('orderid');
                    var invoiceNo = $(element).data('invoiceno');
                    var clientName = $(element).data('client');
                    var clientEmail = $(element).data('email');
                    $('#emailOrderID').val(orderID);
                    $('#emailInvoiceNo').val('#' + invoiceNo);
                    $('#emailClientName').val(clientName);
                    $('#recipientEmail').val(clientEmail || '');
                    $('#emailMessage').val('');
               };
          })(jQuery);
     </script>

</body>

</html>
