<?php
/**
 * Simple eSewa Test
 * This file tests the most basic eSewa integration possible
 */

echo "<h1>Simple eSewa Test</h1>";

// Basic parameters
$amount = 100;
$total_amount = 100;
$transaction_uuid = "TEST_" . time();
$product_code = "EPAYTEST";
$secret_key = "8gBm/:&EnhH.1/q(";

echo "<h2>Basic Parameters</h2>";
echo "Amount: " . $amount . "<br>";
echo "Total Amount: " . $total_amount . "<br>";
echo "Transaction UUID: " . $transaction_uuid . "<br>";
echo "Product Code: " . $product_code . "<br>";

// Generate signature
$signature_data = "total_amount=" . $total_amount . ",transaction_uuid=" . $transaction_uuid . ",product_code=" . $product_code;
$signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));

echo "<h2>Signature Generation</h2>";
echo "Signature Data: " . $signature_data . "<br>";
echo "Generated Signature: " . $signature . "<br>";

// Test Form 1: Basic form
echo "<h2>Test Form 1: Basic Form</h2>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amount' value='100'>";
echo "<input type='hidden' name='tax_amount' value='0'>";
echo "<input type='hidden' name='product_service_charge' value='0'>";
echo "<input type='hidden' name='product_delivery_charge' value='0'>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $transaction_uuid . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Basic Form</button>";
echo "</form>";

// Test Form 2: Minimal form (only required fields)
echo "<h2>Test Form 2: Minimal Form</h2>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $transaction_uuid . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Minimal Form</button>";
echo "</form>";

// Test Form 3: Try with different signature method
echo "<h2>Test Form 3: Different Signature Method</h2>";
$signature_data3 = "total_amount=100,transaction_uuid=" . $transaction_uuid . ",product_code=EPAYTEST";
$signature3 = hash_hmac('sha256', $signature_data3, $secret_key, false); // hex output
$signature3_b64 = base64_encode(hex2bin($signature3));

echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $transaction_uuid . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $signature3_b64 . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Hex Signature</button>";
echo "</form>";

// Test Form 4: Try with eSewa documentation example exactly
echo "<h2>Test Form 4: eSewa Documentation Example</h2>";
$doc_signature_data = "total_amount=100,transaction_uuid=11-201-13,product_code=EPAYTEST";
$doc_signature = base64_encode(hash_hmac('sha256', $doc_signature_data, $secret_key, true));

echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='11-201-13'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $doc_signature . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Documentation Example</button>";
echo "</form>";

// Test Form 5: Try with different URL
echo "<h2>Test Form 5: Different eSewa URL</h2>";
echo "<form action='https://esewa.com.np/epay/main' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amt' value='100'>";
echo "<input type='hidden' name='pdc' value='0'>";
echo "<input type='hidden' name='psc' value='0'>";
echo "<input type='hidden' name='txAmt' value='0'>";
echo "<input type='hidden' name='tAmt' value='100'>";
echo "<input type='hidden' name='pid' value='" . $transaction_uuid . "'>";
echo "<input type='hidden' name='scd' value='EPAYTEST'>";
echo "<input type='hidden' name='su' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='fu' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Old eSewa Format</button>";
echo "</form>";

echo "<h2>Debug Information</h2>";
echo "Expected Documentation Signature: 4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=<br>";
echo "Our Documentation Signature: " . $doc_signature . "<br>";
echo "Match: " . ($doc_signature === "4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=" ? "✅ YES" : "❌ NO") . "<br>";

echo "<h2>Possible Issues</h2>";
echo "<ul>";
echo "<li><strong>Secret Key Issue:</strong> The secret key might be incorrect or have encoding issues</li>";
echo "<li><strong>Test Environment:</strong> eSewa test environment might have restrictions</li>";
echo "<li><strong>API Version:</strong> The API might have changed or require different parameters</li>";
echo "<li><strong>Network Issues:</strong> There might be network connectivity issues</li>";
echo "<li><strong>Rate Limiting:</strong> eSewa might be rate limiting test requests</li>";
echo "</ul>";

echo "<h2>Next Steps</h2>";
echo "<p>1. Try all the test forms above</p>";
echo "<p>2. Check if Test Form 5 (old format) works</p>";
echo "<p>3. If none work, contact eSewa support</p>";
echo "<p>4. Consider using demo payment for now</p>";

echo "<p><a href='esewa_payment.php'>Back to Payment Page</a></p>";
?> 