<?php
/**
 * Test Corrected Signature
 * This file tests the signature generation with the corrected secret key
 */

echo "<h1>Test Corrected Signature</h1>";

// Corrected secret key (without the "(" character)
$secret_key = "8gBm/:&EnhH.1/q";

echo "<h2>Secret Key Test</h2>";
echo "Secret Key: " . $secret_key . "<br>";
echo "Secret Key Length: " . strlen($secret_key) . "<br>";

// Test with your parameters
$transaction_uuid = "TXN_" . time() . "_1234";
$total_amount = 22;
$product_code = "EPAYTEST";

echo "<h2>Test Parameters</h2>";
echo "Transaction UUID: " . $transaction_uuid . "<br>";
echo "Total Amount: " . $total_amount . "<br>";
echo "Product Code: " . $product_code . "<br>";

// Generate signature
$signature_data = "total_amount=" . $total_amount . ",transaction_uuid=" . $transaction_uuid . ",product_code=" . $product_code;
$signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));

echo "<h2>Signature Generation</h2>";
echo "Signature Data: " . $signature_data . "<br>";
echo "Generated Signature: " . $signature . "<br>";
echo "Signature Length: " . strlen($signature) . "<br>";

// Test with eSewa documentation example
echo "<h2>eSewa Documentation Example</h2>";
$doc_signature_data = "total_amount=100,transaction_uuid=11-201-13,product_code=EPAYTEST";
$doc_signature = base64_encode(hash_hmac('sha256', $doc_signature_data, $secret_key, true));
echo "Documentation Data: " . $doc_signature_data . "<br>";
echo "Documentation Signature: " . $doc_signature . "<br>";
echo "Expected: 4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=<br>";
echo "Match: " . ($doc_signature === "4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=" ? "✅ YES" : "❌ NO") . "<br>";

// Generate test form
echo "<h2>Test Form</h2>";
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
echo "<input type='hidden' name='signature' value='" . $signature . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Corrected Signature</button>";
echo "</form>";

echo "<h2>Status</h2>";
if ($doc_signature === "4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=") {
    echo "✅ <strong>Signature generation is working correctly!</strong><br>";
    echo "The secret key has been fixed and signatures should now be valid.<br>";
} else {
    echo "❌ <strong>Signature generation is still not matching eSewa documentation.</strong><br>";
    echo "There might be another issue with the secret key or signature format.<br>";
}

echo "<p><a href='esewa_payment.php'>Go to Payment Page</a></p>";
echo "<p><a href='esewa_fallback.php'>Go to Fallback Payment Page</a></p>";
?> 