<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$servername = "localhost";
$db_username = "root"; // Change if needed
$db_password = "";     // Change if needed
$dbname = "website_db";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username && $email && $password) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $stmt->close();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);
            if ($stmt->execute()) {
                $success = "Registration successful! You can now <a href='login.html'>login</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MIS Coffee</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #f8f5f2; }
        .register-container { background: #f3e5d8; max-width: 400px; margin: 80px auto 0 auto; padding: 2.5rem 2rem 2rem 2rem; border-radius: 28px; box-shadow: 0 6px 32px rgba(78, 52, 46, 0.10); display: flex; flex-direction: column; align-items: center; }
        .register-container h2 { color: #8d6748; margin-bottom: 1.5rem; font-size: 2rem; font-weight: 700; }
        .register-form { width: 100%; display: flex; flex-direction: column; gap: 1.2rem; }
        .register-form label { color: #4e342e; font-weight: 500; margin-bottom: 0.3rem; }
        .register-form input { padding: 0.7rem 1rem; border: 1px solid #e0c9b2; border-radius: 12px; font-size: 1rem; background: #fff7ed; color: #4e342e; outline: none; transition: border 0.2s; }
        .register-form input:focus { border: 1.5px solid #a67c52; }
        .register-btn { background: #a67c52; color: #fff; border: none; padding: 0.7rem 0; border-radius: 20px; font-size: 1.1rem; font-weight: 600; cursor: pointer; margin-top: 0.5rem; transition: background 0.2s; }
        .register-btn:hover { background: #8d6748; }
        .register-note { margin-top: 1.5rem; color: #a67c52; font-size: 0.95rem; text-align: center; }
        .success-msg { color: #357a38; background: #fff7ed; border-radius: 10px; padding: 0.7rem 1rem; margin-bottom: 1rem; text-align: center; font-weight: 500; }
        .error-msg { color: #b94a48; background: #fff7ed; border-radius: 10px; padding: 0.7rem 1rem; margin-bottom: 1rem; text-align: center; font-weight: 500; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register for MIS Coffee</h2>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form class="register-form" action="register.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button class="register-btn" type="submit">Register</button>
        </form>
        <div class="register-note">
            <p>Already have an account? <a href="login.html">Login here</a></p>
        </div>
    </div>
</body>
</html> 