<?php
/**
 * eSewa Payment Diagnostic Tool
 * This file will help identify the exact cause of the payment failure
 */

echo "<h1>eSewa Payment Diagnostic Tool</h1>";

// Test different secret keys
$secret_keys = [
    "8gBm/:&EnhH.1/q",
    "8gBm/:&EnhH.1/q(",
    "8gBm/:&EnhH.1/q)",
    "8gBm/:&EnhH.1/q",
    "8gBm/:&EnhH.1/q",
    "8gBm/:&EnhH.1/q",
    "8gBm/:&EnhH.1/q",
    "8gBm/:&EnhH.1/q"
];

echo "<h2>Secret Key Analysis</h2>";
foreach ($secret_keys as $index => $key) {
    echo "Key " . ($index + 1) . ": " . $key . " (Length: " . strlen($key) . ")<br>";
}

// Test parameters
$test_params = [
    'amount' => 100,
    'tax_amount' => 0,
    'product_service_charge' => 0,
    'product_delivery_charge' => 0,
    'total_amount' => 100,
    'transaction_uuid' => 'TXN_' . time() . '_' . rand(1000, 9999),
    'product_code' => 'EPAYTEST',
    'success_url' => 'http://localhost/MISCOFFEE/payment_success.php',
    'failure_url' => 'http://localhost/MISCOFFEE/payment_failed.php',
    'signed_field_names' => 'total_amount,transaction_uuid,product_code'
];

echo "<h2>Test Parameters</h2>";
echo "<pre>" . json_encode($test_params, JSON_PRETTY_PRINT) . "</pre>";

// Test different signature generation methods
echo "<h2>Signature Generation Tests</h2>";

// Method 1: Standard method
$signature_data1 = "total_amount=" . $test_params['total_amount'] . ",transaction_uuid=" . $test_params['transaction_uuid'] . ",product_code=" . $test_params['product_code'];
$signature1 = base64_encode(hash_hmac('sha256', $signature_data1, $secret_keys[0], true));

echo "<h3>Method 1: Standard</h3>";
echo "Data: " . $signature_data1 . "<br>";
echo "Signature: " . $signature1 . "<br>";

// Method 2: Different field order
$signature_data2 = "transaction_uuid=" . $test_params['transaction_uuid'] . ",total_amount=" . $test_params['total_amount'] . ",product_code=" . $test_params['product_code'];
$signature2 = base64_encode(hash_hmac('sha256', $signature_data2, $secret_keys[0], true));

echo "<h3>Method 2: Different Order</h3>";
echo "Data: " . $signature_data2 . "<br>";
echo "Signature: " . $signature2 . "<br>";

// Method 3: With different amount format
$signature_data3 = "total_amount=100.00,transaction_uuid=" . $test_params['transaction_uuid'] . ",product_code=" . $test_params['product_code'];
$signature3 = base64_encode(hash_hmac('sha256', $signature_data3, $secret_keys[0], true));

echo "<h3>Method 3: Float Amount</h3>";
echo "Data: " . $signature_data3 . "<br>";
echo "Signature: " . $signature3 . "<br>";

// Test different parameter combinations
echo "<h2>Parameter Combination Tests</h2>";

// Test 1: Minimal parameters
echo "<h3>Test 1: Minimal Parameters</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $test_params['transaction_uuid'] . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature1 . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Minimal</button>";
echo "</form>";

// Test 2: All parameters
echo "<h3>Test 2: All Parameters</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
foreach ($test_params as $key => $value) {
    echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
}
echo "<input type='hidden' name='signature' value='" . $signature1 . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test All Parameters</button>";
echo "</form>";

// Test 3: Different signature method
echo "<h3>Test 3: Different Signature</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $test_params['transaction_uuid'] . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature2 . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Different Signature</button>";
echo "</form>";

// Test 4: Try with different URLs
echo "<h3>Test 4: Different eSewa URLs</h3>";

// Test with production URL
echo "<h4>Production URL Test</h4>";
echo "<form action='https://epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $test_params['transaction_uuid'] . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature1 . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Production URL</button>";
echo "</form>";

// Test 5: Try with old eSewa format
echo "<h3>Test 5: Old eSewa Format</h3>";
echo "<form action='https://esewa.com.np/epay/main' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amt' value='100'>";
echo "<input type='hidden' name='pdc' value='0'>";
echo "<input type='hidden' name='psc' value='0'>";
echo "<input type='hidden' name='txAmt' value='0'>";
echo "<input type='hidden' name='tAmt' value='100'>";
echo "<input type='hidden' name='pid' value='" . $test_params['transaction_uuid'] . "'>";
echo "<input type='hidden' name='scd' value='EPAYTEST'>";
echo "<input type='hidden' name='su' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='fu' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Old Format</button>";
echo "</form>";

// Test 6: Try with different product codes
echo "<h3>Test 6: Different Product Codes</h3>";

$product_codes = ['EPAYTEST', 'TEST', 'DEMO', 'COFFEE'];

foreach ($product_codes as $code) {
    $signature_data_code = "total_amount=100,transaction_uuid=" . $test_params['transaction_uuid'] . ",product_code=" . $code;
    $signature_code = base64_encode(hash_hmac('sha256', $signature_data_code, $secret_keys[0], true));
    
    echo "<h4>Product Code: " . $code . "</h4>";
    echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<input type='hidden' name='total_amount' value='100'>";
    echo "<input type='hidden' name='transaction_uuid' value='" . $test_params['transaction_uuid'] . "'>";
    echo "<input type='hidden' name='product_code' value='" . $code . "'>";
    echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
    echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
    echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
    echo "<input type='hidden' name='signature' value='" . $signature_code . "'>";
    echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test " . $code . "</button>";
    echo "</form>";
}

// Network connectivity test
echo "<h2>Network Connectivity Test</h2>";
$test_urls = [
    'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
    'https://epay.esewa.com.np/api/epay/main/v2/form',
    'https://esewa.com.np/epay/main'
];

foreach ($test_urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "URL: " . $url . "<br>";
    echo "HTTP Code: " . $http_code . "<br>";
    echo "Error: " . ($error ? $error : 'None') . "<br>";
    echo "Accessible: " . ($http_code > 0 ? '✅ Yes' : '❌ No') . "<br><br>";
}

echo "<h2>Analysis</h2>";
echo "<p>Try each test form above to identify which combination works. The 400 error suggests:</p>";
echo "<ul>";
echo "<li><strong>Invalid parameters:</strong> One or more parameters might be incorrect</li>";
echo "<li><strong>Wrong product code:</strong> EPAYTEST might not be valid for your region</li>";
echo "<li><strong>API restrictions:</strong> eSewa might have restrictions on test payments</li>";
echo "<li><strong>Network issues:</strong> There might be connectivity problems</li>";
echo "<li><strong>Rate limiting:</strong> eSewa might be blocking multiple test requests</li>";
echo "</ul>";

echo "<h2>Recommendations</h2>";
echo "<p>1. Try the old eSewa format (Test 5) - it's more stable</p>";
echo "<p>2. Try different product codes (Test 6)</p>";
echo "<p>3. Check if any form works without the 400 error</p>";
echo "<p>4. Contact eSewa support if none work</p>";
echo "<p>5. Use demo payment for now</p>";

echo "<p><a href='esewa_payment.php'>Back to Payment Page</a></p>";
?> 