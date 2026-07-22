<?php
$data2 = isset($data2) ? $data2 : array();
$serviceFees = isset($serviceFees) ? $serviceFees : array();
$data3 = isset($data3) ? $data3 : array();
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
                <div class="container-fluid delivery-entry-page berps-page">

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


                    <header class="berps-page-header">
                        <div class="berps-page-header__content">
                            <span class="berps-page-header__eyebrow">Customer Delivery</span>
                            <h1 class="berps-page-title">New Delivery</h1>
                            <p class="berps-page-subtitle">Create a new delivery record for customer items that are still payable</p>
                        </div>
                        <div class="berps-page-header__actions">
                            <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn btn-outline-secondary">
                                <i class="mdi mdi-arrow-left mr-1" aria-hidden="true"></i>Back to List
                            </a>
                        </div>
                    </header>

                    <form method="post" action="<?= base_url('Page/saveCustomerDelivery'); ?>">
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
                                                <input type="text" class="form-control" id="deliveryNo" name="deliveryNo" required readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="invoiceNo" class="form-label">Invoice Number</label>
                                                <input type="text" class="form-control" id="invoiceNo" name="invoiceNo" placeholder="Auto-generated if left blank">
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
                                                                    data-id="<?= htmlspecialchars($client->CustID ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
                                                <input type="text" class="form-control" id="customerAddress" name="customerAddress" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="deliveryDate" class="form-label">Delivery Date *</label>
                                                <input type="date" class="form-control" id="deliveryDate" name="deliveryDate" value="<?= date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="receivedBy" class="form-label">Received By</label>
                                                <input type="text" class="form-control" id="receivedBy" name="receivedBy" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="notes" class="form-label">Notes</label>
                                                <textarea class="form-control" id="notes" name="notes" rows="2" ></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" id="customerID" name="customerID" value="">
                                    <input type="hidden" id="orderID" name="orderID" value="">
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Delivery Items</h2>
                                    </div>

                                    <div id="deliveryItems">
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
                                                <button type="button" class="btn-remove-row" onclick="removeDeliveryItem(this)">
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
                                    </div>

                                    <div class="add-item-container">
                                        <button type="button" class="btn-add-row" onclick="addDeliveryItem()">
                                            <i class="mdi mdi-plus-circle"></i> 
                                            <span>Add Item</span>
                                            <span class="item-counter" id="itemCounter">1</span>
                                        </button>
                                        <div class="add-item-hint">
                                            <small>Click to add more delivery items</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="entry-side">
                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Actions</h2>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 12px;">
                                        <button type="submit" class="btn-submit">
                                            <i class="mdi mdi-content-save"></i> Save Delivery
                                        </button>
                                        <a href="<?= base_url('Page/customerDeliveryList'); ?>" class="btn btn-outline-secondary" style="text-align: center;">
                                            <i class="mdi mdi-cancel mr-1" aria-hidden="true"></i>Cancel
                                        </a>
                                    </div>
                                </div>

                                <div class="entry-card">
                                    <div class="entry-card-header">
                                        <h2 class="entry-card-title">Quick Tips</h2>
                                    </div>
                                    <ul style="margin: 0; padding-left: 20px; color: var(--text-soft); font-size: 0.9rem; line-height: 1.6;">
                                        <li>Select an invoice to link this delivery</li>
                                        <li>Customer address auto-fills when customer is selected</li>
                                        <li>Add multiple items as needed</li>
                                        <li>Line totals calculate automatically</li>
                                        <li>Serial number, model, and brand are optional</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script>
    $(document).ready(function() {
        // Generate auto-increment delivery number
        generateDeliveryNo();
        
        // Generate auto-increment invoice number
        generateInvoiceNo();
        
        // Auto-fill customer address when customer is selected
        $('#customerName').on('change', function() {
            var selected = $(this).find(':selected');
            $('#customerAddress').val(selected.data('address'));
            $('#customerID').val(selected.data('id'));
        });
        
        // Function to generate auto-increment delivery number
        function generateDeliveryNo() {
            $.ajax({
                url: '<?= base_url('Page/getNextDeliveryNo'); ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.deliveryNo) {
                        $('#deliveryNo').val(response.deliveryNo);
                    }
                },
                error: function() {
                    // Fallback: generate delivery number using date and random
                    var today = new Date();
                    var dateStr = today.getFullYear().toString().substr(-2) + 
                                 (today.getMonth() + 1).toString().padStart(2, '0') + 
                                 today.getDate().toString().padStart(2, '0');
                    var random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                    $('#deliveryNo').val('DEL' + dateStr + '-' + random);
                }
            });
        }
        
        // Function to generate auto-increment invoice number
        function generateInvoiceNo() {
            $.ajax({
                url: '<?= base_url('Page/getNextInvoiceNo'); ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.invoiceNo) {
                        // Only set if field is empty
                        if (!$('#invoiceNo').val()) {
                            $('#invoiceNo').val(response.invoiceNo);
                        }
                    }
                },
                error: function() {
                    // Fallback: generate invoice number using date and random
                    var today = new Date();
                    var dateStr = today.getFullYear().toString().substr(-2) + 
                                 (today.getMonth() + 1).toString().padStart(2, '0') + 
                                 today.getDate().toString().padStart(2, '0');
                    var random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                    if (!$('#invoiceNo').val()) {
                        $('#invoiceNo').val('INV' + dateStr + '-' + random);
                    }
                }
            });
        }

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
                '<button type="button" class="btn-remove-row" onclick="removeDeliveryItem(this)">' +
                '<i class="mdi mdi-trash"></i>' +
                '</button>' +
                '</div>' +
                '</div>' +
                '<div class="details-row">' +
                '<div>' +
                '<label class="form-label">Serial Number (Optional)</label>' +
                '<input type="text" class="form-control" name="serialNo[]" placeholder="For serialized items">' +
                '</div>' +
                '<div>' +
                '<label class="form-label">Model (Optional)</label>' +
                '<input type="text" class="form-control" name="model[]" placeholder="Item model">' +
                '</div>' +
                '<div>' +
                '<label class="form-label">Brand (Optional)</label>' +
                '<input type="text" class="form-control" name="brand[]" placeholder="Item brand">' +
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
