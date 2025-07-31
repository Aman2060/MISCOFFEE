<?php
session_start();

// Include eSewa helper
require_once 'esewa_helper.php';

// eSewa payment verification
$pid = $_GET['oid'] ?? '';
$refId = $_GET['refId'] ?? '';
$amt = $_GET['amt'] ?? '';

// Check for error responses from eSewa
$error_code = $_GET['error_code'] ?? '';
$error_message = $_GET['error_message'] ?? '';

// Check if this is a demo payment
$is_demo = (strpos($refId, 'DEMO_') === 0);

$success = false;
$transaction_status = 'UNKNOWN';
$transaction_details = [];

// Convert NPR amount back to USD for display (if needed)
$display_amount = $amt;
$display_currency = 'NPR';
if (is_numeric($amt) && $amt > 100) {
    // If amount is large, it's likely in NPR, convert to USD for display
    $npr_to_usd_rate = 100;
    $display_amount = $amt / $npr_to_usd_rate;
    $display_currency = 'USD';
}

// Check if there's an error response from eSewa
if (!empty($error_code)) {
    $success = false;
    $transaction_status = 'FAILED';
    $transaction_details = [
        'status' => 'FAILED',
        'error_code' => $error_code,
        'error_message' => $error_message,
        'ref_id' => $refId,
        'product_code' => 'EPAYTEST',
        'total_amount' => $amt,
        'transaction_uuid' => $pid
    ];
} elseif ($is_demo) {
    // Demo payment - always successful
    $success = true;
    $transaction_status = 'COMPLETE';
    $transaction_details = [
        'status' => 'COMPLETE',
        'ref_id' => $refId,
        'product_code' => 'DEMO',
        'total_amount' => $amt,
        'transaction_uuid' => $pid
    ];
    
    // Clear cart from database
    if (isset($_SESSION['username'])) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "website_db";

        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if (!$conn->connect_error) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE username = ?");
            $stmt->bind_param("s", $_SESSION['username']);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
    }
} else {
    // Real eSewa payment - verify with eSewa using helper class
    $esewa = new EsewaHelper(true); // true for test mode
    
    // Get transaction details using the helper
    $transaction_details = $esewa->getTransactionDetails($pid, $amt);
    
    if ($transaction_details['success']) {
        $transaction_status = $transaction_details['status'];
        
        // Check if payment is successful
        if ($transaction_status === 'COMPLETE') {
            $success = true;
            
            // Clear cart from database
            if (isset($_SESSION['username'])) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "website_db";

                $conn = new mysqli($servername, $username, $password, $dbname);
                
                if (!$conn->connect_error) {
                    $stmt = $conn->prepare("DELETE FROM cart WHERE username = ?");
                    $stmt->bind_param("s", $_SESSION['username']);
                    $stmt->execute();
                    $stmt->close();
                }
                $conn->close();
            }
        } else {
            // Payment failed or pending
            $success = false;
        }
    } else {
        // Fallback to old verification method if new API fails
        $url = "https://esewa.com.np/epay/transrec";
        $data = [
            'amt' => $amt,
            'rid' => $refId,
            'pid' => $pid,
            'scd' => 'EPAYTEST'
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        if (strpos($response, 'Success') !== false) {
            $success = true;
            $transaction_status = 'COMPLETE';
            $transaction_details = [
                'status' => 'COMPLETE',
                'ref_id' => $refId,
                'product_code' => 'EPAYTEST',
                'total_amount' => $amt,
                'transaction_uuid' => $pid
            ];
            
            // Clear cart from database
            if (isset($_SESSION['username'])) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "website_db";

                $conn = new mysqli($servername, $username, $password, $dbname);
                
                if (!$conn->connect_error) {
                    $stmt = $conn->prepare("DELETE FROM cart WHERE username = ?");
                    $stmt->bind_param("s", $_SESSION['username']);
                    $stmt->execute();
                    $stmt->close();
                }
                $conn->close();
            }
        } else {
            $transaction_status = 'FAILED';
            $transaction_details = [
                'status' => 'FAILED',
                'ref_id' => null,
                'product_code' => 'EPAYTEST',
                'total_amount' => $amt,
                'transaction_uuid' => $pid
            ];
        }
    }
}

// Debug information
$debug_info = [
    'GET_params' => $_GET,
    'success' => $success,
    'transaction_status' => $transaction_status,
    'transaction_details' => $transaction_details
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - MIS Coffee</title>
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
        .success-icon {
            font-size: 4rem;
            color: #60bb46;
            margin-bottom: 1rem;
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
        .home-btn {
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
        .home-btn:hover {
            background: #8d6748;
        }
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .status-complete {
            background: #d4edda;
            color: #155724;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-unknown {
            background: #e2e3e5;
            color: #383d41;
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
            <?php if ($success): ?>
                <div class="success-icon">✅</div>
                <div class="status-title">
                    <?php if ($is_demo): ?>
                        Demo Payment Successful!
                    <?php else: ?>
                        Payment Successful!
                    <?php endif; ?>
                </div>
                <div class="status-message">
                    <?php if ($is_demo): ?>
                        Demo payment completed successfully! Your order has been confirmed and will be processed soon.
                        This was a test payment - no real money was charged.
                    <?php else: ?>
                        Thank you for your purchase! Your order has been confirmed and will be processed soon.
                        You will receive an email confirmation shortly.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="failure-icon">❌</div>
                <div class="status-title">Payment Failed</div>
                <div class="status-message">
                    <?php if (!empty($error_code)): ?>
                        eSewa returned an error: <?php echo htmlspecialchars($error_code); ?>
                        <?php if (!empty($error_message)): ?>
                            - <?php echo htmlspecialchars($error_message); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        Sorry, your payment could not be processed. Please try again or contact our support team.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="transaction-details">
                <h3>Transaction Details</h3>
                <div class="detail-row">
                    <span>Transaction ID:</span>
                    <span><?php echo htmlspecialchars($pid); ?></span>
                </div>
                <div class="detail-row">
                    <span>Reference ID:</span>
                    <span><?php echo htmlspecialchars($refId); ?></span>
                </div>
                <div class="detail-row">
                    <span>Amount:</span>
                    <span><?php echo $display_currency === 'USD' ? '$' : 'NPR '; ?><?php echo number_format($display_amount, 2); ?></span>
                </div>
                <div class="detail-row">
                    <span>Status:</span>
                    <span class="status-badge <?php 
                        echo $transaction_status === 'COMPLETE' ? 'status-complete' : 
                            ($transaction_status === 'PENDING' ? 'status-pending' : 
                            ($transaction_status === 'FAILED' ? 'status-failed' : 'status-unknown')); 
                    ?>">
                        <?php echo htmlspecialchars($transaction_status); ?>
                    </span>
                </div>
                <?php if (!empty($error_code)): ?>
                <div class="detail-row">
                    <span>Error Code:</span>
                    <span style="color: #e74c3c; font-weight: bold;"><?php echo htmlspecialchars($error_code); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($is_demo): ?>
                <div class="detail-row">
                    <span>Payment Type:</span>
                    <span style="color: #60bb46; font-weight: bold;">Demo Payment</span>
                </div>
                <?php else: ?>
                <div class="detail-row">
                    <span>Payment Type:</span>
                    <span style="color: #60bb46; font-weight: bold;">eSewa Payment</span>
                </div>
                <?php endif; ?>
                <?php if (isset($transaction_details['ref_id']) && $transaction_details['ref_id']): ?>
                <div class="detail-row">
                    <span>eSewa Reference:</span>
                    <span><?php echo htmlspecialchars($transaction_details['ref_id']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <a href="index.php" class="home-btn">Back to Home</a>
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