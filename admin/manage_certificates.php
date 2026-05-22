<?php
// Session check aur database connection ke liye config file include ki
require_once 'config.php'; 

// Admin session check ko admin_dashboard.php ke sath match kiya
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Sidebar counts ke liye query executing
$pending_comp = 0;
$pending_certs = 0;

$p_comp_res = mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE status='Pending'");
if ($p_comp_res) {
    $pending_comp = mysqli_fetch_assoc($p_comp_res)['c'];
}

$p_cert_res = mysqli_query($conn, "SELECT COUNT(*) c FROM certificates WHERE status='Pending'");
if ($p_cert_res) {
    $pending_certs = mysqli_fetch_assoc($p_cert_res)['c'];
}

// ── UPDATE STATUS OPERATION (Approve / Reject) ─────────────────
if (isset($_POST['update_status'])) {
    $cert_id = (int)$_POST['cert_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_stmt = mysqli_prepare($conn, "UPDATE certificates SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "si", $status, $cert_id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    
    header("Location: manage_certificates.php");
    exit();
}

// ── DELETE OPERATION ───────────────────────────────────────────
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM certificates WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    
    header("Location: manage_certificates.php");
    exit();
}

// ── READ OPERATION (FIXED: Order by created_at removed to fix SQL exception) ──
$query = "SELECT c.*, u.fullname, u.phone FROM certificates c 
          JOIN users u ON c.user_id = u.id";
$certificates = mysqli_query($conn, $query);
$total = $certificates ? mysqli_num_rows($certificates) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Certificates | Gram Panchayat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="gov-ribbon"><div class="container"><span>🇮🇳 Admin Panel — Manage Certificates</span></div></div>
<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem" class="header-logo">
        <div class="header-text">
            <h1>🏛️ Gram Panchayat — Admin Panel</h1>
            <p>Approve & Issue Citizen Certificates</p>
        </div>
    </div>
</header>
<nav class="main-nav">
    <div class="container">
        <ul class="nav-links"><li><a href="../index.php">🏠 Home</a></li></ul>
        <div class="nav-cta"><a href="logout.php" class="btn btn-danger btn-sm">🚪 Logout</a></div>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar" style="background:var(--saffron, #ff9933);">👑</div>
            <h3><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></h3>
            <p style="color:var(--saffron, #ff9933);">Sarpanch / Admin</p>
        </div>
        <ul class="sidebar-nav">
            <li><a href="admin_dashboard.php"><span class="icon">🏠</span> Dashboard</a></li>
            <li><a href="manage_schemes.php"><span class="icon">📋</span> Manage Schemes</a></li>
            <li><a href="view_complaints.php"><span class="icon">📝</span> View Complaints
                <?php if ($pending_comp > 0): ?>
                    <span style="background:var(--saffron, #ff9933);color:#fff;font-size:.72rem;padding:2px 8px;border-radius:50px;margin-left:auto;"><?= $pending_comp ?></span>
                <?php endif; ?>
            </a></li>
            <li><a href="manage_certificates.php" class="active"><span class="icon">🏆</span> Certificates
                <?php if ($pending_certs > 0): ?>
                    <span style="background:var(--danger, #dc3545);color:#fff;font-size:.72rem;padding:2px 8px;border-radius:50px;margin-left:auto;"><?= $pending_certs ?></span>
                <?php endif; ?>
            </a></li>
            <hr class="sidebar-divider">
            <li><a href="logout.php"><span class="icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="breadcrumb">
            <a href="admin_dashboard.php">Dashboard</a><span class="sep">›</span>
            <span>Manage Certificates</span>
        </div>

        <div class="table-wrapper" style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.05);">
            <div class="page-title-bar">
                <div>
                    <h2 style="margin:0;">🏆 Praman Patra Avedan (Certificates)</h2>
                    <p style="color:#666; margin:5px 0 15px 0;">Review, Approve, or Reject applications submitted by villagers</p>
                </div>
            </div>

            <?php if ($total === 0): ?>
                <div class="empty-state" style="text-align:center; padding:40px; color:#666;">
                    <div class="empty-icon" style="font-size:40px; margin-bottom:10px;">🏆</div>
                    <h3>No Certificate Applications Found</h3>
                    <p>Citizens haven't applied for any certificates yet.</p>
                </div>
            <?php else: ?>
            <table class="data-table" style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:12px;">#</th>
                        <th style="padding:12px;">Citizen Details</th>
                        <th style="padding:12px;">Certificate Type</th>
                        <th style="padding:12px;">Purpose / Reason</th>
                        <th style="padding:12px;">Status</th>
                        <th style="padding:12px; text-align:center;">Action Panel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while ($c = mysqli_fetch_assoc($certificates)): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:12px;"><?= $i++ ?></td>
                        <td style="padding:12px;">
                            <div style="font-weight:600; color:#052c65;"><?= htmlspecialchars($c['fullname']) ?></div>
                            <div style="font-size:.78rem; color:#666;">📞 <?= htmlspecialchars($c['phone']) ?></div>
                        </td>
                        <td style="padding:12px;">
                            <span style="font-weight:600; color:#333; background:#f0f4f9; padding:4px 8px; border-radius:4px; font-size:.85rem;">
                                📄 <?= htmlspecialchars($c['certificate_type'] ?? $c['type'] ?? 'General Certificate') ?>
                            </span>
                        </td>
                        <td style="padding:12px; max-width:220px;">
                            <div style="font-size:.85rem; color:#4a4a4a; line-height:1.4;"><?= htmlspecialchars($c['reason'] ?? $c['description'] ?? 'Not Specified') ?></div>
                        </td>
                        <td style="padding:12px;">
                            <?php 
                            $status = $c['status'] ?? 'Pending';
                            if ($status === 'Approved') {
                                echo '<span class="badge" style="background:#e6f4ea; color:#137333; font-size:.75rem; padding:4px 8px; border-radius:4px; font-weight:600;">✅ Approved</span>';
                            } elseif ($status === 'Rejected') {
                                echo '<span class="badge" style="background:#fce8e6; color:#c5221f; font-size:.75rem; padding:4px 8px; border-radius:4px; font-weight:600;">❌ Rejected</span>';
                            } else {
                                echo '<span class="badge" style="background:#fef7e0; color:#b06000; font-size:.75rem; padding:4px 8px; border-radius:4px; font-weight:600;">⏳ Pending</span>';
                            }
                            ?>
                        </td>
                        <td style="padding:12px;">
                            <div style="display:flex; gap:10px; align-items:center; justify-content:center;">
                                <form method="POST" action="manage_certificates.php" style="display:inline-flex; gap:5px;">
                                    <input type="hidden" name="cert_id" value="<?= $c['id'] ?>">
                                    <select name="status" style="padding:4px; font-size:.8rem; border-radius:4px; border:1px solid #ccc;">
                                        <option value="Pending" <?= $status==='Pending'?'selected':'' ?>>Pending</option>
                                        <option value="Approved" <?= $status==='Approved'?'selected':'' ?>>Approve</option>
                                        <option value="Rejected" <?= $status==='Rejected'?'selected':'' ?>>Reject</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn" style="background:#052c65; color:white; padding:4px 8px; border:none; font-size:.78rem; border-radius:4px; cursor:pointer;">Update</button>
                                </form>

                                <a href="manage_certificates.php?delete=<?= $c['id'] ?>" 
                                   style="color:#dc3545; text-decoration:none; font-size:1.1rem; padding:2px 5px;"
                                   onclick="return confirm('Kya aap is application record ko delete karna chahte hain?')">
                                   🗑️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<footer class="site-footer" style="margin-top:30px; padding:15px 0; border-top:1px solid #eee;"><div class="container"><div class="footer-bottom" style="display:flex; justify-content:between; font-size:.85rem; color:#666;">
    <span>© <?= date('Y') ?> Rampur Gram Panchayat — Admin Panel</span>
    <span>🇮🇳 Digital India</span>
</div></div></footer>
</body>
</html>