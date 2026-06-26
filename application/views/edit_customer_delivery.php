<?php
$delivery = isset($delivery) ? $delivery : null;
$items = isset($items) ? $items : array();
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
                <div class="container-fluid delivery-entry-page">

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

                    <style>
                        .delivery-entry-page {
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
                            --danger: #e11d48;
                            --warning: #d97706;
                            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
                            --shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.05);
                            --radius-xl: 24px;
                            --radius-lg: 18px;
                            --radius-md: 14px;
                            --radius-sm: 10px;
                            --font-body: var(--font-primary);
                            --font-head: var(--font-primary);
                            --font-mono: var(--font-primary);
                            background:
                                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                            min-height: 100vh;
                            padding-bottom: 28px;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                        }

                        .delivery-entry-page * {
                            box-sizing: border-box;
                        }

                        .delivery-entry-page .entry-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            gap: 18px;
                            margin: 24px 0 22px;
                            flex-wrap: wrap;
                        }

                        .delivery-entry-page .entry-eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            padding: 7px 12px;
                            border-radius: 999px;
                            background: rgba(37, 99, 235, 0.08);
                            color: var(--primary-2);
                            font-size: 0.74rem;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 12px;
                        }

                        .delivery-entry-page .entry-eyebrow::before {
                            content: '';
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                        }

                        .delivery-entry-page .entry-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 2.1rem;
                            line-height: 1.05;
                            letter-spacing: -0.05em;
                            font-weight: 800;
                            color: var(--text);
                        }

                        .delivery-entry-page .entry-subtitle {
                            margin-top: 8px;
                            color: var(--text-soft);
                            font-size: 0.96rem;
                            max-width: 760px;
                        }

                        .delivery-entry-page .entry-actions {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .delivery-entry-page .btn-action,
                        .delivery-entry-page .btn-submit,
                        .delivery-entry-page .btn-add-row,
                        .delivery-entry-page .btn-remove-row {
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
                            position: relative;
                            overflow: hidden;
                        }

                        .delivery-entry-page .btn-action {
                            border: 1px solid var(--line-strong);
                            color: var(--text);
                            background: #fff;
                        }

                        .delivery-entry-page .btn-action:hover {
                            color: var(--primary);
                            border-color: #bfd3ef;
                            background: #f9fbff;
                        }

                        .delivery-entry-page .btn-submit {
                            border: none;
                            color: #fff;
                            background: linear-gradient(135deg, var(--primary), var(--primary-2));
                            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                        }

                        .delivery-entry-page .btn-submit:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                        }

                        .delivery-entry-page .btn-remove-row {
                            background: var(--danger);
                            color: #fff;
                            border: none;
                        }

                        .delivery-entry-page .btn-remove-row:hover {
                            background: #dc2626;
                            transform: translateY(-1px);
                        }

                        .delivery-entry-page .entry-layout {
                            display: grid;
                            grid-template-columns: minmax(0, 1.85fr) minmax(300px, 0.95fr);
                            gap: 20px;
                            align-items: start;
                        }

                        .delivery-entry-page .entry-main,
                        .delivery-entry-page .entry-side {
                            display: grid;
                            gap: 20px;
                        }

                        .delivery-entry-page .entry-card {
                            background: var(--surface);
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            padding: 24px;
                            box-shadow: var(--shadow-soft);
                        }

                        .delivery-entry-page .entry-card-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 20px;
                            padding-bottom: 16px;
                            border-bottom: 1px solid var(--line);
                        }

                        .delivery-entry-page .entry-card-title {
                            margin: 0;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
                            font-size: 1.25rem;
                            font-weight: 700;
                            color: var(--text);
                        }

                        .delivery-entry-page .form-group {
                            margin-bottom: 16px;
                        }

                        .delivery-entry-page .form-label {
                            display: block;
                            margin-bottom: 6px;
                            font-size: 0.85rem;
                            font-weight: 600;
                            color: var(--text);
                        }

                        .delivery-entry-page .form-control,
                        .delivery-entry-page .form-select {
                            width: 100%;
                            padding: 10px 14px;
                            border: 1px solid var(--line-strong);
                            border-radius: var(--radius-sm);
                            font-size: 0.9rem;
                            color: var(--text);
                            background: #fff;
                            transition: all 0.16s ease;
                        }

                        .delivery-entry-page .form-control:focus,
                        .delivery-entry-page .form-select:focus {
                            outline: none;
                            border-color: var(--primary);
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
                        }

                        .delivery-entry-page .form-control[readonly] {
                            background: var(--surface-soft);
                            color: var(--text-soft);
                        }

                        .delivery-entry-page .item-row {
                            display: grid;
                            grid-template-columns: 2fr 1fr 1fr 1.2fr 1.2fr auto;
                            gap: 12px;
                            align-items: end;
                            padding: 16px;
                            background: var(--surface-soft);
                            border-radius: var(--radius-md);
                            margin-bottom: 12px;
                        }

                        .delivery-entry-page .details-row {
                            display: grid;
                            grid-template-columns: 1fr 1fr 1fr;
                            gap: 12px;
                            margin-top: 16px;
                        }

                        @media (max-width: 992px) {
                            .delivery-entry-page .entry-layout {
                                grid-template-columns: 1fr;
                            }

                            .delivery-entry-page .item-row {
                                grid-template-columns: 1fr 1fr;
                            }

                            .delivery-entry-page .item-row > *:nth-child(n+3) {
                                grid-column: span 1;
                            }
                        }
                    </style>

                    <div class="entry-header">
                        <div>
                            <div class="entry-eyebrow">Customer Delivery</div>
                            <h1 class="entry-title">Edit Delivery</h1>
                            <p class="entry-subtitle">Update delivery information and items</p>
                        </div>
                        <div class="entry-actions">
                            <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn-action">
                                <i class="mdi mdi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <?php if ($delivery): ?>
                    <form method="post" action="<?= base_url('Page/updateCustomerDelivery'); ?>">
                        <input type="hidden" name="deliveryID" value="<?= htmlspecialchars($delivery->deliveryID, ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <div class="entry-layout">
                            <div class="entry-main">
                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Delivery Information</h2>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="deliveryNo" class="form-label">Delivery No. *</label>
                                                <input type="text" class="form-control" id="deliveryNo" name="deliveryNo" value="<?= htmlspecialchars($delivery->deliveryNo, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="invoiceNo" class="form-label">Invoice Number</label>
                                                <input type="text" class="form-control" id="invoiceNo" name="invoiceNo" value="<?= htmlspecialchars($delivery->invoiceNo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="customerName" class="form-label">Customer Name *</label>
                                                <select class="form-select" id="customerName" name="customerName" required>
                                                    <option value="">Select Customer</option>
                                                    <?php if (!empty($data2)): ?>
                                                        <?php foreach ($data2 as $client): ?>
                                                            <option value="<?= htmlspecialchars($client->Customer, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-address="<?= htmlspecialchars($client->Address ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-id="<?= htmlspecialchars($client->CustID ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    <?= ($client->Customer == $delivery->customerName) ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars($client->Customer, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="customerAddress" class="form-label">Customer Address *</label>
                                                <input type="text" class="form-control" id="customerAddress" name="customerAddress" value="<?= htmlspecialchars($delivery->customerAddress, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="deliveryDate" class="form-label">Delivery Date *</label>
                                                <input type="date" class="form-control" id="deliveryDate" name="deliveryDate" value="<?= htmlspecialchars($delivery->deliveryDate, ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="receivedBy" class="form-label">Received By</label>
                                                <input type="text" class="form-control" id="receivedBy" name="receivedBy" value="<?= htmlspecialchars($delivery->receivedBy ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="notes" class="form-label">Notes</label>
                                                <textarea class="form-control" id="notes" name="notes" rows="2"><?= htmlspecialchars($delivery->notes ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" id="customerID" name="customerID" value="<?= htmlspecialchars($delivery->customerID ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Delivery Items</h2>
                                    </div>

                                    <div id="deliveryItems">
                                        <?php if (!empty($items)): ?>
                                            <?php foreach ($items as $index => $item): ?>
                                                <div class="item-row">
                                                    <div>
                                                        <label class="form-label">Item Description *</label>
                                                        <input type="text" class="form-control itemDescription" name="itemDescription[]" value="<?= htmlspecialchars($item->itemDescription, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                        <input type="hidden" name="itemID[]" value="<?= htmlspecialchars($item->itemID, ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Quantity *</label>
                                                        <input type="number" class="form-control itemQuantity" name="itemQuantity[]" value="<?= htmlspecialchars($item->itemQuantity, ENT_QUOTES, 'UTF-8'); ?>" min="1" required>
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Unit</label>
                                                        <input type="text" class="form-control itemUnit" name="itemUnit[]" value="<?= htmlspecialchars($item->itemUnit ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Unit Price *</label>
                                                        <input type="number" class="form-control itemUnitPrice" name="itemUnitPrice[]" value="<?= htmlspecialchars($item->itemUnitPrice, ENT_QUOTES, 'UTF-8'); ?>" step="0.01" min="0" required>
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Total</label>
                                                        <input type="text" class="form-control lineTotal" readonly value="<?= number_format($item->itemQuantity * $item->itemUnitPrice, 2); ?>">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="button" class="btn-remove-row" onclick="removeDeliveryItem(this)" style="width: 100%; background: var(--danger); color: white; border: none;">
                                                            <i class="mdi mdi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="details-row">
                                                    <div>
                                                        <label class="form-label">Serial Number (Optional)</label>
                                                        <input type="text" class="form-control" name="serialNo[]" value="<?= htmlspecialchars($item->serialNo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Model (Optional)</label>
                                                        <input type="text" class="form-control" name="model[]" value="<?= htmlspecialchars($item->model ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Brand (Optional)</label>
                                                        <input type="text" class="form-control" name="brand[]" value="<?= htmlspecialchars($item->brand ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="item-row">
                                                <div>
                                                    <label class="form-label">Item Description *</label>
                                                    <input type="text" class="form-control itemDescription" name="itemDescription[]" required>
                                                </div>
                                                <div>
                                                    <label class="form-label">Quantity *</label>
                                                    <input type="number" class="form-control itemQuantity" name="itemQuantity[]" value="1" min="1" required>
                                                </div>
                                                <div>
                                                    <label class="form-label">Unit</label>
                                                    <input type="text" class="form-control itemUnit" name="itemUnit[]" placeholder="pcs">
                                                </div>
                                                <div>
                                                    <label class="form-label">Unit Price *</label>
                                                    <input type="number" class="form-control itemUnitPrice" name="itemUnitPrice[]" step="0.01" min="0" required>
                                                </div>
                                                <div>
                                                    <label class="form-label">Total</label>
                                                    <input type="text" class="form-control lineTotal" readonly>
                                                </div>
                                                <div>
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn-remove-row" onclick="removeDeliveryItem(this)" style="width: 100%; background: var(--danger); color: white; border: none;">
                                                        <i class="mdi mdi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="details-row">
                                                <div>
                                                    <label class="form-label">Serial Number (Optional)</label>
                                                    <input type="text" class="form-control" name="serialNo[]" >
                                                </div>
                                                <div>
                                                    <label class="form-label">Model (Optional)</label>
                                                    <input type="text" class="form-control" name="model[]" >
                                                </div>
                                                <div>
                                                    <label class="form-label">Brand (Optional)</label>
                                                    <input type="text" class="form-control" name="brand[]" >
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <button type="button" class="btn-add-row" onclick="addDeliveryItem()">
                                        <i class="mdi mdi-plus-circle"></i> 
                                        <span>Add Item</span>
                                        <span class="item-counter" id="itemCounter"><?= count($items); ?></span>
                                    </button>
                                </div>
                            </div>

                            <div class="entry-side">
                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Actions</h2>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 12px;">
                                        <button type="submit" class="btn-submit">
                                            <i class="mdi mdi-content-save"></i> Update Delivery
                                        </button>
                                        <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn-action" style="text-align: center;">
                                            <i class="mdi mdi-cancel"></i> Cancel
                                        </a>
                                    </div>
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Quick Tips</h2>
                                    </div>
                                    <ul style="margin: 0; padding-left: 20px; color: var(--text-soft); font-size: 0.9rem; line-height: 1.6;">
                                        <li>Update customer information as needed</li>
                                        <li>Modify item quantities and prices</li>
                                        <li>Add or remove items from the delivery</li>
                                        <li>Line totals calculate automatically</li>
                                        <li>Delivery number cannot be changed</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="entry-card">
                            <div class="text-center" style="padding: 40px;">
                                <h3 style="color: var(--danger); margin-bottom: 16px;">Delivery Not Found</h3>
                                <p style="color: var(--text-soft);">The requested delivery record could not be found.</p>
                                <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn-action" style="margin-top: 16px;">
                                    <i class="mdi mdi-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
    $(document).ready(function() {
        // Auto-fill customer address when customer is selected
        $('#customerName').on('change', function() {
            var selected = $(this).find(':selected');
            $('#customerAddress').val(selected.data('address'));
            $('#customerID').val(selected.data('id'));
        });

        // Calculate line totals
        $(document).on('input', '.itemQuantity, .itemUnitPrice', function() {
            var row = $(this).closest('.item-row');
            var qty = parseFloat(row.find('.itemQuantity').val()) || 0;
            var price = parseFloat(row.find('.itemUnitPrice').val()) || 0;
            var total = qty * price;
            row.find('.lineTotal').val(total.toFixed(2));
        });

        // Add item row
        window.addDeliveryItem = function() {
            // Update item counter
            var currentCount = $('#deliveryItems .item-row').length;
            var newCount = currentCount + 1;
            $('#itemCounter').text(newCount);
            
            var html = '<div class="item-row">' +
                '<div>' +
                '<label class="form-label">Item Description *</label>' +
                '<input type="text" class="form-control itemDescription" name="itemDescription[]" required>' +
                '</div>' +
                '<div>' +
                '<label class="form-label">Quantity *</label>' +
                '<input type="number" class="form-control itemQuantity" name="itemQuantity[]" value="1" min="1" required>' +
                '</div>' +
                '<div>' +
                '<label class="form-label">Unit</label>' +
                '<input type="text" class="form-control itemUnit" name="itemUnit[]" placeholder="pcs">' +
                '</div>' +
                '<div>' +
                '<label class="form-label">Unit Price *</label>' +
                '<input type="number" class="form-control itemUnitPrice" name="itemUnitPrice[]" step="0.01" min="0" required>' +
                '</div>' +
                '<div>' +
                '<label class="form-label">Total</label>' +
                '<input type="text" class="form-control lineTotal" readonly>' +
                '</div>' +
                '<div>' +
                '<label class="form-label">&nbsp;</label>' +
                '<button type="button" class="btn-remove-row" onclick="removeDeliveryItem(this)" style="width: 100%; background: var(--danger); color: white; border: none;">' +
                '<i class="mdi mdi-trash"></i>' +
                '</button>' +
                '</div>' +
                '</div>' +
                '<div class="details-row">' +
                '<div>' +
                '<label class="form-label">Serial Number (Optional)</label>' +
                '<input type="text" class="form-control" name="serialNo[]" >' +
                '</div>' +
                '<div>' +
                '<label class="form-label">Model (Optional)</label>' +
                '<input type="text" class="form-control" name="model[]" >' +
                '</div>' +
                '<div>' +
                '<label class="form-label">Brand (Optional)</label>' +
                '<input type="text" class="form-control" name="brand[]" >' +
                '</div>' +
                '</div>';

            $('#deliveryItems').append(html);
        };

        // Remove item row
        window.removeDeliveryItem = function(btn) {
            var itemRow = $(btn).closest('.item-row');
            var detailsRow = itemRow.next('.details-row');
            itemRow.remove();
            detailsRow.remove();
            
            // Update item counter
            var currentCount = $('#deliveryItems .item-row').length;
            $('#itemCounter').text(currentCount);
        };
    });
    </script>

</body>
</html>
