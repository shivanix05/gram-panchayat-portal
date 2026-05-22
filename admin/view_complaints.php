<?php
// FIX 1: config.php admin folder ke andar hi hai, toh direct include kiya bina kisi ../ ke
require_once 'config.php';

// FIX 2: Dobara session_start() likhne ki zaroorat nahi hai kyunki config.php me chal rha hai, notice hat jayega.

// Admin session validation check ko baaki dashboard panels ke sath match kiya
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ── UPDATE STATUS (Prepared Statement) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $cid    = (int)$_POST['complaint_id'];
    
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
    $allowed = ['Pending', 'In Progress', 'Resolved'];
    
    if (in_array($status, $allowed)) {
        $update_stmt = mysqli_prepare($conn, "UPDATE complaints SET status=? WHERE id=?");
        mysqli_stmt_bind_param($update_stmt, "si", $status, $cid);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }
    
    header("Location: view_complaints.php");
    exit();
}

// ── DELETE COMPLAINT (Prepared Statement) ─────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $cid = (int)$_GET['delete'];
    
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM complaints WHERE id=?");
    mysqli_stmt_bind_param($delete_stmt, "i", $cid);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    
    header("Location: view_complaints.php");
    exit();
}

// ── FILTER & READ OPERATION ───────────────────────────────────
$filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$where  = $filter ? "WHERE c.status='$filter'" : '';

$complaints = mysqli_query($conn, "SELECT c.*, u.fullname, u.email, u.phone
                                    FROM complaints c
                                    JOIN users u ON c.user_id = u.id
                                    $where
                                    ORDER BY c.created_at DESC");
$total = mysqli_num_rows($complaints);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaints | Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="gov-ribbon"><div class="container"><span>🇮🇳 Admin Panel — Manage Complaints</span></div></div>
<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem" class="header-logo">
        <div class="header-text"><h1>🏛️ Gram Panchayat — Admin Panel</h1><p>Manage Citizen Complaints</p></div>
    </div>
</header>
<nav class="main-nav">
    <div class="container">
        <ul class="nav-links"><li><a href="../index.php">🏠 Home</a></li></ul>
        <div class="nav-cta"><a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a></div>
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
            <li><a href="manage_schemes.php"><span class="icon">📋</span> Manage Schemes</a></li>
            <li><a href="view_complaints.php" class="active"><span class="icon">📝</span> View Complaints</a></li>
            <li><a href="manage_certificates.php"><span class="icon">🏆</span> Certificates</a></li>
            <hr class="sidebar-divider">
            <li><a href="../logout.php"><span class="icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php // getFlash(); ?>

        <div class="breadcrumb">
            <a href="admin_dashboard.php">Dashboard</a><span class="sep">›</span>
            <span>View Complaints</span>
        </div>

        <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
            <a href="view_complaints.php" class="btn <?= !$filter ? 'btn-navy' : 'btn-outline' ?>" style="<?= !$filter ? '' : 'color:var(--gray-600);border-color:var(--gray-200);background:var(--white);' ?>">All (<?= $total ?>)</a>
            <?php
                $counts = [];
                $cr = mysqli_query($conn, "SELECT status, COUNT(*) c FROM complaints GROUP BY status");
                if ($cr) {
                    while ($row = mysqli_fetch_assoc($cr)) {
                        $counts[$row['status']] = $row['c'];
                    }
                }
            ?>
            <a href="?status=Pending"    class="btn btn-sm <?= $filter==='Pending'   ? 'btn-primary' : '' ?>" style="<?= $filter!=='Pending'   ? 'background:var(--warning-light);color:#92400E;border-color:#D97706;' : '' ?>">⏳ Pending (<?= $counts['Pending'] ?? 0 ?>)</a>
            <a href="?status=In Progress" class="btn btn-sm <?= $filter==='In Progress' ? 'btn-primary' : '' ?>" style="<?= $filter!=='In Progress' ? 'background:var(--navy-light);color:var(--navy);border-color:var(--navy);' : '' ?>">🔄 In Progress (<?= $counts['In Progress'] ?? 0 ?>)</a>
            <a href="?status=Resolved"   class="btn btn-sm <?= $filter==='Resolved'  ? 'btn-primary' : '' ?>" style="<?= $filter!=='Resolved'  ? 'background:var(--green-light);color:var(--green);border-color:var(--green);' : '' ?>">✅ Resolved (<?= $counts['Resolved'] ?? 0 ?>)</a>
        </div>

        <div class="table-wrapper">
            <div class="page-title-bar">
                <div>
                    <h2>📝 Sabhi Shikayatein</h2>
                    <p>Showing <?= $total ?> complaint(s) <?= $filter ? "with status: $filter" : '' ?></p>
                </div>
            </div>

            <?php if ($total === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">✅</div>
                    <h3><?= $filter ? "No '$filter' Complaints" : 'No Complaints Found' ?></h3>
                    <p><?= $filter ? 'Good news — no complaints in this category!' : 'No complaints have been filed yet.' ?></p>
                </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Citizen</th>
                        <th>Title & Description</th>
                        <th>Filed On</th>
                        <th>Current Status</th>
                        <th>Update Status</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while ($c = mysqli_fetch_assoc($complaints)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <div style="font-weight:600;"><?= htmlspecialchars($c['fullname']) ?></div>
                            <div style="font-size:.78rem;color:var(--gray-600);"><?= htmlspecialchars($c['phone']) ?></div>
                        </td>
                        <td style="max-width:220px;">
                            <div style="font-weight:600;margin-bottom:3px;"><?= htmlspecialchars($c['title']) ?></div>
                            <div style="font-size:.82rem;color:var(--gray-600);"><?= htmlspecialchars(substr($c['description'],0,90)) ?>...</div>
                        </td>
                        <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                        <td>
                            <?php
                                $cls  = ['Pending'=>'badge-pending','In Progress'=>'badge-progress','Resolved'=>'badge-resolved'][$c['status']] ?? 'badge-pending';
                                $icon = ['Pending'=>'⏳','In Progress'=>'🔄','Resolved'=>'✅'][$c['status']] ?? '⏳';
                            ?>
                            <span class="badge <?= $cls ?>"><?= $icon ?> <?= htmlspecialchars($c['status']) ?></span>
                        </td>
                        <td>
                            <form method="POST" action="view_complaints.php" style="display:flex;gap:6px;align-items:center;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
                                <select name="status" class="form-control" style="padding:5px 10px;font-size:.82rem;min-width:130px;">
                                    <option value="Pending"     <?= $c['status']==='Pending'     ? 'selected' : '' ?>>⏳ Pending</option>
                                    <option value="In Progress" <?= $c['status']==='In Progress' ? 'selected' : '' ?>>🔄 In Progress</option>
                                    <option value="Resolved"    <?= $c['status']==='Resolved'    ? 'selected' : '' ?>>✅ Resolved</option>
                                </select>
                                <button type="submit" class="btn btn-green btn-sm">Save</button>
                            </form>
                        </td>
                        <td>
                            <a href="view_complaints.php?delete=<?= $c['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this complaint permanently? This is irreversible.')">
                                🗑️ Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<footer class="site-footer"><div class="container"><div class="footer-bottom">
    <span>© <?= date('Y') ?> Rampur Gram Panchayat — Admin Panel</span>
    <span>🇮🇳 Digital India</span>
</div></div></footer>
</body>
</html>