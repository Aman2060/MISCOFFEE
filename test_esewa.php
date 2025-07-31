<?php
/**
 * eSewa Integration Test File
 * This file tests the eSewa payment integration
 */

require_once 'esewa_helper.php';

echo "<h1>eSewa Integration Test</h1>";

// Test 1: Initialize EsewaHelper
echo "<h2>Test 1: Initialize EsewaHelper</h2>";
try {
    $esewa = new EsewaHelper(true); // Test mode
    echo "✅ EsewaHelper initialized successfully<br>";
    echo "Product Code: " . EsewaHelper::TEST_PRODUCT_CODE . "<br>";
    echo "Payment URL: " . EsewaHelper::TEST_PAYMENT_URL . "<br>";
    echo "Status URL: " . EsewaHelper::TEST_STATUS_URL . "<br>";
} catch (Exception $e) {
    echo "❌ Error initializing EsewaHelper: " . $e->getMessage() . "<br>";
}

// Test 2: Generate Payment Data
echo "<h2>Test 2: Generate Payment Data</h2>";
try {
    $payment_data = $esewa->generatePaymentData(
        100.00, // amount
        0, // tax_amount
        0, // service_charge
        0, // delivery_charge
        'http://localhost/MISCOFFEE/payment_success.php',
        'http://localhost/MISCOFFEE/payment_failed.php'
    );
    
    echo "✅ Payment data generated successfully<br>";
    echo "Transaction UUID: " . $payment_data['transaction_uuid'] . "<br>";
    echo "Total Amount: $" . $payment_data['form_data']['total_amount'] . "<br>";
    echo "Signature: " . substr($payment_data['form_data']['signature'], 0, 20) . "...<br>";
    
    // Validate payment parameters
    $validation = $esewa->validatePaymentParams($payment_data['form_data']);
    if ($validation['valid']) {
        echo "✅ Payment parameters are valid<br>";
    } else {
        echo "❌ Payment parameters validation failed:<br>";
        foreach ($validation['errors'] as $error) {
            echo "- " . $error . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error generating payment data: " . $e->getMessage() . "<br>";
}

// Test 3: Test Signature Generation
echo "<h2>Test 3: Test Signature Generation</h2>";
try {
    $test_params = [
        'total_amount' => 100.00,
        'transaction_uuid' => 'TXN_TEST_123',
        'product_code' => 'EPAYTEST'
    ];
    
    $signature = $esewa->generateSignature($test_params);
    echo "✅ Signature generated successfully<br>";
    echo "Test Signature: " . $signature . "<br>";
    
    // Verify signature format (should be base64)
    if (base64_decode($signature, true) !== false) {
        echo "✅ Signature is valid base64 format<br>";
    } else {
        echo "❌ Signature is not valid base64 format<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error generating signature: " . $e->getMessage() . "<br>";
}

// Test 4: Test Status Check API (with dummy data)
echo "<h2>Test 4: Test Status Check API</h2>";
try {
    // Test with a dummy transaction (this will likely return NOT_FOUND)
    $status = $esewa->checkTransactionStatus('TXN_DUMMY_TEST', 100.00);
    
    if ($status['success']) {
        echo "✅ Status check API call successful<br>";
        echo "HTTP Code: " . $status['http_code'] . "<br>";
        echo "Response: " . json_encode($status['data'], JSON_PRETTY_PRINT) . "<br>";
    } else {
        echo "⚠️ Status check API call failed (expected for dummy transaction)<br>";
        echo "Error: " . $status['error'] . "<br>";
        echo "HTTP Code: " . $status['http_code'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error checking transaction status: " . $e->getMessage() . "<br>";
}

// Test 5: Test Test Credentials
echo "<h2>Test 5: Test Credentials</h2>";
try {
    $credentials = EsewaHelper::getTestCredentials();
    echo "✅ Test credentials retrieved successfully<br>";
    echo "Product Code: " . $credentials['product_code'] . "<br>";
    echo "Secret Key: " . substr($credentials['secret_key'], 0, 10) . "...<br>";
    echo "Payment URL: " . $credentials['payment_url'] . "<br>";
    echo "Status URL: " . $credentials['status_url'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error getting test credentials: " . $e->getMessage() . "<br>";
}

// Test 6: Generate Sample Payment Form
echo "<h2>Test 6: Sample Payment Form</h2>";
try {
    $sample_payment = $esewa->generatePaymentData(
        50.00,
        5.00, // tax
        2.00, // service charge
        3.00, // delivery charge
        'http://localhost/MISCOFFEE/payment_success.php',
        'http://localhost/MISCOFFEE/payment_failed.php'
    );
    
    echo "✅ Sample payment form generated<br>";
    echo "<form action='" . $sample_payment['payment_url'] . "' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<h3>Sample eSewa Payment Form</h3>";
    
    foreach ($sample_payment['form_data'] as $key => $value) {
        echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
        echo "<div><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</div>";
    }
    
    echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test eSewa Payment</button>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "❌ Error generating sample payment form: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Summary</h2>";
echo "<p>All tests completed. Check the results above to verify eSewa integration is working correctly.</p>";
echo "<p><strong>Note:</strong> The status check API test may fail with dummy data, which is expected behavior.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Test with real eSewa test credentials</li>";
echo "<li>Verify payment flow end-to-end</li>";
echo "<li>Test transaction verification</li>";
echo "<li>Remove debug information in production</li>";
echo "</ul>";

echo "<p><a href='esewa_payment.php'>Go to Payment Page</a></p>";
?> 