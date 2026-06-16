<?php
// Test PayMongo Checkout Session API
$secret = 'sk_live_MZm9TUUqpExMgryiQejFViQp';

$payload = json_encode([
    'data' => [
        'attributes' => [
            'send_email_receipt' => false,
            'show_description' => true,
            'show_line_items' => true,
            'description' => 'Test Invoice #123',
            'line_items' => [[
                'name' => 'Test Item',
                'quantity' => 1,
                'amount' => 100,
                'currency' => 'PHP',
            ]],
            'payment_method_types' => ['qrph'],
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'reference_number' => 'TEST-001',
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

echo "HTTP: $httpCode\n\n";
$data = json_decode($raw, true);
echo "Checkout URL: " . ($data['data']['attributes']['checkout_url'] ?? 'N/A') . "\n\n";
echo "Full Response:\n";
print_r($data);
