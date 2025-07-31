<?php
session_start();

// Test eSewa connection with proper POST request
$test_url = "https://esewa.com.np/epay/main";
$test_data = [
    'amt' => '100',
    'pdc' => '0',
    'psc' => '0',
    'txAmt' => '0',
    'tAmt' => '100',
    'pid' => 'TEST_' . time(),
    'scd' => 'EPAYTEST',
    'su' => 'http://localhost/MISCOFFEE/payment_success.php',
    'fu' => 'http://localhost/MISCOFFEE/payment_failed.php'
];

// Test if we can reach eSewa with POST request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($test_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

// Check if response contains eSewa content
$is_esewa_page = (strpos($response, 'esewa') !== false || strpos($response, 'ESEWA') !== false);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eSewa Test - MIS Coffee</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .test-section {
            max-width: 800px;
            margin: 3rem auto 2rem auto;
            background: #fff7ed;
            border-radius: 22px;
            box-shadow: 0 4px 18px rgba(78, 52, 46, 0.10);
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .test-title {
            color: #a67c52;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }
        .test-info {
            background: #f8e7d2;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .test-info h3 {
            color: #8d6748;
            margin-bottom: 1rem;
        }
        .test-item {
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e0c7b1;
        }
        .test-item:last-child {
            border-bottom: none;
        }
        .status-success {
            color: #60bb46;
            font-weight: bold;
        }
        .status-error {
            color: #e74c3c;
            font-weight: bold;
        }
        .test-btn {
            background: #60bb46;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            margin: 1rem;
            transition: background 0.2s;
        }
        .test-btn:hover {
            background: #4fa03a;
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
        <section class="test-section">
            <div class="test-title">eSewa Integration Test</div>
            
            <div class="test-info">
                <h3>Connection Status</h3>
                <div class="test-item">
                    <strong>HTTP Status Code:</strong> 
                    <span class="<?php echo $http_code == 200 ? 'status-success' : 'status-error'; ?>">
                        <?php echo $http_code; ?>
                    </span>
                </div>
                <div class="test-item">
                    <strong>cURL Error:</strong> 
                    <span class="<?php echo empty($curl_error) ? 'status-success' : 'status-error'; ?>">
                        <?php echo empty($curl_error) ? 'None' : $curl_error; ?>
                    </span>
                </div>
                <div class="test-item">
                    <strong>Response Length:</strong> 
                    <span><?php echo strlen($response); ?> characters</span>
                </div>
                <div class="test-item">
                    <strong>Final URL:</strong> 
                    <span><?php echo $final_url; ?></span>
                </div>
                <div class="test-item">
                    <strong>eSewa Page Detected:</strong> 
                    <span class="<?php echo $is_esewa_page ? 'status-success' : 'status-error'; ?>">
                        <?php echo $is_esewa_page ? 'Yes' : 'No'; ?>
                    </span>
                </div>
                <div class="test-item">
                    <strong>Test URL:</strong> 
                    <span><?php echo $test_url; ?></span>
                </div>
            </div>

            <div class="test-info">
                <h3>Alternative Test Methods</h3>
                <p>If the main test fails, try these alternatives:</p>
                
                <div style="margin: 1rem 0;">
                    <a href="https://esewa.com.np" target="_blank" class="test-btn" style="text-decoration: none; display: inline-block;">
                        Test eSewa Website (Opens in new tab)
                    </a>
                </div>
                
                <div style="margin: 1rem 0;">
                    <a href="https://esewa.com.np/epay/main" target="_blank" class="test-btn" style="text-decoration: none; display: inline-block; background: #a67c52;">
                        Test eSewa Payment Page (Opens in new tab)
                    </a>
                </div>
                
                <p style="font-size: 0.9rem; color: #666; margin-top: 1rem;">
                    <strong>Note:</strong> The 400 error might be normal if eSewa expects specific parameters. 
                    The important thing is whether you can access eSewa's website directly.
                </p>
            </div>

            <div class="test-info">
                <h3>Test Payment Form</h3>
                <p>This is a simple test to check if eSewa integration works:</p>
                
                <form action="https://esewa.com.np/epay/main" method="POST">
                    <input type="hidden" name="amt" value="100">
                    <input type="hidden" name="pdc" value="0">
                    <input type="hidden" name="psc" value="0">
                    <input type="hidden" name="txAmt" value="0">
                    <input type="hidden" name="tAmt" value="100">
                    <input type="hidden" name="pid" value="TEST_<?php echo time(); ?>">
                    <input type="hidden" name="scd" value="EPAYTEST">
                    <input type="hidden" name="su" value="http://localhost/MISCOFFEE/payment_success.php">
                    <input type="hidden" name="fu" value="http://localhost/MISCOFFEE/payment_failed.php">
                    
                    <button type="submit" class="test-btn">
                        Test eSewa Payment ($100)
                    </button>
                </form>
            </div>

            <div class="test-info">
                <h3>Troubleshooting</h3>
                <ul>
                    <li>Make sure you're using the correct eSewa test environment</li>
                    <li>Check if your server can reach esewa.com.np</li>
                    <li>Verify that all form fields are properly set</li>
                    <li>Ensure your success/failure URLs are accessible</li>
                </ul>
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