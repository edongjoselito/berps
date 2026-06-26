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
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(14, 165, 233, 0.08), transparent 26%),
            radial-gradient(circle at top right, rgba(249, 115, 22, 0.08), transparent 24%),
            linear-gradient(180deg, #f7fafc 0%, #f2f6fa 100%);
        height: 100vh;
        overflow: hidden;
        user-select: none;
    }

    .pos-container {
        display: grid;
        grid-template-columns: 1fr 400px;
        grid-template-rows: 60px 1fr 80px;
        height: 100vh;
        gap: 1px;
        background: rgba(0, 0, 0, 0.1);
    }

    .pos-main,
    .pos-cart {
        min-height: 0;
    }

    @media (max-width: 991.98px) {
        .pos-container {
            grid-template-columns: 1fr;
        }
    }

    /* Header */
    .pos-header {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

        .pos-header h1 {
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pos-header .header-info {
            display: flex;
            gap: 30px;
            align-items: center;
            font-size: 14px;
        }

        .pos-header .header-info span {
            opacity: 0.9;
        }

        .pos-header .exit-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .pos-header .exit-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Content Area */
        .pos-main {
            background: white;
            display: grid;
            grid-template-rows: auto 1fr;
            overflow: hidden;
        }

        /* Search and Barcode Area */
        .search-section {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .search-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .barcode-input {
            flex: 1;
            padding: 15px;
            font-size: 18px;
            border: 2px solid #4a90e2;
            border-radius: 8px;
            background: white;
            font-weight: 500;
            text-transform: uppercase;
        }

        .barcode-input:focus {
            outline: none;
            border-color: #357abd;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .search-btn {
            padding: 15px 25px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: #357abd;
        }

        .manual-btn {
            padding: 15px 25px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .manual-btn:hover {
            background: #5a6268;
        }

        /* Product Grid */
        .products-section {
            padding: 20px;
            overflow-y: auto;
            background: white;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .product-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            border-color: #4a90e2;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .product-card.selected {
            border-color: #28a745;
            background: #f8fff9;
        }

        .product-card .product-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: #333;
        }

        .product-card .product-price {
            font-size: 18px;
            font-weight: 700;
            color: #4a90e2;
            margin-bottom: 5px;
        }

        .product-card .product-stock {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .product-card .product-sku {
            font-size: 11px;
            color: #868e96;
            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif);
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .product-card .stock-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .stock-good { background: #28a745; }
        .stock-low { background: #ffc107; }
        .stock-out { background: #dc3545; }

        /* Cart Sidebar */
        .pos-cart {
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .cart-header {
            padding: 20px;
            background: #34495e;
            border-bottom: 1px solid #495057;
        }

        .cart-header h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .customer-info {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .customer-info input {
            flex: 1;
            padding: 8px;
            border: 1px solid #495057;
            border-radius: 4px;
            background: #2c3e50;
            color: white;
            font-size: 14px;
        }

        .customer-info input::placeholder {
            color: #868e96;
        }

        /* Cart Items */
        .cart-items {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 15px;
        }

        .cart-item {
            background: #34495e;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            position: relative;
        }

        .cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .cart-item-name {
            font-weight: 600;
            font-size: 14px;
        }

        .cart-item-remove {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 12px;
        }

        .cart-item-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .quantity-controls {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .quantity-btn {
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-input {
            width: 40px;
            text-align: center;
            background: #2c3e50;
            color: white;
            border: 1px solid #495057;
            border-radius: 4px;
            padding: 2px;
        }

        .cart-item-total {
            font-weight: 700;
            font-size: 16px;
        }

        /* Cart Summary */
        .cart-summary {
            padding: 20px;
            background: #34495e;
            border-top: 1px solid #495057;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: 700;
            color: #4a90e2;
            padding-top: 10px;
            border-top: 1px solid #495057;
        }

        /* Payment Section */
        .pos-payment {
            grid-column: 1 / -1;
            background: #34495e;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-top: 1px solid #495057;
        }

        .payment-left {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .payment-input {
            padding: 10px 15px;
            border: 2px solid #4a90e2;
            border-radius: 6px;
            background: white;
            font-size: 16px;
            width: 150px;
            font-weight: 600;
        }

        .payment-select {
            padding: 10px 15px;
            border: 2px solid #4a90e2;
            border-radius: 6px;
            background: white;
            font-size: 14px;
            min-width: 120px;
        }

        .payment-buttons {
            display: flex;
            gap: 10px;
        }

        .payment-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pay-btn {
            background: #28a745;
            color: white;
        }

        .pay-btn:hover {
            background: #218838;
        }

        .pay-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .hold-btn {
            background: #ffc107;
            color: #212529;
        }

        .hold-btn:hover {
            background: #e0a800;
        }

        .void-btn {
            background: #dc3545;
            color: white;
        }

        .void-btn:hover {
            background: #c82333;
        }

        /* Keyboard Shortcuts Help */
        .shortcuts-help {
            position: fixed;
            bottom: 90px;
            right: 420px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 12px;
            display: none;
        }

        .shortcuts-help.show {
            display: block;
        }

        /* Quick Product Search */
        .quick-search-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 420px;
            bottom: 80px;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            z-index: 1000;
        }

        .quick-search-overlay.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quick-search-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 70vh;
            overflow-y: auto;
        }

        .quick-search-input {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            border: 2px solid #4a90e2;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .quick-search-results {
            max-height: 400px;
            overflow-y: auto;
        }

        .quick-search-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .quick-search-item:hover {
            background: #f8f9fa;
        }

        .quick-search-item.selected {
            background: #e3f2fd;
            border-color: #4a90e2;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .pos-container {
                grid-template-columns: 1fr 350px;
            }
        }

        @media (max-width: 768px) {
            .pos-container {
                grid-template-columns: 1fr;
                grid-template-rows: 60px 1fr 200px 80px;
            }
            
            .pos-cart {
                grid-column: 1;
                grid-row: 3;
            }
            
            .shortcuts-help {
                right: 20px;
            }
            
            .quick-search-overlay {
                right: 20px;
            }
        }

        /* Loading and Animations */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
<body>
    <div class="pos-container">
        <!-- Header -->
        <header class="pos-header">
            <h1>
                <i class="mdi mdi-cash-register"></i>
                POS System
            </h1>
            <div class="header-info">
                <span>Terminal: POS-1</span>
                <span>User: <?= htmlspecialchars($this->session->userdata('name') ?? 'Operator', ENT_QUOTES, 'UTF-8'); ?></span>
                <span><?= date('M j, Y h:i A'); ?></span>
            </div>
            <button class="exit-btn" onclick="exitPOS()">
                <i class="mdi mdi-exit-to-app"></i> Exit POS
            </button>
        </header>

    <?php if (!empty($notice ?? '')): ?>
        <div style="position: fixed; top: 70px; left: 20px; right: 420px; z-index: 2000;">
            <div class="alert alert-<?= (($notice_type ?? '') === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert" style="margin:0;">
                <?= htmlspecialchars((string) $notice, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    <?php endif; ?>

        <!-- Main Content Area -->
        <main class="pos-main">
            <!-- Search Section -->
            <section class="search-section">
                <div class="search-container">
                    <input type="text" 
                           class="barcode-input" 
                           id="barcodeInput" 
                           placeholder="Scan barcode or enter product code/SKU..."
                           autocomplete="off">
                    <button class="search-btn" onclick="searchProduct()">
                        <i class="mdi mdi-magnify"></i> Search
                    </button>
                    <button class="manual-btn" onclick="showQuickSearch()">
                        <i class="mdi mdi-keyboard"></i> Manual (F1)
                    </button>
                </div>
            </section>

            <!-- Products Section -->
            <section class="products-section">
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $productId = (int) ($product->id ?? 0);
                        $stockQty = (int) ($product->stock_qty ?? 0);
                        $unitPrice = (float) ($product->unit_price ?? 0);
                        $productName = trim((string) ($product->name ?? 'Product'));
                        $productSku = trim((string) ($product->sku ?? ''));
                        $stockClass = $stockQty > 5 ? 'stock-good' : ($stockQty > 0 ? 'stock-low' : 'stock-out');
                        ?>
                        <div class="product-card fade-in" 
                             data-product-id="<?= $productId; ?>"
                             data-product-name="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
                             data-product-price="<?= $unitPrice; ?>"
                             data-product-stock="<?= $stockQty; ?>"
                             data-product-sku="<?= htmlspecialchars($productSku, ENT_QUOTES, 'UTF-8'); ?>"
                             onclick="addToCart(<?= $productId; ?>)">
                            <div class="stock-indicator <?= $stockClass; ?>"></div>
                            <div class="product-name"><?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="product-price"><?= number_format($unitPrice, 2); ?></div>
                            <div class="product-stock">Stock: <?= $stockQty; ?></div>
                            <div class="product-sku"><?= htmlspecialchars($productSku, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>

        <!-- Cart Sidebar -->
        <aside class="pos-cart">
            <div class="cart-header">
                <h2>Shopping Cart</h2>
                <div class="customer-info">
                    <input type="text" 
                           id="customerName" 
                           placeholder="Customer name"
                           onkeyup="updateCustomerInfo()">
                    <input type="text" 
                           id="customerPhone" 
                           placeholder="Phone (optional)"
                           onkeyup="updateCustomerInfo()">
                </div>
            </div>

            <div class="cart-items" id="cartItems">
                <!-- Cart items will be dynamically added here -->
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">0.00</span>
                </div>
                <div class="summary-row">
                    <span>Tax (12%):</span>
                    <span id="tax">0.00</span>
                </div>
                <div class="summary-row">
                    <span>Discount:</span>
                    <span id="discount">0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="total">0.00</span>
                </div>
            </div>
        </aside>

        <!-- Payment Section -->
        <footer class="pos-payment">
            <div class="payment-left">
                <input type="number" 
                       class="payment-input" 
                       id="amountPaid" 
                       placeholder="Amount paid"
                       value="0"
                       step="0.01"
                       onkeyup="calculateChange()">
                <select class="payment-select" id="paymentMode">
                    <?php foreach ($paymentModes as $mode): ?>
                        <option value="<?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div style="color: white; font-size: 14px;">
                    Change: <span id="change" style="font-weight: 700;">0.00</span>
                </div>
            </div>
            <div class="payment-buttons">
                <button class="payment-btn hold-btn" onclick="holdSale()">
                    <i class="mdi mdi-pause"></i> Hold (F9)
                </button>
                <button class="payment-btn void-btn" onclick="voidSale()">
                    <i class="mdi mdi-cancel"></i> Void (F10)
                </button>
                <button class="payment-btn pay-btn" id="payBtn" onclick="processPayment()" disabled>
                    <i class="mdi mdi-cash"></i> Pay (Enter)
                </button>
            </div>

            <form id="posCheckoutForm" method="post" action="<?= base_url('Pos/posStoreTransaction'); ?>" style="display:none;"></form>
        </footer>
    </div>

    <!-- Quick Search Overlay -->
    <div class="quick-search-overlay" id="quickSearchOverlay">
        <div class="quick-search-container">
            <h3>Quick Product Search</h3>
            <input type="text" 
                   class="quick-search-input" 
                   id="quickSearchInput" 
                   placeholder="Type to search products..."
                   onkeyup="quickSearch(event)">
            <div class="quick-search-results" id="quickSearchResults">
                <!-- Search results will be displayed here -->
            </div>
            <div style="margin-top: 15px; text-align: center;">
                <button onclick="hideQuickSearch()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 6px;">
                    Cancel (ESC)
                </button>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts Help -->
    <div class="shortcuts-help" id="shortcutsHelp">
        <strong>Keyboard Shortcuts:</strong><br>
        F1 - Quick Search<br>
        F9 - Hold Sale<br>
        F10 - Void Sale<br>
        Enter - Process Payment<br>
        ESC - Cancel/Exit<br>
        Ctrl+Z - Undo Last Item
    </div>

    <script>
        // Global variables
        let cart = [];
        let currentSaleId = null;
        let productsData = <?= json_encode(array_map(function($p) {
            return [
                'id' => (int)($p->id ?? 0),
                'name' => trim((string)($p->name ?? '')),
                'price' => (float)($p->unit_price ?? 0),
                'stock' => (int)($p->stock_qty ?? 0),
                'sku' => trim((string)($p->sku ?? '')),
                'category' => trim((string)($p->category ?? ''))
            ];
        }, $products)); ?>;

        // Initialize POS
        document.addEventListener('DOMContentLoaded', function() {
            initializePOS();
            setupKeyboardShortcuts();
            focusBarcodeInput();
        });

        function initializePOS() {
            updateCartDisplay();
            updateSummary();
            
            // Auto-focus barcode input
            document.getElementById('barcodeInput').focus();
            
            // Setup barcode scanner detection
            setupBarcodeScanner();
        }

        function setupBarcodeScanner() {
            const barcodeInput = document.getElementById('barcodeInput');
            let barcodeBuffer = '';
            let lastKeyTime = 0;
            
            barcodeInput.addEventListener('keypress', function(e) {
                const currentTime = new Date().getTime();
                const timeDiff = currentTime - lastKeyTime;
                
                // If typing is fast (< 50ms between keys), likely barcode scanner
                if (timeDiff < 50) {
                    barcodeBuffer += e.key;
                    
                    // If Enter is pressed, process barcode
                    if (e.key === 'Enter' && barcodeBuffer.length > 3) {
                        e.preventDefault();
                        processBarcode(barcodeBuffer);
                        barcodeBuffer = '';
                        barcodeInput.value = '';
                    }
                } else {
                    // Manual typing, reset buffer
                    barcodeBuffer = '';
                }
                
                lastKeyTime = currentTime;
            });
        }

        function processBarcode(barcode) {
            // Search for product by barcode/SKU
            const product = productsData.find(p => 
                p.sku === barcode || p.name.toLowerCase().includes(barcode.toLowerCase())
            );
            
            if (product) {
                addToCart(product.id);
                showNotification('Product added: ' + product.name, 'success');
            } else {
                showNotification('Product not found: ' + barcode, 'error');
                beep();
            }
        }

        function searchProduct() {
            const searchTerm = document.getElementById('barcodeInput').value.trim();
            if (!searchTerm) return;
            
            processBarcode(searchTerm);
            document.getElementById('barcodeInput').value = '';
        }

        function addToCart(productId) {
            const product = productsData.find(p => p.id === productId);
            if (!product) return;
            
            // Check stock
            if (product.stock <= 0) {
                showNotification('Product out of stock', 'error');
                beep();
                return;
            }
            
            // Check if already in cart
            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                if (existingItem.quantity >= product.stock) {
                    showNotification('Insufficient stock', 'error');
                    beep();
                    return;
                }
                existingItem.quantity++;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1,
                    stock: product.stock
                });
            }
            
            updateCartDisplay();
            updateSummary();
            
            // Visual feedback
            const productCard = document.querySelector(`[data-product-id="${productId}"]`);
            if (productCard) {
                productCard.classList.add('selected');
                setTimeout(() => productCard.classList.remove('selected'), 300);
            }
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
            updateSummary();
        }

        function updateQuantity(index, change) {
            const item = cart[index];
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                removeFromCart(index);
            } else if (newQuantity <= item.stock) {
                item.quantity = newQuantity;
                updateCartDisplay();
                updateSummary();
            } else {
                showNotification('Insufficient stock', 'error');
                beep();
            }
        }

        function setQuantity(index, quantity) {
            const item = cart[index];
            const newQuantity = parseInt(quantity) || 0;
            
            if (newQuantity <= 0) {
                removeFromCart(index);
            } else if (newQuantity <= item.stock) {
                item.quantity = newQuantity;
                updateCartDisplay();
                updateSummary();
            } else {
                showNotification('Insufficient stock', 'error');
                beep();
                item.quantity = item.stock;
                updateCartDisplay();
            }
        }

        function updateCartDisplay() {
            const cartItemsContainer = document.getElementById('cartItems');
            
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<div style="text-align: center; padding: 20px; opacity: 0.6;">Cart is empty</div>';
                return;
            }
            
            cartItemsContainer.innerHTML = cart.map((item, index) => `
                <div class="cart-item fade-in">
                    <div class="cart-item-header">
                        <div class="cart-item-name">${item.name}</div>
                        <button class="cart-item-remove" onclick="removeFromCart(${index})">×</button>
                    </div>
                    <div class="cart-item-details">
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                            <input type="number" 
                                   class="quantity-input" 
                                   value="${item.quantity}" 
                                   min="1" 
                                   max="${item.stock}"
                                   onchange="setQuantity(${index}, this.value)">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                        <div class="cart-item-total"> ${(item.price * item.quantity).toFixed(2)}</div>
                    </div>
                    <div style="font-size: 12px; opacity: 0.7;">
                        Stock: ${item.stock} | Unit: ${item.price.toFixed(2)}
                    </div>
                </div>
            `).join('');
        }

        function updateSummary() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.12; // 12% VAT
            const discount = 0; // TODO: Implement discount logic
            const total = subtotal + tax - discount;
            
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('tax').textContent = tax.toFixed(2);
            document.getElementById('discount').textContent = discount.toFixed(2);
            document.getElementById('total').textContent = total.toFixed(2);
            
            // Update pay button state
            const payBtn = document.getElementById('payBtn');
            payBtn.disabled = cart.length === 0;
            
            // Calculate change
            calculateChange();
        }

        function calculateChange() {
            const total = parseFloat(document.getElementById('total').textContent) || 0;
            const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
            const change = amountPaid - total;
            
            document.getElementById('change').textContent = change.toFixed(2);
            
            // Enable/disable pay button
            const payBtn = document.getElementById('payBtn');
            payBtn.disabled = cart.length === 0 || amountPaid < total;
        }

        function showQuickSearch() {
            document.getElementById('quickSearchOverlay').classList.add('show');
            document.getElementById('quickSearchInput').focus();
            document.getElementById('quickSearchInput').value = '';
            quickSearch(event);
        }

        function hideQuickSearch() {
            document.getElementById('quickSearchOverlay').classList.remove('show');
            focusBarcodeInput();
        }

        function quickSearch(event) {
            const searchTerm = event.target.value.toLowerCase();
            const resultsContainer = document.getElementById('quickSearchResults');
            
            if (!searchTerm) {
                resultsContainer.innerHTML = '<div style="text-align: center; padding: 20px; opacity: 0.6;">Type to search products</div>';
                return;
            }
            
            const results = productsData.filter(product => 
                product.name.toLowerCase().includes(searchTerm) ||
                product.sku.toLowerCase().includes(searchTerm) ||
                product.category.toLowerCase().includes(searchTerm)
            );
            
            if (results.length === 0) {
                resultsContainer.innerHTML = '<div style="text-align: center; padding: 20px; opacity: 0.6;">No products found</div>';
                return;
            }
            
            resultsContainer.innerHTML = results.map((product, index) => `
                <div class="quick-search-item ${index === 0 ? 'selected' : ''}" 
                     onclick="selectQuickSearchProduct(${product.id})"
                     data-product-id="${product.id}">
                    <div style="font-weight: 600;">${product.name}</div>
                    <div style="font-size: 14px; opacity: 0.7;">
                        SKU: ${product.sku} | Stock: ${product.stock} | Price: ${product.price.toFixed(2)}
                    </div>
                </div>
            `).join('');
        }

        function selectQuickSearchProduct(productId) {
            addToCart(productId);
            hideQuickSearch();
        }

        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Prevent default for our shortcuts
                if (e.key === 'F1' || e.key === 'F9' || e.key === 'F10' || 
                    (e.key === 'Enter' && e.target.tagName !== 'INPUT')) {
                    e.preventDefault();
                }
                
                switch(e.key) {
                    case 'F1':
                        showQuickSearch();
                        break;
                    case 'F9':
                        holdSale();
                        break;
                    case 'F10':
                        voidSale();
                        break;
                    case 'Enter':
                        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                            processPayment();
                        }
                        break;
                    case 'Escape':
                        if (document.getElementById('quickSearchOverlay').classList.contains('show')) {
                            hideQuickSearch();
                        }
                        break;
                    case 'z':
                        if (e.ctrlKey) {
                            e.preventDefault();
                            undoLastItem();
                        }
                        break;
                }
            });
            
            // Show shortcuts help on hover
            let helpTimeout;
            document.addEventListener('keydown', function() {
                clearTimeout(helpTimeout);
                document.getElementById('shortcutsHelp').classList.add('show');
                helpTimeout = setTimeout(() => {
                    document.getElementById('shortcutsHelp').classList.remove('show');
                }, 3000);
            });
        }

        function focusBarcodeInput() {
            setTimeout(() => {
                document.getElementById('barcodeInput').focus();
            }, 100);
        }

        function updateCustomerInfo() {
            // TODO: Update customer information
            console.log('Customer info updated');
        }

        function processPayment() {
            if (cart.length === 0) {
                showNotification('Cart is empty', 'error');
                return;
            }
            
            const total = parseFloat(document.getElementById('total').textContent) || 0;
            const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
            
            if (amountPaid < total) {
                showNotification('Insufficient payment amount', 'error');
                return;
            }

            submitSaleToServer();
        }

        function submitSaleToServer() {
            const form = document.getElementById('posCheckoutForm');
            if (!form) {
                showNotification('Checkout form missing.', 'error');
                return;
            }

            // Clear previous inputs
            form.innerHTML = '';

            const customerName = (document.getElementById('customerName').value || '').trim();
            const paymentMode = document.getElementById('paymentMode').value;
            const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;

            function addHidden(name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }

            // Required sale meta
            addHidden('customer_name', customerName);
            addHidden('payment_mode', paymentMode);
            addHidden('payment_term', 'Full');
            addHidden('initial_payment', amountPaid.toFixed(2));
            addHidden('transaction_date', new Date().toISOString().slice(0, 10));

            // Line items
            cart.forEach(item => {
                addHidden('product_id[]', item.id);
                addHidden('quantity[]', item.quantity);
                addHidden('unit_price[]', item.price);
            });

            form.submit();
        }

        function holdSale() {
            if (cart.length === 0) {
                showNotification('Cart is empty', 'error');
                return;
            }
            
            // TODO: Implement hold sale functionality
            showNotification('Sale held successfully', 'success');
            
            cart = [];
            updateCartDisplay();
            updateSummary();
            focusBarcodeInput();
        }

        function voidSale() {
            if (cart.length === 0) {
                showNotification('Cart is empty', 'error');
                return;
            }
            
            if (confirm('Are you sure you want to void this sale?')) {
                cart = [];
                updateCartDisplay();
                updateSummary();
                document.getElementById('amountPaid').value = '0';
                focusBarcodeInput();
                showNotification('Sale voided', 'info');
            }
        }

        function undoLastItem() {
            if (cart.length > 0) {
                cart.pop();
                updateCartDisplay();
                updateSummary();
                showNotification('Last item removed', 'info');
            }
        }

        function exitPOS() {
            if (cart.length > 0) {
                if (!confirm('You have items in cart. Are you sure you want to exit?')) {
                    return;
                }
            }
            
            window.location.href = '<?= base_url('Pos/posAdmin'); ?>';
        }

        function showNotification(message, type) {
            // Simple notification system
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 10000;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
            
            switch(type) {
                case 'success':
                    notification.style.background = '#28a745';
                    break;
                case 'error':
                    notification.style.background = '#dc3545';
                    break;
                case 'info':
                    notification.style.background = '#17a2b8';
                    break;
                default:
                    notification.style.background = '#6c757d';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        function beep() {
            // Simple beep sound for errors
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        }
    </script>
</body>
</html>
