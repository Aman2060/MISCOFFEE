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

// Get cart items from database
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - MIS Coffee</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .cart-section {
            max-width: 700px;
            margin: 3rem auto 2rem auto;
            background: #fff7ed;
            border-radius: 22px;
            box-shadow: 0 4px 18px rgba(78, 52, 46, 0.10);
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .cart-title {
            color: #a67c52;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }
        .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.7rem;
        }
        .cart-table th, .cart-table td {
            text-align: center;
            padding: 0.7rem 0.5rem;
        }
        .cart-table th {
            color: #8d6748;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .cart-table td {
            background: #f8e7d2;
            border-radius: 12px;
            font-size: 1.05rem;
        }
        .cart-action-btn {
            background: #c97c63;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.3rem 0.8rem;
            font-size: 1rem;
            cursor: pointer;
            margin: 0 0.2rem;
            transition: background 0.2s;
        }
        .cart-action-btn:hover {
            background: #a67c52;
        }
        .cart-empty {
            text-align: center;
            color: #a67c52;
            font-size: 1.2rem;
            margin: 2.5rem 0 2rem 0;
        }
        .cart-total {
            text-align: right;
            font-size: 1.2rem;
            color: #8d6748;
            font-weight: 600;
            margin-top: 1.5rem;
        }
        .cart-buy-btn {
            display: block;
            margin: 2rem auto 0 auto;
            background: linear-gradient(90deg, #c97c63 60%, #a67c52 100%);
            color: #fff;
            border: none;
            border-radius: 18px;
            padding: 0.8rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(201, 124, 99, 0.10);
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }
        .cart-buy-btn:hover {
            background: linear-gradient(90deg, #a67c52 60%, #c97c63 100%);
            transform: scale(1.05);
        }
        .test-link {
            text-align: center;
            margin-top: 1rem;
        }
        .test-link a {
            color: #a67c52;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .test-link a:hover {
            text-decoration: underline;
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
        <section class="cart-section">
            <div class="cart-title">Your Cart</div>
            <?php if (empty($cart_items)): ?>
                <div class="cart-empty">Your cart is empty. <a href="index.php#shop">Add some beans!</a></div>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Bean</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Count</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $index => $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['count']; ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['count'], 2); ?></td>
                                <td>
                                    <button class="cart-action-btn" onclick="addOne(<?php echo $index; ?>)">+</button>
                                    <button class="cart-action-btn" onclick="removeOne(<?php echo $index; ?>)">-</button>
                                    <button class="cart-action-btn" onclick="removeItem(<?php echo $index; ?>)">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-total">Total: $<?php echo number_format($total, 2); ?></div>
                <button class="cart-buy-btn" onclick="buyCart()">Buy Now</button>
            <?php endif; ?>
            
            <div class="test-link">
                <a href="test_cart.php">🔧 Test Cart System</a> | 
                <a href="debug_payment.php">🏦 Debug Payment System</a> | 
                <a href="test_esewa_complete.php">🔍 Complete eSewa Test</a> | 
                <a href="test_esewa_connectivity.php">🌐 eSewa Connectivity</a>
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
    const cartItems = <?php echo json_encode($cart_items); ?>;
    
    function addOne(idx) {
        updateCartItem(cartItems[idx].product, cartItems[idx].quantity, cartItems[idx].count + 1, 'update');
    }
    
    function removeOne(idx) {
        if (cartItems[idx].count > 1) {
            updateCartItem(cartItems[idx].product, cartItems[idx].quantity, cartItems[idx].count - 1, 'update');
        } else {
            removeCartItem(cartItems[idx].product, cartItems[idx].quantity);
        }
    }
    
    function removeItem(idx) {
        removeCartItem(cartItems[idx].product, cartItems[idx].quantity);
    }
    
    function updateCartItem(product, quantity, count, action) {
        fetch('cart_db.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=${action}&product=${encodeURIComponent(product)}&quantity=${encodeURIComponent(quantity)}&count=${count}`
        })
        .then(response => response.text())
        .then(data => {
            console.log('Database response:', data);
            if (data === 'success') {
                location.reload(); // Reload to show updated cart
            } else {
                alert('Error updating cart: ' + data);
            }
        })
        .catch(error => {
            console.error('Error updating database:', error);
            alert('Error updating cart. Please try again.');
        });
    }
    
    function removeCartItem(product, quantity) {
        fetch('cart_db.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&product=${encodeURIComponent(product)}&quantity=${encodeURIComponent(quantity)}`
        })
        .then(response => response.text())
        .then(data => {
            console.log('Database response:', data);
            if (data === 'success') {
                location.reload(); // Reload to show updated cart
            } else {
                alert('Error removing item: ' + data);
            }
        })
        .catch(error => {
            console.error('Error removing from database:', error);
            alert('Error removing item. Please try again.');
        });
    }
    
    function buyCart() {
        // Redirect to eSewa payment page
        window.location.href = 'esewa_payment.php';
    }
    </script>
</body>
</html> 