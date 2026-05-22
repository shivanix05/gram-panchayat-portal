<?php
// FIXED PATH: config.php is in the same directory (user/), no need for ../
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$uid = (int)$_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIXED: Clean inputs using native mysqli_real_escape_string directly
    $cert_type   = mysqli_real_escape_string($conn, trim($_POST['certificate_type'] ?? ''));
    $applicant   = mysqli_real_escape_string($conn, trim($_POST['applicant_name'] ?? ''));
    $dob         = mysqli_real_escape_string($conn, trim($_POST['dob'] ?? ''));
    $father_name = mysqli_real_escape_string($conn, trim($_POST['father_name'] ?? ''));
    $mother_name = mysqli_real_escape_string($conn, trim($_POST['mother_name'] ?? ''));
    $address     = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));

    $allowed_types = ['Birth Certificate', 'Death Certificate', 'Income Certificate'];

    if (!in_array($cert_type, $allowed_types)) {
        $error = 'Please select a valid certificate type.';
    } elseif (empty($applicant)) {
        $error = 'Applicant name is required.';
    } elseif (empty($address)) {
        $error = 'Address is required.';
    } else {
        // Build JSON for extra details
        $details = json_encode([
            'dob'         => $dob,
            'father_name' => $father_name,
            'mother_name' => $mother_name,
            'address'     => $address,
        ]);
        $details_esc = mysqli_real_escape_string($conn, $details);

        // Prepared statement used to avoid SQL inject vulnerabilities securely
        $stmt = mysqli_prepare($conn, "INSERT INTO certificates (user_id, certificate_type, applicant_name, details_json) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $uid, $cert_type, $applicant, $details_esc);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Your application for <strong>$cert_type</strong> has been submitted! Admin will review it within 7 working days.";
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Certificate | Gram Panchayat</title>
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
                <li style="margin-bottom: 8px;"><a href="my_complaints.php" style="display: block; padding: 10px 12px; color: #333; border-radius: 4px; text-decoration: none;">📋 My Complaints</a></li>
                <li style="margin-bottom: 8px;"><a href="apply_certificate.php" style="display: block; padding: 10px 12px; background: #e8f0fe; color: #1a73e8; border-radius: 4px; font-weight: 600; text-decoration: none;">🏆 Apply Certificate</a></li>
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
                <span style="color:#333;">Apply Certificate</span>
            </div>

            <div class="form-card" style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05); max-width:640px; margin:0 auto; border-top: 4px solid #137333;">
                <div class="form-card-header" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                    <h2 style="margin:0; color:#052c65; font-family:'Baloo 2', cursive;">🏆 Praman Patra Hetu Avedan</h2>
                    <p style="margin:5px 0 0; color:#666; font-size:.9rem;">Apply for Birth, Death, or Income Certificate</p>
                </div>
                <div class="form-body">

                    <?php if ($success): ?>
                        <div class="alert alert-success" style="background:#e6f4ea; color:#137333; padding:12px; border-radius:4px; margin-bottom:15px; font-weight:600;">✅ <?= $success ?></div>
                        <div style="text-align:center; padding:10px 0;">
                            <a href="my_certificates.php" class="btn btn-navy" style="background:#052c65; color:#fff; padding:8px 16px; border-radius:4px; text-decoration:none; font-weight:600; font-size:.9rem;">View My Applications →</a>
                            <a href="apply_certificate.php" class="btn btn-primary" style="background:#ff9933; color:#fff; padding:8px 16px; border-radius:4px; text-decoration:none; font-weight:600; font-size:.9rem; margin-left:10px;">Apply Another</a>
                        </div>
                    <?php else: ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error" style="background:#fce8e6; color:#c5221f; padding:12px; border-radius:4px; margin-bottom:15px; font-weight:600;">❌ <?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="apply_certificate.php">

                        <div class="form-group" style="margin-bottom: 18px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Certificate Type <span style="color:#c5221f;">*</span></label>
                            <select name="certificate_type" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; background:#fff;" required>
                                <option value="">-- Select Certificate --</option>
                                <option value="Birth Certificate"  <?= (($_POST['certificate_type'] ?? '')==='Birth Certificate')  ? 'selected' : '' ?>>🍼 Birth Certificate</option>
                                <option value="Death Certificate"  <?= (($_POST['certificate_type'] ?? '')==='Death Certificate')  ? 'selected' : '' ?>>☠️ Death Certificate</option>
                                <option value="Income Certificate" <?= (($_POST['certificate_type'] ?? '')==='Income Certificate') ? 'selected' : '' ?>>💰 Income Certificate</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 18px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Applicant's Full Name <span style="color:#c5221f;">*</span></label>
                            <input type="text" name="applicant_name" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;"
                                   placeholder="Full name as per records"
                                   value="<?= htmlspecialchars($_POST['applicant_name'] ?? '') ?>" required>
                        </div>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 18px;">
                            <div class="form-group" style="flex: 1; min-width: 240px;">
                                <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Father's Name</label>
                                <input type="text" name="father_name" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;"
                                       placeholder="Father's full name"
                                       value="<?= htmlspecialchars($_POST['father_name'] ?? '') ?>">
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 240px;">
                                <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Mother's Name</label>
                                <input type="text" name="mother_name" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;"
                                       placeholder="Mother's full name"
                                       value="<?= htmlspecialchars($_POST['mother_name'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 18px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Date of Birth / Death</label>
                            <input type="date" name="dob" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;"
                                   value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Permanent Address <span style="color:#c5221f;">*</span></label>
                            <textarea name="address" class="form-control" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-family:inherit;"
                                      placeholder="Village, Tehsil, District, Pincode"
                                      required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <div class="alert alert-warning" style="background:#fff3cd; color:#856404; padding:12px; border-radius:4px; margin-bottom:22px; font-size:.88rem;">
                            ⚠️ Application processing may take <strong>7–10 working days</strong>. You will be notified upon approval.
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg" style="width:100%; background:#052c65; color:#fff; border:none; padding:12px; font-size:1.05rem; font-weight:600; border-radius:4px; cursor:pointer;">📤 Submit Application</button>
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