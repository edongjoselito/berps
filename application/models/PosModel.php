<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PosModel extends CI_Model
{
    private $productsTable = 'POS_products';
    private $salesTable = 'pos_sales';
    private $saleItemsTable = 'pos_sale_items';
    private $paymentsTable = 'pos_payments';
    private $installmentTable = 'pos_installment_schedule';
    private $movementsTable = 'pos_inventory_movements';

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function ensureSchema()
    {
        $this->ensureProductsTable();
        $this->ensureSalesTable();
        $this->ensureSaleItemsTable();
        $this->ensurePaymentsTable();
        $this->ensureInstallmentTable();
        $this->ensureInventoryMovementsTable();
    }

    public function getBusinessTaxProfile($settingsID)
    {
        $row = $this->db
            ->where('settingsID', (int) $settingsID)
            ->get('pos_settings', 1)
            ->row();

        $companyType = strtolower(trim((string) ($row->CompType ?? '')));
        if ($companyType === '') {
            return 'vat';
        }

        if (strpos($companyType, 'non-vat') !== false || strpos($companyType, 'non vat') !== false) {
            return 'non_vat';
        }

        return 'vat';
    }

    public function getProductChoices($settingsID)
    {
        $settingsID = (int) $settingsID;

        return $this->db
            ->where('settingsID', $settingsID)
            ->where('status', 'active')
            ->where('stock_qty >', 0)
            ->order_by('name', 'ASC')
            ->get($this->productsTable)
            ->result();
    }

    public function productHasSales($settingsID, $productId)
    {
        return (int) $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('product_id', (int) $productId)
            ->from($this->saleItemsTable)
            ->count_all_results() > 0;
    }

    public function getDashboardMetrics($settingsID)
    {
        $settingsID = (int) $settingsID;
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('-6 days'));
        $monthStart = date('Y-m-01');
        $todayPlus30 = date('Y-m-d', strtotime('+30 days'));

        $salesTodayCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('status !=', 'Voided')
            ->where('transaction_date', $today)
            ->from($this->salesTable)
            ->count_all_results();

        $salesWeekCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('status !=', 'Voided')
            ->where('transaction_date >=', $weekStart)
            ->where('transaction_date <=', $today)
            ->from($this->salesTable)
            ->count_all_results();

        $salesTodayAmountRow = $this->db
            ->select('COALESCE(SUM(grand_total), 0) AS total_amount', false)
            ->where('settingsID', $settingsID)
            ->where('status !=', 'Voided')
            ->where('transaction_date', $today)
            ->get($this->salesTable)
            ->row();

        $salesMonthAmountRow = $this->db
            ->select('COALESCE(SUM(grand_total), 0) AS total_amount', false)
            ->where('settingsID', $settingsID)
            ->where('status !=', 'Voided')
            ->where('transaction_date >=', $monthStart)
            ->where('transaction_date <=', $today)
            ->get($this->salesTable)
            ->row();

        $outstandingRow = $this->db
            ->select('COALESCE(SUM(balance_due), 0) AS total_amount', false)
            ->where('settingsID', $settingsID)
            ->where('status !=', 'Voided')
            ->where('balance_due >', 0)
            ->get($this->salesTable)
            ->row();

        $lowStockCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('status', 'active')
            ->where('stock_qty <= reorder_level', null, false)
            ->from($this->productsTable)
            ->count_all_results();

        $expiringCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('status', 'active')
            ->where("expiry_date >= '$today' AND expiry_date <= '$todayPlus30'", null, false)
            ->where("expiry_date IS NOT NULL", null, false)
            ->from($this->productsTable)
            ->count_all_results();

        $activeProducts = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('status', 'active')
            ->from($this->productsTable)
            ->count_all_results();

        $expiredProducts = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('status', 'active')
            ->where("expiry_date < '$today'", null, false)
            ->where("expiry_date IS NOT NULL", null, false)
            ->from($this->productsTable)
            ->count_all_results();

        $installmentCount = (int) $this->db
            ->where('settingsID', $settingsID)
            ->where('status !=', 'Voided')
            ->where('payment_term', 'installment')
            ->where('balance_due >', 0)
            ->from($this->salesTable)
            ->count_all_results();

        return [
            'kpi_new_transactions' => $salesTodayCount,
            'kpi_completed_sales' => $salesWeekCount,
            'kpi_low_stock' => $lowStockCount,
            'kpi_expiring' => $expiringCount,
            'summary_active_products' => $activeProducts,
            'summary_expired_products' => $expiredProducts,
            'summary_low_stock' => $lowStockCount,
            'summary_expiring' => $expiringCount,
            'summary_sales_today_amount' => round((float) ($salesTodayAmountRow->total_amount ?? 0), 2),
            'summary_sales_month_amount' => round((float) ($salesMonthAmountRow->total_amount ?? 0), 2),
            'summary_outstanding_balance' => round((float) ($outstandingRow->total_amount ?? 0), 2),
            'summary_open_installments' => $installmentCount,
        ];
    }

    public function getSales($settingsID, array $filters = [])
    {
        $settingsID = (int) $settingsID;
        $includeVoided = !empty($filters['include_voided']);
        $status = trim((string) ($filters['status'] ?? ''));
        $search = trim((string) ($filters['search'] ?? ''));
        $dateFrom = $this->normalizeDate($filters['date_from'] ?? null);
        $dateTo = $this->normalizeDate($filters['date_to'] ?? null);

        $this->db
            ->from($this->salesTable)
            ->where('settingsID', $settingsID);

        if (!$includeVoided) {
            $this->db->where('status !=', 'Voided');
        }

        if ($status !== '') {
            $this->db->where('status', $status);
        }

        if ($search !== '') {
            $this->db->group_start()
                ->like('sale_no', $search)
                ->or_like('customer_name', $search)
                ->or_like('cashier_name', $search)
                ->group_end();
        }

        if ($dateFrom !== null) {
            $this->db->where('transaction_date >=', $dateFrom);
        }

        if ($dateTo !== null) {
            $this->db->where('transaction_date <=', $dateTo);
        }

        return $this->db
            ->order_by('transaction_date', 'DESC')
            ->order_by('id', 'DESC')
            ->get()
            ->result();
    }

    public function getSale($settingsID, $saleId)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('id', (int) $saleId)
            ->get($this->salesTable, 1)
            ->row();
    }

    public function getSaleItems($settingsID, $saleId)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('sale_id', (int) $saleId)
            ->order_by('id', 'ASC')
            ->get($this->saleItemsTable)
            ->result();
    }

    public function getSalePayments($settingsID, $saleId)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('sale_id', (int) $saleId)
            ->order_by('payment_date', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->paymentsTable)
            ->result();
    }

    public function getInstallmentSchedule($settingsID, $saleId)
    {
        return $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('sale_id', (int) $saleId)
            ->order_by('installment_no', 'ASC')
            ->get($this->installmentTable)
            ->result();
    }

    public function createSale($settingsID, $cashierName, array $payload)
    {
        $settingsID = (int) $settingsID;
        $cashierName = trim((string) $cashierName);

        if ($settingsID <= 0) {
            return ['success' => false, 'error' => 'Missing business settings context for POS.'];
        }

        $items = $this->prepareSaleItems($settingsID, $payload);
        if (isset($items['error'])) {
            return ['success' => false, 'error' => $items['error']];
        }

        $discountType = $this->normalizeDiscountType($payload['discount_type'] ?? 'none');
        $discountRate = $this->normalizeMoney($payload['discount_rate'] ?? 0);
        $discountValue = $this->normalizeMoney($payload['discount_value'] ?? 0);
        $customer = $this->resolveCustomerDetails($settingsID, $payload, $discountType);

        if (isset($customer['error'])) {
            return ['success' => false, 'error' => $customer['error']];
        }

        $computed = $this->calculateSaleTotals($settingsID, $items['items'], [
            'discount_type' => $discountType,
            'discount_rate' => $discountRate,
            'discount_value' => $discountValue,
        ]);

        if (empty($computed['items'])) {
            return ['success' => false, 'error' => 'Add at least one valid line item before saving the sale.'];
        }

        $paymentTerm = $this->normalizePaymentTerm($payload['payment_term'] ?? 'full');
        $paymentMode = $this->normalizePaymentMode($payload['payment_mode'] ?? 'Cash');
        $notes = trim((string) ($payload['notes'] ?? ''));
        $transactionDate = $this->normalizeDate($payload['transaction_date'] ?? date('Y-m-d'));
        $transactionDate = $transactionDate ?: date('Y-m-d');
        $terminalNo = trim((string) ($payload['terminal_no'] ?? 'POS-1'));
        $initialPayment = $this->normalizeMoney($payload['initial_payment'] ?? 0);
        $grandTotal = round((float) $computed['totals']['grand_total'], 2);

        if ($paymentTerm === 'full' && $initialPayment + 0.0001 < $grandTotal) {
            return ['success' => false, 'error' => 'Full payment transactions must receive the full amount. Use installment payment for partial collections.'];
        }

        $amountPaid = min($initialPayment, $grandTotal);
        $changeAmount = $paymentTerm === 'full' ? round(max(0, $initialPayment - $grandTotal), 2) : 0;
        $balanceDue = round(max(0, $grandTotal - $amountPaid), 2);

        if ($balanceDue > 0 && $paymentTerm !== 'installment') {
            return ['success' => false, 'error' => 'The transaction still has an outstanding balance. Switch the payment term to installment to continue.'];
        }

        $installmentCount = 0;
        $installmentIntervalDays = 0;
        $firstDueDate = null;
        $scheduleRows = [];

        if ($paymentTerm === 'installment' && $balanceDue > 0) {
            $installmentCount = max(1, (int) ($payload['installment_count'] ?? 1));
            $installmentIntervalDays = max(1, (int) ($payload['installment_interval_days'] ?? 30));
            $firstDueDate = $this->normalizeDate($payload['first_due_date'] ?? date('Y-m-d', strtotime('+30 days')));
            $firstDueDate = $firstDueDate ?: date('Y-m-d', strtotime('+30 days'));
            $scheduleRows = $this->buildInstallmentSchedule($settingsID, $balanceDue, $installmentCount, $firstDueDate, $installmentIntervalDays);
        }

        $saleNo = $this->nextSaleNumber($settingsID, $transactionDate);
        $timestamp = date('Y-m-d H:i:s');
        $timeNow = date('H:i:s');
        $status = $this->resolveSaleStatus($grandTotal, $amountPaid, $balanceDue);

        $saleData = [
            'settingsID' => $settingsID,
            'sale_no' => $saleNo,
            'transaction_date' => $transactionDate,
            'transaction_time' => $timeNow,
            'terminal_no' => $terminalNo,
            'customer_id' => $customer['customer_id'],
            'customer_name' => $customer['customer_name'],
            'customer_address' => $customer['customer_address'],
            'customer_tin' => $customer['customer_tin'],
            'customer_discount_id' => $customer['customer_discount_id'],
            'customer_discount_type' => $discountType,
            'cashier_name' => $cashierName !== '' ? $cashierName : 'POS User',
            'payment_term' => $paymentTerm,
            'payment_mode' => $paymentMode,
            'subtotal' => $computed['totals']['subtotal'],
            'discount_amount' => $computed['totals']['discount_amount'],
            'vatable_sales' => $computed['totals']['vatable_sales'],
            'vat_exempt_sales' => $computed['totals']['vat_exempt_sales'],
            'zero_rated_sales' => $computed['totals']['zero_rated_sales'],
            'vat_amount' => $computed['totals']['vat_amount'],
            'grand_total' => $grandTotal,
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'change_amount' => $changeAmount,
            'installment_count' => $installmentCount,
            'installment_interval_days' => $installmentIntervalDays,
            'first_due_date' => $firstDueDate,
            'notes' => $notes,
            'discount_label' => $computed['discount_label'],
            'status' => $status,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        $this->db->trans_begin();
        $this->db->insert($this->salesTable, $saleData);
        $saleId = (int) $this->db->insert_id();

        foreach ($computed['items'] as $item) {
            $this->db->insert($this->saleItemsTable, [
                'sale_id' => $saleId,
                'settingsID' => $settingsID,
                'product_id' => (int) $item['product_id'],
                'sku' => $item['sku'],
                'product_name' => $item['product_name'],
                'category' => $item['category'],
                'unit' => $item['unit'],
                'tax_type' => $item['tax_type'],
                'discount_eligible' => (int) $item['discount_eligible'],
                'quantity' => (int) $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'unit_price' => $item['unit_price'],
                'line_subtotal' => $item['gross'],
                'line_discount' => $item['line_discount'],
                'line_vatable_sales' => $item['line_vatable_sales'],
                'line_vat_exempt_sales' => $item['line_vat_exempt_sales'],
                'line_zero_rated_sales' => $item['line_zero_rated_sales'],
                'line_vat_amount' => $item['line_vat_amount'],
                'line_total' => $item['line_total'],
                'created_at' => $timestamp,
            ]);

            $adjustResult = $this->adjustProductStock(
                $settingsID,
                (int) $item['product_id'],
                -1 * (int) $item['quantity'],
                'sale',
                'Sold via ' . $saleNo,
                $saleData['cashier_name'],
                $saleId
            );

            if (!$adjustResult['success']) {
                $this->db->trans_rollback();
                return ['success' => false, 'error' => $adjustResult['error']];
            }
        }

        if ($amountPaid > 0) {
            $this->db->insert($this->paymentsTable, [
                'sale_id' => $saleId,
                'settingsID' => $settingsID,
                'payment_date' => $transactionDate,
                'payment_time' => $timeNow,
                'amount' => $amountPaid,
                'payment_mode' => $paymentMode,
                'reference_no' => trim((string) ($payload['payment_reference'] ?? '')),
                'received_by' => $saleData['cashier_name'],
                'remarks' => $paymentTerm === 'installment' ? 'Initial installment payment' : 'Initial payment',
                'is_initial' => 1,
                'created_at' => $timestamp,
            ]);
        }

        foreach ($scheduleRows as $row) {
            $this->db->insert($this->installmentTable, [
                'sale_id' => $saleId,
                'settingsID' => $settingsID,
                'installment_no' => (int) $row['installment_no'],
                'due_date' => $row['due_date'],
                'amount_due' => $row['amount_due'],
                'amount_paid' => 0,
                'status' => 'Unpaid',
                'created_at' => $timestamp,
            ]);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'error' => 'Unable to save the POS transaction right now.'];
        }

        $this->db->trans_commit();

        return [
            'success' => true,
            'sale_id' => $saleId,
            'sale_no' => $saleNo,
            'status' => $status,
        ];
    }

    public function recordPayment($settingsID, $saleId, array $payload)
    {
        $settingsID = (int) $settingsID;
        $saleId = (int) $saleId;
        $sale = $this->getSale($settingsID, $saleId);

        if (!$sale) {
            return ['success' => false, 'error' => 'Sale record not found.'];
        }

        if ((string) $sale->status === 'Voided') {
            return ['success' => false, 'error' => 'Voided sales can no longer accept payments.'];
        }

        $amount = $this->normalizeMoney($payload['amount'] ?? 0);
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Enter a payment amount greater than zero.'];
        }

        $currentBalance = round((float) ($sale->balance_due ?? 0), 2);
        if ($amount - $currentBalance > 0.009) {
            return ['success' => false, 'error' => 'Payment is greater than the current balance.'];
        }

        $paymentDate = $this->normalizeDate($payload['payment_date'] ?? date('Y-m-d'));
        $paymentDate = $paymentDate ?: date('Y-m-d');
        $paymentMode = $this->normalizePaymentMode($payload['payment_mode'] ?? 'Cash');
        $referenceNo = trim((string) ($payload['reference_no'] ?? ''));
        $remarks = trim((string) ($payload['remarks'] ?? ''));
        $receivedBy = trim((string) ($payload['received_by'] ?? ''));
        $receivedBy = $receivedBy !== '' ? $receivedBy : trim((string) ($sale->cashier_name ?? 'POS User'));
        $timestamp = date('Y-m-d H:i:s');

        $newAmountPaid = round((float) $sale->amount_paid + $amount, 2);
        $newBalance = round(max(0, (float) $sale->grand_total - $newAmountPaid), 2);
        $newStatus = $this->resolveSaleStatus((float) $sale->grand_total, $newAmountPaid, $newBalance);

        $this->db->trans_begin();

        $this->db->insert($this->paymentsTable, [
            'sale_id' => $saleId,
            'settingsID' => $settingsID,
            'payment_date' => $paymentDate,
            'payment_time' => date('H:i:s'),
            'amount' => $amount,
            'payment_mode' => $paymentMode,
            'reference_no' => $referenceNo,
            'received_by' => $receivedBy,
            'remarks' => $remarks,
            'is_initial' => 0,
            'created_at' => $timestamp,
        ]);

        $this->db
            ->where('settingsID', $settingsID)
            ->where('id', $saleId)
            ->update($this->salesTable, [
                'amount_paid' => $newAmountPaid,
                'balance_due' => $newBalance,
                'payment_mode' => $paymentMode,
                'status' => $newStatus,
                'updated_at' => $timestamp,
            ]);

        if ((int) ($sale->installment_count ?? 0) > 0) {
            $this->applyPaymentToSchedule($settingsID, $saleId, $amount, $timestamp);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'error' => 'Unable to record the payment right now.'];
        }

        $this->db->trans_commit();

        return [
            'success' => true,
            'status' => $newStatus,
            'balance_due' => $newBalance,
        ];
    }

    public function voidSale($settingsID, $saleId, $voidedBy, $reason = '')
    {
        $settingsID = (int) $settingsID;
        $saleId = (int) $saleId;
        $sale = $this->getSale($settingsID, $saleId);

        if (!$sale) {
            return ['success' => false, 'error' => 'Sale record not found.'];
        }

        if ((string) $sale->status === 'Voided') {
            return ['success' => false, 'error' => 'This sale has already been voided.'];
        }

        $items = $this->getSaleItems($settingsID, $saleId);
        $timestamp = date('Y-m-d H:i:s');
        $voidedBy = trim((string) $voidedBy);
        $voidedBy = $voidedBy !== '' ? $voidedBy : 'POS User';
        $reason = trim((string) $reason);

        $this->db->trans_begin();

        foreach ($items as $item) {
            $adjustResult = $this->adjustProductStock(
                $settingsID,
                (int) ($item->product_id ?? 0),
                (int) ($item->quantity ?? 0),
                'void_restore',
                'Stock restored from voided sale ' . (string) $sale->sale_no,
                $voidedBy,
                $saleId
            );

            if (!$adjustResult['success']) {
                $this->db->trans_rollback();
                return ['success' => false, 'error' => $adjustResult['error']];
            }
        }

        $this->db
            ->where('settingsID', $settingsID)
            ->where('id', $saleId)
            ->update($this->salesTable, [
                'status' => 'Voided',
                'balance_due' => 0,
                'void_reason' => $reason,
                'voided_by' => $voidedBy,
                'voided_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        $this->db
            ->where('settingsID', $settingsID)
            ->where('sale_id', $saleId)
            ->update($this->installmentTable, [
                'status' => 'Voided',
                'paid_at' => $timestamp,
            ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'error' => 'Unable to void the sale right now.'];
        }

        $this->db->trans_commit();

        return ['success' => true];
    }

    public function syncProductOpeningStock($settingsID, $productId, $stockQty, $actorName = '')
    {
        $stockQty = (int) $stockQty;
        if ($stockQty === 0) {
            return;
        }

        $existing = $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('product_id', (int) $productId)
            ->where('movement_type', 'opening')
            ->get($this->movementsTable, 1)
            ->row();

        if ($existing) {
            return;
        }

        $this->recordInventoryMovement($settingsID, $productId, 'opening', $stockQty, 0, $stockQty, 'Initial stock balance', $actorName);
    }

    public function syncProductAdjustment($settingsID, $productId, $oldQty, $newQty, $actorName = '')
    {
        $oldQty = (int) $oldQty;
        $newQty = (int) $newQty;

        if ($oldQty === $newQty) {
            return;
        }

        $difference = $newQty - $oldQty;
        $directionLabel = $difference > 0 ? 'Stock increased from product maintenance' : 'Stock reduced from product maintenance';
        $this->recordInventoryMovement($settingsID, $productId, 'adjustment', $difference, $oldQty, $newQty, $directionLabel, $actorName);
    }

    public function adjustProductStock($settingsID, $productId, $qtyChange, $movementType, $remarks = '', $actorName = '', $saleId = null)
    {
        $settingsID = (int) $settingsID;
        $productId = (int) $productId;
        $qtyChange = (int) $qtyChange;

        if ($productId <= 0) {
            return ['success' => false, 'error' => 'Inventory adjustment is missing a product reference.'];
        }

        if ($qtyChange === 0) {
            return ['success' => true];
        }

        $product = $this->db
            ->where('settingsID', $settingsID)
            ->where('id', $productId)
            ->get($this->productsTable, 1)
            ->row();

        if (!$product) {
            return ['success' => false, 'error' => 'The selected product no longer exists in inventory.'];
        }

        $beforeQty = (int) ($product->stock_qty ?? 0);
        $afterQty = $beforeQty + $qtyChange;

        if ($afterQty < 0) {
            return ['success' => false, 'error' => 'Not enough stock is available for ' . trim((string) ($product->name ?? 'the selected item')) . '.'];
        }

        $this->db
            ->where('settingsID', $settingsID)
            ->where('id', $productId)
            ->update($this->productsTable, [
                'stock_qty' => $afterQty,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->recordInventoryMovement($settingsID, $productId, $movementType, $qtyChange, $beforeQty, $afterQty, $remarks, $actorName, $saleId);

        return [
            'success' => true,
            'before' => $beforeQty,
            'after' => $afterQty,
        ];
    }

    public function recentInventoryMovements($settingsID, $dateFrom = null, $dateTo = null, $limit = 50)
    {
        $settingsID = (int) $settingsID;
        $dateFrom = $this->normalizeDate($dateFrom);
        $dateTo = $this->normalizeDate($dateTo);

        $this->db
            ->select('m.*, p.sku, p.name')
            ->from($this->movementsTable . ' m')
            ->join($this->productsTable . ' p', 'p.id = m.product_id AND p.settingsID = m.settingsID', 'left')
            ->where('m.settingsID', $settingsID);

        if ($dateFrom !== null) {
            $this->db->where('DATE(m.created_at) >=', $dateFrom, false);
        }

        if ($dateTo !== null) {
            $this->db->where('DATE(m.created_at) <=', $dateTo, false);
        }

        return $this->db
            ->order_by('m.id', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }

    public function reportBundle($settingsID, $dateFrom = null, $dateTo = null)
    {
        $settingsID = (int) $settingsID;
        $dateFrom = $this->normalizeDate($dateFrom) ?: date('Y-m-01');
        $dateTo = $this->normalizeDate($dateTo) ?: date('Y-m-d');

        $sales = $this->getSales($settingsID, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'include_voided' => false,
        ]);

        $payments = $this->db
            ->select('p.*, s.sale_no, s.customer_name, s.payment_term')
            ->from($this->paymentsTable . ' p')
            ->join($this->salesTable . ' s', 's.id = p.sale_id AND s.settingsID = p.settingsID', 'inner')
            ->where('p.settingsID', $settingsID)
            ->where('s.status !=', 'Voided')
            ->where('p.payment_date >=', $dateFrom)
            ->where('p.payment_date <=', $dateTo)
            ->order_by('p.payment_date', 'ASC')
            ->order_by('p.id', 'ASC')
            ->get()
            ->result();

        $productRows = $this->db
            ->select('i.*, s.transaction_date, s.customer_name, s.cashier_name')
            ->from($this->saleItemsTable . ' i')
            ->join($this->salesTable . ' s', 's.id = i.sale_id AND s.settingsID = i.settingsID', 'inner')
            ->where('i.settingsID', $settingsID)
            ->where('s.status !=', 'Voided')
            ->where('s.transaction_date >=', $dateFrom)
            ->where('s.transaction_date <=', $dateTo)
            ->order_by('i.id', 'ASC')
            ->get()
            ->result();

        $installmentsDue = $this->db
            ->select('sch.*, s.sale_no, s.customer_name, s.balance_due')
            ->from($this->installmentTable . ' sch')
            ->join($this->salesTable . ' s', 's.id = sch.sale_id AND s.settingsID = sch.settingsID', 'inner')
            ->where('sch.settingsID', $settingsID)
            ->where('s.status !=', 'Voided')
            ->where('sch.status !=', 'Paid')
            ->where('sch.status !=', 'Voided')
            ->where('sch.due_date <=', $dateTo)
            ->order_by('sch.due_date', 'ASC')
            ->get()
            ->result();

        $summary = [
            'gross_sales' => 0.0,
            'discount_amount' => 0.0,
            'vat_amount' => 0.0,
            'collections' => 0.0,
            'sales_count' => count($sales),
            'installment_count' => 0,
            'outstanding_balance' => 0.0,
        ];

        $dailyMap = $this->initializeDateMap($dateFrom, $dateTo, [
            'sales' => 0.0,
            'collections' => 0.0,
            'transactions' => 0,
        ]);
        $cashierMap = [];
        $paymentModeMap = [];
        $productMap = [];

        foreach ($sales as $sale) {
            $gross = round((float) ($sale->grand_total ?? 0), 2);
            $summary['gross_sales'] += $gross;
            $summary['discount_amount'] += round((float) ($sale->discount_amount ?? 0), 2);
            $summary['vat_amount'] += round((float) ($sale->vat_amount ?? 0), 2);
            $summary['outstanding_balance'] += round((float) ($sale->balance_due ?? 0), 2);

            if ((string) ($sale->payment_term ?? '') === 'installment') {
                $summary['installment_count']++;
            }

            $dayKey = (string) ($sale->transaction_date ?? '');
            if (isset($dailyMap[$dayKey])) {
                $dailyMap[$dayKey]['sales'] += $gross;
                $dailyMap[$dayKey]['transactions']++;
            }

            $cashierKey = trim((string) ($sale->cashier_name ?? 'Unassigned'));
            if (!isset($cashierMap[$cashierKey])) {
                $cashierMap[$cashierKey] = [
                    'cashier_name' => $cashierKey,
                    'transactions' => 0,
                    'gross_sales' => 0.0,
                    'outstanding_balance' => 0.0,
                ];
            }

            $cashierMap[$cashierKey]['transactions']++;
            $cashierMap[$cashierKey]['gross_sales'] += $gross;
            $cashierMap[$cashierKey]['outstanding_balance'] += round((float) ($sale->balance_due ?? 0), 2);
        }

        foreach ($payments as $payment) {
            $amount = round((float) ($payment->amount ?? 0), 2);
            $summary['collections'] += $amount;

            $dayKey = (string) ($payment->payment_date ?? '');
            if (isset($dailyMap[$dayKey])) {
                $dailyMap[$dayKey]['collections'] += $amount;
            }

            $paymentModeKey = trim((string) ($payment->payment_mode ?? 'Unspecified'));
            if (!isset($paymentModeMap[$paymentModeKey])) {
                $paymentModeMap[$paymentModeKey] = [
                    'payment_mode' => $paymentModeKey,
                    'count' => 0,
                    'amount' => 0.0,
                ];
            }

            $paymentModeMap[$paymentModeKey]['count']++;
            $paymentModeMap[$paymentModeKey]['amount'] += $amount;
        }

        foreach ($productRows as $row) {
            $productKey = trim((string) ($row->product_name ?? 'Unknown Product'));
            if (!isset($productMap[$productKey])) {
                $productMap[$productKey] = [
                    'product_name' => $productKey,
                    'sku' => trim((string) ($row->sku ?? '')),
                    'quantity' => 0,
                    'revenue' => 0.0,
                ];
            }

            $productMap[$productKey]['quantity'] += (int) ($row->quantity ?? 0);
            $productMap[$productKey]['revenue'] += round((float) ($row->line_total ?? 0), 2);
        }

        foreach ($dailyMap as $key => $value) {
            $dailyMap[$key]['sales'] = round((float) $value['sales'], 2);
            $dailyMap[$key]['collections'] = round((float) $value['collections'], 2);
        }

        foreach ($cashierMap as $key => $value) {
            $cashierMap[$key]['gross_sales'] = round((float) $value['gross_sales'], 2);
            $cashierMap[$key]['outstanding_balance'] = round((float) $value['outstanding_balance'], 2);
        }

        foreach ($paymentModeMap as $key => $value) {
            $paymentModeMap[$key]['amount'] = round((float) $value['amount'], 2);
        }

        foreach ($productMap as $key => $value) {
            $productMap[$key]['revenue'] = round((float) $value['revenue'], 2);
        }

        uasort($cashierMap, function ($left, $right) {
            if ($left['gross_sales'] === $right['gross_sales']) {
                return strcmp($left['cashier_name'], $right['cashier_name']);
            }
            return ($left['gross_sales'] < $right['gross_sales']) ? 1 : -1;
        });

        uasort($paymentModeMap, function ($left, $right) {
            if ($left['amount'] === $right['amount']) {
                return strcmp($left['payment_mode'], $right['payment_mode']);
            }
            return ($left['amount'] < $right['amount']) ? 1 : -1;
        });

        uasort($productMap, function ($left, $right) {
            if ($left['revenue'] === $right['revenue']) {
                return strcmp($left['product_name'], $right['product_name']);
            }
            return ($left['revenue'] < $right['revenue']) ? 1 : -1;
        });

        $summary['gross_sales'] = round($summary['gross_sales'], 2);
        $summary['discount_amount'] = round($summary['discount_amount'], 2);
        $summary['vat_amount'] = round($summary['vat_amount'], 2);
        $summary['collections'] = round($summary['collections'], 2);
        $summary['outstanding_balance'] = round($summary['outstanding_balance'], 2);

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => $summary,
            'sales' => $sales,
            'payments' => $payments,
            'daily' => array_values($dailyMap),
            'cashier' => array_values($cashierMap),
            'payment_modes' => array_values($paymentModeMap),
            'top_products' => array_values(array_slice($productMap, 0, 15)),
            'installments_due' => $installmentsDue,
            'inventory_movements' => $this->recentInventoryMovements($settingsID, $dateFrom, $dateTo, 25),
            'low_stock' => $this->db
                ->where('settingsID', $settingsID)
                ->where('status', 'active')
                ->where('stock_qty <= reorder_level', null, false)
                ->order_by('stock_qty', 'ASC')
                ->get($this->productsTable)
                ->result(),
        ];
    }

    private function prepareSaleItems($settingsID, array $payload)
    {
        $productIds = array_values((array) ($payload['product_id'] ?? []));
        $quantities = array_values((array) ($payload['quantity'] ?? []));
        $prices = array_values((array) ($payload['unit_price'] ?? []));

        $normalizedIds = [];
        foreach ($productIds as $rawId) {
            $productId = (int) $rawId;
            if ($productId > 0) {
                $normalizedIds[$productId] = $productId;
            }
        }

        if (empty($normalizedIds)) {
            return ['error' => 'Choose at least one product for the sale.'];
        }

        $products = $this->db
            ->where('settingsID', (int) $settingsID)
            ->where_in('id', array_values($normalizedIds))
            ->get($this->productsTable)
            ->result();

        $productsById = [];
        foreach ($products as $product) {
            $productsById[(int) $product->id] = $product;
        }

        $items = [];
        foreach ($productIds as $index => $rawId) {
            $productId = (int) $rawId;
            $quantity = isset($quantities[$index]) ? (int) $quantities[$index] : 0;

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            if (!isset($productsById[$productId])) {
                return ['error' => 'One of the selected products could not be found.'];
            }

            $product = $productsById[$productId];
            $availableStock = (int) ($product->stock_qty ?? 0);

            if ($availableStock < $quantity) {
                return ['error' => trim((string) $product->name) . ' only has ' . $availableStock . ' item(s) left in stock.'];
            }

            $unitPrice = $this->normalizeMoney($prices[$index] ?? $product->unit_price ?? 0);

            $items[] = [
                'product_id' => $productId,
                'sku' => trim((string) ($product->sku ?? '')),
                'product_name' => trim((string) ($product->name ?? 'Product')),
                'category' => trim((string) ($product->category ?? '')),
                'unit' => trim((string) ($product->unit ?? 'pcs')),
                'tax_type' => $this->normalizeTaxType($product->tax_type ?? 'vatable'),
                'discount_eligible' => (int) ($product->discount_eligible ?? 1),
                'quantity' => $quantity,
                'unit_cost' => $this->normalizeMoney($product->unit_cost ?? 0),
                'unit_price' => $unitPrice,
            ];
        }

        if (empty($items)) {
            return ['error' => 'Add at least one item with a valid quantity.'];
        }

        return ['items' => $items];
    }

    private function resolveCustomerDetails($settingsID, array $payload, $discountType)
    {
        $customerId = trim((string) ($payload['customer_id'] ?? ''));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $customerAddress = trim((string) ($payload['customer_address'] ?? ''));
        $customerTin = trim((string) ($payload['customer_tin'] ?? ''));
        $customerDiscountId = trim((string) ($payload['customer_discount_id'] ?? ''));

        if ($customerId !== '') {
            $client = $this->db
                ->where('settingsID', (int) $settingsID)
                ->where('CustID', $customerId)
                ->get('customers', 1)
                ->row();

            if ($client) {
                if ($customerName === '') {
                    $customerName = trim((string) ($client->Customer ?? ''));
                }

                if ($customerAddress === '') {
                    $customerAddress = trim((string) ($client->Address ?? ''));
                }
            }
        }

        if ($customerName === '') {
            $customerName = 'Walk-in Customer';
        }

        if (in_array($discountType, ['senior', 'pwd'], true) && $customerDiscountId === '') {
            return ['error' => 'Enter the Senior Citizen or PWD ID/reference before finalizing the sale.'];
        }

        return [
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'customer_address' => $customerAddress,
            'customer_tin' => $customerTin,
            'customer_discount_id' => $customerDiscountId,
        ];
    }

    private function calculateSaleTotals($settingsID, array $items, array $options)
    {
        $businessTaxProfile = $this->getBusinessTaxProfile($settingsID);
        $discountType = $this->normalizeDiscountType($options['discount_type'] ?? 'none');
        $discountRate = $this->normalizeMoney($options['discount_rate'] ?? 0);
        $discountValue = $this->normalizeMoney($options['discount_value'] ?? 0);

        $preparedItems = [];
        $subtotal = 0.0;
        $discountableBase = 0.0;

        foreach ($items as $item) {
            $gross = round((float) $item['quantity'] * (float) $item['unit_price'], 2);
            $item['gross'] = $gross;
            $preparedItems[] = $item;
            $subtotal += $gross;

            if ((int) $item['discount_eligible'] === 1) {
                $discountableBase += $gross;
            }
        }

        $allocatedDiscounts = array_fill(0, count($preparedItems), 0.0);
        if ($discountType === 'regular_percent' && $discountableBase > 0) {
            $regularDiscount = min($discountableBase, round($discountableBase * max(0, min(100, $discountRate)) / 100, 2));
            $allocatedDiscounts = $this->allocateDiscountAcrossItems($preparedItems, $regularDiscount);
        } elseif ($discountType === 'regular_amount' && $discountableBase > 0) {
            $regularDiscount = min($discountableBase, $discountValue);
            $allocatedDiscounts = $this->allocateDiscountAcrossItems($preparedItems, $regularDiscount);
        }

        $totals = [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => 0.0,
            'vatable_sales' => 0.0,
            'vat_exempt_sales' => 0.0,
            'zero_rated_sales' => 0.0,
            'vat_amount' => 0.0,
            'grand_total' => 0.0,
        ];

        foreach ($preparedItems as $index => $item) {
            $gross = round((float) $item['gross'], 2);
            $taxType = $this->normalizeTaxType($item['tax_type'] ?? 'vatable');

            if ($businessTaxProfile !== 'vat' && $taxType === 'vatable') {
                $taxType = 'vat_exempt';
            }

            $lineDiscount = round((float) ($allocatedDiscounts[$index] ?? 0), 2);
            $lineVatableSales = 0.0;
            $lineVatExemptSales = 0.0;
            $lineZeroRatedSales = 0.0;
            $lineVatAmount = 0.0;
            $lineTotal = 0.0;

            if (in_array($discountType, ['senior', 'pwd'], true) && (int) $item['discount_eligible'] === 1) {
                if ($businessTaxProfile === 'vat' && $taxType === 'vatable') {
                    $vatlessAmount = round($gross / 1.12, 2);
                    $lineDiscount = round($vatlessAmount * 0.20, 2);
                    $lineVatExemptSales = $vatlessAmount;
                    $lineTotal = round($vatlessAmount - $lineDiscount, 2);
                } else {
                    $lineDiscount = round($gross * 0.20, 2);
                    $lineTotal = round($gross - $lineDiscount, 2);
                    if ($taxType === 'zero_rated') {
                        $lineZeroRatedSales = $lineTotal;
                    } else {
                        $lineVatExemptSales = $lineTotal;
                    }
                }
            } else {
                $discountedGross = round(max(0, $gross - $lineDiscount), 2);
                if ($taxType === 'vatable') {
                    $lineVatableSales = round($discountedGross / 1.12, 2);
                    $lineVatAmount = round($discountedGross - $lineVatableSales, 2);
                    $lineTotal = $discountedGross;
                } elseif ($taxType === 'zero_rated') {
                    $lineZeroRatedSales = $discountedGross;
                    $lineTotal = $discountedGross;
                } else {
                    $lineVatExemptSales = $discountedGross;
                    $lineTotal = $discountedGross;
                }
            }

            $preparedItems[$index]['line_discount'] = round($lineDiscount, 2);
            $preparedItems[$index]['line_vatable_sales'] = round($lineVatableSales, 2);
            $preparedItems[$index]['line_vat_exempt_sales'] = round($lineVatExemptSales, 2);
            $preparedItems[$index]['line_zero_rated_sales'] = round($lineZeroRatedSales, 2);
            $preparedItems[$index]['line_vat_amount'] = round($lineVatAmount, 2);
            $preparedItems[$index]['line_total'] = round($lineTotal, 2);

            $totals['discount_amount'] += round($lineDiscount, 2);
            $totals['vatable_sales'] += round($lineVatableSales, 2);
            $totals['vat_exempt_sales'] += round($lineVatExemptSales, 2);
            $totals['zero_rated_sales'] += round($lineZeroRatedSales, 2);
            $totals['vat_amount'] += round($lineVatAmount, 2);
            $totals['grand_total'] += round($lineTotal, 2);
        }

        foreach ($totals as $key => $value) {
            $totals[$key] = round((float) $value, 2);
        }

        return [
            'items' => $preparedItems,
            'totals' => $totals,
            'discount_label' => $this->buildDiscountLabel($discountType, $discountRate, $discountValue),
        ];
    }

    private function allocateDiscountAcrossItems(array $items, $discountAmount)
    {
        $discountAmount = round((float) $discountAmount, 2);
        $allocations = array_fill(0, count($items), 0.0);

        if ($discountAmount <= 0) {
            return $allocations;
        }

        $eligibleTotal = 0.0;
        foreach ($items as $item) {
            if ((int) ($item['discount_eligible'] ?? 1) === 1) {
                $eligibleTotal += round((float) ($item['gross'] ?? 0), 2);
            }
        }

        if ($eligibleTotal <= 0) {
            return $allocations;
        }

        $remaining = $discountAmount;
        $lastEligibleIndex = null;
        foreach ($items as $index => $item) {
            if ((int) ($item['discount_eligible'] ?? 1) === 1) {
                $lastEligibleIndex = $index;
            }
        }

        foreach ($items as $index => $item) {
            if ((int) ($item['discount_eligible'] ?? 1) !== 1) {
                continue;
            }

            if ($index === $lastEligibleIndex) {
                $allocations[$index] = round($remaining, 2);
                break;
            }

            $share = round(((float) ($item['gross'] ?? 0) / $eligibleTotal) * $discountAmount, 2);
            $allocations[$index] = $share;
            $remaining = round($remaining - $share, 2);
        }

        return $allocations;
    }

    private function buildInstallmentSchedule($settingsID, $balanceDue, $installmentCount, $firstDueDate, $intervalDays)
    {
        $settingsID = (int) $settingsID;
        $balanceDue = round((float) $balanceDue, 2);
        $installmentCount = max(1, (int) $installmentCount);
        $intervalDays = max(1, (int) $intervalDays);
        $schedule = [];

        $remaining = $balanceDue;
        for ($index = 1; $index <= $installmentCount; $index++) {
            $dueDate = date('Y-m-d', strtotime($firstDueDate . ' +' . (($index - 1) * $intervalDays) . ' days'));
            $amountDue = $index === $installmentCount
                ? round($remaining, 2)
                : round($balanceDue / $installmentCount, 2);

            $schedule[] = [
                'settingsID' => $settingsID,
                'installment_no' => $index,
                'due_date' => $dueDate,
                'amount_due' => $amountDue,
            ];

            $remaining = round($remaining - $amountDue, 2);
        }

        return $schedule;
    }

    private function applyPaymentToSchedule($settingsID, $saleId, $paymentAmount, $timestamp)
    {
        $remaining = round((float) $paymentAmount, 2);
        if ($remaining <= 0) {
            return;
        }

        $schedules = $this->db
            ->where('settingsID', (int) $settingsID)
            ->where('sale_id', (int) $saleId)
            ->where('status !=', 'Paid')
            ->where('status !=', 'Voided')
            ->order_by('installment_no', 'ASC')
            ->get($this->installmentTable)
            ->result();

        foreach ($schedules as $schedule) {
            if ($remaining <= 0) {
                break;
            }

            $amountDue = round((float) ($schedule->amount_due ?? 0), 2);
            $amountPaid = round((float) ($schedule->amount_paid ?? 0), 2);
            $balance = round(max(0, $amountDue - $amountPaid), 2);
            if ($balance <= 0) {
                continue;
            }

            $applied = min($remaining, $balance);
            $newAmountPaid = round($amountPaid + $applied, 2);
            $newStatus = $newAmountPaid + 0.009 >= $amountDue ? 'Paid' : 'Partially Paid';

            $this->db
                ->where('settingsID', (int) $settingsID)
                ->where('id', (int) $schedule->id)
                ->update($this->installmentTable, [
                    'amount_paid' => $newAmountPaid,
                    'status' => $newStatus,
                    'paid_at' => $timestamp,
                ]);

            $remaining = round($remaining - $applied, 2);
        }
    }

    private function recordInventoryMovement($settingsID, $productId, $movementType, $qtyChange, $beforeQty, $afterQty, $remarks = '', $actorName = '', $saleId = null)
    {
        $this->db->insert($this->movementsTable, [
            'settingsID' => (int) $settingsID,
            'product_id' => (int) $productId,
            'sale_id' => $saleId !== null ? (int) $saleId : null,
            'movement_type' => trim((string) $movementType),
            'quantity_change' => (int) $qtyChange,
            'qty_before' => (int) $beforeQty,
            'qty_after' => (int) $afterQty,
            'remarks' => trim((string) $remarks),
            'created_by' => trim((string) $actorName),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function resolveSaleStatus($grandTotal, $amountPaid, $balanceDue)
    {
        $grandTotal = round((float) $grandTotal, 2);
        $amountPaid = round((float) $amountPaid, 2);
        $balanceDue = round((float) $balanceDue, 2);

        if ($balanceDue <= 0.009) {
            return 'Paid';
        }

        if ($amountPaid > 0.009 && $amountPaid < $grandTotal) {
            return 'Partially Paid';
        }

        return 'Unpaid';
    }

    private function initializeDateMap($dateFrom, $dateTo, array $defaults = [])
    {
        $map = [];
        $cursor = strtotime($dateFrom);
        $end = strtotime($dateTo);

        while ($cursor !== false && $cursor <= $end) {
            $key = date('Y-m-d', $cursor);
            $map[$key] = array_merge(['date' => $key], $defaults);
            $cursor = strtotime('+1 day', $cursor);
        }

        return $map;
    }

    private function buildDiscountLabel($discountType, $discountRate, $discountValue)
    {
        switch ($discountType) {
            case 'regular_percent':
                return 'Regular Discount (' . number_format((float) $discountRate, 2) . '%)';
            case 'regular_amount':
                return 'Regular Discount (PHP ' . number_format((float) $discountValue, 2) . ')';
            case 'senior':
                return 'Senior Citizen Discount';
            case 'pwd':
                return 'PWD Discount';
            default:
                return 'No Discount';
        }
    }

    private function normalizePaymentTerm($value)
    {
        $value = strtolower(trim((string) $value));
        return $value === 'installment' ? 'installment' : 'full';
    }

    private function normalizePaymentMode($value)
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : 'Cash';
    }

    private function normalizeDiscountType($value)
    {
        $value = strtolower(trim((string) $value));

        if (in_array($value, ['regular_percent', 'regular_amount', 'senior', 'pwd'], true)) {
            return $value;
        }

        return 'none';
    }

    private function normalizeTaxType($value)
    {
        $value = strtolower(trim((string) $value));

        if (in_array($value, ['vat_exempt', 'zero_rated'], true)) {
            return $value;
        }

        return 'vatable';
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function normalizeMoney($value)
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $value = str_replace(',', '', (string) $value);
        if (!is_numeric($value)) {
            return 0.0;
        }

        return round(max(0, (float) $value), 2);
    }

    private function nextSaleNumber($settingsID, $transactionDate)
    {
        $dateKey = date('Ymd', strtotime($transactionDate ?: date('Y-m-d')));
        $prefix = 'POS-' . $dateKey . '-';
        $numbers = [];

        $rows = $this->db
            ->select('sale_no')
            ->where('settingsID', (int) $settingsID)
            ->like('sale_no', $prefix, 'after')
            ->get($this->salesTable)
            ->result();

        foreach ($rows as $row) {
            $saleNo = (string) ($row->sale_no ?? '');
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $saleNo, $matches)) {
                $numbers[] = (int) $matches[1];
            }
        }

        sort($numbers, SORT_NUMERIC);
        $next = 1;
        foreach ($numbers as $number) {
            if ($number === $next) {
                $next++;
                continue;
            }

            if ($number > $next) {
                break;
            }
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function ensureProductsTable()
    {
        if (!$this->db->table_exists($this->productsTable)) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `{$this->productsTable}` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `settingsID` int unsigned NOT NULL DEFAULT 0,
                    `sku` varchar(50) NOT NULL,
                    `barcode` varchar(60) DEFAULT NULL,
                    `name` varchar(150) NOT NULL,
                    `category` varchar(80) DEFAULT NULL,
                    `unit` varchar(20) DEFAULT 'pcs',
                    `unit_cost` double NOT NULL DEFAULT 0,
                    `unit_price` double NOT NULL DEFAULT 0,
                    `stock_qty` int NOT NULL DEFAULT 0,
                    `reorder_level` int NOT NULL DEFAULT 5,
                    `tax_type` varchar(20) NOT NULL DEFAULT 'vatable',
                    `discount_eligible` tinyint(1) NOT NULL DEFAULT 1,
                    `expiry_date` date DEFAULT NULL,
                    `status` varchar(20) NOT NULL DEFAULT 'active',
                    `created_at` datetime DEFAULT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_pos_products_settings` (`settingsID`),
                    KEY `idx_pos_products_sku` (`sku`),
                    KEY `idx_pos_products_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
            ");
            return;
        }

        $fieldMap = [
            'barcode' => "ALTER TABLE `{$this->productsTable}` ADD COLUMN `barcode` varchar(60) DEFAULT NULL AFTER `sku`",
            'unit' => "ALTER TABLE `{$this->productsTable}` ADD COLUMN `unit` varchar(20) DEFAULT 'pcs' AFTER `category`",
            'reorder_level' => "ALTER TABLE `{$this->productsTable}` ADD COLUMN `reorder_level` int NOT NULL DEFAULT 5 AFTER `stock_qty`",
            'tax_type' => "ALTER TABLE `{$this->productsTable}` ADD COLUMN `tax_type` varchar(20) NOT NULL DEFAULT 'vatable' AFTER `reorder_level`",
            'discount_eligible' => "ALTER TABLE `{$this->productsTable}` ADD COLUMN `discount_eligible` tinyint(1) NOT NULL DEFAULT 1 AFTER `tax_type`",
            'created_at' => "ALTER TABLE `{$this->productsTable}` ADD COLUMN `created_at` datetime DEFAULT NULL AFTER `status`",
            'updated_at' => "ALTER TABLE `{$this->productsTable}` ADD COLUMN `updated_at` datetime DEFAULT NULL AFTER `created_at`",
        ];

        foreach ($fieldMap as $field => $sql) {
            if (!$this->db->field_exists($field, $this->productsTable)) {
                $this->db->query($sql);
            }
        }
    }

    private function ensureSalesTable()
    {
        if ($this->db->table_exists($this->salesTable)) {
            return;
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `{$this->salesTable}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `settingsID` int unsigned NOT NULL,
                `sale_no` varchar(50) NOT NULL,
                `transaction_date` date NOT NULL,
                `transaction_time` time DEFAULT NULL,
                `terminal_no` varchar(50) DEFAULT NULL,
                `customer_id` varchar(50) DEFAULT NULL,
                `customer_name` varchar(150) NOT NULL DEFAULT 'Walk-in Customer',
                `customer_address` varchar(255) DEFAULT NULL,
                `customer_tin` varchar(50) DEFAULT NULL,
                `customer_discount_id` varchar(80) DEFAULT NULL,
                `customer_discount_type` varchar(20) NOT NULL DEFAULT 'none',
                `cashier_name` varchar(120) NOT NULL,
                `payment_term` varchar(20) NOT NULL DEFAULT 'full',
                `payment_mode` varchar(40) DEFAULT NULL,
                `subtotal` double NOT NULL DEFAULT 0,
                `discount_amount` double NOT NULL DEFAULT 0,
                `vatable_sales` double NOT NULL DEFAULT 0,
                `vat_exempt_sales` double NOT NULL DEFAULT 0,
                `zero_rated_sales` double NOT NULL DEFAULT 0,
                `vat_amount` double NOT NULL DEFAULT 0,
                `grand_total` double NOT NULL DEFAULT 0,
                `amount_paid` double NOT NULL DEFAULT 0,
                `balance_due` double NOT NULL DEFAULT 0,
                `change_amount` double NOT NULL DEFAULT 0,
                `installment_count` int unsigned NOT NULL DEFAULT 0,
                `installment_interval_days` int unsigned NOT NULL DEFAULT 0,
                `first_due_date` date DEFAULT NULL,
                `notes` text,
                `discount_label` varchar(100) DEFAULT NULL,
                `status` varchar(30) NOT NULL DEFAULT 'Unpaid',
                `void_reason` varchar(255) DEFAULT NULL,
                `voided_by` varchar(120) DEFAULT NULL,
                `voided_at` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_pos_sales_settings_date` (`settingsID`, `transaction_date`),
                KEY `idx_pos_sales_status` (`status`),
                KEY `idx_pos_sales_sale_no` (`sale_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");
    }

    private function ensureSaleItemsTable()
    {
        if ($this->db->table_exists($this->saleItemsTable)) {
            return;
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `{$this->saleItemsTable}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `sale_id` int unsigned NOT NULL,
                `settingsID` int unsigned NOT NULL,
                `product_id` int unsigned DEFAULT NULL,
                `sku` varchar(50) DEFAULT NULL,
                `product_name` varchar(150) NOT NULL,
                `category` varchar(80) DEFAULT NULL,
                `unit` varchar(20) DEFAULT NULL,
                `tax_type` varchar(20) NOT NULL DEFAULT 'vatable',
                `discount_eligible` tinyint(1) NOT NULL DEFAULT 1,
                `quantity` int unsigned NOT NULL DEFAULT 1,
                `unit_cost` double NOT NULL DEFAULT 0,
                `unit_price` double NOT NULL DEFAULT 0,
                `line_subtotal` double NOT NULL DEFAULT 0,
                `line_discount` double NOT NULL DEFAULT 0,
                `line_vatable_sales` double NOT NULL DEFAULT 0,
                `line_vat_exempt_sales` double NOT NULL DEFAULT 0,
                `line_zero_rated_sales` double NOT NULL DEFAULT 0,
                `line_vat_amount` double NOT NULL DEFAULT 0,
                `line_total` double NOT NULL DEFAULT 0,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_pos_sale_items_sale` (`sale_id`, `settingsID`),
                KEY `idx_pos_sale_items_product` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");
    }

    private function ensurePaymentsTable()
    {
        if ($this->db->table_exists($this->paymentsTable)) {
            return;
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `{$this->paymentsTable}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `sale_id` int unsigned NOT NULL,
                `settingsID` int unsigned NOT NULL,
                `payment_date` date NOT NULL,
                `payment_time` time DEFAULT NULL,
                `amount` double NOT NULL DEFAULT 0,
                `payment_mode` varchar(40) DEFAULT NULL,
                `reference_no` varchar(100) DEFAULT NULL,
                `received_by` varchar(120) DEFAULT NULL,
                `remarks` varchar(255) DEFAULT NULL,
                `is_initial` tinyint(1) NOT NULL DEFAULT 0,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_pos_payments_sale` (`sale_id`, `settingsID`),
                KEY `idx_pos_payments_date` (`payment_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");
    }

    private function ensureInstallmentTable()
    {
        if ($this->db->table_exists($this->installmentTable)) {
            return;
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `{$this->installmentTable}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `sale_id` int unsigned NOT NULL,
                `settingsID` int unsigned NOT NULL,
                `installment_no` int unsigned NOT NULL DEFAULT 1,
                `due_date` date NOT NULL,
                `amount_due` double NOT NULL DEFAULT 0,
                `amount_paid` double NOT NULL DEFAULT 0,
                `status` varchar(20) NOT NULL DEFAULT 'Unpaid',
                `paid_at` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_pos_installments_sale` (`sale_id`, `settingsID`),
                KEY `idx_pos_installments_due` (`due_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");
    }

    private function ensureInventoryMovementsTable()
    {
        if ($this->db->table_exists($this->movementsTable)) {
            return;
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `{$this->movementsTable}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `settingsID` int unsigned NOT NULL,
                `product_id` int unsigned NOT NULL,
                `sale_id` int unsigned DEFAULT NULL,
                `movement_type` varchar(30) NOT NULL,
                `quantity_change` int NOT NULL DEFAULT 0,
                `qty_before` int NOT NULL DEFAULT 0,
                `qty_after` int NOT NULL DEFAULT 0,
                `remarks` varchar(255) DEFAULT NULL,
                `created_by` varchar(120) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_pos_movements_product` (`product_id`, `settingsID`),
                KEY `idx_pos_movements_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");
    }
}
