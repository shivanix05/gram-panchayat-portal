<?php
// config.php is in the same directory (user/), so it's directly accessible
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Gram Panchayat Portal | E-Government Services</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏛️</text></svg>">
</head>
<body>

<!-- ── Top Ribbon ──────────────────────────────────────────── -->
<div class="gov-ribbon">
    <div class="container">
        <span>🇮🇳 भारत सरकार | Government of India — Ministry of Panchayati Raj</span>
        <span>
            <a href="#">Skip to Main Content</a> &nbsp;|&nbsp;
            <a href="#">Screen Reader</a> &nbsp;|&nbsp;
            <a href="#">हिन्दी</a>
        </span>
    </div>
</div>

<!-- ── Header ─────────────────────────────────────────────── -->
<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg"
             alt="Emblem of India" class="header-logo">
        <div class="header-text">
            <h1>🏛️ Digital Gram Panchayat Portal</h1>
            <p>Rampur Gram Panchayat &nbsp;|&nbsp; Madhya Pradesh &nbsp;|&nbsp; Panchayati Raj Department</p>
        </div>
    </div>
</header>

<!-- ── Navigation ─────────────────────────────────────────── -->
<nav class="main-nav">
    <div class="container">
        <ul class="nav-links">
            <li><a href="index.php" class="active">🏠 Home</a></li>
            <li><a href="#schemes">📋 Schemes</a></li>
            <li><a href="#about">ℹ️ About</a></li>
            <li><a href="#services">🔧 Services</a></li>
            <li><a href="#contact">📞 Contact</a></li>
        </ul>
        <div class="nav-cta">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user_dashboard.php" class="btn btn-primary btn-sm">My Dashboard</a>
            <?php else: ?>
                <a href="login.php"    class="btn btn-outline btn-sm">🔑 Login</a>
                <a href="register.php" class="btn btn-primary btn-sm">📝 Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ── Marquee ─────────────────────────────────────────────── -->
<div class="marquee-wrap">
    <p>📢 &nbsp;&nbsp; Pradhan Mantri Awas Yojana applications open till 30 June 2025 &nbsp;&nbsp;|&nbsp;&nbsp;
       🔔 Water Tax payment last date: 15 July 2025 &nbsp;&nbsp;|&nbsp;&nbsp;
       ✅ Gram Sabha meeting scheduled for 20 June 2025 at Panchayat Bhawan &nbsp;&nbsp;|&nbsp;&nbsp;
       📣 New BPL List released — check eligibility now</p>
</div>