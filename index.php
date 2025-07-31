<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIS Coffee - Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">MIS Coffee</div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#explore">Explore Beans</a></li>
                <li><a href="#shop">Shop</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="#contact">Contact</a></li>
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
        <section class="slideshow" id="home">
            <div class="slides">
                <img src="coffee1.jpg" alt="Coffee Bean 1" class="slide active">
                <img src="coffee2.jpg" alt="Coffee Bean 2" class="slide">
                <img src="coffee3.jpg" alt="Coffee Bean 3" class="slide">
            </div>
            <div class="slideshow-controls">
                <span class="prev">&#10094;</span>
                <span class="next">&#10095;</span>
            </div>
            <div class="slideshow-content">
                <h1>Discover the World of Coffee</h1>
                <p>Explore our finest selection of coffee beans from around the globe.</p>
                <button class="explore-btn" onclick="location.href='#explore'">Explore Beans</button>
            </div>
        </section>
        <section class="about" id="about">
            <h2>About Us</h2>
            <p>At MIS Coffee, we are passionate about bringing you the best coffee experience. Our beans are sourced from the finest farms and roasted to perfection.</p>
        </section>
        <section class="explore" id="explore">
            <h2>Explore Our Beans</h2>
            <div class="bean-cards">
                <div class="bean-card">
                    <img src="b4.jpg" alt="Bean 1">
                    <h3>Arabica</h3>
                    <p>Smooth, sweet, and aromatic. Perfect for any time of day.</p>
                </div>
                <div class="bean-card">
                    <img src="b2.jpg" alt="Bean 2">
                    <h3>Beans aroma</h3>
                    <p>Strong, bold, and full-bodied. For those who love a kick.</p>
                </div>
                <div class="bean-card">
                    <img src="b3.jpg" alt="Bean 3">
                    <h3>Bean hora</h3>
                    <p>Exotic, fruity, and unique. A rare treat for coffee lovers.</p>
                </div>
            </div>
        </section>
        <section class="shop styled-shop-section" id="shop">
            <h2>Shop</h2>
            <p class="shop-subtitle">Buy your favorite beans, freshly roasted and delivered to your door.</p>
            <div class="shop-beans">
                <div class="shop-bean-card fade-in">
                    <div class="shop-card-accent"><span>☕</span></div>
                    <div class="shop-bean-img-box"><img class="shop-bean-img" src="b4.jpg" alt="Arabica"></div>
                    <h3>Arabica</h3>
                    <span class="bean-price">$18/kg</span>
                    <p>Smooth, sweet, and aromatic. Perfect for any time of day.</p>
                    <form class="shop-card-actions-row" onsubmit="return false;">
                        <label for="arabica-qty">Qty:</label>
                        <select id="arabica-qty">
                            <option value="200g">200g</option>
                            <option value="1kg">1kg</option>
                            <option value="3kg">3kg</option>
                        </select>
                        <button class="buy-btn">Buy</button>
                        <button class="add-cart-btn" onclick="addToCart('Arabica', document.getElementById('arabica-qty').value, 18)">Add to Cart</button>
                    </form>
                </div>
                <div class="shop-bean-card fade-in">
                    <div class="shop-card-accent"><span>🌱</span></div>
                    <div class="shop-bean-img-box"><img class="shop-bean-img" src="b2.jpg" alt="Beans aroma"></div>
                    <h3>Beans aroma</h3>
                    <span class="bean-price">$15/kg</span>
                    <p>Strong, bold, and full-bodied. For those who love a kick.</p>
                    <form class="shop-card-actions-row" onsubmit="return false;">
                        <label for="aroma-qty">Qty:</label>
                        <select id="aroma-qty">
                            <option value="200g">200g</option>
                            <option value="1kg">1kg</option>
                            <option value="3kg">3kg</option>
                        </select>
                        <button class="buy-btn">Buy</button>
                        <button class="add-cart-btn" onclick="addToCart('Beans aroma', document.getElementById('aroma-qty').value, 15)">Add to Cart</button>
                    </form>
                </div>
                <div class="shop-bean-card fade-in">
                    <div class="shop-card-accent"><span>⭐</span></div>
                    <div class="shop-bean-img-box"><img class="shop-bean-img" src="b3.jpg" alt="Bean hora"></div>
                    <h3>Bean hora</h3>
                    <span class="bean-price">$22/kg</span>
                    <p>Exotic, fruity, and unique. A rare treat for coffee lovers.</p>
                    <form class="shop-card-actions-row" onsubmit="return false;">
                        <label for="hora-qty">Qty:</label>
                        <select id="hora-qty">
                            <option value="200g">200g</option>
                            <option value="1kg">1kg</option>
                            <option value="3kg">3kg</option>
                        </select>
                        <button class="buy-btn">Buy</button>
                        <button class="add-cart-btn" onclick="addToCart('Bean hora', document.getElementById('hora-qty').value, 22)">Add to Cart</button>
                    </form>
                </div>
            </div>
        </section>
        <section class="contact" id="contact">
            <h2>Contact Us</h2>
            <p>Email: info@miscoffee.com | Phone: +1 234 567 890</p>
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
    <script src="script.js"></script>
</body>
</html> 