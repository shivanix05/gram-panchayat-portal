<?php
// Session check aur database connection ke liye config file include ki
require_once 'config.php'; 

// Admin session check ko admin_dashboard.php ke sath match kiya
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Sidebar counts ke liye query executing - Safely checking rows
$pending_comp_res = mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE status='Pending'");
$pending_comp = $pending_comp_res ? mysqli_fetch_assoc($pending_comp_res)['c'] : 0;

$pending_certs_res = mysqli_query($conn, "SELECT COUNT(*) c FROM certificates WHERE status='Pending'");
$pending_certs = $pending_certs_res ? mysqli_fetch_assoc($pending_certs_res)['c'] : 0;

// ── DELETE OPERATION (CRUD - Delete) ───────────────────────────
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM schemes WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    
    header("Location: manage_schemes.php");
    exit();
}

// ── CREATE OPERATION (CRUD - Create) ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    
    $title       = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $eligibility = mysqli_real_escape_string($conn, $_POST['eligibility'] ?? '');
    $launch_date = mysqli_real_escape_string($conn, $_POST['launch_date'] ?? '');

    if (!empty($title) && !empty($description) && !empty($eligibility) && !empty($launch_date)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO schemes (title, description, eligibility, launch_date) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $title, $description, $eligibility, $launch_date);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    header("Location: manage_schemes.php");
    exit();
}

// ── READ OPERATION (CRUD - Read) ─────────────────────────────
$schemes = mysqli_query($conn, "SELECT * FROM schemes ORDER BY created_at DESC");
$total_schemes = $schemes ? mysqli_num_rows($schemes) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schemes | Gram Panchayat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="gov-ribbon"><div class="container"><span>🇮🇳 Digital Gram Panchayat Portal — Sarpanch Admin Panel</span></div></div>
<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem" class="header-logo">
        <div class="header-text">
            <h1>🏛️ Gram Panchayat — Admin Panel</h1>
            <p>Sarpanch Control Panel | <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></p>
        </div>
    </div>
</header>
<nav class="main-nav">
    <div class="container">
        <ul class="nav-links">
            <li><a href="admin_dashboard.php" class="active">⚙️ Admin</a></li>
        </ul>
        <div class="nav-cta">
            <span style="color:rgba(255,255,255,.75);font-size:.88rem;">👑 <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar" style="background:var(--saffron);">👑</div>
            <h3><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></h3>
            <p style="color:var(--saffron);">Sarpanch / Admin</p>
        </div>
        <ul class="sidebar-nav">
            <li><a href="admin_dashboard.php"><span class="icon">🏠</span> Dashboard</a></li>
            <li><a href="manage_schemes.php" class="active"><span class="icon">📋</span> Manage Schemes</a></li>
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
            <li><a href="../index.php"><span class="icon">🌐</span> View Public Site</a></li>
            <li><a href="logout.php"><span class="icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="breadcrumb" style="margin-bottom: 20px;">
            <a href="admin_dashboard.php">Dashboard</a> <span class="sep">›</span>
            <span>Manage Schemes</span>
        </div>

        <div class="table-wrapper mb-3" style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <div class="page-title-bar">
                <div>
                    <h2 style="margin-top:0;">➕ Nayi Yojana Jodein</h2>
                    <p style="color:#666; margin-bottom:20px;">Add a new government scheme for citizens</p>
                </div>
            </div>
            
            <form method="POST" action="manage_schemes.php">
                <input type="hidden" name="action" value="add">
                <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
                    <div class="form-group" style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:bold;">Scheme Title <span style="color:red;">*</span></label>
                        <input type="text" name="title" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" placeholder="e.g., Pradhan Mantri Awas Yojana" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:bold;">Launch Date <span style="color:red;">*</span></label>
                        <input type="date" name="launch_date" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" required>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Description <span style="color:red;">*</span></label>
                    <textarea name="description" class="form-control" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" placeholder="Full details of the scheme..." required></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Eligibility Criteria <span style="color:red;">*</span></label>
                    <textarea name="eligibility" class="form-control" rows="2" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" placeholder="Who is eligible for this scheme..." required></textarea>
                </div>
                
                <button type="submit" class="btn btn-green" style="padding:10px 20px; font-weight:bold; cursor:pointer;">📤 Add Scheme</button>
            </form>
        </div>

        <div class="table-wrapper" style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.05);">
            <div class="table-header" style="border-bottom:1px solid #eee; margin-bottom:15px; padding-bottom:10px;">
                <h3 style="margin:0;">📋 All Schemes (<?= $total_schemes ?>)</h3>
            </div>
            
            <?php if ($total_schemes === 0): ?>
                <div class="empty-state" style="text-align:center; padding:30px; color:#666;">
                    <div class="empty-icon" style="font-size:40px;">📋</div>
                    <h3>No Schemes Added Yet</h3>
                    <p>Use the form above to add your first government scheme.</p>
                </div>
            <?php else: ?>
            <table class="data-table" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Eligibility</th>
                        <th>Launch Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while ($s = mysqli_fetch_assoc($schemes)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td style="font-weight:600; color:var(--navy);"><?= htmlspecialchars($s['title']) ?></td>
                        <td style="font-size:.85rem; max-width:180px;"><?= htmlspecialchars(substr($s['description'], 0, 80)) ?>...</td>
                        <td style="font-size:.85rem; max-width:140px;"><?= htmlspecialchars(substr($s['eligibility'], 0, 60)) ?>...</td>
                        <td><?= date('d M Y', strtotime($s['launch_date'])) ?></td>
                        <td>
                            <a href="update_scheme.php?id=<?= $s['id'] ?>" class="btn btn-sm" style="background:var(--navy); color:white; text-decoration:none; padding:4px 8px;">✏️ Edit</a>
                            <a href="manage_schemes.php?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm" style="text-decoration:none; padding:4px 8px;" onclick="return confirm('Delete this scheme permanently?')">🗑️ Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<footer class="site-footer" style="margin-top:40px;"><div class="container"><div class="footer-bottom" style="display:flex; justify-content:space-between;">
    <span>© <?= date('Y') ?> Rampur Gram Panchayat — Admin Panel</span>
    <span>🇮🇳 Digital India</span>
</div></div></footer>
</body>
</html>