<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// config.php is in the same directory (user/), so it's directly accessible
require_once 'config.php';

// Auth guard strict check - agar login nahi hai to direct login.php par bhejo
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    $_SESSION['flash_message'] = 'Please login to access your dashboard.';
    $_SESSION['flash_type'] = 'error';
    header('Location: login.php');
    exit();
}

$uid = (int)$_SESSION['user_id'];

// Safe SQL aggregations for stats cards
$total_complaints_res   = mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE user_id=$uid");
$total_complaints       = $total_complaints_res ? mysqli_fetch_assoc($total_complaints_res)['c'] : 0;

$pending_complaints_res = mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE user_id=$uid AND status='Pending'");
$pending_complaints     = $pending_complaints_res ? mysqli_fetch_assoc($pending_complaints_res)['c'] : 0;

$resolved_complaints_res= mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE user_id=$uid AND status='Resolved'");
$resolved_complaints    = $resolved_complaints_res ? mysqli_fetch_assoc($resolved_complaints_res)['c'] : 0;

$total_certs_res        = mysqli_query($conn, "SELECT COUNT(*) c FROM certificates WHERE user_id=$uid");
$total_certs            = $total_certs_res ? mysqli_fetch_assoc($total_certs_res)['c'] : 0;

// Fetch last 5 complaints for recent table
$recent = mysqli_query($conn, "SELECT * FROM complaints WHERE user_id=$uid ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | Digital Gram Panchayat</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏛️</text></svg>">
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
                <li style="margin-bottom: 8px;">
                    <a href="user_dashboard.php" class="active" style="display: block; padding: 10px 12px; background: #e8f0fe; color: #1a73e8; border-radius: 4px; font-weight: 600; text-decoration: none;">
                        <span class="icon">🏠</span> Dashboard
                    </a>
                </li>
                <li style="margin-bottom: 8px;">
                    <a href="file_complaint.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">
                        <span class="icon">📝</span> File Complaint
                    </a>
                </li>
                <li style="margin-bottom: 8px;">
                    <a href="my_complaints.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">
                        <span class="icon">📋</span> My Complaints
                    </a>
                </li>
                <li style="margin-bottom: 8px;">
                    <a href="apply_certificate.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">
                        <span class="icon">🏆</span> Apply Certificate
                    </a>
                </li>
                <li style="margin-bottom: 8px;">
                    <a href="my_certificates.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">
                        <span class="icon">📄</span> My Certificates
                    </a>
                </li>
                <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
                <li style="margin-bottom: 8px;">
                    <a href="view_schemes.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">
                        <span class="icon">📢</span> View Schemes
                    </a>
                </li>
                <li>
                    <a href="logout.php" style="display: block; padding: 10px 12px; color: #c5221f; border-radius: 4px; text-decoration: none; font-weight: 600;">
                        <span class="icon">🚪</span> Logout
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content" style="flex: 3; min-width: 300px;">
            <div class="breadcrumb" style="font-size: .88rem; color: #666; margin-bottom: 15px;">
                <a href="index.php" style="color:#052c65; text-decoration:none; font-weight:600;">Home</a>
                <span style="margin: 0 5px;">&rsaquo;</span>
                <span style="color:#333;">Dashboard</span>
            </div>

            <div style="margin-bottom:24px; background:#fff; padding:20px; border-radius:8px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); border-top: 3px solid #052c65;">
                <h2 style="font-size:1.6rem; color:#052c65; margin:0 0 5px 0; font-family:'Baloo 2', cursive;">
                    Namaskar, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Nagarik')[0]) ?>! 🙏
                </h2>
                <p style="color:#666; margin:0;">Welcome to your Digital Panchayat dashboard. Manage your records, complaints, and certificate requests instantly.</p>
            </div>

            <div class="stat-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div class="stat-card" style="background:#fff; padding:15px; border-radius:8px; display:flex; align-items:center; gap:15px; box-shadow:0 2px 6px rgba(0,0,0,0.03); border-left:4px solid #ff9933;">
                    <div style="font-size:1.8rem;">📝</div>
                    <div>
                        <div style="font-size:1.5rem; font-weight:700; color:#333;"><?= $total_complaints ?></div>
                        <div style="font-size:.85rem; color:#666;">Total Complaints</div>
                    </div>
                </div>
                <div class="stat-card" style="background:#fff; padding:15px; border-radius:8px; display:flex; align-items:center; gap:15px; box-shadow:0 2px 6px rgba(0,0,0,0.03); border-left:4px solid #d97706;">
                    <div style="font-size:1.8rem;">⏳</div>
                    <div>
                        <div style="font-size:1.5rem; font-weight:700; color:#333;"><?= $pending_complaints ?></div>
                        <div style="font-size:.85rem; color:#666;">Pending</div>
                    </div>
                </div>
                <div class="stat-card" style="background:#fff; padding:15px; border-radius:8px; display:flex; align-items:center; gap:15px; box-shadow:0 2px 6px rgba(0,0,0,0.03); border-left:4px solid #137333;">
                    <div style="font-size:1.8rem;">✅</div>
                    <div>
                        <div style="font-size:1.5rem; font-weight:700; color:#333;"><?= $resolved_complaints ?></div>
                        <div style="font-size:.85rem; color:#666;">Resolved</div>
                    </div>
                </div>
                <div class="stat-card" style="background:#fff; padding:15px; border-radius:8px; display:flex; align-items:center; gap:15px; box-shadow:0 2px 6px rgba(0,0,0,0.03); border-left:4px solid #052c65;">
                    <div style="font-size:1.8rem;">🏆</div>
                    <div>
                        <div style="font-size:1.5rem; font-weight:700; color:#333;"><?= $total_certs ?></div>
                        <div style="font-size:.85rem; color:#666;">Certificates</div>
                    </div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-bottom:25px;">
                <a href="file_complaint.php" class="service-card" style="background:#fff; padding:20px; border-radius:8px; text-decoration:none; color:#333; box-shadow:0 2px 6px rgba(0,0,0,0.03); display:block;">
                    <div style="font-size:1.5rem; margin-bottom:10px;">📝</div>
                    <h3 style="margin:0 0 5px 0; font-size:1.1rem; color:#052c65; font-family:'Baloo 2', cursive;">File a Complaint</h3>
                    <p style="margin:0; font-size:.85rem; color:#666;">Submit a new complaint to your Panchayat</p>
                </a>
                <a href="apply_certificate.php" class="service-card" style="background:#fff; padding:20px; border-radius:8px; text-decoration:none; color:#333; box-shadow:0 2px 6px rgba(0,0,0,0.03); display:block;">
                    <div style="font-size:1.5rem; margin-bottom:10px;">🏆</div>
                    <h3 style="margin:0 0 5px 0; font-size:1.1rem; color:#137333; font-family:'Baloo 2', cursive;">Apply Certificate</h3>
                    <p style="margin:0; font-size:.85rem; color:#666;">Birth, Death, or Income certificate apply</p>
                </a>
                <a href="view_schemes.php" class="service-card" style="background:#fff; padding:20px; border-radius:8px; text-decoration:none; color:#333; box-shadow:0 2px 6px rgba(0,0,0,0.03); display:block;">
                    <div style="font-size:1.5rem; margin-bottom:10px;">📢</div>
                    <h3 style="margin:0 0 5px 0; font-size:1.1rem; color:#052c65; font-family:'Baloo 2', cursive;">View Schemes</h3>
                    <p style="margin:0; font-size:.85rem; color:#666;">Browse latest active government schemes</p>
                </a>
            </div>

            <div style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h3 style="margin:0; color:#333; font-family:'Baloo 2', cursive; font-size:1.2rem;">📋 Recent Complaints</h3>
                        <p style="margin:3px 0 0; font-size:.82rem; color:#666;">Your last 5 filed records</p>
                    </div>
                    <a href="my_complaints.php" class="btn btn-navy btn-sm" style="background:#052c65; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:.85rem; font-weight:600;">View All</a>
                </div>

                <?php if (!$recent || mysqli_num_rows($recent) === 0): ?>
                    <div style="text-align:center; padding:30px 10px;">
                        <div style="font-size:2.5rem; margin-bottom:10px;">📋</div>
                        <h3 style="margin:0 0 5px 0; color:#555;">No Complaints Yet</h3>
                        <p style="margin:0 0 15px 0; font-size:.9rem; color:#777;">File your first complaint to get started.</p>
                        <a href="file_complaint.php" class="btn btn-primary" style="background:#ff9933; color:#fff; padding:8px 16px; border-radius:4px; text-decoration:none; font-size:.9rem; font-weight:600;">File Complaint →</a>
                    </div>
                <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width:100%; border-collapse:collapse; text-align:left; font-size:.9rem; min-width: 500px;">
                        <thead>
                            <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                                <th style="padding:12px 10px;">#</th>
                                <th style="padding:12px 10px;">Title</th>
                                <th style="padding:12px 10px;">Date</th>
                                <th style="padding:12px 10px;">Status</th>
                                <th style="padding:12px 10px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; while ($c = mysqli_fetch_assoc($recent)): 
                                $badge_style = "background:#fef3c7; color:#d97706;";
                                if ($c['status'] === 'Resolved') { $badge_style = "background:#e6f4ea; color:#137333;"; }
                                elseif ($c['status'] === 'In Progress') { $badge_style = "background:#e8f0fe; color:#1a73e8;"; }
                            ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:12px 10px;"><?= $i++ ?></td>
                                <td style="padding:12px 10px; font-weight:600; color:#333;"><?= htmlspecialchars($c['title']) ?></td>
                                <td style="padding:12px 10px; color:#555;"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                                <td style="padding:12px 10px;">
                                    <span style="padding:4px 8px; border-radius:4px; font-size:.78rem; font-weight:600; <?= $badge_style ?>">
                                        <?= htmlspecialchars($c['status']) ?>
                                    </span>
                                </td>
                                <td style="padding:12px 10px;"><a href="my_complaints.php" style="color:#1a73e8; font-weight:600; text-decoration:none;">View</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<footer style="background:#052c65; color:#fff; padding:15px 0; text-align:center; margin-top:40px;">
    <div class="container" style="font-size:.85rem; opacity:0.8;">
        <span>© <?= date('Y') ?> Rampur Gram Panchayat | All Rights Reserved</span> | <span>🇮🇳 Digital India</span>
    </div>
</footer>

</body>
</html>