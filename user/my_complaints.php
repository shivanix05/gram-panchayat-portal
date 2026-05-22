<?php
// FIXED PATH: config.php is in the same directory (user/), no need for ../
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$uid = (int)$_SESSION['user_id'];
$msg_status = '';
$msg_text = '';

// ── Withdraw / Delete complaint ──────────────────────────────
if (isset($_GET['withdraw']) && is_numeric($_GET['withdraw'])) {
    $cid = (int)$_GET['withdraw'];
    
    // Only allow if complaint is Pending and belongs to this user
    $check = mysqli_query($conn, "SELECT id FROM complaints WHERE id=$cid AND user_id=$uid AND status='Pending'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM complaints WHERE id=$cid AND user_id=$uid");
        header('Location: my_complaints.php?status=success&msg=' . urlencode('Complaint withdrawn successfully.'));
        exit();
    } else {
        header('Location: my_complaints.php?status=error&msg=' . urlencode('Cannot withdraw — complaint may already be In Progress or Resolved.'));
        exit();
    }
}

// Extract status messages from redirection parameters safely
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $msg_status = $_GET['status'];
    $msg_text = urldecode($_GET['msg']);
}

// ── Fetch complaints ─────────────────────────────────────────
$complaints = mysqli_query($conn, "SELECT * FROM complaints WHERE user_id=$uid ORDER BY created_at DESC");
$total   = mysqli_num_rows($complaints);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints | Gram Panchayat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("header.php"); ?>

<div class="container" style="padding-top:20px; padding-bottom:40px;">
    <div class="dashboard-layout" style="display: flex; gap: 25px; align-items: flex-start; flex-wrap: wrap;">
        
        <aside class="sidebar" style="flex: 1; min-width: 250px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <div class="sidebar-user" style="text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                <div class="sidebar-user-avatar" style="width: 60px; height: 60px; background: #052c65; color: #fff; font-size: 1.8rem; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 10px; text-transform: uppercase;">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'N', 0, 1)) ?>
                </div>
                <h3 style="margin:0; font-size:1.1rem; color:#333; font-family:'Baloo 2', cursive;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Citizen') ?></h3>
                <p style="margin:3px 0 0; font-size:.85rem; color:#777;">Registered Citizen</p>
            </div>
            
            <ul class="sidebar-nav" style="list-style: none; padding: 0; margin: 0;">
                <li style="margin-bottom: 8px;"><a href="user_dashboard.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">🏠 Dashboard</a></li>
                <li style="margin-bottom: 8px;"><a href="file_complaint.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📝 File Complaint</a></li>
                <li style="margin-bottom: 8px;"><a href="my_complaints.php" style="display: block; padding: 10px 12px; background: #e8f0fe; color: #1a73e8; border-radius: 4px; font-weight: 600; text-decoration: none;">📋 My Complaints</a></li>
                <li style="margin-bottom: 8px;"><a href="apply_certificate.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">🏆 Apply Certificate</a></li>
                <li style="margin-bottom: 8px;"><a href="my_certificates.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📄 My Certificates</a></li>
                <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
                <li style="margin-bottom: 8px;"><a href="view_schemes.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📢 View Schemes</a></li>
                <li><a href="logout.php" style="display: block; padding: 10px 12px; color: #c5221f; border-radius: 4px; text-decoration: none; font-weight: 600;">🚪 Logout</a></li>
            </ul>
        </aside>

        <main class="main-content" style="flex: 3; min-width: 300px;">
            
            <?php if ($msg_status === 'success'): ?>
                <div class="alert alert-success" style="background:#e6f4ea; color:#137333; padding:12px; border-radius:4px; margin-bottom:15px; font-weight:600;">✅ <?= htmlspecialchars($msg_text) ?></div>
            <?php elseif ($msg_status === 'error'): ?>
                <div class="alert alert-error" style="background:#fce8e6; color:#c5221f; padding:12px; border-radius:4px; margin-bottom:15px; font-weight:600;">❌ <?= htmlspecialchars($msg_text) ?></div>
            <?php endif; ?>

            <div class="breadcrumb" style="font-size: .88rem; color: #666; margin-bottom: 15px;">
                <a href="index.php" style="color:#052c65; text-decoration:none; font-weight:600;">Home</a> <span class="sep">›</span>
                <a href="user_dashboard.php" style="color:#052c65; text-decoration:none; font-weight:600;">Dashboard</a> <span class="sep">›</span>
                <span style="color:#333;">My Complaints</span>
            </div>

            <div class="table-wrapper" style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                <div class="page-title-bar" style="display:flex; justify-content:between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:15px;">
                    <div style="flex-grow: 1;">
                        <h2 style="margin:0; color:#052c65; font-family:'Baloo 2', cursive;">📋 Meri Shikayatein</h2>
                        <p style="margin:4px 0 0; color:#666; font-size:.9rem;">Total <?= $total ?> complaint(s) filed by you</p>
                    </div>
                    <a href="file_complaint.php" class="btn btn-primary btn-sm" style="background:#052c65; color:#fff; padding:8px 14px; text-decoration:none; font-size:.88rem; border-radius:4px; font-weight:600;">+ New Complaint</a>
                </div>

                <?php if ($total === 0): ?>
                    <div class="empty-state" style="text-align:center; padding:40px 20px;">
                        <div class="empty-icon" style="font-size:3rem; margin-bottom:10px;">📋</div>
                        <h3 style="margin:0 0 8px; color:#333;">Koi Shikayat Nahi Mili</h3>
                        <p style="margin:0 0 15px; color:#777; font-size:.95rem;">Aapne abhi tak koi complaint file nahi ki hai.</p>
                        <a href="file_complaint.php" class="btn btn-primary" style="background:#ff9933; color:#fff; padding:8px 16px; text-decoration:none; border-radius:4px; font-weight:600; font-size:.9rem;">File First Complaint</a>
                    </div>
                <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="data-table" style="width:100%; border-collapse:collapse; text-align:left; font-size:.92rem;">
                        <thead>
                            <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                                <th style="padding:12px 10px; color:#333; font-weight:600;">#</th>
                                <th style="padding:12px 10px; color:#333; font-weight:600;">Title</th>
                                <th style="padding:12px 10px; color:#333; font-weight:600;">Description</th>
                                <th style="padding:12px 10px; color:#333; font-weight:600;">Filed On</th>
                                <th style="padding:12px 10px; color:#333; font-weight:600;">Status</th>
                                <th style="padding:12px 10px; color:#333; font-weight:600;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; while ($c = mysqli_fetch_assoc($complaints)): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:12px 10px; color:#666;"><?= $i++ ?></td>
                                <td style="padding:12px 10px; font-weight:600; color:#333;"><?= htmlspecialchars($c['title']) ?></td>
                                <td style="padding:12px 10px; max-width:220px; color:#555; font-size:.87rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($c['description']) ?>
                                </td>
                                <td style="padding:12px 10px; color:#666; white-space:nowrap;"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                                <td style="padding:12px 10px;">
                                    <?php
                                        // Dynamic conditional styles configuration
                                        $bg_color = '#fff3cd'; $txt_color = '#856404'; $icon = '⏳';
                                        if($c['status'] === 'In Progress') { $bg_color = '#e8f0fe'; $txt_color = '#1a73e8'; $icon = '🔄'; }
                                        elseif($c['status'] === 'Resolved') { $bg_color = '#e6f4ea'; $txt_color = '#137333'; $icon = '✅'; }
                                    ?>
                                    <span style="background:<?= $bg_color ?>; color:<?= $txt_color ?>; padding:4px 8px; border-radius:4px; font-size:.8rem; font-weight:600; display:inline-block; white-space:nowrap;">
                                        <?= $icon ?> <?= htmlspecialchars($c['status']) ?>
                                    </span>
                                </td>
                                <td style="padding:12px 10px;">
                                    <?php if ($c['status'] === 'Pending'): ?>
                                        <a href="my_complaints.php?withdraw=<?= $c['id'] ?>"
                                           style="background:#fce8e6; color:#c5221f; padding:5px 10px; border-radius:4px; text-decoration:none; font-size:.82rem; font-weight:600; display:inline-block;"
                                           onclick="return confirm('Are you sure you want to withdraw this complaint?')">
                                            🗑️ Withdraw
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size:.8rem; color:#aaa; font-style:italic;">Locked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-info" style="background:#e8f0fe; color:#1a73e8; padding:12px; border-radius:4px; margin-top:20px; font-size:.88rem;">
                ℹ️ <strong>Note:</strong> You can only withdraw complaints that are still "Pending". Once the admin starts reviewing or working on it, withdrawal option gets locked.
            </div>
        </main>
    </div>
</div>

<footer class="site-footer" style="background:#052c65; color:#fff; padding:15px 0; text-align:center; margin-top:40px;">
    <div class="container" style="font-size:.85rem; opacity:0.8;">
        <span>© <?= date('Y') ?> Rampur Gram Panchayat | All Rights Reserved</span> | <span>🇮🇳 Digital India</span>
    </div>
</footer>
</body>
</html>