<?php
// Sahi path: user folder se ek step bahar nikal kar admin/config.php ko include kiya
require_once '../admin/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL se Scheme ID nikalna
$scheme_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($scheme_id <= 0) {
    // Agar koi galat ID daale to wapas schemes list page par bhej do
    header("Location: view_schemes.php");
    exit();
}

// Database se sirf isi ek scheme ki poori details nikalna
$query = mysqli_prepare($conn, "SELECT * FROM schemes WHERE id = ?");
mysqli_stmt_bind_param($query, "i", $scheme_id);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$scheme = mysqli_fetch_assoc($result);
mysqli_stmt_close($query);

// Agar scheme database me nahi mili toh wapas list par bhej do
if (!$scheme) {
    header("Location: view_schemes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($scheme['title']) ?> | Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="gov-ribbon"><div class="container"><span>🇮🇳 Digital Gram Panchayat Portal — Scheme Specifications</span></div></div>

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
            <li><a href="view_schemes.php" class="active">📋 Schemes</a></li>
        </ul>
        <div class="nav-cta">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="color:rgba(255,255,255,.85); font-size:.9rem; margin-right:10px;">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline btn-sm">🔑 Login</a>
                <a href="register.php" class="btn btn-primary btn-sm">📝 Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container" style="padding-top:30px; padding-bottom:40px; max-width: 800px;">
    
    <a href="view_schemes.php" style="text-decoration:none; color:#052c65; font-weight:bold; display:inline-block; margin-bottom:15px;">← Wapas Yojana List Me Jayein</a>

    <div class="scheme-details-card" style="background:#fff; padding:35px; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid #ff9933;">
        
        <div style="border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 25px;">
            <span style="background:#fff3cd; color:#856404; padding:4px 12px; border-radius:50px; font-size:.8rem; font-weight:bold; text-transform:uppercase; border:1px solid #ffeeba;">Government Scheme</span>
            <h1 style="color:#052c65; margin:10px 0; font-size:1.8rem;"><?= htmlspecialchars($scheme['title']) ?></h1>
            <p style="color:#777; margin:0; font-size:.9rem;">📅 <strong>Launch Date:</strong> <?= date('d M Y', strtotime($scheme['launch_date'])) ?></p>
        </div>

        <div style="margin-bottom:30px;">
            <h3 style="color:#052c65; font-size:1.25rem; border-left:4px solid #ff9933; padding-left:10px; margin-bottom:12px;">📋 Yojana Ki Jankari (Description)</h3>
            <p style="color:#333; line-height:1.7; font-size:1rem; text-align:justify; white-space: pre-line;">
                <?= htmlspecialchars($scheme['description']) ?>
            </p>
        </div>

        <div style="margin-bottom:35px; background:#f8f9fa; padding:20px; border-radius:6px; border:1px solid #e9ecef;">
            <h3 style="color:#137333; font-size:1.25rem; border-left:4px solid #137333; padding-left:10px; margin-top:0; margin-bottom:12px;">✅ Patrata (Eligibility Criteria)</h3>
            <p style="color:#444; line-height:1.6; font-size:.98rem; white-space: pre-line;">
                <?= htmlspecialchars($scheme['eligibility']) ?>
            </p>
        </div>

        <div style="display:flex; gap:15px; flex-wrap:wrap; margin-top:20px; padding-top:20px; border-top:1px solid #eee;">
            <a href="apply_scheme.php?scheme_id=<?= $scheme['id'] ?>" class="btn" style="flex:1; text-align:center; padding:12px; background:#052c65; color:#fff; text-decoration:none; border-radius:4px; font-size:1.05rem; font-weight:bold; box-shadow: 0 4px 6px rgba(5,44,101,0.15);">📝 Apply For This Scheme (Aavedan Karein)</a>
            
            <a href="view_schemes.php" style="padding:12px 20px; background:#eee; color:#333; text-decoration:none; border-radius:4px; font-weight:bold; text-align:center; font-size:.95rem;">Back to List</a>
        </div>

    </div>
</div>

<footer class="site-footer" style="margin-top:40px; padding:15px 0; border-top:1px solid #eee; text-align:center;">
    <div class="container">
        <div class="footer-bottom" style="font-size:.85rem; color:#666;">
            <span>© <?= date('Y') ?> Rampur Gram Panchayat | All Rights Reserved</span> | <span>🇮🇳 Digital India Initiative</span>
        </div>
    </div>
</footer>

</body>
</html>