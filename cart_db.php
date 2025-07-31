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

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "User not logged in";
    exit();
}

$action = $_POST['action'] ?? '';
$username = $_SESSION['username'];

switch ($action) {
    case 'add':
        $product = $_POST['product'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $price = $_POST['price'] ?? '';
        
        // Validate inputs
        if (empty($product) || empty($quantity) || empty($price)) {
            echo "error: Missing required fields";
            exit();
        }
        
        // Check if item already exists
        $stmt = $conn->prepare("SELECT id, count FROM cart WHERE username = ? AND product = ? AND quantity = ?");
        $stmt->bind_param("sss", $username, $product, $quantity);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing item - increment count
            $row = $result->fetch_assoc();
            $new_count = $row['count'] + 1;
            $stmt = $conn->prepare("UPDATE cart SET count = ? WHERE username = ? AND product = ? AND quantity = ?");
            $stmt->bind_param("isss", $new_count, $username, $product, $quantity);
        } else {
            // Insert new item
            $stmt = $conn->prepare("INSERT INTO cart (username, product, quantity, price, count) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("sssd", $username, $product, $quantity, $price);
        }
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }
        $stmt->close();
        break;
        
    case 'update':
        $product = $_POST['product'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $count = $_POST['count'] ?? 1;
        
        $stmt = $conn->prepare("UPDATE cart SET count = ? WHERE username = ? AND product = ? AND quantity = ?");
        $stmt->bind_param("isss", $count, $username, $product, $quantity);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }
        $stmt->close();
        break;
        
    case 'remove':
        $product = $_POST['product'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        
        $stmt = $conn->prepare("DELETE FROM cart WHERE username = ? AND product = ? AND quantity = ?");
        $stmt->bind_param("sss", $username, $product, $quantity);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }
        $stmt->close();
        break;
        
    case 'clear':
        $stmt = $conn->prepare("DELETE FROM cart WHERE username = ?");
        $stmt->bind_param("s", $username);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }
        $stmt->close();
        break;
        
    default:
        echo "Invalid action";
}

$conn->close();
?> 