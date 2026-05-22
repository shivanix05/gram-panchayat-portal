<?php
// FIXED: Include config to setup database connection $conn and sessions
require_once 'config.php';
require_once 'header.php';

$schemes_res = mysqli_query($conn, "SELECT * FROM schemes ORDER BY created_at DESC");
$total_schemes = mysqli_num_rows($schemes_res);

// Detect folder structure context dynamically to fix link breakdown
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$apply_path = ($current_dir === 'user') ? 'apply_certificate.php' : 'user/apply_certificate.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sarkari Yojanaen | Gram Panchayat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

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
                <li style="margin-bottom: 8px;"><a href="my_complaints.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📋 My Complaints</a></li>
                <li style="margin-bottom: 8px;"><a href="apply_certificate.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">🏆 Apply Certificate</a></li>
                <li style="margin-bottom: 8px;"><a href="my_certificates.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📄 My Certificates</a></li>
                <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
                <li style="margin-bottom: 8px;"><a href="view_schemes.php" style="display: block; padding: 10px 12px; background: #e8f0fe; color: #1a73e8; border-radius: 4px; font-weight: 600; text-decoration: none;">📢 View Schemes</a></li>
                <li><a href="logout.php" style="display: block; padding: 10px 12px; color: #c5221f; border-radius: 4px; text-decoration: none; font-weight: 600;">🚪 Logout</a></li>
            </ul>
        </aside>

        <main class="main-content" style="flex: 3; min-width: 300px;">
            
            <div class="breadcrumb" style="font-size: .88rem; color: #666; margin-bottom: 15px;">
                <a href="index.php" style="color:#052c65; text-decoration:none; font-weight:600;">Home</a> <span class="sep">›</span>
                <a href="user_dashboard.php" style="color:#052c65; text-decoration:none; font-weight:600;">Dashboard</a> <span class="sep">›</span>
                <span style="color:#333;">Sarkari Yojanaen</span>
            </div>

            <div class="table-wrapper" style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05); border-top: 4px solid #ff9933;">
                <div class="page-title-bar" style="margin-bottom:25px; border-bottom:1px solid #eee; padding-bottom:15px;">
                    <h2 style="margin:0; color:#052c65; font-family:'Baloo 2', cursive;">📋 Sarkari Yojanaen</h2>
                    <p style="margin:4px 0 0; color:#666; font-size:.9rem;">Browse all government schemes available for our gram panchayat citizens</p>
                </div>

                <?php if ($total_schemes === 0): ?>
                    <div class="empty-state" style="text-align:center; padding:40px 20px;">
                        <div class="empty-icon" style="font-size:3rem; margin-bottom:10px;">📋</div>
                        <h3 style="margin:0 0 8px; color:#333;">Koi Yojana Abhi Uplabdh Nahi</h3>
                        <p style="margin:0; color:#777; font-size:.95rem;">Admin jald hi nayi yojanaen add karega. Baad mein check karein.</p>
                    </div>
                <?php else: ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                    <?php while ($s = mysqli_fetch_assoc($schemes_res)): ?>
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; flex-direction: column; overflow: hidden; transition: box-shadow 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                        
                        <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #edf2f7;">
                            <h3 style="margin: 0 0 8px; color: #052c65; font-size: 1.1rem; font-weight: 700; line-height: 1.4;"><?= htmlspecialchars($s['title']) ?></h3>
                            <div style="font-size: 0.8rem; color: #718096; font-weight: 600;">
                                📅 Launch: <span style="color: #2d3748;"><?= date('d M Y', strtotime($s['launch_date'] ?? 'now')) ?></span>
                            </div>
                        </div>
                        
                        <div style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1;">
                            <p style="margin: 0 0 15px; color: #4a5568; font-size: 0.88rem; line-height: 1.5; flex-grow: 1;">
                                <?= nl2br(htmlspecialchars($s['description'])) ?>
                            </p>
                            
                            <div style="background: #fefcbf; border-left: 3px solid #ecc94b; padding: 10px; border-radius: 0 4px 4px 0; margin-bottom: 15px;">
                                <div style="font-weight: 700; font-size: 0.82rem; color: #975a16; margin-bottom: 4px;">
                                    ✅ Eligibility Criteria
                                </div>
                                <p style="margin: 0; color: #744210; font-size: 0.82rem; line-height: 1.4;">
                                    <?= nl2br(htmlspecialchars($s['eligibility'])) ?>
                                </p>
                            </div>
                            <a href="scheme_details.php?id=<?= $s['id'] ?>" class="btn btn-sm" style="background:#ff9933; color:white; text-decoration:none; padding:6px 12px; border-radius:4px;">👁️ Read More</a>
                            <br>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                                <a href="apply_scheme.php?scheme_id=<?= $s['id'] ?>" class="btn btn-primary" style="background: #137333; color: #fff; padding: 8px; text-align: center; text-decoration: none; font-weight: 600; border-radius: 4px; font-size: 0.85rem; display: block;">Apply Now →</a>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <a href="login.php" class="btn btn-primary" style="background: #052c65; color: #fff; padding: 8px; text-align: center; text-decoration: none; font-weight: 600; border-radius: 4px; font-size: 0.85rem; display: block;">Login to Apply →</a>
                            <?php endif; ?>
                        </div>

                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
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