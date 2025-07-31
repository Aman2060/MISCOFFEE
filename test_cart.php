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

echo "<h2>Cart System Test</h2>";

// Test 1: Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p style='color: red;'>❌ User not logged in. Please login first.</p>";
    echo "<p><a href='login.html'>Go to Login</a></p>";
} else {
    echo "<p style='color: green;'>✅ User logged in: " . htmlspecialchars($_SESSION['username']) . "</p>";
    
    // Test 2: Check cart table structure
    $result = $conn->query("DESCRIBE cart");
    if ($result) {
        echo "<h3>Cart Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test 3: Show current cart items
    $stmt = $conn->prepare("SELECT product, quantity, price, count FROM cart WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>Current Cart Items:</h3>";
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
    } else {
        echo "<p style='color: orange;'>⚠️ Cart is empty</p>";
    }
    $stmt->close();
    
    // Test 4: Test add to cart functionality
    echo "<h3>Test Add to Cart:</h3>";
    echo "<form method='POST' action='test_cart.php'>";
    echo "<input type='hidden' name='test_action' value='add'>";
    echo "<select name='test_product'>";
    echo "<option value='Arabica'>Arabica</option>";
    echo "<option value='Beans aroma'>Beans aroma</option>";
    echo "<option value='Bean hora'>Bean hora</option>";
    echo "</select>";
    echo "<select name='test_quantity'>";
    echo "<option value='200g'>200g</option>";
    echo "<option value='1kg'>1kg</option>";
    echo "<option value='3kg'>3kg</option>";
    echo "</select>";
    echo "<input type='submit' value='Add Test Item'>";
    echo "</form>";
}

// Handle test actions
if (isset($_POST['test_action']) && $_POST['test_action'] === 'add' && isset($_SESSION['username'])) {
    $product = $_POST['test_product'] ?? '';
    $quantity = $_POST['test_quantity'] ?? '';
    
    // Calculate price based on quantity
    $base_price = 0;
    if ($product === 'Arabica') $base_price = 18;
    elseif ($product === 'Beans aroma') $base_price = 15;
    elseif ($product === 'Bean hora') $base_price = 22;
    
    $actual_price = $base_price;
    if ($quantity === '200g') {
        $actual_price = $base_price * 0.2;
    } elseif ($quantity === '3kg') {
        $actual_price = $base_price * 3;
    }
    
    // Add to cart
    $stmt = $conn->prepare("SELECT id, count FROM cart WHERE username = ? AND product = ? AND quantity = ?");
    $stmt->bind_param("sss", $_SESSION['username'], $product, $quantity);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing item
        $row = $result->fetch_assoc();
        $new_count = $row['count'] + 1;
        $stmt = $conn->prepare("UPDATE cart SET count = ? WHERE username = ? AND product = ? AND quantity = ?");
        $stmt->bind_param("isss", $new_count, $_SESSION['username'], $product, $quantity);
    } else {
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO cart (username, product, quantity, price, count) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("sssd", $_SESSION['username'], $product, $quantity, $actual_price);
    }
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Test item added successfully!</p>";
        echo "<script>location.reload();</script>";
    } else {
        echo "<p style='color: red;'>❌ Error adding test item: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
form { margin: 10px 0; }
select, input[type="submit"] { margin: 5px; padding: 5px; }
</style> 