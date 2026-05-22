<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sahi path se admin folder ki config include ki
require_once 'config.php';

// Agar user pehle se logged in hai to sahi dashboard bhejdo
if (isset($_SESSION['user_id'])) { 
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}

$errors = [];
$success_msg = '';
$old = []; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agar clean() function config me na ho to mysqli_real_escape_string use karega
    function clean_input($conn, $data) {
        return mysqli_real_escape_string($conn, trim($data));
    }

    $old['fullname'] = clean_input($conn, $_POST['fullname'] ?? '');
    $old['email']    = clean_input($conn, $_POST['email']    ?? '');
    $old['phone']    = clean_input($conn, $_POST['phone']    ?? '');
    $password        = $_POST['password']  ?? '';
    $confirm         = $_POST['confirm']   ?? '';

    // ── Validation ─────────────────────────────────────────
    if (empty($old['fullname']))        $errors[] = 'Full name is required.';
    elseif (strlen($old['fullname']) < 3) $errors[] = 'Name must be at least 3 characters.';

    if (empty($old['email']))           $errors[] = 'Email is required.';
    elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    if (empty($old['phone']))           $errors[] = 'Phone number is required.';
    elseif (!preg_match('/^[6-9]\d{9}$/', $old['phone'])) $errors[] = 'Enter a valid 10-digit Indian mobile number.';

    if (empty($password))               $errors[] = 'Password is required.';
    elseif (strlen($password) < 6)      $errors[] = 'Password must be at least 6 characters.';

    if ($password !== $confirm)         $errors[] = 'Passwords do not match.';

    // ── Check duplicate email ───────────────────────────────
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $old['email']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'This email is already registered. Please login.';
        }
        mysqli_stmt_close($stmt);
    }

    // ── Insert ──────────────────────────────────────────────
    if (empty($errors)) {
        $hashed = sha1($password); // SHA1 as per project spec
        $stmt = mysqli_prepare($conn, "INSERT INTO users (fullname, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
        mysqli_stmt_bind_param($stmt, "ssss", $old['fullname'], $old['email'], $old['phone'], $hashed);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = 'Registration successful! You can now login.';
            $old = []; // clear form
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Gram Panchayat Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="gov-ribbon"><div class="container"><span>🇮🇳 Digital Gram Panchayat Portal — Citizen Registration</span></div></div>

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
            <a href="login.php" class="btn btn-outline btn-sm">🔑 Login</a>
            <a href="register.php" class="btn btn-primary btn-sm active">📝 Register</a>
        </div>
    </div>
</nav>

<div class="container" style="padding-top:30px;">
    <div class="form-card" style="background:#fff; max-width:600px; margin:0 auto; padding:25px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.05);">
        <div class="form-card-header" style="text-align:center; margin-bottom:20px;">
            <h2>📝 Nagarik Panjiyan</h2>
            <p style="color:#666;">Create your account to access all Panchayat services</p>
        </div> 
        <div class="form-body">

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" style="background:#e6f4ea; color:#137333; padding:12px; border-radius:4px; margin-bottom:15px;">
                    ✅ <?= $success_msg ?> <a href="login.php" style="font-weight:700; color:#137333; text-decoration:underline;">Login Now →</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="background:#fce8e6; color:#c5221f; padding:12px; border-radius:4px; margin-bottom:15px;">
                    <div>
                    <?php foreach ($errors as $e): ?>
                        ❌ <?= $e ?><br>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($success_msg)): ?>
            <form method="POST" action="register.php" novalidate>

                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Full Name <span style="color:red;">*</span></label>
                    <input type="text" name="fullname" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"
                           placeholder="Enter your full name"
                           value="<?= htmlspecialchars($old['fullname'] ?? '') ?>" required>
                </div>

                <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
                    <div class="form-group" style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Email Address <span style="color:red;">*</span></label>
                        <input type="email" name="email" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"
                               placeholder="you@example.com"
                               value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Mobile Number <span style="color:red;">*</span></label>
                        <input type="tel" name="phone" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"
                               placeholder="10-digit mobile"
                               value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                               maxlength="10" required>
                    </div>
                </div>

                <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
                    <div class="form-group" style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Password <span style="color:red;">*</span></label>
                        <input type="password" name="password" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"
                               placeholder="Minimum 6 characters" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Confirm Password <span style="color:red;">*</span></label>
                        <input type="password" name="confirm" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"
                               placeholder="Re-enter password" required>
                    </div>
                </div>

                <div style="background:#e8f0fe; border-radius:4px; padding:12px; font-size:.85rem; color:#1a73e8; margin-bottom:18px;">
                    🔒 Your information is safe and will be used only for Panchayat services.
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; background:#052c65; color:#fff; border:none; border-radius:4px; font-size:1rem; cursor:pointer; font-weight:600;">📝 Register Now</button>

                <p class="text-center" style="text-align:center; margin-top:15px; font-size:.9rem; color:#666;">
                    Already registered? <a href="login.php" style="font-weight:700; color:#052c65; text-decoration:underline;">Login here →</a>
                </p>
            </form>
            <?php endif; ?>
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