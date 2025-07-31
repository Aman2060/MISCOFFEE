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

// Get cart items and calculate total
$cart_items = [];
$total = 0;
if (isset($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT product, quantity, price, count FROM cart WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['count'];
    }
    $stmt->close();
}

$conn->close();

// Generate unique transaction ID
$txn_id = 'ESEWA_' . time() . '_' . rand(1000, 9999);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eSewa Payment - MIS Coffee</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .payment-section {
            max-width: 600px;
            margin: 3rem auto 2rem auto;
            background: #fff7ed;
            border-radius: 22px;
            box-shadow: 0 4px 18px rgba(78, 52, 46, 0.10);
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .payment-title {
            color: #a67c52;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }
        .order-summary {
            background: #f8e7d2;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e0c7b1;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .total-amount {
            font-size: 1.3rem;
            font-weight: 700;
            color: #8d6748;
            text-align: right;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #a67c52;
        }
        .esewa-form {
            text-align: center;
        }
        .esewa-btn {
            background: #60bb46;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 4px 12px rgba(96, 187, 70, 0.3);
            margin: 0.5rem;
        }
        .esewa-btn:hover {
            background: #4fa03a;
        }
        .back-btn {
            background: #a67c52;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 20px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
            transition: background 0.2s;
        }
        .back-btn:hover {
            background: #8d6748;
        }
        .payment-info {
            background: #f8e7d2;
            border-radius: 12px;
            padding: 1rem;
            margin: 1rem 0;
            text-align: left;
        }
        .payment-info h4 {
            color: #8d6748;
            margin-bottom: 0.5rem;
        }
        .payment-info ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .payment-info li {
            margin-bottom: 0.3rem;
            color: #4e342e;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">MIS Coffee</div>
            <ul class="nav-links">
                <li><a href="index.php#home">Home</a></li>
                <li><a href="index.php#about">About Us</a></li>
                <li><a href="index.php#explore">Explore Beans</a></li>
                <li><a href="index.php#shop">Shop</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="index.php#contact">Contact</a></li>
            </ul>
            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-dropdown">
                    <span class="user-greeting">👋 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="dropdown-content">
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <button class="login-btn" onclick="window.location.href='login.html'">Login</button>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <section class="payment-section">
            <div class="payment-title">eSewa Payment</div>
            
            <div class="order-summary">
                <h3 style="color: #8d6748; margin-bottom: 1rem;">Order Summary</h3>
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['product']); ?> (<?php echo htmlspecialchars($item['quantity']); ?>) x <?php echo $item['count']; ?></span>
                        <span>$<?php echo number_format($item['price'] * $item['count'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="total-amount">
                    Total: $<?php echo number_format($total, 2); ?>
                </div>
            </div>

            <div class="payment-info">
                <h4>eSewa Test Environment</h4>
                <ul>
                    <li><strong>Test Account:</strong> Use any valid email/phone number</li>
                    <li><strong>Test Amount:</strong> Any amount works in test mode</li>
                    <li><strong>Test Card:</strong> Use eSewa's test payment methods</li>
                    <li><strong>Note:</strong> This is a test environment - no real money will be charged</li>
                </ul>
            </div>

            <div class="esewa-form">
                <!-- Method 1: Direct Form Submission -->
                <form action="https://esewa.com.np/epay/main" method="POST" id="esewaForm1">
                    <input type="hidden" name="amt" value="<?php echo number_format($total, 2, '.', ''); ?>">
                    <input type="hidden" name="pdc" value="0">
                    <input type="hidden" name="psc" value="0">
                    <input type="hidden" name="txAmt" value="0">
                    <input type="hidden" name="tAmt" value="<?php echo number_format($total, 2, '.', ''); ?>">
                    <input type="hidden" name="pid" value="<?php echo $txn_id; ?>">
                    <input type="hidden" name="scd" value="EPAYTEST">
                    <input type="hidden" name="su" value="http://localhost/MISCOFFEE/payment_success.php">
                    <input type="hidden" name="fu" value="http://localhost/MISCOFFEE/payment_failed.php">
                    
                    <button type="submit" class="esewa-btn">
                        🏦 Pay with eSewa (Method 1)
                    </button>
                </form>

                <!-- Method 2: JavaScript Submission -->
                <button type="button" class="esewa-btn" onclick="submitEsewaForm()">
                    🏦 Pay with eSewa (Method 2)
                </button>

                <!-- Method 3: New Window -->
                <button type="button" class="esewa-btn" onclick="openEsewaWindow()">
                    🏦 Pay with eSewa (New Window)
                </button>
                
                <button class="back-btn" onclick="window.location.href='cart.php'">Back to Cart</button>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 MIS Coffee. All rights reserved.</p>
            <p>Follow us:
                <a href="#">Instagram</a> |
                <a href="#">Facebook</a> |
                <a href="#">Twitter</a>
            </p>
        </div>
    </footer>

    <script>
    function submitEsewaForm() {
        const form = document.getElementById('esewaForm1');
        if (form) {
            console.log('Submitting eSewa form via JavaScript...');
            form.submit();
        } else {
            alert('Form not found. Please try another method.');
        }
    }

    function openEsewaWindow() {
        const form = document.getElementById('esewaForm1');
        if (form) {
            // Create a new form in a new window
            const newWindow = window.open('', '_blank', 'width=800,height=600');
            newWindow.document.write('<html><head><title>eSewa Payment</title></head><body>');
            newWindow.document.write('<form action="https://esewa.com.np/epay/main" method="POST">');
            newWindow.document.write('<input type="hidden" name="amt" value="<?php echo number_format($total, 2, '.', ''); ?>">');
            newWindow.document.write('<input type="hidden" name="pdc" value="0">');
            newWindow.document.write('<input type="hidden" name="psc" value="0">');
            newWindow.document.write('<input type="hidden" name="txAmt" value="0">');
            newWindow.document.write('<input type="hidden" name="tAmt" value="<?php echo number_format($total, 2, '.', ''); ?>">');
            newWindow.document.write('<input type="hidden" name="pid" value="<?php echo $txn_id; ?>">');
            newWindow.document.write('<input type="hidden" name="scd" value="EPAYTEST">');
            newWindow.document.write('<input type="hidden" name="su" value="http://localhost/MISCOFFEE/payment_success.php">');
            newWindow.document.write('<input type="hidden" name="fu" value="http://localhost/MISCOFFEE/payment_failed.php">');
            newWindow.document.write('<button type="submit" style="padding: 1rem 2rem; background: #60bb46; color: white; border: none; border-radius: 25px; font-size: 1.2rem; cursor: pointer;">Pay with eSewa</button>');
            newWindow.document.write('</form>');
            newWindow.document.write('</body></html>');
            newWindow.document.close();
        } else {
            alert('Form not found. Please try another method.');
        }
    }
    </script>
</body>
</html> 