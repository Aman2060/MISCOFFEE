// Slideshow functionality
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const prevBtn = document.querySelector('.prev');
const nextBtn = document.querySelector('.next');

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(currentSlide);
}

if (prevBtn && nextBtn) {
    prevBtn.addEventListener('click', prevSlide);
    nextBtn.addEventListener('click', nextSlide);
}

// Auto-slide every 5 seconds
setInterval(nextSlide, 5000);

// Initial display
showSlide(currentSlide);

// Login button interaction
const loginBtn = document.querySelector('.login-btn');
if (loginBtn) {
    loginBtn.addEventListener('click', () => {
        window.location.href = 'login.html';
    });
}

function addToCart(name, qty, basePrice) {
    // Calculate actual price based on quantity
    let actualPrice = basePrice;
    if (qty === '200g') {
        actualPrice = basePrice * 0.2; // 200g = 0.2kg
    } else if (qty === '3kg') {
        actualPrice = basePrice * 3; // 3kg = 3x base price
    }
    // 1kg uses base price as is
    
    // Save to database
    saveToDatabase(name, qty, actualPrice, 'add');
    
    alert('Added to cart!');
}

function saveToDatabase(product, quantity, price, action) {
    fetch('cart_db.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&product=${encodeURIComponent(product)}&quantity=${encodeURIComponent(quantity)}&price=${encodeURIComponent(price)}`
    })
    .then(response => response.text())
    .then(data => {
        console.log('Database response:', data);
        if (data === 'success') {
            console.log('Successfully saved to database');
        } else {
            console.error('Database error:', data);
        }
    })
    .catch(error => {
        console.error('Error saving to database:', error);
    });
}

function removeFromDatabase(product, quantity) {
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
    })
    .catch(error => {
        console.error('Error removing from database:', error);
    });
}