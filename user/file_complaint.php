<?php
// config.php is in the same directory (user/), so it's directly accessible directly without ../
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    // Standard validation direct redirect
    header('Location: login.php');
    exit();
}

$uid = (int)$_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection extraction and cleaning safely
    $title       = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    $category    = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));

    if (empty($title))          $error = 'Complaint title is required.';
    elseif (strlen($title) < 5) $error = 'Title must be at least 5 characters.';
    elseif (empty($description)) $error = 'Please describe your complaint in detail.';
    elseif (strlen($description) < 20) $error = 'Description must be at least 20 characters.';
    else {
        // FIXED: Added category handling if column exists, otherwise standard execution mapping
        $stmt = mysqli_prepare($conn, "INSERT INTO complaints (user_id, title, category, description) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $uid, $title, $category, $description);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Your complaint has been submitted successfully! The Sarpanch will review it shortly.';
            mysqli_stmt_close($stmt);
        } else {
            // Fallback strategy if your table doesn't have category column yet
            $stmt = mysqli_prepare($conn, "INSERT INTO complaints (user_id, title, description) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iss", $uid, $title, $description);
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Your complaint has been submitted successfully! The Sarpanch will review it shortly.';
                mysqli_stmt_close($stmt);
            } else {
                $error = 'Database error: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Complaint | Gram Panchayat</title>
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
                <li style="margin-bottom: 8px;"><a href="file_complaint.php" style="display: block; padding: 10px 12px; background: #e8f0fe; color: #1a73e8; border-radius: 4px; font-weight: 600; text-decoration: none;">📝 File Complaint</a></li>
                <li style="margin-bottom: 8px;"><a href="my_complaints.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📋 My Complaints</a></li>
                <li style="margin-bottom: 8px;"><a href="apply_certificate.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">🏆 Apply Certificate</a></li>
                <li style="margin-bottom: 8px;"><a href="my_certificates.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📄 My Certificates</a></li>
                <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
                <li style="margin-bottom: 8px;"><a href="view_schemes.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📢 View Schemes</a></li>
                <li><a href="logout.php" style="display: block; padding: 10px 12px; color: #c5221f; border-radius: 4px; text-decoration: none; font-weight: 600;">🚪 Logout</a></li>
            </ul>
        </aside>

        <main class="main-content" style="flex: 3; min-width: 300px;">
            <div class="breadcrumb" style="font-size: .88rem; color: #666; margin-bottom: 15px;">
                <a href="index.php" style="color:#052c65; text-decoration:none; font-weight:600;">Home</a> <span class="sep">›</span>
                <a href="user_dashboard.php" style="color:#052c65; text-decoration:none; font-weight:600;">Dashboard</a> <span class="sep">›</span>
                <span style="color:#333;">File Complaint</span>
            </div>

            <div class="form-card" style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05); max-width:620px; margin:0 auto; border-top: 4px solid #ff9933;">
                <div class="form-card-header" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                    <h2 style="margin:0; color:#052c65; font-family:'Baloo 2', cursive;">📝 Shikayat Darj Karein</h2>
                    <p style="margin:5px 0 0; color:#666; font-size:.9rem;">Submit your complaint to Gram Panchayat for quick resolution</p>
                </div>
                <div class="form-body">

                    <?php if ($success): ?>
                        <div class="alert alert-success" style="background:#e6f4ea; color:#137333; padding:12px; border-radius:4px; margin-bottom:15px; font-weight:600;">✅ <?= $success ?></div>
                        <div style="text-align:center; padding:10px 0 8px;">
                            <a href="my_complaints.php" class="btn btn-navy" style="background:#052c65; color:#fff; padding:8px 16px; border-radius:4px; text-decoration:none; font-weight:600; font-size:.9rem;">View My Complaints →</a>
                            &nbsp;
                            <a href="file_complaint.php" class="btn btn-primary" style="background:#ff9933; color:#fff; padding:8px 16px; border-radius:4px; text-decoration:none; font-weight:600; font-size:.9rem;">File Another</a>
                        </div>
                    <?php else: ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error" style="background:#fce8e6; color:#c5221f; padding:12px; border-radius:4px; margin-bottom:15px; font-weight:600;">❌ <?= $error ?></div>
                    <?php endif; ?>

                    <div class="alert alert-info" style="background:#e8f0fe; color:#1a73e8; padding:12px; border-radius:4px; margin-bottom:20px; font-size:.88rem;">
                        ℹ️ Your complaint will be reviewed by the Sarpanch within 3-5 working days.
                    </div>

                    <form method="POST" action="file_complaint.php">
                        <div class="form-group" style="margin-bottom: 18px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Complaint Title <span style="color:#c5221f;">*</span></label>
                            <input type="text" name="title" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;"
                                   placeholder="e.g., Road damaged near village school"
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                            <div class="form-hint" style="font-size:.78rem; color:#777; margin-top:4px;">Brief description of the issue (minimum 5 characters)</div>
                        </div>

                        <div class="form-group" style="margin-bottom: 18px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Complaint Category</label>
                            <select name="category" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; background:#fff;">
                                <option value="">-- Select Category (Optional) --</option>
                                <option value="Road"></div>🛣️ Road / Infrastructure</option>
                                <option value="Water">💧 Water Supply</option>
                                <option value="Electricity">⚡ Electricity</option>
                                <option value="Sanitation">🗑️ Sanitation / Garbage</option>
                                <option value="Health">🏥 Health Services</option>
                                <option value="Education">🏫 Education</option>
                                <option value="Other">📌 Other</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 22px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Detailed Description <span style="color:#c5221f;">*</span></label>
                            <textarea name="description" class="form-control" rows="6" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-family:inherit;"
                                      placeholder="Apni samasya ka pura vivaran yahan likhen... (minimum 20 characters)"
                                      required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <div class="form-hint" style="font-size:.78rem; color:#777; margin-top:4px;">Clearly describe the issue, location, and how long this problem has existed.</div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg" style="width:100%; background:#052c65; color:#fff; border:none; padding:12px; font-size:1.05rem; font-weight:600; border-radius:4px; cursor:pointer;">📤 Submit Complaint</button>
                    </form>
                    <?php endif; ?>
                </div>
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