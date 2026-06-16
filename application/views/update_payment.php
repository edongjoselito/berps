<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

  <div id="wrapper">

    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid update-payment-page">
          <style>
            .update-payment-page .page-title-box {
              padding: 12px 0;
              margin-bottom: 18px;
            }

            .update-payment-page .form-section-title {
              font-size: 1rem;
              font-weight: 600;
              margin-bottom: 18px;
            }

            .update-payment-page .form-control {
              border-radius: 8px;
            }

            .update-payment-page .card {
              border-radius: 16px;
            }

            .update-payment-page .card-header {
              border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }

            .update-payment-page .btn-light {
              background: #f5f7fb;
              border-color: #f5f7fb;
            }

            .update-payment-page .btn-light:hover {
              background: #e9eefb;
              border-color: #e9eefb;
            }
          </style>

          <div class="row">
            <div class="col-12">
              <div class="page-title-box">
                <h4 class="page-title">Update Payment<br />
                  <span class="badge badge-purple mb-1">Modify payment details</span>
                </h4>
                <div class="clearfix"></div>
                <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:20px 0;" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Payment Details</h5>
                  <a href="<?= base_url(); ?>Page/paymentList" class="btn btn-sm btn-outline-primary">
                    <i class="mdi mdi-chevron-left mr-1"></i> Back to Payments
                  </a>
                </div>
                <div class="card-body">
                  <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updatePayment?id=<?= $data[0]->paymentID; ?>" novalidate>
                    <input type="hidden" name="id" value="<?= $data[0]->paymentID; ?>">

                    <div class="form-group">
                      <label for="payment-customer">Payor</label>
                      <select class="form-control" id="payment-customer" name="CustID" required>
                        <option value="">Select customer</option>
                        <?php if (!empty($data2)): ?>
                          <?php foreach ($data2 as $customer): ?>
                            <option value="<?= htmlspecialchars((string) $customer->CustID, ENT_QUOTES, 'UTF-8'); ?>" <?= ((string) ($data[0]->CustID ?? '') === (string) $customer->CustID) ? 'selected' : ''; ?>>
                              <?= htmlspecialchars((string) $customer->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string) $customer->CustID, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                    </div>

                    <div class="form-row">
                      <div class="form-group col-md-3">
                        <label for="payment-date">Payment Date</label>
                        <input type="date" class="form-control" id="payment-date" name="PDate" value="<?= $data[0]->PDate; ?>" required>
                      </div>
                      <div class="form-group col-md-3">
                        <label for="payment-amount">Amount Paid</label>
                        <input type="number" class="form-control" id="payment-amount" name="AmountPaid" min="0" step="0.01" value="<?= $data[0]->AmountPaid; ?>" required>
                      </div>
                      <div class="form-group col-md-3">
                        <label for="payment-tax">Tax 2307</label>
                        <input type="number" class="form-control" id="payment-tax" name="TaxAmount" min="0" step="0.01" value="<?= htmlspecialchars((string) ($data[0]->TaxAmount ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                        <small class="form-text text-muted">For government payments with BIR Form 2307.</small>
                      </div>
                      <div class="form-group col-md-3">
                        <label for="payment-orno">O.R. No.</label>
                        <input type="text" class="form-control" id="payment-orno" name="ORNo" value="<?= $data[0]->ORNo; ?>">
                      </div>
                    </div>

                    <div class="alert alert-light border">
                      Total invoice credit will be computed as <strong>Amount Paid + Tax 2307</strong>.
                    </div>

                    <div class="form-group">
                      <label for="payment-reference">Payment Reference</label>
                      <input type="text" class="form-control" id="payment-reference" name="PaymentReference" value="<?= htmlspecialchars((string) ($data[0]->PaymentReference ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                      <label for="payment-invoice">Invoice No.</label>
                      <input type="text" class="form-control" id="payment-invoice" name="InvoiceNo" value="<?= $data[0]->InvoiceNo; ?>">
                    </div>

                    <div class="form-group">
                      <label for="payment-description">Transaction / Description</label>
                      <input type="text" class="form-control" id="payment-description" name="TransDescription" value="<?= $data[0]->TransDescription; ?>" required>
                    </div>

                    <div class="text-right">
                      <button type="submit" name="submit" class="btn btn-primary">Save Changes</button>
                      <a href="<?= base_url(); ?>Page/paymentList" class="btn btn-light ml-2">Cancel</a>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
