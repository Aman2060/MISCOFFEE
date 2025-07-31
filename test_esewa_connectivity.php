<?php
echo "<h2>🔍 eSewa Connectivity Test</h2>";

// Test 1: Check if we can reach eSewa test environment
echo "<h3>🌐 eSewa Test Environment Connectivity:</h3>";

$test_urls = [
    'Test Payment URL' => 'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
    'Test Status URL' => 'https://rc.esewa.com.np/api/epay/transaction/status/',
    'Test Environment' => 'https://rc-epay.esewa.com.np/'
];

foreach ($test_urls as $name => $url) {
    echo "<p><strong>$name:</strong> ";
    
    // Test with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<span style='color: red;'>❌ Error: $error</span>";
    } elseif ($http_code == 200) {
        echo "<span style='color: green;'>✅ Accessible (HTTP $http_code)</span>";
    } else {
        echo "<span style='color: orange;'>⚠️ HTTP $http_code</span>";
    }
    echo "</p>";
}

// Test 2: Check if we can make a simple POST request
echo "<h3>📤 POST Request Test:</h3>";

$test_data = [
    'product_code' => 'EPAYTEST',
    'total_amount' => '100',
    'transaction_uuid' => 'TEST_' . time(),
    'success_url' => 'https://example.com/success',
    'failure_url' => 'https://example.com/failure'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($test_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>POST Test:</strong> ";
if ($error) {
    echo "<span style='color: red;'>❌ Error: $error</span>";
} else {
    echo "<span style='color: green;'>✅ POST request sent (HTTP $http_code)</span>";
    if ($response) {
        echo "<br><small>Response length: " . strlen($response) . " characters</small>";
    }
}
echo "</p>";

// Test 3: Check if our helper can generate valid data
echo "<h3>🔧 Helper Function Test:</h3>";

if (file_exists('esewa_helper.php')) {
    require_once 'esewa_helper.php';
    
    try {
        $esewa = new EsewaHelper(true);
        echo "<p style='color: green;'>✅ eSewa helper loaded successfully</p>";
        
        // Test payment data generation
        $payment_data = $esewa->generatePaymentData(
            100, // 1 NPR
            0, // tax_amount
            0, // service_charge
            0, // delivery_charge
            'https://example.com/success',
            'https://example.com/failure'
        );
        
        echo "<p style='color: green;'>✅ Payment data generated successfully</p>";
        echo "<p><strong>Transaction UUID:</strong> " . $payment_data['transaction_uuid'] . "</p>";
        
        // Test signature generation
        $form_data = $payment_data['form_data'];
        $signature = $esewa->generateSignatureAlternative($form_data);
        echo "<p style='color: green;'>✅ Signature generated: " . substr($signature, 0, 20) . "...</p>";
        
        // Show the form data that would be sent
        echo "<h4>Form Data:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($form_data as $key => $value) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($key) . "</td>";
            echo "<td>" . htmlspecialchars($value) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Helper error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ esewa_helper.php not found</p>";
}

// Test 4: Check if there are any common issues
echo "<h3>🔍 Common Issues Check:</h3>";

// Check PHP version
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Check if cURL is available
if (function_exists('curl_init')) {
    echo "<p style='color: green;'>✅ cURL extension is available</p>";
} else {
    echo "<p style='color: red;'>❌ cURL extension is not available</p>";
}

// Check if hash_hmac is available
if (function_exists('hash_hmac')) {
    echo "<p style='color: green;'>✅ hash_hmac function is available</p>";
} else {
    echo "<p style='color: red;'>❌ hash_hmac function is not available</p>";
}

// Check if we can make HTTPS requests
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/get');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "<p style='color: green;'>✅ HTTPS requests work properly</p>";
} else {
    echo "<p style='color: red;'>❌ HTTPS requests may have issues (HTTP $http_code)</p>";
}

echo "<h3>💡 Recommendations:</h3>";
echo "<ul>";
echo "<li>If eSewa URLs are not accessible, there might be network restrictions</li>";
echo "<li>If POST requests fail, there might be firewall or proxy issues</li>";
echo "<li>If helper functions fail, check the eSewa credentials and API changes</li>";
echo "<li>Try using the demo payment first to test the flow</li>";
echo "</ul>";

echo "<p><a href='test_esewa_complete.php' style='background: #a67c52; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Run Complete Test</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2 { color: #a67c52; }
h3 { color: #8d6748; margin-top: 20px; }
table { margin: 10px 0; background: white; }
th, td { padding: 8px; text-align: left; }
th { background: #f8e7d2; }
ul { background: white; padding: 20px; border-radius: 8px; }
li { margin: 5px 0; }
</style> 