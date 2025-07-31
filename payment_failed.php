<?php 
session_start();

// eSewa payment failure parameters
$pid = $_GET['oid'] ?? '';
$refId = $_GET['refId'] ?? '';
$amt = $_GET['amt'] ?? '';

// Check for error responses from eSewa
$error_code = $_GET['error_code'] ?? '';
$error_message = $_GET['error_message'] ?? '';

// Convert NPR amount back to USD for display (if needed)
$display_amount = $amt;
$display_currency = 'NPR';
if (is_numeric($amt) && $amt > 100) {
    // If amount is large, it's likely in NPR, convert to USD for display
    $npr_to_usd_rate = 100;
    $display_amount = $amt / $npr_to_usd_rate;
    $display_currency = 'USD';
}

// Determine the failure reason
$failure_reason = "Payment was cancelled or failed to process.";
if (!empty($error_code)) {
    $failure_reason = "eSewa returned an error: " . htmlspecialchars($error_code);
    if (!empty($error_message)) {
        $failure_reason .= " - " . htmlspecialchars($error_message);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - MIS Coffee</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .payment-status {
            max-width: 600px;
            margin: 3rem auto 2rem auto;
            background: #fff7ed;
            border-radius: 22px;
            box-shadow: 0 4px 18px rgba(78, 52, 46, 0.10);
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
        }
        .failure-icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        .status-title {
            color: #a67c52;
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        .status-message {
            color: #4e342e;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .transaction-details {
            background: #f8e7d2;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        .transaction-details h3 {
            color: #8d6748;
            margin-bottom: 1rem;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.3rem 0;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .retry-btn {
            background: #a67c52;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 20px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .retry-btn:hover {
            background: #8d6748;
        }
        .cart-btn {
            background: #c97c63;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 20px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .cart-btn:hover {
            background: #a67c52;
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
        <section class="payment-status">
            <div class="failure-icon">❌</div>
            <div class="status-title">Payment Failed</div>
            <div class="status-message">
                <?php echo $failure_reason; ?>
                <br><br>
                Don't worry, your cart items are still saved. You can try the payment again or return to your cart to make changes.
            </div>

            <?php if (!empty($pid) || !empty($refId) || !empty($amt)): ?>
            <div class="transaction-details">
                <h3>Transaction Details</h3>
                <?php if (!empty($pid)): ?>
                <div class="detail-row">
                    <span>Transaction ID:</span>
                    <span><?php echo htmlspecialchars($pid); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($refId)): ?>
                <div class="detail-row">
                    <span>Reference ID:</span>
                    <span><?php echo htmlspecialchars($refId); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($amt)): ?>
                <div class="detail-row">
                    <span>Amount:</span>
                    <span><?php echo $display_currency === 'USD' ? '$' : 'NPR '; ?><?php echo number_format($display_amount, 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($error_code)): ?>
                <div class="detail-row">
                    <span>Error Code:</span>
                    <span style="color: #e74c3c; font-weight: bold;"><?php echo htmlspecialchars($error_code); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="esewa_payment.php" class="retry-btn">Try Payment Again</a>
                <a href="cart.php" class="cart-btn">Back to Cart</a>
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
</body>
</html> 