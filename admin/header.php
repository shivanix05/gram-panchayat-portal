<?php require_once '../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Gram Panchayat</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="gov-ribbon"><div class="container"><span>🇮🇳 Admin Panel — Control Center</span></div></div>
<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem" class="header-logo">
        <div class="header-text"><h1>🏛️ Gram Panchayat — Admin</h1><p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></p></div>
    </div>
</header>
<nav class="main-nav">
    <div class="container">
        <ul class="nav-links"><li><a href="../index.php">🏠 Public View</a></li></ul>
        <div class="nav-cta"><a href="logout.php" class="btn btn-danger btn-sm">🚪 Logout</a></div>
    </div>
</nav>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar" style="background:var(--saffron);">👑</div>
            <h3><?= htmlspecialchars($_SESSION['user_name']) ?></h3>
            <p style="color:var(--saffron);">Sarpanch / Admin</p>
        </div>
        <ul class="sidebar-nav">
            <li><a href="admin_dashboard.php"><span class="icon">🏠</span> Dashboard</a></li>
            <li><a href="manage_schemes.php"><span class="icon">📋</span> Manage Schemes</a></li>
            <li><a href="view_complaints.php"><span class="icon">📝</span> View Complaints</a></li>
            <li><a href="manage_certificates.php"><span class="icon">🏆</span> Certificates</a></li>
            <hr class="sidebar-divider">
            <li><a href="logout.php"><span class="icon">🚪</span> Logout</a></li>
        </ul>
    </aside>
    <main class="main-content">