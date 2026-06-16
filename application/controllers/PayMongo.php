<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PayMongo online-payment gateway.
 *
 * Flow:
 *   1) createCheckout($invoiceId)   - JSON endpoint. Creates a PayMongo
 *      Checkout Session for the invoice, inserts a row into `online_payment`,
 *      and returns the checkout URL plus a QR code image the client scans.
 *   2) status($onlinePaymentId)     - JSON polling endpoint. Returns the
 *      current status of the online_payment row. Also upgrades the row to
 *      'paid' (and writes a `payments` record) if PayMongo now reports the
 *      checkout as paid. This lets the UI detect payment without a webhook.
 *   3) webhook()                    - Publicly accessible. PayMongo posts
 *      payment.paid / checkout_session.payment.paid events here.
 *   4) return_page($onlinePaymentId) - Browser redirect target after PayMongo
 *      success/cancel URL. Just shows a status page.
 */
class PayMongo extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        $this->load->model('CashModel');
        $this->config->load('paymongo', true);
        $this->load->library('Paymongo_client');
        $this->paymongo_client->configure([
            'secret_key' => $this->config->item('paymongo_secret_key', 'paymongo'),
            'api_base' => 'https://api.paymongo.com/v1',
        ]);
        date_default_timezone_set('Asia/Manila');
        $this->_ensureOnlinePaymentTable();
    }

    private function _ensureOnlinePaymentTable()
    {
        if ($this->db->table_exists('online_payment')) {
            return;
        }

        $this->db->query("
            CREATE TABLE `online_payment` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `settingsID` INT(11) NOT NULL,
              `orderID` INT(11) DEFAULT NULL,
              `InvoiceNo` VARCHAR(100) DEFAULT NULL,
              `CustID` VARCHAR(100) DEFAULT NULL,
              `Customer` VARCHAR(255) DEFAULT NULL,
              `provider` VARCHAR(50) NOT NULL DEFAULT 'paymongo',
              `payment_method` VARCHAR(50) DEFAULT 'qrph',
              `amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
              `currency` VARCHAR(10) NOT NULL DEFAULT 'PHP',
              `source_id` VARCHAR(255) DEFAULT NULL,
              `payment_intent_id` VARCHAR(255) DEFAULT NULL,
              `payment_method_id` VARCHAR(255) DEFAULT NULL,
              `checkout_id` VARCHAR(255) DEFAULT NULL,
              `checkout_url` TEXT DEFAULT NULL,
              `qr_code_url` TEXT DEFAULT NULL,
              `qr_code_data` LONGTEXT DEFAULT NULL,
              `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
              `paymongo_payment_id` VARCHAR(255) DEFAULT NULL,
              `paid_at` DATETIME DEFAULT NULL,
              `expires_at` DATETIME DEFAULT NULL,
              `reference_no` VARCHAR(255) DEFAULT NULL,
              `raw_create_response` LONGTEXT DEFAULT NULL,
              `raw_webhook_payload` LONGTEXT DEFAULT NULL,
              `client_ip` VARCHAR(64) DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_online_payment_invoice` (`settingsID`, `InvoiceNo`),
              KEY `idx_online_payment_order` (`settingsID`, `orderID`),
              KEY `idx_online_payment_source` (`source_id`),
              KEY `idx_online_payment_intent` (`payment_intent_id`),
              KEY `idx_online_payment_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    // ---------------------------------------------------------------------
    // ENDPOINTS
    // ---------------------------------------------------------------------

    /**
     * POST PayMongo/createCheckout
     * Body: invoice_id (orderID) OR invoice_no
     * Returns JSON { ok, online_payment_id, qr_code_url, amount }
     * Uses Payment Intent + Payment Method API to get direct QR Ph image
     */
    public function createCheckout()
    {
        $this->_requireLoggedIn();

        $invoiceId = (int) ($this->input->post('invoice_id') ?: $this->input->get('invoice_id'));
        $invoiceNo = trim((string) ($this->input->post('invoice_no') ?: $this->input->get('invoice_no')));
        $settingsID = (int) $this->session->userdata('settingsID');

        $invoice = null;
        if ($invoiceId > 0) {
            $invoice = $this->CashModel->getInvoiceByOrderID($invoiceId, $settingsID);
        }
        if (!$invoice && $invoiceNo !== '') {
            $invoice = $this->CashModel->getInvoiceByInvoiceNo($invoiceNo, $settingsID);
        }
        if (!$invoice) {
            return $this->_json(array('ok' => false, 'error' => 'Invoice not found.'));
        }

        $balance = (float) ($invoice->Balance ?? 0);
        if ($balance <= 0) {
            return $this->_json(array('ok' => false, 'error' => 'Invoice has no outstanding balance.'));
        }

        $secret = trim((string) $this->config->item('paymongo_secret_key', 'paymongo'));
        if ($secret === '' || strpos($secret, 'REPLACE_ME') !== false) {
            return $this->_json(array(
                'ok'    => false,
                'error' => 'PayMongo secret key not configured. Edit application/config/paymongo.php.',
            ));
        }

        $row = array(
            'settingsID'  => $settingsID,
            'orderID'     => (int) $invoice->orderID,
            'InvoiceNo'   => (string) $invoice->InvoiceNo,
            'CustID'      => (string) ($invoice->CustID ?? ''),
            'Customer'    => (string) ($invoice->Customer ?? ''),
            'provider'    => 'paymongo',
            'payment_method' => 'qrph',
            'amount'      => $balance,
            'currency'    => 'PHP',
            'status'      => 'pending',
            'client_ip'   => $this->input->ip_address(),
        );
        $this->db->insert('online_payment', $row);
        $onlinePaymentId = (int) $this->db->insert_id();

        $customerName = trim((string) ($invoice->Customer ?? ''));
        $amountInCents = (int) round($balance * 100);

        // Step 1: Create Payment Intent
        $intentPayload = array(
            'amount' => $amountInCents,
            'currency' => 'PHP',
            'capture_type' => 'automatic',
            'payment_method_allowed' => ['qrph'],
            'statement_descriptor' => substr('INV ' . ($invoice->InvoiceNo ?? $onlinePaymentId), 0, 50),
        );

        $intent = $this->paymongo_client->create_payment_intent($intentPayload);
        if (!$intent['ok']) {
            $this->db->where('id', $onlinePaymentId)->update('online_payment', array(
                'status' => 'failed',
                'raw_create_response' => json_encode($intent),
            ));
            return $this->_json(array('ok' => false, 'error' => 'Payment intent failed: ' . ($intent['error'] ?? 'unknown')));
        }

        $intentData = $intent['body']['data'] ?? array();
        $intentId = trim((string) ($intentData['id'] ?? ''));

        // Step 2: Create Payment Method (qrph)
        $methodPayload = array(
            'type' => 'qrph',
            'billing' => array(
                'email' => 'customer@example.com', // Required by PayMongo
                'name' => $customerName !== '' ? $customerName : 'Customer',
            ),
        );

        $paymentMethod = $this->paymongo_client->create_payment_method($methodPayload);
        if (!$paymentMethod['ok']) {
            $this->db->where('id', $onlinePaymentId)->update('online_payment', array(
                'status' => 'failed',
                'raw_create_response' => json_encode($paymentMethod),
            ));
            return $this->_json(array('ok' => false, 'error' => 'Payment method failed: ' . ($paymentMethod['error'] ?? 'unknown')));
        }

        $methodData = $paymentMethod['body']['data'] ?? array();
        $methodId = trim((string) ($methodData['id'] ?? ''));

        // Step 3: Attach Payment Method to Intent
        $returnUrl = base_url('PayMongo/return_page/' . $onlinePaymentId . '?result=success');
        $attach = $this->paymongo_client->attach_payment_intent($intentId, $methodId, $returnUrl);
        if (!$attach['ok']) {
            $this->db->where('id', $onlinePaymentId)->update('online_payment', array(
                'status' => 'failed',
                'raw_create_response' => json_encode($attach),
            ));
            return $this->_json(array('ok' => false, 'error' => 'Attach failed: ' . ($attach['error'] ?? 'unknown')));
        }

        // Extract QR Ph image URL from next_action.code
        $attachData = $attach['body']['data'] ?? array();
        $attrs = $attachData['attributes'] ?? array();
        $nextAction = $attrs['next_action'] ?? array();
        $code = $nextAction['code'] ?? array();
        $qrImageUrl = trim((string) ($code['image_url'] ?? ''));
        $qrCodeId = trim((string) ($code['id'] ?? ''));

        if ($qrImageUrl === '') {
            return $this->_json(array('ok' => false, 'error' => 'QR image not available from PayMongo.'));
        }

        // Save to database
        $this->db->where('id', $onlinePaymentId)->update('online_payment', array(
            'payment_intent_id'   => $intentId,
            'payment_method_id'   => $methodId,
            'qr_code_url'         => $qrImageUrl,
            'raw_create_response' => json_encode($attach),
        ));

        return $this->_json(array(
            'ok'                 => true,
            'online_payment_id'  => $onlinePaymentId,
            'qr_code_url'        => $qrImageUrl,
            'amount'             => $balance,
            'currency'           => 'PHP',
            'status'             => 'pending',
            'provider_status'    => 'PENDING',
            'payment_method'     => 'qrph',
        ));
    }

    /**
     * GET PayMongo/status/{id}
     * Returns JSON { ok, status, paid_at, amount }
     * Also promotes pending -> paid when PayMongo reports it so the UI can
     * react even without webhook delivery.
     */
    public function status($id = 0)
    {
        $this->_requireLoggedIn();
        $id = (int) $id;
        $settingsID = (int) $this->session->userdata('settingsID');

        $row = $this->db
            ->where('id', $id)
            ->where('settingsID', $settingsID)
            ->get('online_payment')
            ->row();

        if (!$row) {
            return $this->_json(array('ok' => false, 'error' => 'Not found.'));
        }

        if ($row->status !== 'paid' && $row->checkout_id) {
            $refreshed = $this->_refreshCheckoutStatus($row);
            if ($refreshed) {
                $row = $refreshed;
            }
        }

        // Also check source status for QR Ph payments
        if ($row->status !== 'paid' && $row->source_id) {
            $refreshed = $this->_refreshSourceStatus($row);
            if ($refreshed) {
                $row = $refreshed;
            }
        }

        // Check Payment Intent status for new QR Ph flow
        if ($row->status !== 'paid' && $row->payment_intent_id) {
            $refreshed = $this->_refreshPaymentIntentStatus($row);
            if ($refreshed) {
                $row = $refreshed;
            }
        }

        return $this->_json(array(
            'ok'               => true,
            'online_payment_id' => (int) $row->id,
            'status'           => $row->status,
            'provider_status'  => $row->paymongo_payment_id ? 'PAID' : ($row->status === 'paid' ? 'PAID' : 'PENDING'),
            'paid_at'          => $row->paid_at,
            'amount'           => (float) $row->amount,
            'payment_method'   => $row->payment_method,
        ));
    }

    /**
     * POST PayMongo/webhook
     * Public endpoint called by PayMongo. Marks the online_payment as paid
     * and records a row in `payments`.
     */
    public function webhook()
    {
        $rawBody = file_get_contents('php://input');
        $signatureHeader = isset($_SERVER['HTTP_PAYMONGO_SIGNATURE']) ? $_SERVER['HTTP_PAYMONGO_SIGNATURE'] : '';

        $secret = trim((string) $this->config->item('paymongo_webhook_secret', 'paymongo'));
        if ($secret !== '' && !$this->_verifyWebhookSignature($rawBody, $signatureHeader, $secret)) {
            log_message('error', 'PayMongo webhook signature mismatch');
            $this->output->set_status_header(401);
            return;
        }

        $payload = json_decode($rawBody, true);
        if (!$payload) {
            $this->output->set_status_header(400);
            return;
        }

        $event     = $payload['data'] ?? array();
        $eventType = (string) ($event['attributes']['type'] ?? '');
        $inner     = $event['attributes']['data'] ?? array();
        $innerAttr = $inner['attributes'] ?? array();
        $innerId   = (string) ($inner['id'] ?? '');

        $checkoutSessionId = '';
        $paymentIntentId   = '';
        $sourceId          = '';
        $paymongoPaymentId = '';

        if ($eventType === 'checkout_session.payment.paid') {
            $checkoutSessionId = $innerId;
            $payments = $innerAttr['payments'] ?? array();
            if (!empty($payments) && isset($payments[0]['id'])) {
                $paymongoPaymentId = (string) $payments[0]['id'];
            }
        } elseif ($eventType === 'payment.paid' || $eventType === 'payment.failed') {
            $paymongoPaymentId = $innerId;
            $paymentIntentId   = (string) ($innerAttr['payment_intent_id'] ?? '');
            $source            = $innerAttr['source'] ?? array();
            if (is_array($source)) {
                $sourceId = (string) ($source['id'] ?? '');
            }
        } elseif ($eventType === 'payment_intent.succeeded') {
            $paymentIntentId = $innerId;
            // Find the payment_id from the charges
            $charges = $innerAttr['charges'] ?? array();
            if (!empty($charges) && isset($charges[0]['id'])) {
                $paymongoPaymentId = (string) $charges[0]['id'];
            }
        } elseif ($eventType === 'source.chargeable') {
            $sourceId = $innerId;
        }

        $row = null;
        if ($checkoutSessionId !== '') {
            $row = $this->db->where('checkout_id', $checkoutSessionId)->get('online_payment')->row();
        }
        if (!$row && $paymentIntentId !== '') {
            $row = $this->db->where('payment_intent_id', $paymentIntentId)->get('online_payment')->row();
        }
        if (!$row && $sourceId !== '') {
            $row = $this->db->where('source_id', $sourceId)->get('online_payment')->row();
        }
        if (!$row) {
            $metadata = $innerAttr['metadata'] ?? array();
            $metaId = (int) ($metadata['online_payment_id'] ?? 0);
            if ($metaId > 0) {
                $row = $this->db->where('id', $metaId)->get('online_payment')->row();
            }
        }

        if (!$row) {
            log_message('error', 'PayMongo webhook: unable to match event to online_payment row. Type=' . $eventType);
            $this->output->set_status_header(200);
            return;
        }

        $update = array(
            'raw_webhook_payload' => substr($rawBody, 0, 60000),
        );

        if ($eventType === 'checkout_session.payment.paid' || $eventType === 'payment.paid' || $eventType === 'payment_intent.succeeded') {
            $update['status'] = 'paid';
            $update['paid_at'] = date('Y-m-d H:i:s');
            if ($paymongoPaymentId !== '') {
                $update['paymongo_payment_id'] = $paymongoPaymentId;
            }
            log_message('info', 'PayMongo webhook: marking as paid. Event=' . $eventType . ' online_payment_id=' . ($row->id ?? 'unknown'));
        } elseif ($eventType === 'payment.failed' || $eventType === 'payment_intent.payment_failed') {
            $update['status'] = 'failed';
        }

        $this->db->where('id', $row->id)->update('online_payment', $update);

        if (($update['status'] ?? '') === 'paid' && $row->status !== 'paid') {
            $this->_recordLedgerPayment($row->id);
        }

        $this->output->set_status_header(200);
    }

    /**
     * Landing page after PayMongo redirects the client back.
     */
    public function return_page($id = 0)
    {
        $id = (int) $id;
        $row = $this->db->where('id', $id)->get('online_payment')->row();
        $result = $this->input->get('result') === 'cancel' ? 'cancel' : 'success';

        $this->load->view('paymongo_return', array(
            'row'    => $row,
            'result' => $result,
        ));
    }

    // ---------------------------------------------------------------------
    // INTERNALS
    // ---------------------------------------------------------------------

    private function _requireLoggedIn()
    {
        if ($this->session->userdata('logged_in') !== TRUE) {
            $this->output->set_status_header(401);
            echo json_encode(array('ok' => false, 'error' => 'Not logged in.'));
            exit;
        }
    }

    private function _json($data)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    private function _qrImageUrl($url)
    {
        if (!$url) {
            return '';
        }
        // Public QR image for the checkout URL. The PayMongo checkout page
        // itself renders the actual QR Ph code; this image lets the client
        // scan from the staff's screen to open that page on their phone.
        return 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . rawurlencode($url);
    }

    private function _paymongoRequest($method, $url, $body = null)
    {
        $secret = trim((string) $this->config->item('paymongo_secret_key', 'paymongo'));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($secret . ':'),
        ));
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return array('ok' => false, 'error' => $err, 'http_code' => 0, 'body' => null);
        }

        $decoded = json_decode($raw, true);
        $ok = $httpCode >= 200 && $httpCode < 300 && isset($decoded['data']);

        $errorMsg = '';
        if (!$ok && isset($decoded['errors'][0])) {
            $e = $decoded['errors'][0];
            $errorMsg = trim(($e['code'] ?? '') . ' ' . ($e['detail'] ?? ''));
        }

        return array(
            'ok'        => $ok,
            'http_code' => $httpCode,
            'body'      => $decoded,
            'error'     => $ok ? '' : ($errorMsg !== '' ? $errorMsg : 'HTTP ' . $httpCode),
        );
    }

    private function _refreshCheckoutStatus($row)
    {
        if (!$row->checkout_id) {
            return null;
        }

        $secret = trim((string) $this->config->item('paymongo_secret_key', 'paymongo'));
        if ($secret === '' || strpos($secret, 'REPLACE_ME') !== false) {
            return null;
        }

        $response = $this->_paymongoRequest('GET', 'https://api.paymongo.com/v1/checkout_sessions/' . rawurlencode($row->checkout_id));
        if (!$response['ok']) {
            return null;
        }

        $attrs = $response['body']['data']['attributes'] ?? array();
        $payments = $attrs['payments'] ?? array();
        $paidPayment = null;
        foreach ($payments as $p) {
            $paid = $p['attributes']['status'] ?? '';
            if ($paid === 'paid') {
                $paidPayment = $p;
                break;
            }
        }

        if (!$paidPayment) {
            return null;
        }

        $this->db->where('id', $row->id)->update('online_payment', array(
            'status'              => 'paid',
            'paid_at'             => date('Y-m-d H:i:s'),
            'paymongo_payment_id' => (string) ($paidPayment['id'] ?? ''),
        ));

        if ($row->status !== 'paid') {
            $this->_recordLedgerPayment($row->id);
        }

        return $this->db->where('id', $row->id)->get('online_payment')->row();
    }

    /**
     * Check source status for QR Ph payments.
     */
    private function _refreshSourceStatus($row)
    {
        if (!$row->source_id) {
            return null;
        }

        $secret = trim((string) $this->config->item('paymongo_secret_key', 'paymongo'));
        if ($secret === '' || strpos($secret, 'REPLACE_ME') !== false) {
            return null;
        }

        $response = $this->_paymongoRequest('GET', 'https://api.paymongo.com/v1/sources/' . rawurlencode($row->source_id));
        if (!$response['ok']) {
            return null;
        }

        $attrs = $response['body']['data']['attributes'] ?? array();
        $status = $attrs['status'] ?? '';

        // Source statuses: pending, chargeable, consumed, cancelled, failed
        if ($status !== 'chargeable' && $status !== 'consumed') {
            return null;
        }

        $this->db->where('id', $row->id)->update('online_payment', array(
            'status'   => 'paid',
            'paid_at'  => date('Y-m-d H:i:s'),
        ));

        if ($row->status !== 'paid') {
            $this->_recordLedgerPayment($row->id);
        }

        return $this->db->where('id', $row->id)->get('online_payment')->row();
    }

    /**
     * Check Payment Intent status for QR Ph payments (new flow).
     */
    private function _refreshPaymentIntentStatus($row)
    {
        if (!$row->payment_intent_id) {
            return null;
        }

        $secret = trim((string) $this->config->item('paymongo_secret_key', 'paymongo'));
        if ($secret === '' || strpos($secret, 'REPLACE_ME') !== false) {
            return null;
        }

        $response = $this->_paymongoRequest('GET', 'https://api.paymongo.com/v1/payment_intents/' . rawurlencode($row->payment_intent_id));
        if (!$response['ok']) {
            return null;
        }

        $attrs = $response['body']['data']['attributes'] ?? array();
        $status = $attrs['status'] ?? '';
        $payments = $attrs['payments'] ?? array();

        // Payment Intent statuses: awaiting_payment_method, awaiting_next_action, processing, succeeded, failed
        if ($status !== 'succeeded' && empty($payments)) {
            return null;
        }

        // Check if any payment succeeded
        $paidPayment = null;
        foreach ($payments as $p) {
            $pStatus = $p['attributes']['status'] ?? '';
            if ($pStatus === 'paid') {
                $paidPayment = $p;
                break;
            }
        }

        if (!$paidPayment && $status !== 'succeeded') {
            return null;
        }

        $this->db->where('id', $row->id)->update('online_payment', array(
            'status'              => 'paid',
            'paid_at'             => date('Y-m-d H:i:s'),
            'paymongo_payment_id' => (string) ($paidPayment['id'] ?? ''),
            'raw_create_response' => substr(json_encode($response['body']), 0, 60000),
        ));

        if ($row->status !== 'paid') {
            $this->_recordLedgerPayment($row->id);
        }

        return $this->db->where('id', $row->id)->get('online_payment')->row();
    }

    /**
     * Writes a `payments` row and resyncs the invoice totals so the paid
     * online payment flows into the regular AR history.
     */
    private function _recordLedgerPayment($onlinePaymentId)
    {
        $row = $this->db->where('id', (int) $onlinePaymentId)->get('online_payment')->row();
        if (!$row || $row->status !== 'paid') {
            log_message('info', 'PayMongo _recordLedgerPayment: skipped - row not found or not paid. ID=' . $onlinePaymentId);
            return;
        }

        $invoiceNo = trim((string) $row->InvoiceNo);
        if ($invoiceNo === '') {
            log_message('info', 'PayMongo _recordLedgerPayment: skipped - no InvoiceNo. ID=' . $onlinePaymentId);
            return;
        }
        log_message('info', 'PayMongo _recordLedgerPayment: processing ID=' . $onlinePaymentId . ' InvoiceNo=' . $invoiceNo);

        // Idempotency — if we've already inserted a payments row for this
        // PayMongo payment id or this online_payment id, skip.
        $existing = $this->db
            ->where('settingsID', (int) $row->settingsID)
            ->where('InvoiceNo', $invoiceNo)
            ->where('PaymentReference', 'PayMongo:' . $row->id)
            ->count_all_results('payments');
        if ($existing > 0) {
            log_message('info', 'PayMongo _recordLedgerPayment: skipped - payment already exists. ID=' . $onlinePaymentId);
            return;
        }

        $paymentData = array(
            'InvoiceNo'        => $invoiceNo,
            'PDate'            => $row->paid_at ? date('Y-m-d', strtotime($row->paid_at)) : date('Y-m-d'),
            'AmountPaid'       => (float) $row->amount,
            'TaxAmount'        => 0,
            'ORNo'             => $row->paymongo_payment_id ?: '',
            'PaymentReference' => 'PayMongo:' . $row->id,
            'Cashier'          => 'PayMongo',
            'PaymentSource'    => 'PayMongo ' . strtoupper((string) $row->payment_method),
            'CustID'           => $row->CustID ?: null,
            'Customer'         => $row->Customer,
            'TransDescription' => 'Online payment via PayMongo',
            'ORStat'           => 'Valid',
            'TerminalNo'       => '',
            'settingsID'       => (int) $row->settingsID,
        );
        $this->db->insert('payments', $paymentData);
        log_message('info', 'PayMongo _recordLedgerPayment: inserted payment. ID=' . $onlinePaymentId . ' Amount=' . $row->amount);

        $this->_syncInvoiceTotals((int) $row->settingsID, $invoiceNo);
        log_message('info', 'PayMongo _recordLedgerPayment: synced invoice totals. InvoiceNo=' . $invoiceNo);
    }

    private function _syncInvoiceTotals($settingsID, $invoiceNo)
    {
        $invoice = $this->CashModel->getInvoiceByInvoiceNo($invoiceNo, $settingsID);
        if (!$invoice) {
            log_message('error', 'PayMongo _syncInvoiceTotals: invoice not found. InvoiceNo=' . $invoiceNo . ' settingsID=' . $settingsID);
            return;
        }
        log_message('info', 'PayMongo _syncInvoiceTotals: found invoice. orderID=' . $invoice->orderID . ' Current Balance=' . $invoice->Balance . ' Current AmountPaid=' . $invoice->AmountPaid);

        $sumRow = $this->db
            ->select('COALESCE(SUM(AmountPaid + COALESCE(TaxAmount, 0)), 0) AS total_paid', false)
            ->from('payments')
            ->where('settingsID', $settingsID)
            ->where('InvoiceNo', $invoiceNo)
            ->where('ORStat', 'Valid')
            ->get()
            ->row();

        $totalPaid = round((float) ($sumRow->total_paid ?? 0), 2);
        $balance = max(0, round((float) $invoice->TotalDue - $totalPaid, 2));

        log_message('info', 'PayMongo _syncInvoiceTotals: calculated. TotalDue=' . $invoice->TotalDue . ' TotalPaid=' . $totalPaid . ' NewBalance=' . $balance);

        $this->db
            ->where('orderID', (int) $invoice->orderID)
            ->where('settingsID', $settingsID)
            ->update('invoice', array(
                'AmountPaid' => $totalPaid,
                'Balance'    => $balance,
            ));

        log_message('info', 'PayMongo _syncInvoiceTotals: updated invoice. orderID=' . $invoice->orderID . ' AmountPaid=' . $totalPaid . ' Balance=' . $balance);

        if ($this->db->table_exists('customers')) {
            // no-op: invoice totals are what show in the client profile;
            // `customers` has no rolled-up columns to update here.
        }
    }

    private function _verifyWebhookSignature($rawBody, $signatureHeader, $secret)
    {
        if ($signatureHeader === '') {
            return false;
        }

        // Header format: t=<timestamp>,te=<test-sig>,li=<live-sig>
        $parts = array();
        foreach (explode(',', $signatureHeader) as $segment) {
            $kv = explode('=', trim($segment), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $timestamp = $parts['t'] ?? '';
        $provided = $parts['li'] ?? ($parts['te'] ?? '');
        if ($timestamp === '' || $provided === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);
        return hash_equals($expected, $provided);
    }
}
