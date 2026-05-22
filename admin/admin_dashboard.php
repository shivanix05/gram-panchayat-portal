

<?php
// Session check aur database connection ke liye config file
require_once 'config.php'; 

// FIX: Agar admin session nahi hai, ya role admin nahi hai toh direct admin_login.php par bhejo
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Dashboard stats
$total_users      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='user'"))['c'];
$total_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM complaints"))['c'];
$pending_comp     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE status='Pending'"))['c'];
$resolved_comp    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE status='Resolved'"))['c'];
$total_schemes    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM schemes"))['c'];
$pending_certs    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM certificates WHERE status='Pending'"))['c'];

// Recent registrations
$recent_users = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 5");

// Recent complaints
$recent_comp  = mysqli_query($conn, "SELECT c.*, u.fullname FROM complaints c JOIN users u ON c.user_id=u.id ORDER BY c.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Gram Panchayat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="gov-ribbon"><div class="container"><span>🇮🇳 Digital Gram Panchayat Portal — Sarpanch Admin Panel</span></div></div>
<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem" class="header-logo">
        <div class="header-text">
            <h1>🏛️ Gram Panchayat — Admin Panel</h1>
            <p>Sarpanch Control Panel | <?= htmlspecialchars($_SESSION['admin_name']) ?></p>
        </div>
    </div>
</header>
<nav class="main-nav">
    <div class="container">
        <ul class="nav-links">
            <li><a href="admin_dashboard.php" class="active">⚙️ Admin</a></li>
        </ul>
        <div class="nav-cta">
            <span style="color:rgba(255,255,255,.75);font-size:.88rem;">👑 <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="dashboard-layout">
    <!-- Admin Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar" style="background:var(--saffron);">👑</div>
            <h3><?= htmlspecialchars($_SESSION['admin_name']) ?></h3>
            <p style="color:var(--saffron);">Sarpanch / Admin</p>
        </div>
        <ul class="sidebar-nav">
            <li><a href="admin_dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a></li>
            <li><a href="manage_schemes.php"><span class="icon">📋</span> Manage Schemes</a></li>
            <li><a href="view_complaints.php"><span class="icon">📝</span> View Complaints
                <?php if ($pending_comp > 0): ?>
                    <span style="background:var(--saffron);color:#fff;font-size:.72rem;padding:2px 8px;border-radius:50px;margin-left:auto;"><?= $pending_comp ?></span>
                <?php endif; ?>
            </a></li>
            <li><a href="manage_certificates.php"><span class="icon">🏆</span> Certificates
                <?php if ($pending_certs > 0): ?>
                    <span style="background:var(--danger);color:#fff;font-size:.72rem;padding:2px 8px;border-radius:50px;margin-left:auto;"><?= $pending_certs ?></span>
                <?php endif; ?>
            </a></li>
            <hr class="sidebar-divider">
            <li><a href="C:\piyuswh vishwakarma\user\index.php"><span class="icon">🌐</span> View Public Site</a></li>
            <li><a href="logout.php"><span class="icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        
        <div style="margin-bottom:24px;">
            <h2 style="font-family:'Baloo 2',cursive;font-size:1.6rem;color:var(--navy);">
                Namaskar, Sarpanch Ji! 👑
            </h2>
            <p style="color:var(--gray-600);">Here's an overview of your Gram Panchayat's digital activity.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stat-cards" style="grid-template-columns:repeat(3,1fr); display:grid; gap:15px; margin-bottom:20px;">
            <div class="stat-card">
                <div class="stat-card-icon icon-navy" style="background:var(--navy-light);color:var(--navy);">👥</div>
                <div class="stat-card-info">
                    <div class="value"><?= $total_users ?></div>
                    <div class="label">Registered Citizens</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:#FEF3C7;color:#D97706;">⏳</div>
                <div class="stat-card-info">
                    <div class="value"><?= $pending_comp ?></div>
                    <div class="label">Pending Complaints</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:var(--green-light);color:var(--green);">✅</div>
                <div class="stat-card-info">
                    <div class="value"><?= $resolved_comp ?></div>
                    <div class="label">Resolved Complaints</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:var(--saffron-light);color:var(--saffron);">📋</div>
                <div class="stat-card-info">
                    <div class="value"><?= $total_schemes ?></div>
                    <div class="label">Active Schemes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:var(--danger-light);color:var(--danger);">🏆</div>
                <div class="stat-card-info">
                    <div class="value"><?= $pending_certs ?></div>
                    <div class="label">Pending Certificates</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:#F3E8FF;color:#7C3AED;">📊</div>
                <div class="stat-card-info">
                    <div class="value"><?= $total_complaints ?></div>
                    <div class="label">Total Complaints</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-3 mb-3" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:15px; margin-bottom:20px;">
            <a href="manage_schemes.php" class="service-card" style="text-decoration:none; color:inherit;">
                <div class="service-icon icon-navy">📋</div>
                <h3>Add New Scheme</h3>
                <p>Post a new government scheme for citizens</p>
            </a>
            <a href="view_complaints.php" class="service-card" style="text-decoration:none; color:inherit;">
                <div class="service-icon icon-saffron">📝</div>
                <h3>Review Complaints</h3>
                <p><?= $pending_comp ?> pending complaints need attention</p>
            </a>
            <a href="manage_certificates.php" class="service-card" style="text-decoration:none; color:inherit;">
                <div class="service-icon icon-green">🏆</div>
                <h3>Approve Certificates</h3>
                <p><?= $pending_certs ?> certificate applications pending</p>
            </a>
        </div>

        <!-- Recent Data Grid -->
        <div class="grid grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <!-- Recent Users -->
            <div class="table-wrapper">
                <div class="table-header">
                    <h3>👥 Recent Registrations</h3>
                </div>
                <table class="data-table" width="100%" border="1" style="border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr><th>Name</th><th>Phone</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($recent_users)): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['fullname']) ?></td>
                            <td><?= htmlspecialchars($u['phone']) ?></td>
                            <td><?= date('d M', strtotime($u['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Complaints -->
            <div class="table-wrapper">
                <div class="table-header" style="display:flex; justify-content:between; align-items:center;">
                    <h3>📝 Recent Complaints</h3>
                </div>
                <table class="data-table" width="100%" border="1" style="border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr><th>User</th><th>Title</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($c = mysqli_fetch_assoc($recent_comp)): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['fullname']) ?></td>
                            <td style="font-size:.87rem;"><?= htmlspecialchars(substr($c['title'],0,30)) ?>...</td>
                            <td>
                                <?php $cls = ['Pending'=>'badge-pending','In Progress'=>'badge-progress','Resolved'=>'badge-resolved'][$c['status']] ?? 'badge-pending'; ?>
                                <span class="badge <?= $cls ?>"><?= $c['status'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<footer class="site-footer" style="margin-top:40px;"><div class="container"><div class="footer-bottom" style="display:flex; justify-content:space-between;">
    <span>© <?= date('Y') ?> Rampur Gram Panchayat — Admin Panel</span>
    <span>🇮🇳 Digital India</span>
</div></div></footer>
</body>
</html>
