<?php
/**
 * eSewa Signature Test File
 * This file tests different signature generation methods to identify the correct one
 */

require_once 'esewa_helper.php';

echo "<h1>eSewa Signature Test</h1>";

// Initialize eSewa helper
$esewa = new EsewaHelper(true);

// Test parameters
$test_params = [
    'amount' => 100.00,
    'tax_amount' => 0,
    'product_service_charge' => 0,
    'product_delivery_charge' => 0,
    'total_amount' => 100.00,
    'transaction_uuid' => 'TXN_' . time() . '_' . rand(1000, 9999),
    'product_code' => 'EPAYTEST',
    'success_url' => 'http://localhost/MISCOFFEE/payment_success.php',
    'failure_url' => 'http://localhost/MISCOFFEE/payment_failed.php',
    'signed_field_names' => 'total_amount,transaction_uuid,product_code'
];

echo "<h2>Test Parameters</h2>";
echo "<pre>" . json_encode($test_params, JSON_PRETTY_PRINT) . "</pre>";

// Test Method 1: Dynamic signature generation
echo "<h2>Method 1: Dynamic Signature Generation</h2>";
$signature1 = $esewa->generateSignature($test_params);
echo "Signature: " . $signature1 . "<br>";
echo "Signature Length: " . strlen($signature1) . "<br>";

// Test Method 2: Alternative signature generation
echo "<h2>Method 2: Alternative Signature Generation</h2>";
$signature2 = $esewa->generateSignatureAlternative($test_params);
echo "Signature: " . $signature2 . "<br>";
echo "Signature Length: " . strlen($signature2) . "<br>";

// Test Method 3: Manual signature generation (exact eSewa format)
echo "<h2>Method 3: Manual Signature Generation</h2>";
$signature_data = "total_amount=" . $test_params['total_amount'] . 
                 ",transaction_uuid=" . $test_params['transaction_uuid'] . 
                 ",product_code=" . $test_params['product_code'];
$signature3 = base64_encode(hash_hmac('sha256', $signature_data, $esewa->getTestCredentials()['secret_key'], true));
echo "Signature Data: " . $signature_data . "<br>";
echo "Signature: " . $signature3 . "<br>";
echo "Signature Length: " . strlen($signature3) . "<br>";

// Test Method 4: Using different field order
echo "<h2>Method 4: Different Field Order</h2>";
$signature_data4 = "transaction_uuid=" . $test_params['transaction_uuid'] . 
                  ",total_amount=" . $test_params['total_amount'] . 
                  ",product_code=" . $test_params['product_code'];
$signature4 = base64_encode(hash_hmac('sha256', $signature_data4, $esewa->getTestCredentials()['secret_key'], true));
echo "Signature Data: " . $signature_data4 . "<br>";
echo "Signature: " . $signature4 . "<br>";
echo "Signature Length: " . strlen($signature4) . "<br>";

// Compare signatures
echo "<h2>Signature Comparison</h2>";
echo "Method 1 vs Method 2: " . ($signature1 === $signature2 ? "✅ Same" : "❌ Different") . "<br>";
echo "Method 1 vs Method 3: " . ($signature1 === $signature3 ? "✅ Same" : "❌ Different") . "<br>";
echo "Method 2 vs Method 3: " . ($signature2 === $signature3 ? "✅ Same" : "❌ Different") . "<br>";

// Generate test forms with different signatures
echo "<h2>Test Forms</h2>";

// Form with Method 1 signature
echo "<h3>Form with Method 1 Signature</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
foreach ($test_params as $key => $value) {
    echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
}
echo "<input type='hidden' name='signature' value='" . htmlspecialchars($signature1) . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Method 1</button>";
echo "</form>";

// Form with Method 2 signature
echo "<h3>Form with Method 2 Signature</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
foreach ($test_params as $key => $value) {
    echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
}
echo "<input type='hidden' name='signature' value='" . htmlspecialchars($signature2) . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Method 2</button>";
echo "</form>";

// Form with Method 3 signature
echo "<h3>Form with Method 3 Signature</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
foreach ($test_params as $key => $value) {
    echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
}
echo "<input type='hidden' name='signature' value='" . htmlspecialchars($signature3) . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Method 3</button>";
echo "</form>";

// Test with different signed_field_names
echo "<h2>Test with Different Signed Fields</h2>";

$test_params2 = $test_params;
$test_params2['signed_field_names'] = 'transaction_uuid,total_amount,product_code';
$signature_diff_order = $esewa->generateSignature($test_params2);

echo "<h3>Form with Different Field Order</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
foreach ($test_params2 as $key => $value) {
    echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
}
echo "<input type='hidden' name='signature' value='" . htmlspecialchars($signature_diff_order) . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Different Order</button>";
echo "</form>";

echo "<h2>Instructions</h2>";
echo "<p>Try each form above to see which signature method works with eSewa. The form that doesn't return 'Invalid payload signature' error is the correct one.</p>";
echo "<p><strong>Note:</strong> You may need to try multiple times as eSewa might have rate limiting or other restrictions.</p>";

echo "<p><a href='esewa_payment.php'>Back to Payment Page</a></p>";
?> 