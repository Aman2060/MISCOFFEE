<?php
/**
 * eSewa Secret Key and Signature Test
 * This file tests the secret key and signature generation using eSewa documentation examples
 */

echo "<h1>eSewa Secret Key and Signature Test</h1>";

// Test secret key from eSewa documentation
$secret_key = "8gBm/:&EnhH.1/q(";

echo "<h2>Secret Key Test</h2>";
echo "Secret Key: " . $secret_key . "<br>";
echo "Secret Key Length: " . strlen($secret_key) . "<br>";
echo "Secret Key Bytes: " . bin2hex($secret_key) . "<br>";

// Test with eSewa documentation example
echo "<h2>eSewa Documentation Example</h2>";
$test_data = "total_amount=100,transaction_uuid=11-201-13,product_code=EPAYTEST";
echo "Test Data: " . $test_data . "<br>";

$signature = base64_encode(hash_hmac('sha256', $test_data, $secret_key, true));
echo "Generated Signature: " . $signature . "<br>";
echo "Expected Signature (from docs): 4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=<br>";

// Test with our actual parameters
echo "<h2>Our Parameters Test</h2>";
$our_data = "total_amount=100.00,transaction_uuid=TXN_" . time() . "_1234,product_code=EPAYTEST";
echo "Our Data: " . $our_data . "<br>";

$our_signature = base64_encode(hash_hmac('sha256', $our_data, $secret_key, true));
echo "Our Signature: " . $our_signature . "<br>";

// Test different data formats
echo "<h2>Different Data Format Tests</h2>";

// Test 1: Integer amount
$data1 = "total_amount=100,transaction_uuid=TXN_" . time() . "_1234,product_code=EPAYTEST";
$sig1 = base64_encode(hash_hmac('sha256', $data1, $secret_key, true));
echo "Integer Amount (100): " . $sig1 . "<br>";

// Test 2: Float amount
$data2 = "total_amount=100.00,transaction_uuid=TXN_" . time() . "_1234,product_code=EPAYTEST";
$sig2 = base64_encode(hash_hmac('sha256', $data2, $secret_key, true));
echo "Float Amount (100.00): " . $sig2 . "<br>";

// Test 3: String amount
$data3 = "total_amount=100.0,transaction_uuid=TXN_" . time() . "_1234,product_code=EPAYTEST";
$sig3 = base64_encode(hash_hmac('sha256', $data3, $secret_key, true));
echo "String Amount (100.0): " . $sig3 . "<br>";

// Test with different field orders
echo "<h2>Field Order Tests</h2>";

$data_order1 = "transaction_uuid=TXN_" . time() . "_1234,total_amount=100,product_code=EPAYTEST";
$sig_order1 = base64_encode(hash_hmac('sha256', $data_order1, $secret_key, true));
echo "Order 1 (uuid,amount,code): " . $sig_order1 . "<br>";

$data_order2 = "product_code=EPAYTEST,total_amount=100,transaction_uuid=TXN_" . time() . "_1234";
$sig_order2 = base64_encode(hash_hmac('sha256', $data_order2, $secret_key, true));
echo "Order 2 (code,amount,uuid): " . $sig_order2 . "<br>";

// Test with different secret key encodings
echo "<h2>Secret Key Encoding Tests</h2>";

// Test with URL decoded secret key
$secret_key_url_decoded = urldecode($secret_key);
$sig_url_decoded = base64_encode(hash_hmac('sha256', $test_data, $secret_key_url_decoded, true));
echo "URL Decoded Secret Key: " . $sig_url_decoded . "<br>";

// Test with HTML decoded secret key
$secret_key_html_decoded = html_entity_decode($secret_key);
$sig_html_decoded = base64_encode(hash_hmac('sha256', $test_data, $secret_key_html_decoded, true));
echo "HTML Decoded Secret Key: " . $sig_html_decoded . "<br>";

// Generate a test form with the working signature
echo "<h2>Test Form</h2>";
$test_uuid = "TXN_" . time() . "_1234";
$test_amount = 100;
$test_data_form = "total_amount=" . $test_amount . ",transaction_uuid=" . $test_uuid . ",product_code=EPAYTEST";
$test_signature = base64_encode(hash_hmac('sha256', $test_data_form, $secret_key, true));

echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amount' value='100'>";
echo "<input type='hidden' name='tax_amount' value='0'>";
echo "<input type='hidden' name='product_service_charge' value='0'>";
echo "<input type='hidden' name='product_delivery_charge' value='0'>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $test_uuid . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . $test_signature . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test eSewa Payment</button>";
echo "</form>";

echo "<h2>Debug Information</h2>";
echo "Test Data Used: " . $test_data_form . "<br>";
echo "Generated Signature: " . $test_signature . "<br>";
echo "Signature Length: " . strlen($test_signature) . "<br>";

echo "<p><a href='test_signature.php'>Go to Signature Test</a></p>";
echo "<p><a href='esewa_payment.php'>Go to Payment Page</a></p>";
?> 