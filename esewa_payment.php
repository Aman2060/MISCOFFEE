<?php
session_start();

// Include eSewa helper
require_once 'esewa_helper.php';

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

// Initialize eSewa helper (test mode)
$esewa = new EsewaHelper(true);

// Generate payment data
$success_url = "https://20c76a48ef1c.ngrok-free.app/MISCOFFEE/payment_success.php";
$failure_url = "https://20c76a48ef1c.ngrok-free.app/MISCOFFEE/payment_failed.php";

// Convert USD to NPR (Nepalese Rupees) - eSewa expects NPR amounts
$usd_to_npr_rate = 100;
$total_npr = round($total * $usd_to_npr_rate); // Use round() to ensure integer

$payment_data = $esewa->generatePaymentData(
    $total_npr, // Now integer
    0, // tax_amount
    0, // service_charge
    0, // delivery_charge
    $success_url,
    $failure_url
);

$form_data = $payment_data['form_data'];
$form_data['total_amount'] = intval($form_data['total_amount']); // Ensure integer
$form_data['amt'] = intval($form_data['amt']); // If used
$form_data['txAmt'] = intval($form_data['txAmt']); // If used

$transaction_uuid = $payment_data['transaction_uuid'];

// Use the alternative signature method which follows eSewa documentation exactly
$form_data['signature'] = $esewa->generateSignatureAlternative($form_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - MIS Coffee</title>
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
            margin-bottom: 1rem;
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
        .debug-info {
            background: #f3e5d8;
            border-radius: 8px;
            padding: 0.5rem;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #666;
            text-align: left;
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
                    Total: $<?php echo number_format($total, 2); ?> (NPR <?php echo number_format($total_npr, 2); ?>)
                </div>
                
                <!-- Debug Information (remove in production) -->
                <div class="debug-info">
                    <strong>Debug Info:</strong><br>
                    Transaction UUID: <?php echo $transaction_uuid; ?><br>
                    <strong>Original Amount (USD):</strong> $<?php echo number_format($total, 2); ?><br>
                    <strong>Converted Amount (NPR):</strong> NPR <?php echo number_format($total_npr, 2); ?><br>
                    <strong>Amount Sent to eSewa:</strong> NPR <?php echo number_format($form_data['total_amount'], 2); ?><br>
                    Product Code: <?php echo $form_data['product_code']; ?><br>
                    <strong>Signature:</strong> <?php echo substr($form_data['signature'], 0, 20) . '...'; ?><br>
                    <strong>Signed Fields:</strong> <?php echo $form_data['signed_field_names']; ?><br>
                    <strong>Secret Key:</strong> <?php echo substr($esewa->getTestCredentials()['secret_key'], 0, 10) . '...'; ?><br>
                    <strong>Signature Data:</strong> total_amount=<?php echo $form_data['total_amount']; ?>,transaction_uuid=<?php echo $form_data['transaction_uuid']; ?>,product_code=<?php echo $form_data['product_code']; ?>
                </div>
            </div>

            <div class="esewa-form">
                <div class="payment-info">
                    <h4 style="color: #8d6748; margin-bottom: 0.5rem;">Payment Options:</h4>
                    <p style="margin: 0;">
                        <strong>💳 Demo Payment:</strong> Simulates payment process (Recommended for testing)<br>
                        <strong>🏦 eSewa:</strong> Real payment gateway using latest eSewa ePay API
                    </p>
                </div>

                <!-- Demo Payment Button -->
                <button type="button" class="demo-btn" onclick="processDemoPayment()">
                    💳 Demo Payment - $<?php echo number_format($total, 2); ?> (NPR <?php echo number_format($total_npr, 2); ?>)
                </button>

                <!-- eSewa Payment Form (Latest API) -->
                <form action="<?php echo $payment_data['payment_url']; ?>" method="POST" id="esewaForm">
                    <?php foreach ($form_data as $key => $value): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                    <?php endforeach; ?>
                    
                    <button type="submit" class="esewa-btn">🏦 Pay with eSewa</button>
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
            const successUrl = 'payment_success.php?oid=<?php echo $transaction_uuid; ?>&refId=DEMO_' + Date.now() + '&amt=<?php echo $total_npr; ?>';
            
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
    
    // Add form submission handling
    document.getElementById('esewaForm').addEventListener('submit', function(e) {
        console.log('Submitting eSewa payment form...');
        console.log('Total amount:', <?php echo $form_data['total_amount']; ?>);
        console.log('Transaction UUID:', '<?php echo $transaction_uuid; ?>');
        console.log('Signature:', '<?php echo $form_data['signature']; ?>');
    });
    </script>
</body>
</html>