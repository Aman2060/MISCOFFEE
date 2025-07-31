<?php
/**
 * Debug Signature Issue
 * This file will help identify the exact cause of the signature error
 */

echo "<h1>eSewa Signature Debug</h1>";

// Your exact parameters from debug info
$transaction_uuid = "TXN_1753932984_8548";
$total_amount = 22;
$product_code = "EPAYTEST";
$secret_key = "8gBm/:&EnhH.1/q(";

echo "<h2>Your Parameters</h2>";
echo "Transaction UUID: " . $transaction_uuid . "<br>";
echo "Total Amount: " . $total_amount . "<br>";
echo "Product Code: " . $product_code . "<br>";
echo "Secret Key: " . $secret_key . "<br>";

// Test 1: Your exact signature data
echo "<h2>Test 1: Your Exact Signature Data</h2>";
$signature_data1 = "total_amount=22,transaction_uuid=TXN_1753932984_8548,product_code=EPAYTEST";
echo "Signature Data: " . $signature_data1 . "<br>";
$signature1 = base64_encode(hash_hmac('sha256', $signature_data1, $secret_key, true));
echo "Generated Signature: " . $signature1 . "<br>";

// Test 2: Try with different amount format
echo "<h2>Test 2: Different Amount Format</h2>";
$signature_data2 = "total_amount=22.00,transaction_uuid=TXN_1753932984_8548,product_code=EPAYTEST";
echo "Signature Data: " . $signature_data2 . "<br>";
$signature2 = base64_encode(hash_hmac('sha256', $signature_data2, $secret_key, true));
echo "Generated Signature: " . $signature2 . "<br>";

// Test 3: Try with different field order
echo "<h2>Test 3: Different Field Order</h2>";
$signature_data3 = "transaction_uuid=TXN_1753932984_8548,total_amount=22,product_code=EPAYTEST";
echo "Signature Data: " . $signature_data3 . "<br>";
$signature3 = base64_encode(hash_hmac('sha256', $signature_data3, $secret_key, true));
echo "Generated Signature: " . $signature3 . "<br>";

// Test 4: Try with eSewa documentation format exactly
echo "<h2>Test 4: eSewa Documentation Format</h2>";
$signature_data4 = "total_amount=100,transaction_uuid=11-201-13,product_code=EPAYTEST";
echo "Signature Data: " . $signature_data4 . "<br>";
$signature4 = base64_encode(hash_hmac('sha256', $signature_data4, $secret_key, true));
echo "Generated Signature: " . $signature4 . "<br>";
echo "Expected (from docs): 4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=<br>";
echo "Match: " . ($signature4 === "4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=" ? "✅ YES" : "❌ NO") . "<br>";

// Test 5: Try with different secret key encoding
echo "<h2>Test 5: Secret Key Encoding Tests</h2>";

// Test with raw secret key
$signature5a = base64_encode(hash_hmac('sha256', $signature_data1, $secret_key, true));
echo "Raw Secret Key: " . $signature5a . "<br>";

// Test with URL decoded secret key
$secret_key_url = urldecode($secret_key);
$signature5b = base64_encode(hash_hmac('sha256', $signature_data1, $secret_key_url, true));
echo "URL Decoded Secret Key: " . $signature5b . "<br>";

// Test with HTML decoded secret key
$secret_key_html = html_entity_decode($secret_key);
$signature5c = base64_encode(hash_hmac('sha256', $signature_data1, $secret_key_html, true));
echo "HTML Decoded Secret Key: " . $signature5c . "<br>";

// Test 6: Try with different HMAC methods
echo "<h2>Test 6: Different HMAC Methods</h2>";

// Test with raw output
$signature6a = base64_encode(hash_hmac('sha256', $signature_data1, $secret_key, true));
echo "Raw Output (true): " . $signature6a . "<br>";

// Test with hex output
$signature6b = base64_encode(hash_hmac('sha256', $signature_data1, $secret_key, false));
echo "Hex Output (false): " . $signature6b . "<br>";

// Test 7: Try with different transaction UUID format
echo "<h2>Test 7: Different Transaction UUID Format</h2>";

// Test with simple UUID
$signature_data7 = "total_amount=22,transaction_uuid=123456,product_code=EPAYTEST";
echo "Signature Data: " . $signature_data7 . "<br>";
$signature7 = base64_encode(hash_hmac('sha256', $signature_data7, $secret_key, true));
echo "Generated Signature: " . $signature7 . "<br>";

// Test 8: Try with different amount values
echo "<h2>Test 8: Different Amount Values</h2>";

$amounts = [100, 100.00, "100", "100.00", 22, 22.00, "22", "22.00"];

foreach ($amounts as $amount) {
    $signature_data8 = "total_amount=" . $amount . ",transaction_uuid=TXN_1753932984_8548,product_code=EPAYTEST";
    $signature8 = base64_encode(hash_hmac('sha256', $signature_data8, $secret_key, true));
    echo "Amount: " . $amount . " (" . gettype($amount) . ") - Signature: " . substr($signature8, 0, 20) . "...<br>";
}

// Generate test forms
echo "<h2>Test Forms</h2>";

// Form with Test 1 signature
echo "<h3>Form with Test 1 Signature</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amount' value='22'>";
echo "<input type='hidden' name='tax_amount' value='0'>";
echo "<input type='hidden' name='product_service_charge' value='0'>";
echo "<input type='hidden' name='product_delivery_charge' value='0'>";
echo "<input type='hidden' name='total_amount' value='22'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $transaction_uuid . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature1 . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Form 1</button>";
echo "</form>";

// Form with Test 2 signature
echo "<h3>Form with Test 2 Signature (22.00)</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amount' value='22'>";
echo "<input type='hidden' name='tax_amount' value='0'>";
echo "<input type='hidden' name='product_service_charge' value='0'>";
echo "<input type='hidden' name='product_delivery_charge' value='0'>";
echo "<input type='hidden' name='total_amount' value='22.00'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $transaction_uuid . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature2 . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Form 2</button>";
echo "</form>";

// Form with Test 7 signature (simple UUID)
echo "<h3>Form with Test 7 Signature (Simple UUID)</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amount' value='22'>";
echo "<input type='hidden' name='tax_amount' value='0'>";
echo "<input type='hidden' name='product_service_charge' value='0'>";
echo "<input type='hidden' name='product_delivery_charge' value='0'>";
echo "<input type='hidden' name='total_amount' value='22'>";
echo "<input type='hidden' name='transaction_uuid' value='123456'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature7 . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Form 7</button>";
echo "</form>";

echo "<h2>Analysis</h2>";
echo "<p>If Test 4 doesn't match the expected signature from eSewa documentation, then there's an issue with the secret key or our understanding of the signature generation.</p>";
echo "<p>Try the different test forms above to see if any work. If none work, the issue might be:</p>";
echo "<ul>";
echo "<li>Secret key is incorrect or has encoding issues</li>";
echo "<li>eSewa expects a different signature format</li>";
echo "<li>There are additional required parameters</li>";
echo "<li>The test environment has restrictions</li>";
echo "</ul>";

echo "<p><a href='esewa_payment.php'>Back to Payment Page</a></p>";
?> 