<?php
// Test full PayMongo Checkout Session with line_items
$secret = 'sk_live_MZm9TUUqpExMgryiQejFViQp';

$payload = json_encode([
    'data' => [
        'attributes' => [
            'send_email_receipt' => false,
            'show_description' => true,
            'show_line_items' => true,
            'description' => 'Invoice Payment',
            'line_items' => [[
                'name' => 'Invoice #123',
                'quantity' => 1,
                'amount' => 10000, // 100.00 PHP
                'currency' => 'PHP',
            ]],
            'payment_method_types' => ['qrph'],
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'reference_number' => 'INV-123',
        ],
    ],
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1/checkout_sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($secret . ':'),
]);

$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n\n";

if ($httpCode == 200) {
    $data = json_decode($raw, true);
    $attrs = $data['data']['attributes'] ?? [];
    
    echo "=== KEY FIELDS ===\n";
    echo "checkout_url: " . ($attrs['checkout_url'] ?? 'N/A') . "\n";
    echo "payment_intent_id: " . ($attrs['payment_intent_id'] ?? 'N/A') . "\n";
    echo "\n=== ALL ATTRIBUTES ===\n";
    print_r($attrs);
    
    // Check if there's payment method options with QR
    if (isset($attrs['payment_method_options'])) {
        echo "\n=== PAYMENT METHOD OPTIONS ===\n";
        print_r($attrs['payment_method_options']);
    }
} else {
    echo "Error:\n";
    echo $raw;
}
