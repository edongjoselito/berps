<?php
$taxProfile = strtolower((string) ($default_tax_profile ?? 'vat'));
$taxProfileLabel = $taxProfile === 'vat' ? 'VAT-registered (12% VAT-inclusive pricing)' : 'Non-VAT setup';
$products = is_array($products ?? null) ? $products : [];
$clients = is_array($clients ?? null) ? $clients : [];
$paymentModes = is_array($payment_modes ?? null) ? $payment_modes : ['Cash', 'GCash', 'Bank Transfer', 'Debit/Credit Card', 'Cheque'];
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
                <div class="container-fluid pos-sale-page">
                    <style>
                        .pos-sale-page {
                            --surface: #ffffff;
                            --surface-soft: #f7fafc;
                            --line: #d8e2ec;
                            --text: #163047;
                            --text-soft: #60758a;
                            --primary: #0f766e;
                            --primary-deep: #115e59;
                            --accent: #f59e0b;
                            --danger: #dc2626;
                            --shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
                            background:
                                radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 28%),
                                radial-gradient(circle at left top, rgba(245, 158, 11, 0.10), transparent 26%),
                                linear-gradient(180deg, #f6fafc 0%, #f2f6fa 100%);
                            min-height: 100vh;
                            padding: 24px 0 32px;
                        }

                        .pos-sale-page .page-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 16px;
                            flex-wrap: wrap;
                            margin-bottom: 18px;
                        }

                        .pos-sale-page .page-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 2rem;
                            font-weight: 800;
                            letter-spacing: -0.04em;
                        }

                        .pos-sale-page .page-subtitle {
                            margin: 8px 0 0;
                            color: var(--text-soft);
                            max-width: 760px;
                        }

                        .pos-sale-page .page-actions {
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .pos-sale-page .btn-ghost,
                        .pos-sale-page .btn-primary-soft {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border-radius: 12px;
                            padding: 10px 16px;
                            font-weight: 700;
                            text-decoration: none;
                        }

                        .pos-sale-page .btn-ghost {
                            border: 1px solid var(--line);
                            background: rgba(255, 255, 255, 0.85);
                            color: var(--text);
                        }

                        .pos-sale-page .btn-primary-soft {
                            border: 1px solid rgba(15, 118, 110, 0.15);
                            background: rgba(15, 118, 110, 0.10);
                            color: var(--primary-deep);
                        }

                        .pos-sale-page .tax-banner {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                            padding: 14px 18px;
                            background: linear-gradient(135deg, #fef3c7, #fffbeb);
                            border: 1px solid #fcd34d;
                            border-radius: 16px;
                            margin-bottom: 18px;
                            color: #7c5200;
                            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.08);
                            flex-wrap: wrap;
                        }

                        .pos-sale-page .tax-banner strong {
                            display: block;
                            color: #7c5200;
                        }

                        .pos-sale-page .theme-card {
                            background: var(--surface);
                            border: 1px solid rgba(255, 255, 255, 0.75);
                            border-radius: 22px;
                            box-shadow: var(--shadow);
                            overflow: hidden;
                            margin-bottom: 18px;
                        }

                        .pos-sale-page .theme-card-head {
                            padding: 18px 20px;
                            border-bottom: 1px solid #e9eef4;
                            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 252, 0.94));
                        }

                        .pos-sale-page .theme-card-title {
                            margin: 0;
                            color: var(--text);
                            font-size: 1rem;
                            font-weight: 800;
                        }

                        .pos-sale-page .theme-card-subtitle {
                            margin-top: 6px;
                            color: var(--text-soft);
                            font-size: 0.9rem;
                        }

                        .pos-sale-page .theme-card-body {
                            padding: 20px;
                        }

                        .pos-sale-page .table thead th {
                            border-top: 0;
                            color: #506579;
                            font-size: 0.76rem;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                        }

                        .pos-sale-page .table td {
                            vertical-align: middle;
                        }

                        .pos-sale-page .line-meta {
                            font-size: 0.78rem;
                            color: var(--text-soft);
                            margin-top: 4px;
                        }

                        .pos-sale-page .summary-grid {
                            display: grid;
                            gap: 12px;
                        }

                        .pos-sale-page .summary-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            padding: 10px 0;
                            border-bottom: 1px dashed #e2e8f0;
                            color: var(--text);
                        }

                        .pos-sale-page .summary-row:last-child {
                            border-bottom: 0;
                            padding-bottom: 0;
                        }

                        .pos-sale-page .summary-row.total-row {
                            margin-top: 4px;
                            padding-top: 14px;
                            border-top: 2px solid #dbeafe;
                            font-size: 1.05rem;
                            font-weight: 800;
                        }

                        .pos-sale-page .summary-label {
                            color: var(--text-soft);
                            font-weight: 600;
                        }

                        .pos-sale-page .summary-value {
                            font-weight: 800;
                            color: var(--text);
                        }

                        .pos-sale-page .summary-value.text-danger {
                            color: var(--danger) !important;
                        }

                        .pos-sale-page .summary-value.text-success {
                            color: var(--primary-deep) !important;
                        }

                        .pos-sale-page .summary-meta {
                            margin-top: 12px;
                            padding: 12px 14px;
                            border-radius: 14px;
                            background: #f7fafc;
                            color: var(--text-soft);
                            font-size: 0.84rem;
                        }

                        .pos-sale-page .btn-line-remove {
                            border: 0;
                            background: rgba(220, 38, 38, 0.10);
                            color: var(--danger);
                            width: 36px;
                            height: 36px;
                            border-radius: 10px;
                        }

                        .pos-sale-page .btn-add-row {
                            border: 1px dashed rgba(15, 118, 110, 0.45);
                            background: rgba(15, 118, 110, 0.06);
                            color: var(--primary-deep);
                            width: 100%;
                            border-radius: 14px;
                            padding: 11px 16px;
                            font-weight: 700;
                        }

                        .pos-sale-page .inline-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 10px;
                            border-radius: 999px;
                            background: rgba(15, 118, 110, 0.08);
                            color: var(--primary-deep);
                            font-size: 0.78rem;
                            font-weight: 700;
                        }

                        .pos-sale-page .line-total-cell {
                            min-width: 120px;
                            font-weight: 800;
                            color: var(--text);
                        }

                        @media (max-width: 991.98px) {
                            .pos-sale-page .page-title {
                                font-size: 1.7rem;
                            }
                        }
                    </style>

                    <?php if (!empty($notice)): ?>
                        <div class="alert alert-<?= ($notice_type === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="page-header">
                        <div>
                            <h4 class="page-title"><?= htmlspecialchars($page_title ?? 'New POS Sale', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="page-subtitle"><?= htmlspecialchars($page_subtitle ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="page-actions">
                            <a class="btn-ghost" href="<?= base_url('Pos/posTransactionHistory'); ?>">
                                <i class="mdi mdi-history"></i> Sales History
                            </a>
                            <a class="btn-primary-soft" href="<?= base_url('Pos/posReports'); ?>">
                                <i class="mdi mdi-chart-box-outline"></i> POS Reports
                            </a>
                        </div>
                    </div>

                    <div class="tax-banner">
                        <div>
                            <strong>Philippine POS Setup Active</strong>
                            <span><?= htmlspecialchars($taxProfileLabel, ENT_QUOTES, 'UTF-8'); ?>. Senior Citizen and PWD discounts follow VAT-less treatment when the business is VAT-registered.</span>
                        </div>
                        <span class="inline-pill">
                            <i class="mdi mdi-receipt"></i>
                            <?= htmlspecialchars(trim((string) ($business->CompTin ?? 'TIN not set yet')), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <form method="post" action="<?= base_url('Pos/posStoreTransaction'); ?>" id="posSaleForm">
                        <div class="row">
                            <div class="col-xl-8">
                                <div class="theme-card">
                                    <div class="theme-card-head">
                                        <div class="theme-card-title">Sale Items</div>
                                        <div class="theme-card-subtitle">Select inventory items, confirm stock, and optionally adjust selling price before checkout.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0" id="saleItemsTable">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width:260px;">Product</th>
                                                        <th style="width:120px;">Qty</th>
                                                        <th style="width:160px;">Unit Price</th>
                                                        <th style="min-width:180px;">Stock / Tax</th>
                                                        <th style="width:130px;" class="text-right">Line Total</th>
                                                        <th style="width:70px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="saleItemsBody"></tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <button type="button" class="btn-add-row" id="addLineItemBtn">
                                                <i class="mdi mdi-plus-circle-outline"></i> Add Another Item
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="theme-card">
                                    <div class="theme-card-head">
                                        <div class="theme-card-title">Customer and Compliance</div>
                                        <div class="theme-card-subtitle">Support walk-in or existing customers, plus TIN and Senior/PWD references when needed.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="customerSelect">Existing Client</label>
                                                <select class="form-control" name="customer_id" id="customerSelect">
                                                    <option value="">Walk-in Customer</option>
                                                    <?php foreach ($clients as $client): ?>
                                                        <?php
                                                        $clientId = trim((string) ($client->CustID ?? ''));
                                                        $clientName = trim((string) ($client->Customer ?? ''));
                                                        $clientAddress = trim((string) ($client->Address ?? ''));
                                                        ?>
                                                        <option value="<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-name="<?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-address="<?= htmlspecialchars($clientAddress, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= htmlspecialchars($clientName . ($clientId !== '' ? ' (' . $clientId . ')' : ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="transactionDate">Transaction Date</label>
                                                <input type="date" class="form-control" id="transactionDate" name="transaction_date" value="<?= date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="customerName">Customer Name</label>
                                                <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Walk-in Customer">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="customerTin">Customer TIN</label>
                                                <input type="text" class="form-control" id="customerTin" name="customer_tin" placeholder="Optional unless required">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-8">
                                                <label for="customerAddress">Customer Address</label>
                                                <input type="text" class="form-control" id="customerAddress" name="customer_address" placeholder="Delivery or billing address">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="terminalNo">Terminal / POS ID</label>
                                                <input type="text" class="form-control" id="terminalNo" name="terminal_no" value="POS-1">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label for="discountType">Discount Type</label>
                                                <select class="form-control" id="discountType" name="discount_type">
                                                    <option value="none">None</option>
                                                    <option value="regular_percent">Regular % Discount</option>
                                                    <option value="regular_amount">Regular Amount Discount</option>
                                                    <option value="senior">Senior Citizen</option>
                                                    <option value="pwd">PWD</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4" id="discountRateGroup">
                                                <label for="discountRate">Discount Rate (%)</label>
                                                <input type="number" step="0.01" class="form-control" id="discountRate" name="discount_rate" value="0">
                                            </div>
                                            <div class="form-group col-md-4" id="discountValueGroup" style="display:none;">
                                                <label for="discountValue">Discount Amount</label>
                                                <input type="number" step="0.01" class="form-control" id="discountValue" name="discount_value" value="0">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6" id="discountIdGroup" style="display:none;">
                                                <label for="customerDiscountId">Senior/PWD ID or Reference</label>
                                                <input type="text" class="form-control" id="customerDiscountId" name="customer_discount_id" placeholder="Required for Senior/PWD sales">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="paymentReference">Payment Reference</label>
                                                <input type="text" class="form-control" id="paymentReference" name="payment_reference" placeholder="Reference no. for GCash, bank, or card">
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="saleNotes">Notes</label>
                                            <textarea class="form-control" id="saleNotes" name="notes" rows="3" placeholder="Optional remarks, delivery note, or collection arrangement"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="theme-card">
                                    <div class="theme-card-head">
                                        <div class="theme-card-title">Payment Terms</div>
                                        <div class="theme-card-subtitle">Choose full payment or installment collection with due dates.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="form-group">
                                            <label for="paymentTerm">Payment Term</label>
                                            <select class="form-control" id="paymentTerm" name="payment_term">
                                                <option value="full">Full Payment</option>
                                                <option value="installment">Installment</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="paymentMode">Payment Mode</label>
                                            <select class="form-control" id="paymentMode" name="payment_mode">
                                                <?php foreach ($paymentModes as $mode): ?>
                                                    <option value="<?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="initialPayment">Amount Received / Downpayment</label>
                                            <input type="number" step="0.01" class="form-control" id="initialPayment" name="initial_payment" value="0">
                                        </div>
                                        <div id="installmentFields" style="display:none;">
                                            <div class="form-group">
                                                <label for="installmentCount">Number of Installments</label>
                                                <input type="number" min="1" class="form-control" id="installmentCount" name="installment_count" value="3">
                                            </div>
                                            <div class="form-group">
                                                <label for="firstDueDate">First Due Date</label>
                                                <input type="date" class="form-control" id="firstDueDate" name="first_due_date" value="<?= date('Y-m-d', strtotime('+30 days')); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="installmentIntervalDays">Days Between Due Dates</label>
                                                <input type="number" min="1" class="form-control" id="installmentIntervalDays" name="installment_interval_days" value="30">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="theme-card">
                                    <div class="theme-card-head">
                                        <div class="theme-card-title">Live Summary</div>
                                        <div class="theme-card-subtitle">Totals are computed using the same VAT and discount logic applied on save.</div>
                                    </div>
                                    <div class="theme-card-body">
                                        <div class="summary-grid">
                                            <div class="summary-row">
                                                <span class="summary-label">Subtotal</span>
                                                <span class="summary-value" data-summary="subtotal">0.00</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Discount</span>
                                                <span class="summary-value" data-summary="discount">0.00</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">VAT</span>
                                                <span class="summary-value" data-summary="vat">0.00</span>
                                            </div>
                                            <div class="summary-row total-row">
                                                <span class="summary-label">Grand Total</span>
                                                <span class="summary-value" data-summary="grand_total">0.00</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Paid Now</span>
                                                <span class="summary-value text-success" data-summary="paid_now">0.00</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Balance</span>
                                                <span class="summary-value text-danger" data-summary="balance">0.00</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Change</span>
                                                <span class="summary-value" data-summary="change">0.00</span>
                                            </div>
                                        </div>
                                        <div class="summary-meta" id="summaryMeta">
                                            Prices are treated as <?= $taxProfile === 'vat' ? 'VAT-inclusive shelf prices' : 'tax-exempt selling prices'; ?> for this business profile.
                                        </div>
                                        <button type="submit" class="btn btn-success btn-block mt-4" id="submitSaleBtn">
                                            <i class="mdi mdi-content-save-outline"></i> Save POS Sale
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>

    <template id="saleItemRowTemplate">
        <tr>
            <td>
                <select class="form-control product-select" name="product_id[]">
                    <option value="">Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $productId = (int) ($product->id ?? 0);
                        $stockQty = (int) ($product->stock_qty ?? 0);
                        $unitPrice = number_format((float) ($product->unit_price ?? 0), 2, '.', '');
                        $taxTypeOption = trim((string) ($product->tax_type ?? 'vatable'));
                        $discountEligible = (int) ($product->discount_eligible ?? 1);
                        $optionLabel = trim((string) ($product->name ?? 'Product')) . ' [' . trim((string) ($product->sku ?? '')) . ']';
                        ?>
                        <option value="<?= $productId; ?>"
                            data-price="<?= htmlspecialchars($unitPrice, ENT_QUOTES, 'UTF-8'); ?>"
                            data-stock="<?= htmlspecialchars((string) $stockQty, ENT_QUOTES, 'UTF-8'); ?>"
                            data-unit="<?= htmlspecialchars((string) ($product->unit ?? 'pcs'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-tax="<?= htmlspecialchars($taxTypeOption, ENT_QUOTES, 'UTF-8'); ?>"
                            data-discount-eligible="<?= htmlspecialchars((string) $discountEligible, ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="number" min="1" class="form-control quantity-input" name="quantity[]" value="1">
            </td>
            <td>
                <input type="number" step="0.01" min="0" class="form-control price-input" name="unit_price[]" value="0.00">
            </td>
            <td>
                <div class="line-meta stock-meta">Stock: 0</div>
                <div class="line-meta tax-meta">Tax: vatable</div>
            </td>
            <td class="text-right line-total-cell">0.00</td>
            <td class="text-center">
                <button type="button" class="btn-line-remove remove-line-btn" title="Remove">
                    <i class="mdi mdi-close"></i>
                </button>
            </td>
        </tr>
    </template>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        (function() {
            var businessTaxProfile = <?= json_encode($taxProfile); ?>;
            var saleItemsBody = document.getElementById('saleItemsBody');
            var rowTemplate = document.getElementById('saleItemRowTemplate');

            function toNumber(value) {
                var parsed = parseFloat((value || '0').toString().replace(/,/g, ''));
                return Number.isFinite(parsed) ? parsed : 0;
            }

            function money(value) {
                return toNumber(value).toFixed(2);
            }

            function discountType() {
                return document.getElementById('discountType').value || 'none';
            }

            function allocateDiscount(items, totalDiscount) {
                var remaining = totalDiscount;
                var eligibleTotal = items.reduce(function(sum, item) {
                    return sum + (item.discountEligible ? item.gross : 0);
                }, 0);
                var allocations = items.map(function() {
                    return 0;
                });

                if (eligibleTotal <= 0 || totalDiscount <= 0) {
                    return allocations;
                }

                var lastEligibleIndex = -1;
                items.forEach(function(item, index) {
                    if (item.discountEligible) {
                        lastEligibleIndex = index;
                    }
                });

                items.forEach(function(item, index) {
                    if (!item.discountEligible) {
                        return;
                    }

                    if (index === lastEligibleIndex) {
                        allocations[index] = Number(remaining.toFixed(2));
                        return;
                    }

                    var share = Number(((item.gross / eligibleTotal) * totalDiscount).toFixed(2));
                    allocations[index] = share;
                    remaining = Number((remaining - share).toFixed(2));
                });

                return allocations;
            }

            function currentItems() {
                return Array.prototype.slice.call(saleItemsBody.querySelectorAll('tr')).map(function(row) {
                    var option = row.querySelector('.product-select option:checked');
                    var productId = row.querySelector('.product-select').value;
                    var quantity = toNumber(row.querySelector('.quantity-input').value);
                    var price = toNumber(row.querySelector('.price-input').value);
                    var gross = Number((quantity * price).toFixed(2));

                    return {
                        row: row,
                        productId: productId,
                        quantity: quantity,
                        price: price,
                        gross: gross,
                        stock: option ? toNumber(option.getAttribute('data-stock')) : 0,
                        taxType: option ? (option.getAttribute('data-tax') || 'vatable') : 'vatable',
                        unit: option ? (option.getAttribute('data-unit') || 'pcs') : 'pcs',
                        discountEligible: option ? (option.getAttribute('data-discount-eligible') || '1') === '1' : true
                    };
                }).filter(function(item) {
                    return item.productId !== '' && item.quantity > 0;
                });
            }

            function recalc() {
                var items = currentItems();
                var type = discountType();
                var discountRate = Math.max(0, toNumber(document.getElementById('discountRate').value));
                var discountValue = Math.max(0, toNumber(document.getElementById('discountValue').value));
                var subtotal = items.reduce(function(sum, item) {
                    return sum + item.gross;
                }, 0);
                var discountBase = items.reduce(function(sum, item) {
                    return sum + (item.discountEligible ? item.gross : 0);
                }, 0);
                var totalRegularDiscount = 0;

                if (type === 'regular_percent') {
                    totalRegularDiscount = Math.min(discountBase, Number((discountBase * Math.min(discountRate, 100) / 100).toFixed(2)));
                } else if (type === 'regular_amount') {
                    totalRegularDiscount = Math.min(discountBase, discountValue);
                }

                var allocations = allocateDiscount(items, totalRegularDiscount);
                var totals = {
                    subtotal: Number(subtotal.toFixed(2)),
                    discount: 0,
                    vat: 0,
                    total: 0
                };
                var activeRows = items.map(function(item) {
                    return item.row;
                });

                items.forEach(function(item, index) {
                    var lineDiscount = allocations[index] || 0;
                    var lineVat = 0;
                    var lineTotal = 0;
                    var lineDisplayTax = item.taxType;

                    if ((type === 'senior' || type === 'pwd') && item.discountEligible) {
                        if (businessTaxProfile === 'vat' && item.taxType === 'vatable') {
                            var vatless = Number((item.gross / 1.12).toFixed(2));
                            lineDiscount = Number((vatless * 0.20).toFixed(2));
                            lineTotal = Number((vatless - lineDiscount).toFixed(2));
                            lineDisplayTax = 'vat-exempt after VAT-less';
                        } else {
                            lineDiscount = Number((item.gross * 0.20).toFixed(2));
                            lineTotal = Number((item.gross - lineDiscount).toFixed(2));
                            lineDisplayTax = item.taxType;
                        }
                    } else {
                        var discountedGross = Number(Math.max(0, item.gross - lineDiscount).toFixed(2));
                        if (businessTaxProfile === 'vat' && item.taxType === 'vatable') {
                            var vatableSales = Number((discountedGross / 1.12).toFixed(2));
                            lineVat = Number((discountedGross - vatableSales).toFixed(2));
                        }
                        lineTotal = discountedGross;
                    }

                    totals.discount = Number((totals.discount + lineDiscount).toFixed(2));
                    totals.vat = Number((totals.vat + lineVat).toFixed(2));
                    totals.total = Number((totals.total + lineTotal).toFixed(2));

                    var stockMeta = item.row.querySelector('.stock-meta');
                    var taxMeta = item.row.querySelector('.tax-meta');
                    var lineTotalCell = item.row.querySelector('.line-total-cell');
                    stockMeta.textContent = 'Stock: ' + item.stock + ' ' + item.unit;
                    taxMeta.textContent = 'Tax: ' + lineDisplayTax.replace(/_/g, ' ');
                    if (item.quantity > item.stock) {
                        stockMeta.innerHTML = '<span class="text-danger">Stock: ' + item.stock + ' ' + item.unit + ' only</span>';
                    }
                    lineTotalCell.textContent = money(lineTotal);
                });

                Array.prototype.slice.call(saleItemsBody.querySelectorAll('tr')).forEach(function(row) {
                    if (activeRows.indexOf(row) === -1) {
                        var option = row.querySelector('.product-select option:checked');
                        var stockMeta = row.querySelector('.stock-meta');
                        var taxMeta = row.querySelector('.tax-meta');
                        var lineTotalCell = row.querySelector('.line-total-cell');
                        if (option && row.querySelector('.product-select').value !== '') {
                            stockMeta.textContent = 'Stock: ' + (option.getAttribute('data-stock') || '0') + ' ' + (option.getAttribute('data-unit') || 'pcs');
                            taxMeta.textContent = 'Tax: ' + (option.getAttribute('data-tax') || 'vatable').replace(/_/g, ' ');
                        } else {
                            stockMeta.textContent = 'Stock: 0';
                            taxMeta.textContent = 'Tax: vatable';
                        }
                        lineTotalCell.textContent = money(0);
                    }
                });

                var received = Math.max(0, toNumber(document.getElementById('initialPayment').value));
                var paymentTerm = document.getElementById('paymentTerm').value;
                var paidNow = Math.min(received, totals.total);
                var balance = Math.max(0, totals.total - paidNow);
                var change = paymentTerm === 'full' ? Math.max(0, received - totals.total) : 0;

                document.querySelector('[data-summary="subtotal"]').textContent = money(totals.subtotal);
                document.querySelector('[data-summary="discount"]').textContent = money(totals.discount);
                document.querySelector('[data-summary="vat"]').textContent = money(totals.vat);
                document.querySelector('[data-summary="grand_total"]').textContent = money(totals.total);
                document.querySelector('[data-summary="paid_now"]').textContent = money(paidNow);
                document.querySelector('[data-summary="balance"]').textContent = money(balance);
                document.querySelector('[data-summary="change"]').textContent = money(change);

                document.getElementById('summaryMeta').textContent =
                    paymentTerm === 'installment'
                        ? 'Installment schedule will be created for the remaining balance after the downpayment is posted.'
                        : 'Full payment records the collected amount now and computes change when cash tendered is greater than the total.';
            }

            function addRow() {
                var rowNode = rowTemplate.content.firstElementChild.cloneNode(true);
                saleItemsBody.appendChild(rowNode);
                bindRow(rowNode);
                recalc();
            }

            function bindRow(row) {
                row.querySelector('.product-select').addEventListener('change', function() {
                    var option = this.options[this.selectedIndex];
                    var priceInput = row.querySelector('.price-input');
                    if (option && option.value !== '') {
                        priceInput.value = money(option.getAttribute('data-price') || 0);
                    } else {
                        priceInput.value = money(0);
                    }
                    recalc();
                });

                row.querySelector('.quantity-input').addEventListener('input', recalc);
                row.querySelector('.price-input').addEventListener('input', recalc);
                row.querySelector('.remove-line-btn').addEventListener('click', function() {
                    if (saleItemsBody.children.length === 1) {
                        row.querySelector('.product-select').value = '';
                        row.querySelector('.quantity-input').value = 1;
                        row.querySelector('.price-input').value = money(0);
                    } else {
                        row.remove();
                    }
                    recalc();
                });
            }

            function syncDiscountFields() {
                var type = discountType();
                document.getElementById('discountRateGroup').style.display = type === 'regular_percent' ? '' : 'none';
                document.getElementById('discountValueGroup').style.display = type === 'regular_amount' ? '' : 'none';
                document.getElementById('discountIdGroup').style.display = (type === 'senior' || type === 'pwd') ? '' : 'none';
                recalc();
            }

            function syncPaymentTermFields() {
                var paymentTerm = document.getElementById('paymentTerm').value;
                document.getElementById('installmentFields').style.display = paymentTerm === 'installment' ? '' : 'none';
                recalc();
            }

            document.getElementById('addLineItemBtn').addEventListener('click', addRow);
            document.getElementById('discountType').addEventListener('change', syncDiscountFields);
            document.getElementById('discountRate').addEventListener('input', recalc);
            document.getElementById('discountValue').addEventListener('input', recalc);
            document.getElementById('paymentTerm').addEventListener('change', syncPaymentTermFields);
            document.getElementById('initialPayment').addEventListener('input', recalc);

            document.getElementById('customerSelect').addEventListener('change', function() {
                var option = this.options[this.selectedIndex];
                if (!option || !option.value) {
                    document.getElementById('customerName').value = '';
                    document.getElementById('customerAddress').value = '';
                    return;
                }
                document.getElementById('customerName').value = option.getAttribute('data-name') || '';
                document.getElementById('customerAddress').value = option.getAttribute('data-address') || '';
            });

            document.getElementById('posSaleForm').addEventListener('submit', function(event) {
                var items = currentItems();
                if (!items.length) {
                    event.preventDefault();
                    alert('Select at least one product before saving the sale.');
                    return;
                }

                var invalidStock = items.some(function(item) {
                    return item.quantity > item.stock;
                });

                if (invalidStock) {
                    event.preventDefault();
                    alert('One or more items exceed the available stock. Please adjust the quantities first.');
                    return;
                }

                var type = discountType();
                if ((type === 'senior' || type === 'pwd') && !document.getElementById('customerDiscountId').value.trim()) {
                    event.preventDefault();
                    alert('Enter the Senior Citizen or PWD ID/reference before saving the sale.');
                }
            });

            addRow();
            syncDiscountFields();
            syncPaymentTermFields();
        })();
    </script>
</body>

</html>
