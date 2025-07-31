<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "website_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>🔍 Complete eSewa Payment Diagnostic</h2>";

// Test 1: Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p style='color: red;'>❌ User not logged in. Please login first.</p>";
    echo "<p><a href='login.html'>Go to Login</a></p>";
    exit();
}

echo "<p style='color: green;'>✅ User logged in: " . htmlspecialchars($_SESSION['username']) . "</p>";

// Test 2: Check cart items
$stmt = $conn->prepare("SELECT product, quantity, price, count FROM cart WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p style='color: orange;'>⚠️ Cart is empty. Please add items first.</p>";
    echo "<p><a href='index.php#shop'>Go to Shop</a></p>";
    exit();
}

echo "<h3>📦 Cart Items:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Product</th><th>Quantity</th><th>Price (USD)</th><th>Count</th><th>Subtotal</th></tr>";
$total = 0;
while ($row = $result->fetch_assoc()) {
    $subtotal = $row['price'] * $row['count'];
    $total += $subtotal;
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['product']) . "</td>";
    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
    echo "<td>$" . number_format($row['price'], 2) . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "<td>$" . number_format($subtotal, 2) . "</td>";
    echo "</tr>";
}
echo "<tr><td colspan='4'><strong>Total:</strong></td><td><strong>$" . number_format($total, 2) . "</strong></td></tr>";
echo "</table>";

// Convert to NPR
$npr_total = round($total * 100);
echo "<p><strong>Total in NPR:</strong> NPR " . number_format($npr_total, 2) . "</p>";

// Test 3: Check eSewa helper
echo "<h3>🏦 eSewa Helper Test:</h3>";
if (!file_exists('esewa_helper.php')) {
    echo "<p style='color: red;'>❌ esewa_helper.php not found</p>";
    exit();
}

require_once 'esewa_helper.php';

try {
    $esewa = new EsewaHelper(true); // test mode
    echo "<p style='color: green;'>✅ eSewa helper initialized</p>";
    
    // Test credentials
    $credentials = $esewa->getTestCredentials();
    echo "<p><strong>Product Code:</strong> " . $credentials['product_code'] . "</p>";
    echo "<p><strong>Secret Key:</strong> " . substr($credentials['secret_key'], 0, 10) . "...</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ eSewa helper error: " . $e->getMessage() . "</p>";
    exit();
}

// Test 4: Generate payment data
echo "<h3>📋 Payment Data Generation:</h3>";
try {
    $success_url = "https://20c76a48ef1c.ngrok-free.app/MISCOFFEE/payment_success.php";
    $failure_url = "https://20c76a48ef1c.ngrok-free.app/MISCOFFEE/payment_failed.php";
    
    $payment_data = $esewa->generatePaymentData(
        $npr_total,
        0, // tax_amount
        0, // service_charge
        0, // delivery_charge
        $success_url,
        $failure_url
    );
    
    echo "<p style='color: green;'>✅ Payment data generated successfully</p>";
    echo "<p><strong>Transaction UUID:</strong> " . $payment_data['transaction_uuid'] . "</p>";
    echo "<p><strong>Payment URL:</strong> " . $payment_data['payment_url'] . "</p>";
    
    $form_data = $payment_data['form_data'];
    
    // Test 5: Generate signature
    echo "<h3>🔐 Signature Generation:</h3>";
    $signature = $esewa->generateSignatureAlternative($form_data);
    echo "<p style='color: green;'>✅ Signature generated: " . substr($signature, 0, 20) . "...</p>";
    
    // Test 6: Show complete form data
    echo "<h3>📝 Complete Form Data:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Value</th><th>Type</th></tr>";
    foreach ($form_data as $key => $value) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($key) . "</td>";
        echo "<td>" . htmlspecialchars($value) . "</td>";
        echo "<td>" . gettype($value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 7: Test URL accessibility
    echo "<h3>🔗 URL Accessibility Test:</h3>";
    
    // Test success URL
    $success_response = @file_get_contents($success_url . "?test=1");
    if ($success_response !== false) {
        echo "<p style='color: green;'>✅ Success URL accessible</p>";
    } else {
        echo "<p style='color: red;'>❌ Success URL not accessible</p>";
    }
    
    // Test failure URL
    $failure_response = @file_get_contents($failure_url . "?test=1");
    if ($failure_response !== false) {
        echo "<p style='color: green;'>✅ Failure URL accessible</p>";
    } else {
        echo "<p style='color: red;'>❌ Failure URL not accessible</p>";
    }
    
    // Test 8: Create test payment form
    echo "<h3>🧪 Test Payment Form:</h3>";
    echo "<form action='" . $payment_data['payment_url'] . "' method='POST' target='_blank'>";
    foreach ($form_data as $key => $value) {
        echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
    }
    echo "<button type='submit' style='background: #60bb46; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;'>🚀 Test eSewa Payment</button>";
    echo "</form>";
    
    // Test 9: Create demo payment test
    echo "<h3>🎭 Demo Payment Test:</h3>";
    $demo_url = "payment_success.php?oid=" . $payment_data['transaction_uuid'] . "&refId=DEMO_" . time() . "&amt=" . $npr_total;
    echo "<p><a href='" . $demo_url . "' style='background: #a67c52; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>🎭 Test Demo Payment</a></p>";
    
    // Test 10: Create failure test
    echo "<h3>❌ Failure Test:</h3>";
    $failure_test_url = "payment_failed.php?oid=" . $payment_data['transaction_uuid'] . "&refId=FAIL_" . time() . "&amt=" . $npr_total . "&error_code=TEST_ERROR&error_message=Test error message";
    echo "<p><a href='" . $failure_test_url . "' style='background: #e74c3c; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>❌ Test Failure Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error generating payment data: " . $e->getMessage() . "</p>";
}

$stmt->close();
$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2 { color: #a67c52; }
h3 { color: #8d6748; margin-top: 20px; }
table { margin: 10px 0; background: white; }
th, td { padding: 8px; text-align: left; }
th { background: #f8e7d2; }
form { margin: 10px 0; }
button { margin: 5px; }
a { color: #a67c52; text-decoration: none; }
a:hover { text-decoration: underline; }
.test-section { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; }
</style> 