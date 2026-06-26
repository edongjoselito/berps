<?php
$record = isset($record) ? $record : (!empty($data) ? $data[0] : null);
$pageLabel = isset($pageLabel) ? $pageLabel : 'Job Order';
$backUrl = isset($backUrl) ? $backUrl : base_url() . 'Page/joList';
$backLabel = isset($backLabel) ? $backLabel : 'Job Order List';
$returnTo = isset($returnTo) ? $returnTo : 'joList';
$clients = isset($data2) && is_array($data2) ? $data2 : [];
$serviceFees = isset($serviceFees) && is_array($serviceFees) ? $serviceFees : [];
$isInvoicePage = strtolower((string) $returnTo) === 'invlist' || strtolower((string) $pageLabel) === 'invoice';
$isGeneratedRecurring = $record && !empty($record->recurringTemplateID);
$buildServiceFeeDescription = static function ($serviceRow) {
  $category = trim((string) (is_object($serviceRow) ? ($serviceRow->FeesDescription ?? '') : ($serviceRow['FeesDescription'] ?? '')));
  $subCategory = trim((string) (is_object($serviceRow) ? ($serviceRow->subCategory ?? '') : ($serviceRow['subCategory'] ?? '')));
  $details = trim((string) (is_object($serviceRow) ? ($serviceRow->feeDetails ?? '') : ($serviceRow['feeDetails'] ?? '')));

  $parts = array();
  if ($category !== '') {
    $parts[] = $category;
  }
  if ($subCategory !== '') {
    $parts[] = $subCategory;
  }

  $description = !empty($parts) ? implode(' - ', $parts) : ($category !== '' ? $category : 'Service item');
  if ($details !== '') {
    $description .= ' (' . $details . ')';
  }

  return $description;
};
$serviceCatalog = array();
$serviceCategories = array();
$selectedServiceCategory = '';
$selectedServiceFeeId = '';
$currentDescription = trim((string) ($record->JobDescription ?? ''));
$currentTotalDue = (float) ($record->TotalDue ?? 0);
foreach ($serviceFees as $serviceRow) {
  $category = trim((string) ($serviceRow->FeesDescription ?? ''));
  if ($category === '') {
    continue;
  }

  $serviceCategories[$category] = $category;
  $catalogItem = array(
    'id' => (int) ($serviceRow->feesID ?? 0),
    'category' => $category,
    'subCategory' => trim((string) ($serviceRow->subCategory ?? '')),
    'details' => trim((string) ($serviceRow->feeDetails ?? '')),
    'amount' => number_format((float) ($serviceRow->Amount ?? 0), 2, '.', ''),
    'description' => $buildServiceFeeDescription($serviceRow),
  );

  if ($selectedServiceFeeId === '' && $currentDescription !== '') {
    $descriptionMatches = strcasecmp($catalogItem['description'], $currentDescription) === 0;
    $amountMatches = abs((float) $catalogItem['amount'] - $currentTotalDue) < 0.005;
    if ($descriptionMatches && $amountMatches) {
      $selectedServiceFeeId = (string) $catalogItem['id'];
      $selectedServiceCategory = $catalogItem['category'];
    }
  }

  $serviceCatalog[] = $catalogItem;
}
if (!empty($serviceCategories)) {
  natcasesort($serviceCategories);
}
$serviceCategories = array_values($serviceCategories);
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BERPS</title>
  <!-- Iconic Fonts -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="<?= base_url(); ?>vendors/iconic-fonts/font-awesome/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/flat-icons/flaticon.css">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/cryptocoins/cryptocoins.css">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/cryptocoins/cryptocoins-colors.css">
  <!-- Bootstrap core CSS -->
  <link href="<?= base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery UI -->
  <link href="<?= base_url(); ?>assets/css/jquery-ui.min.css" rel="stylesheet">
  <!-- Medjestic styles -->
  <link href="<?= base_url(); ?>assets/css/style.css" rel="stylesheet">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url(); ?>favicon.ico">
  <script type="text/javascript" src="<?= base_url(); ?>assets/js/calculate.js"></script>
  <script type="text/javascript">
    var serviceCatalog = <?= json_encode(array_values($serviceCatalog), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    function normalizeAmount(value) {
      var parsed = parseFloat(value);
      return isFinite(parsed) && parsed >= 0 ? parsed.toFixed(2) : '0.00';
    }

    function balance() {
      var totalDueField = document.getElementById('TotalDue');
      var amountPaidField = document.getElementById('AmountPaid');
      var balanceField = document.getElementById('Balance');

      if (!totalDueField || !amountPaidField || !balanceField) {
        return;
      }

      var totalDue = parseFloat(totalDueField.value) || 0;
      var amountPaid = parseFloat(amountPaidField.value) || 0;
      var result = totalDue - amountPaid;

      balanceField.value = result.toFixed(2);
    }

    function syncInvoiceItemPrice() {
      var totalDueField = document.getElementById('TotalDue');
      var itemQuantityField = document.getElementById('itemQuantity');
      var itemDurationUnitField = document.getElementById('itemDurationUnit');
      var itemUnitPriceField = document.getElementById('itemUnitPrice');
      var quantity = itemQuantityField ? (parseFloat(itemQuantityField.value) || 1) : 1;

      if (itemQuantityField) {
        itemQuantityField.value = quantity > 0 ? String(Math.round(quantity)) : '1';
      }

      if (itemDurationUnitField && !itemDurationUnitField.value) {
        itemDurationUnitField.value = 'each';
      }

      if (totalDueField && itemUnitPriceField) {
        itemUnitPriceField.value = normalizeAmount((parseFloat(totalDueField.value) || 0) / (quantity > 0 ? quantity : 1));
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      balance();
      syncInvoiceItemPrice();

      var recurringFrequencySelect = document.querySelector('select[name="recurringFrequency"]');
      var coverageOptionRow = document.getElementById('coverage-option-row');
      var coverageOptionSelect = document.getElementById('coverage-option');
      var coverageOptionHelp = document.getElementById('coverage-option-help');

      function syncCoverageOptionState() {
        if (!coverageOptionRow || !coverageOptionSelect) {
          return;
        }

        var isRecurring = recurringFrequencySelect && recurringFrequencySelect.value !== 'none';
        var isGeneratedCoverageField = coverageOptionSelect.getAttribute('data-generated-lock') === '1';

        coverageOptionRow.style.display = 'flex';
        coverageOptionSelect.disabled = isGeneratedCoverageField;

        if (coverageOptionHelp) {
          if (isGeneratedCoverageField) {
            coverageOptionHelp.textContent = 'This billing period is inherited from the recurring template.';
          } else if (isRecurring) {
            coverageOptionHelp.textContent = 'Select whether this invoice covers the previous period or the upcoming period relative to the schedule date.';
          } else {
            coverageOptionHelp.textContent = 'You can choose the billing period now. It will apply once you set a recurring frequency.';
          }
        }
      }

      if (recurringFrequencySelect) {
        recurringFrequencySelect.addEventListener('change', syncCoverageOptionState);
      }

      syncCoverageOptionState();

      var customerSelect = document.getElementById('validationCustomer');
      var addressField = document.getElementById('validationAddress');
      var serviceCategoryField = document.getElementById('serviceCategory');
      var servicePlanField = document.getElementById('servicePlan');
      var serviceDetailsField = document.getElementById('serviceDetails');
      var jobDescriptionField = document.getElementById('jobDescription');
      var totalDueField = document.getElementById('TotalDue');
      var serviceCatalogById = {};
      var serviceCatalogByCategory = {};

      function syncAddress() {
        if (!customerSelect || !addressField) {
          return;
        }

        var selected = customerSelect.options[customerSelect.selectedIndex];
        var selectedValue = customerSelect.value || '';
        var selectedAddress = selected ? (selected.getAttribute('data-address') || '') : '';
        if (selectedValue || !addressField.value) {
          addressField.value = selectedAddress;
        }
      }

      function buildServicePlanLabel(serviceItem) {
        var label = serviceItem.subCategory || serviceItem.details || 'Default rate';
        var amount = parseFloat(serviceItem.amount || 0) || 0;
        return label + ' - PHP ' + amount.toLocaleString('en-PH', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      }

      function populateServicePlans(categoryValue, selectedPlanId) {
        if (!servicePlanField) {
          return;
        }

        var selectedCategory = categoryValue ? String(categoryValue) : '';
        var plans = selectedCategory && serviceCatalogByCategory[selectedCategory]
          ? serviceCatalogByCategory[selectedCategory]
          : [];

        servicePlanField.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = plans.length ? 'Select a sub category / plan' : 'Select a service category first';
        servicePlanField.appendChild(placeholder);

        plans.forEach(function(plan) {
          var option = document.createElement('option');
          option.value = String(plan.id || '');
          option.textContent = buildServicePlanLabel(plan);
          if (String(plan.id || '') === String(selectedPlanId || '')) {
            option.selected = true;
          }
          servicePlanField.appendChild(option);
        });

        servicePlanField.disabled = plans.length === 0;

        if (!servicePlanField.value && plans.length === 1) {
          servicePlanField.value = String(plans[0].id || '');
        }
      }

      function applySelectedServicePlan(planId) {
        if (!servicePlanField) {
          return;
        }

        var serviceItem = serviceCatalogById[String(planId || '')] || null;
        if (serviceDetailsField) {
          serviceDetailsField.value = serviceItem ? (serviceItem.details || serviceItem.subCategory || '') : '';
        }

        if (!serviceItem) {
          syncInvoiceItemPrice();
          return;
        }

        if (jobDescriptionField) {
          jobDescriptionField.value = serviceItem.description || '';
        }

        var itemQuantityField = document.getElementById('itemQuantity');
        var itemDurationUnitField = document.getElementById('itemDurationUnit');
        if (itemQuantityField) {
          itemQuantityField.value = '1';
        }
        if (itemDurationUnitField) {
          itemDurationUnitField.value = 'each';
        }

        if (totalDueField) {
          totalDueField.value = normalizeAmount(serviceItem.amount);
        }

        syncInvoiceItemPrice();
        balance();
      }

      if (customerSelect) {
        customerSelect.addEventListener('change', syncAddress);
        syncAddress();
      }

      serviceCatalog.forEach(function(serviceItem) {
        var normalized = {
          id: String(serviceItem.id || ''),
          category: serviceItem.category ? String(serviceItem.category) : '',
          subCategory: serviceItem.subCategory ? String(serviceItem.subCategory) : '',
          details: serviceItem.details ? String(serviceItem.details) : '',
          amount: normalizeAmount(serviceItem.amount),
          description: serviceItem.description ? String(serviceItem.description) : ''
        };

        serviceCatalogById[normalized.id] = normalized;
        if (!serviceCatalogByCategory[normalized.category]) {
          serviceCatalogByCategory[normalized.category] = [];
        }
        serviceCatalogByCategory[normalized.category].push(normalized);
      });

      if (serviceCategoryField && servicePlanField) {
        serviceCategoryField.addEventListener('change', function() {
          populateServicePlans(serviceCategoryField.value || '', '');
          applySelectedServicePlan(servicePlanField.value || '');
        });

        servicePlanField.addEventListener('change', function() {
          applySelectedServicePlan(servicePlanField.value || '');
        });

        var initialCategory = serviceCategoryField.getAttribute('data-initial-category') || serviceCategoryField.value || '';
        var initialPlanId = servicePlanField.getAttribute('data-initial-fee-id') || servicePlanField.value || '';

        populateServicePlans(initialCategory, initialPlanId);
        applySelectedServicePlan(servicePlanField.value || initialPlanId);
      }

      if (totalDueField) {
        totalDueField.addEventListener('input', syncInvoiceItemPrice);
      }
    });
  </script>
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
</head>

<body class="ms-body ms-aside-left-open ms-primary-theme ms-has-quickbar">

  <!-- Setting Panel -->

  <!-- Sidebar Navigation Left -->
  <?php include('includes/sidebar.php'); ?>

  <!-- Sidebar Right -->

  <!-- Main Content -->
  <main class="body-content">

    <!-- Navigation Bar -->
    <?php include('includes/nav.php'); ?>


    <!-- Body Content Wrapper -->
    <div class="ms-content-wrapper">
      <div class="row">
        <div class="col-md-12">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
              <li class="breadcrumb-item"><a href="<?= base_url(); ?>Page/admin"><i class="material-icons">home</i> Home</a></li>
              <li class="breadcrumb-item"><a href="#"><?= htmlspecialchars($pageLabel, ENT_QUOTES, 'UTF-8'); ?></a></li>
              <li class="breadcrumb-item active" aria-current="page">Update <?= htmlspecialchars($pageLabel, ENT_QUOTES, 'UTF-8'); ?></li>
            </ol>
          </nav>
        </div>
        <div class="col-xl-12 col-md-12">
          <div class="ms-panel">
            <div class="ms-panel-header ms-panel-custome">
              <h6>Update <?= htmlspecialchars($pageLabel, ENT_QUOTES, 'UTF-8'); ?></h6>
              <a href="<?= $backUrl; ?>" class="ms-text-primary"><?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
            <div class="ms-panel-body">
              <?php if ($record): ?>
                <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateJO" novalidate>
                  <input type="hidden" name="id" value="<?= (int) $record->orderID; ?>">
                  <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="itemQuantity" id="itemQuantity" value="<?= htmlspecialchars((string) ((isset($record->itemQuantity) && (float) $record->itemQuantity > 0) ? (int) $record->itemQuantity : 1), ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="itemDurationUnit" id="itemDurationUnit" value="<?= htmlspecialchars((string) (!empty($record->itemDurationUnit) ? $record->itemDurationUnit : 'each'), ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="itemUnitPrice" id="itemUnitPrice" value="<?= htmlspecialchars(number_format((float) (!empty($record->itemUnitPrice) ? $record->itemUnitPrice : $record->TotalDue), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                  <div class="form-row">
                    <div class="col-md-3 mb-3">
                      <label for="validationCustom050">Invoice No.</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="InvoiceNo" id="validationCustom050" value="<?= htmlspecialchars((string) $record->InvoiceNo, ENT_QUOTES, 'UTF-8'); ?>" required readonly>
                      </div>
                    </div>

                    <div class="col-md-9 mb-3">
                      <label for="validationCustom020">Customer</label>
                      <div class="input-group">
                        <select class="form-control" name="CustID" id="validationCustomer" required>
                          <option value="">Select customer</option>
                          <?php foreach ($clients as $client): ?>
                            <option value="<?= htmlspecialchars((string) $client->CustID, ENT_QUOTES, 'UTF-8'); ?>" data-address="<?= htmlspecialchars((string) ($client->Address ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?= ((string) ($record->CustID ?? '') === (string) $client->CustID) ? 'selected' : ''; ?>>
                              <?= htmlspecialchars((string) $client->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars((string) $client->CustID, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>

                  </div>

                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="validationCustom020">Customer Address</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="CustAddress" value="<?= htmlspecialchars((string) $record->CustAddress, ENT_QUOTES, 'UTF-8'); ?>" id="validationAddress" readonly>
                      </div>
                    </div>

                  </div>

                  <div class="form-row">
                    <div class="col-md-6 mb-2">
                      <label for="serviceCategory">Service Category</label>
                      <select class="form-control" name="serviceCategory" id="serviceCategory" data-initial-category="<?= htmlspecialchars($selectedServiceCategory, ENT_QUOTES, 'UTF-8'); ?>">
                        <option value="">Manual entry / no preset</option>
                        <?php foreach ($serviceCategories as $serviceCategory): ?>
                          <option value="<?= htmlspecialchars((string) $serviceCategory, ENT_QUOTES, 'UTF-8'); ?>" <?= $selectedServiceCategory === (string) $serviceCategory ? 'selected' : ''; ?>>
                            <?= htmlspecialchars((string) $serviceCategory, ENT_QUOTES, 'UTF-8'); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <small class="form-text text-muted">Choose a saved service to load its sub category and price automatically.</small>
                    </div>
                    <div class="col-md-6 mb-2">
                      <label for="servicePlan">Sub Category / Plan</label>
                      <select class="form-control" name="serviceFeeID" id="servicePlan" data-initial-fee-id="<?= htmlspecialchars($selectedServiceFeeId, ENT_QUOTES, 'UTF-8'); ?>" <?= empty($serviceCategories) ? 'disabled' : ''; ?>>
                        <option value=""><?= $selectedServiceCategory !== '' ? 'Select a sub category / plan' : 'Select a service category first'; ?></option>
                      </select>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="serviceDetails">Price List Details</label>
                      <input type="text" class="form-control" id="serviceDetails" value="" readonly>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="col-md-6 mb-2">
                      <label>Job Description</label>
                      <input type="text" class="form-control" name="JobDescription" value="<?= htmlspecialchars((string) $record->JobDescription, ENT_QUOTES, 'UTF-8'); ?>" id="jobDescription" required>
                    </div>

                    <div class="col-md-6 mb-2">
                      <label>Notes</label>
                      <input type="text" class="form-control" name="Notes" value="<?= htmlspecialchars((string) $record->Notes, ENT_QUOTES, 'UTF-8'); ?>" id="validationCustom020">
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="col-md-4 mb-2">
                      <label>Total Due</label>
                      <input type="number" class="form-control" name="TotalDue" id="TotalDue" value="<?= htmlspecialchars((string) $record->TotalDue, ENT_QUOTES, 'UTF-8'); ?>" min="0" step="0.01" oninput="balance()" required>
                    </div>

                    <div class="col-md-4 mb-2">
                      <label>Amount Paid</label>
                      <input type="number" class="form-control" name="AmountPaid" id="AmountPaid" value="<?= htmlspecialchars((string) $record->AmountPaid, ENT_QUOTES, 'UTF-8'); ?>" step="0.01" oninput="balance()" required readonly>
                    </div>

                    <div class="col-md-4 mb-2">
                      <label>Balance</label>
                      <input type="number" class="form-control" name="Balance" value="<?= htmlspecialchars((string) $record->Balance, ENT_QUOTES, 'UTF-8'); ?>" id="Balance" step="0.01" required readonly>
                    </div>
                  </div>

                  <?php if ($isInvoicePage): ?>
                    <div class="form-row">
                      <div class="col-md-6 mb-2">
                        <label>Recurring Frequency</label>
                        <select class="form-control" name="recurringFrequency" <?= $isGeneratedRecurring ? 'disabled' : ''; ?>>
                          <option value="none" <?= (($record->recurringFrequency ?? 'none') === 'none') ? 'selected' : ''; ?>>No (One-time)</option>
                          <option value="daily" <?= (($record->recurringFrequency ?? 'none') === 'daily') ? 'selected' : ''; ?>>Daily</option>
                          <option value="weekly" <?= (($record->recurringFrequency ?? 'none') === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                          <option value="monthly" <?= (($record->recurringFrequency ?? 'none') === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                          <option value="quarterly" <?= (($record->recurringFrequency ?? 'none') === 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                          <option value="yearly" <?= (($record->recurringFrequency ?? 'none') === 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                        </select>
                        <small class="form-text text-muted">Select <strong>No</strong> for a one-time invoice. Choose any frequency to make the invoice recurring.</small>
                      </div>
                      <div class="col-md-6 mb-2">
                        <label>Schedule Date</label>
                        <input type="date" class="form-control" name="recurringScheduleDate" value="<?= htmlspecialchars((string) ($record->recurringScheduleDate ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?= $isGeneratedRecurring ? 'readonly' : ''; ?>>
                        <small class="form-text text-muted">
                          <?= $isGeneratedRecurring ? 'This invoice was generated from a recurring template.' : 'Recurring invoices generate 10 days before the schedule date for daily, weekly, monthly, quarterly, or yearly schedules.'; ?>
                        </small>
                      </div>
                    </div>
                    <div class="row" id="coverage-option-row">
                      <div class="col-md-12 mb-2">
                        <label>Coverage Period</label>
                        <select class="form-control" id="coverage-option" name="coverageOption" data-generated-lock="<?= $isGeneratedRecurring ? '1' : '0'; ?>" <?= $isGeneratedRecurring ? 'disabled' : ''; ?>>
                          <option value="coming" <?= (($record->coverageOption ?? 'coming') === 'coming') ? 'selected' : ''; ?>>Upcoming Period</option>
                          <option value="previous" <?= (($record->coverageOption ?? 'coming') === 'previous') ? 'selected' : ''; ?>>Previous Period</option>
                        </select>
                        <small class="form-text text-muted" id="coverage-option-help">
                          <?php if ($isGeneratedRecurring): ?>
                            This billing period is inherited from the recurring template.
                          <?php elseif (($record->recurringFrequency ?? 'none') !== 'none'): ?>
                            Select whether this invoice covers the previous period or the upcoming period relative to the schedule date.
                          <?php else: ?>
                            You can choose the billing period now. It will apply once you set a recurring frequency.
                          <?php endif; ?>
                        </small>
                      </div>
                    </div>
                  <?php endif; ?>

                  <input type="submit" name="submit" class="btn btn-primary mt-4 d-inline w-20" value="Update <?= htmlspecialchars($pageLabel, ENT_QUOTES, 'UTF-8'); ?>">
                  <button class="btn btn-warning mt-4 d-inline w-20" type="reset
                ">Reset</button>
                  <!-- <button name="submit" class="btn btn-primary mt-4 d-inline w-20" type="submit">Accept Payment</button>  -->
                </form>
              <?php else: ?>
                <div class="alert alert-warning mb-0">
                  This record could not be found.
                  <a href="<?= $backUrl; ?>" class="alert-link ml-2">Return to list</a>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>



      </div>
    </div>

  </main>

  <!-- SCRIPTS -->
  <!-- Global Required Scripts Start -->
  <script src="<?= base_url(); ?>assets/js/jquery-3.3.1.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/popper.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/perfect-scrollbar.js"> </script>
  <script src="<?= base_url(); ?>assets/js/jquery-ui.min.js"> </script>
  <!-- Global Required Scripts End -->

  <!-- Page Specific Scripts Start -->
  <script src="<?= base_url(); ?>assets/js/Chart.bundle.min.js"> </script>
  <script src="<?= base_url(); ?>assets/js/client-managemenet.js"> </script>
  <!-- Page Specific Scripts Finish -->

  <!-- Medjestic core JavaScript -->
  <script src="<?= base_url(); ?>assets/js/framework.js"></script>

  <!-- Settings -->
  <script src="<?= base_url(); ?>assets/js/settings.js"></script>

</body>

</html>
