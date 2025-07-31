<?php
/**
 * eSewa Environment Test
 * This file tests if the eSewa test environment is working properly
 */

echo "<h1>eSewa Environment Test</h1>";

// Test 1: Check if eSewa test environment is accessible
echo "<h2>Test 1: eSewa Test Environment Accessibility</h2>";

$test_urls = [
    'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
    'https://esewa.com.np/epay/main',
    'https://esewa.com.np'
];

foreach ($test_urls as $url) {
    echo "<h3>Testing: " . $url . "</h3>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    echo "HTTP Code: " . $http_code . "<br>";
    echo "Content Type: " . $content_type . "<br>";
    echo "Error: " . ($error ? $error : 'None') . "<br>";
    echo "Response Length: " . strlen($response) . " bytes<br>";
    echo "Status: " . ($http_code == 200 ? '✅ Accessible' : '❌ Not Accessible') . "<br><br>";
}

// Test 2: Try a simple GET request to see if eSewa responds
echo "<h2>Test 2: Simple GET Request Test</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET Request to eSewa Form URL<br>";
echo "HTTP Code: " . $http_code . "<br>";
echo "Response: " . substr($response, 0, 200) . "...<br><br>";

// Test 3: Check if eSewa documentation is accessible
echo "<h2>Test 3: eSewa Documentation Accessibility</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://developer.esewa.com.np/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "eSewa Developer Documentation<br>";
echo "HTTP Code: " . $http_code . "<br>";
echo "Status: " . ($http_code == 200 ? '✅ Accessible' : '❌ Not Accessible') . "<br><br>";

// Test 4: Try with different user agents
echo "<h2>Test 4: Different User Agent Test</h2>";

$user_agents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
    'curl/7.68.0'
];

foreach ($user_agents as $ua) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "User Agent: " . $ua . "<br>";
    echo "HTTP Code: " . $http_code . "<br>";
    echo "Status: " . ($http_code == 200 ? '✅ OK' : '❌ Failed') . "<br><br>";
}

// Test 5: Check if there are any regional restrictions
echo "<h2>Test 5: Regional Access Test</h2>";

echo "<p>eSewa is primarily for Nepal. If you're testing from outside Nepal, there might be regional restrictions.</p>";
echo "<p>Current server location: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Your IP might be detected as: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "</p>";

// Test 6: Try with a very simple form
echo "<h2>Test 6: Minimal Form Test</h2>";

echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<h3>Minimal eSewa Form</h3>";
echo "<input type='hidden' name='total_amount' value='100'>";
echo "<input type='hidden' name='transaction_uuid' value='TEST_" . time() . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='dGVzdA=='>"; // Just a test signature
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Minimal Form</button>";
echo "</form>";

echo "<h2>Analysis</h2>";
echo "<p>If the eSewa URLs are not accessible (HTTP 200), then:</p>";
echo "<ul>";
echo "<li><strong>Network Issue:</strong> Your server might not be able to reach eSewa</li>";
echo "<li><strong>Regional Restriction:</strong> eSewa might be blocked in your region</li>";
echo "<li><strong>DNS Issue:</strong> The eSewa domains might not be resolving</li>";
echo "<li><strong>Firewall:</strong> Your server firewall might be blocking the requests</li>";
echo "</ul>";

echo "<h2>Recommendations</h2>";
echo "<p>1. <strong>Check Network:</strong> Ensure your server can reach eSewa domains</p>";
echo "<p>2. <strong>Use VPN:</strong> If testing from outside Nepal, try using a Nepal-based VPN</p>";
echo "<p>3. <strong>Contact eSewa:</strong> Ask eSewa support about test environment access</p>";
echo "<p>4. <strong>Use Demo Payment:</strong> For now, use the demo payment option</p>";
echo "<p>5. <strong>Test Locally:</strong> Try testing from a Nepal-based server</p>";

echo "<p><a href='diagnose_esewa_issue.php'>Go to Diagnostic Tool</a></p>";
echo "<p><a href='esewa_payment.php'>Back to Payment Page</a></p>";
?> 