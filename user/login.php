<?php
// Sahi path se admin folder ki config include ki
require_once '../admin/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FIXED: Agar user already logged in hai toh use direct home page (index.php) bhejdo
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $hashed = sha1($password);
        // Query sirf 'user' role wale records ko hi check karegi
        $sql    = "SELECT * FROM users WHERE email = '$email' AND password = '$hashed' AND role = 'user' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Sessions store karna
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email']= $user['email'];
            $_SESSION['role']      = $user['role'];

            // FIXED: Login karne ke baad ab redirect direct index.php par hoga
            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Gram Panchayat Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="gov-ribbon"><div class="container"><span>🇮🇳 Digital Gram Panchayat Portal — Secure Login</span></div></div>

<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem" class="header-logo">
        <div class="header-text">
            <h1>🏛️ Digital Gram Panchayat Portal</h1>
            <p>Rampur Gram Panchayat | Madhya Pradesh</p>
        </div>
    </div>
</header>

<nav class="main-nav">
    <div class="container">
        <ul class="nav-links">
            <li><a href="index.php">🏠 Home</a></li>
            <li><a href="view_schemes.php">📋 Schemes</a></li>
        </ul>
        <div class="nav-cta">
            <a href="login.php" class="btn btn-outline btn-sm active">🔑 Login</a>
            <a href="register.php" class="btn btn-primary btn-sm">📝 Register</a>
        </div>
    </div>
</nav>

<div class="container" style="padding-top:30px;">
    <div class="form-card" style="background:#fff; max-width:450px; margin:0 auto; padding:25px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.05);">
        <div class="form-card-header" style="text-align:center; margin-bottom:20px;">
            <h2>🔑 Nagarik Login</h2>
            <p style="color:#666;">Access your Panchayat services securely</p>
        </div>
        <div class="form-body">

            <?php if ($error): ?>
                <div class="alert alert-error" style="background:#fce8e6; color:#c5221f; padding:12px; border-radius:4px; margin-bottom:15px;">
                    ❌ <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Email Address <span style="color:red;">*</span></label>
                    <input type="email" name="email" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"
                           placeholder="Enter your registered email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Password <span style="color:red;">*</span></label>
                    <input type="password" name="password" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"
                           placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; background:#052c65; color:#fff; border:none; border-radius:4px; font-size:1rem; cursor:pointer; font-weight:600;">🔑 Login Karein</button>

                <p class="text-center" style="text-align:center; margin-top:15px; font-size:.9rem; color:#666;">
                    New user? <a href="register.php" style="font-weight:700; color:#052c65; text-decoration:underline;">Register here →</a>
                </p>
            </form>
        </div>
    </div>
</div>

<footer class="site-footer" style="margin-top:30px; padding:15px 0; border-top:1px solid #eee; text-align:center;">
    <div class="container">
        <div class="footer-bottom" style="font-size:.85rem; color:#666;">
            <span>© <?= date('Y') ?> Rampur Gram Panchayat | All Rights Reserved</span> | <span>🇮🇳 Digital India Initiative</span>
        </div>
    </div>
</footer>

</body>
</html>