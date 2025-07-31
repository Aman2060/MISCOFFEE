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
$transaction_uuid = 'TXN_' . time() . '_' . rand(1000, 9999);
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
        .payment-options {
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
        .demo-btn {
            background: #a67c52;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin: 0.5rem;
        }
        .demo-btn:hover {
            background: #8d6748;
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
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
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
            <div class="payment-title">Complete Your Payment</div>
            
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

            <div class="payment-options">
                <div class="payment-info">
                    <h4 style="color: #8d6748; margin-bottom: 0.5rem;">Payment Options:</h4>
                    <p style="margin: 0;">
                        <strong>💳 Demo Payment:</strong> Simulates payment process (Recommended for testing)<br>
                        <strong>🏦 eSewa (New API):</strong> Latest eSewa ePay API<br>
                        <strong>🏦 eSewa (Legacy):</strong> Traditional eSewa format (More stable)
                    </p>
                </div>

                <!-- Demo Payment Button -->
                <button type="button" class="demo-btn" onclick="processDemoPayment()">
                    💳 Demo Payment - $<?php echo number_format($total, 2); ?>
                </button>

                <!-- eSewa New API Form -->
                <form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST" style="display: inline;">
                    <input type="hidden" name="amount" value="<?php echo $total; ?>">
                    <input type="hidden" name="tax_amount" value="0">
                    <input type="hidden" name="product_service_charge" value="0">
                    <input type="hidden" name="product_delivery_charge" value="0">
                    <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                    <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
                    <input type="hidden" name="product_code" value="EPAYTEST">
                    <input type="hidden" name="success_url" value="http://localhost/MISCOFFEE/payment_success.php">
                    <input type="hidden" name="failure_url" value="http://localhost/MISCOFFEE/payment_failed.php">
                    <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
                    <input type="hidden" name="signature" value="<?php 
                        $signature_data = "total_amount=" . $total . ",transaction_uuid=" . $transaction_uuid . ",product_code=EPAYTEST";
                        echo base64_encode(hash_hmac('sha256', $signature_data, "8gBm/:&EnhH.1/q", true));
                    ?>">
                    <button type="submit" class="esewa-btn">🏦 eSewa (New API)</button>
                </form>

                <!-- eSewa Legacy Form -->
                <form action="https://esewa.com.np/epay/main" method="POST" style="display: inline;">
                    <input type="hidden" name="amt" value="<?php echo $total; ?>">
                    <input type="hidden" name="pdc" value="0">
                    <input type="hidden" name="psc" value="0">
                    <input type="hidden" name="txAmt" value="0">
                    <input type="hidden" name="tAmt" value="<?php echo $total; ?>">
                    <input type="hidden" name="pid" value="<?php echo $transaction_uuid; ?>">
                    <input type="hidden" name="scd" value="EPAYTEST">
                    <input type="hidden" name="su" value="http://localhost/MISCOFFEE/payment_success.php">
                    <input type="hidden" name="fu" value="http://localhost/MISCOFFEE/payment_failed.php">
                    <button type="submit" class="esewa-btn">🏦 eSewa (Legacy)</button>
                </form>
                
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
    function processDemoPayment() {
        console.log('Starting demo payment process...');
        
        // Show loading
        const btn = document.querySelector('.demo-btn');
        btn.textContent = 'Processing Demo Payment...';
        btn.disabled = true;
        
        // Simulate payment processing
        setTimeout(() => {
            console.log('Demo payment completed, redirecting...');
            
            // Create success URL with demo parameters
            const successUrl = 'payment_success.php?oid=<?php echo $transaction_uuid; ?>&refId=DEMO_' + Date.now() + '&amt=<?php echo $total; ?>';
            
            console.log('Redirecting to:', successUrl);
            
            // Clear cart and redirect
            try {
                fetch('cart_db.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clear'
                }).catch(error => {
                    console.log('Cart clearing error (non-critical):', error);
                });
                
                window.location.href = successUrl;
            } catch (error) {
                console.error('Error in demo payment:', error);
                window.location.href = successUrl;
            }
        }, 1500);
    }
    </script>
</body>
</html> 