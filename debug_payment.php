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

echo "<h2>🔧 Payment System Diagnostic Tool</h2>";

// Test 1: Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p style='color: red;'>❌ User not logged in. Please login first.</p>";
    echo "<p><a href='login.html'>Go to Login</a></p>";
} else {
    echo "<p style='color: green;'>✅ User logged in: " . htmlspecialchars($_SESSION['username']) . "</p>";
    
    // Test 2: Check cart items
    $stmt = $conn->prepare("SELECT product, quantity, price, count FROM cart WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>📦 Cart Items:</h3>";
    if ($result->num_rows > 0) {
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
        
        // Show NPR conversion
        $npr_total = $total * 100;
        echo "<p><strong>Total in NPR:</strong> NPR " . number_format($npr_total, 2) . "</p>";
        
        // Test 3: Test eSewa helper
        echo "<h3>🏦 eSewa Integration Test:</h3>";
        if (file_exists('esewa_helper.php')) {
            require_once 'esewa_helper.php';
            
            try {
                $esewa = new EsewaHelper(true); // test mode
                
                // Test payment data generation
                $payment_data = $esewa->generatePaymentData(
                    $npr_total,
                    0, // tax_amount
                    0, // service_charge
                    0, // delivery_charge
                    "payment_success.php",
                    "payment_failed.php"
                );
                
                echo "<p style='color: green;'>✅ eSewa helper initialized successfully</p>";
                echo "<p><strong>Transaction UUID:</strong> " . $payment_data['transaction_uuid'] . "</p>";
                echo "<p><strong>Payment URL:</strong> " . $payment_data['payment_url'] . "</p>";
                
                // Test signature generation
                $form_data = $payment_data['form_data'];
                $signature = $esewa->generateSignatureAlternative($form_data);
                echo "<p><strong>Signature Generated:</strong> " . substr($signature, 0, 20) . "...</p>";
                
                // Show form data
                echo "<h4>Form Data to be sent to eSewa:</h4>";
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
                echo "<p style='color: red;'>❌ eSewa helper error: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ esewa_helper.php not found</p>";
        }
        
        // Test 4: Test payment URLs
        echo "<h3>🔗 Payment URL Test:</h3>";
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $base_url = dirname($current_url);
        
        echo "<p><strong>Current URL:</strong> " . $current_url . "</p>";
        echo "<p><strong>Base URL:</strong> " . $base_url . "</p>";
        echo "<p><strong>Success URL:</strong> " . $base_url . "/payment_success.php</p>";
        echo "<p><strong>Failure URL:</strong> " . $base_url . "/payment_failed.php</p>";
        
        // Test 5: Quick payment test
        echo "<h3>🧪 Quick Payment Test:</h3>";
        echo "<form method='POST' action='debug_payment.php'>";
        echo "<input type='hidden' name='test_payment' value='1'>";
        echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Payment Flow</button>";
        echo "</form>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ Cart is empty. Add some items first.</p>";
        echo "<p><a href='index.php#shop'>Go to Shop</a></p>";
    }
    $stmt->close();
}

// Handle test payment
if (isset($_POST['test_payment']) && isset($_SESSION['username'])) {
    echo "<h3>🧪 Payment Test Results:</h3>";
    
    // Simulate a test payment
    $test_transaction_id = "TEST_" . time();
    $test_ref_id = "DEMO_" . time();
    $test_amount = 2200; // NPR amount
    
    echo "<p><strong>Test Transaction ID:</strong> " . $test_transaction_id . "</p>";
    echo "<p><strong>Test Reference ID:</strong> " . $test_ref_id . "</p>";
    echo "<p><strong>Test Amount (NPR):</strong> " . number_format($test_amount, 2) . "</p>";
    
    // Test success URL
    $success_url = "payment_success.php?oid=" . $test_transaction_id . "&refId=" . $test_ref_id . "&amt=" . $test_amount;
    echo "<p><a href='" . $success_url . "' style='background: #60bb46; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Success Page</a></p>";
    
    // Test failure URL
    $failure_url = "payment_failed.php?oid=" . $test_transaction_id . "&refId=" . $test_ref_id . "&amt=" . $test_amount . "&error_code=TEST_ERROR&error_message=Test error message";
    echo "<p><a href='" . $failure_url . "' style='background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Failure Page</a></p>";
}

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
</style> 