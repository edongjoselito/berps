<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| PayMongo API credentials.
| Get your keys from https://dashboard.paymongo.com/developers
| Use test keys (pk_test_..., sk_test_...) while developing, then swap to live keys.
*/

$config['paymongo_public_key'] = 'pk_live_ieS5yr78pQJsnJYHrYTfpwRW';
$config['paymongo_secret_key'] = 'sk_live_MZm9TUUqpExMgryiQejFViQp';

// Shared secret configured on the PayMongo webhook. Leave blank to disable
// signature verification (not recommended for production).
$config['paymongo_webhook_secret'] = 'whsk_988vsvAJXT4wm9PAZBqFK3rc';

// Allowed payment methods when creating a Checkout Session fallback.
$config['paymongo_payment_methods'] = array('qrph');
