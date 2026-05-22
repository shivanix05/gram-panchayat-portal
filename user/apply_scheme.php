<?php
// Sahi path: user folder se ek step bahar nikal kar admin/config.php ko include kiya
require_once '../admin/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Agar user logged in nahi hai, toh use pehle login page par bhejo
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// URL se Scheme ID nikalna taaki pata chale kis yojana ke liye apply ho raha hai
$scheme_id = isset($_GET['scheme_id']) ? (int)$_GET['scheme_id'] : 0;
$scheme_title = '';

if ($scheme_id > 0) {
    $s_query = mysqli_query($conn, "SELECT title FROM schemes WHERE id = $scheme_id");
    if ($s_query && mysqli_num_rows($s_query) > 0) {
        $scheme_title = mysqli_fetch_assoc($s_query)['title'];
    }
}

// Agar direct page khola hai bina scheme select kiye, toh sabhi active schemes nikal lo dropdown ke liye
$all_schemes = mysqli_query($conn, "SELECT id, title FROM schemes ORDER BY created_at DESC");

// ── FORM SUBMISSION PROCESSING ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id     = $_SESSION['user_id'];
    $scheme_id   = (int)($_POST['scheme_id'] ?? 0);
    $mobile      = mysqli_real_escape_string($conn, trim($_POST['mobile'] ?? ''));
    $gender      = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    $dob         = mysqli_real_escape_string($conn, $_POST['dob'] ?? '');
    $category    = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $income      = mysqli_real_escape_string($conn, trim($_POST['income'] ?? ''));
    $address     = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));

    if ($scheme_id <= 0 || empty($mobile) || empty($gender) || empty($dob) || empty($income) || empty($address)) {
        $error = 'Kripya sabhi zaroori fields ko sahi se bharein.';
    } else {
        // Document Uploading System: user folder se ek step bahar 'uploads' folder banega
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $aadhar_file  = $_FILES['aadhar_card'] ?? null;
        $samagra_file = $_FILES['samagra_id'] ?? null;
        
        $aadhar_path  = '';
        $samagra_path = '';

        // Aadhar Card Upload Check
        if ($aadhar_file && $aadhar_file['error'] === 0) {
            $ext = pathinfo($aadhar_file['name'], PATHINFO_EXTENSION);
            // Database me path store karne ke liye hum 'uploads/filename' rakhenge
            $db_aadhar_path = "uploads/aadhar_" . $user_id . "_" . time() . "." . $ext;
            // Actual file transfer ke liye relative path
            $target_path = $upload_dir . "aadhar_" . $user_id . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($aadhar_file['tmp_name'], $target_path)) {
                $aadhar_path = $db_aadhar_path;
            }
        }

        // Samagra ID Upload Check
        if ($samagra_file && $samagra_file['error'] === 0) {
            $ext = pathinfo($samagra_file['name'], PATHINFO_EXTENSION);
            $db_samagra_path = "uploads/samagra_" . $user_id . "_" . time() . "." . $ext;
            $target_path = $upload_dir . "samagra_" . $user_id . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($samagra_file['tmp_name'], $target_path)) {
                $samagra_path = $db_samagra_path;
            }
        }

        if (empty($aadhar_path)) {
            $error = 'Aadhar Card upload karna compulsory hai.';
        } else {
            // Database me applications table ke andar data insert karna
            $sql = "INSERT INTO scheme_applications (user_id, scheme_id, mobile, gender, dob, category, annual_income, address, aadhar_doc, samagra_doc, status, applied_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iissssssss", $user_id, $scheme_id, $mobile, $gender, $dob, $category, $income, $address, $aadhar_path, $samagra_path);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Aapka aavedan (Application) safaltapoorvak jama ho gaya hai! Panchayat jald hi iski jaanch karegi.';
                // Form input fields reset karna
                $mobile = $gender = $dob = $category = $income = $address = '';
            } else {
                $error = 'Database me save karte samay dikkat aayi: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Scheme | Gram Panchayat Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="gov-ribbon"><div class="container"><span>🇮🇳 Digital Gram Panchayat Portal — Government Scheme Application Form</span></div></div>

<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem" class="header-logo">
        <div class="header-text">
            <h1>🏛️ Digital Gram Panchayat Portal</h1>
            <p>Rampur Gram Panchayat | Madhya Pradesh</p>
        </div>
    </div>
</header>

<nav class="main-nav">
    <div class="container">
        <ul class="nav-links">
            <li><a href="index.php">🏠 Home</a></li>
            <li><a href="view_schemes.php">📋 Schemes</a></li>
        </ul>
        <div class="nav-cta">
            <span style="color:rgba(255,255,255,.85); font-size:.9rem; margin-right:10px;">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="container" style="padding-top:30px; padding-bottom:40px; max-width: 800px;">
    <div class="form-card" style="background:#fff; padding:30px; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.06); border-top: 4px solid #052c65;">
        
        <div class="form-card-header" style="border-bottom:1px solid #eee; margin-bottom:25px; padding-bottom:15px;">
            <h2 style="margin:0; color:#052c65;">📝 Yojana Ke Liye Aavedan Karein</h2>
            <p style="color:#666; margin:5px 0 0 0;">Fill up your complete authentic details for verifications</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" style="background:#fce8e6; color:#c5221f; padding:12px; border-radius:4px; margin-bottom:20px; border-left:4px solid #c5221f;">
                ❌ <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="background:#e6f4ea; color:#137333; padding:12px; border-radius:4px; margin-bottom:20px; border-left:4px solid #137333;">
                ✅ <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="apply_scheme.php?scheme_id=<?= $scheme_id ?>" enctype="multipart/form-data">
            
            <h3 style="color:#ff9933; border-bottom: 1px dashed #ddd; padding-bottom: 5px; margin-top: 0;">1. Basic Information</h3>
            
            <div style="display:flex; gap:15px; margin-bottom:15px; flex-wrap: wrap;">
                <div style="flex:1; min-width: 250px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Applicant Full Name</label>
                    <input type="text" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f9f9f9;" value="<?= htmlspecialchars($_SESSION['user_name']) ?>" disabled>
                </div>
                <div style="flex:1; min-width: 250px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Email Address</label>
                    <input type="email" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f9f9f9;" value="<?= htmlspecialchars($_SESSION['user_email']) ?>" disabled>
                </div>
            </div>

            <div style="display:flex; gap:15px; margin-bottom:20px; flex-wrap: wrap;">
                <div style="flex:1; min-width: 250px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Select Scheme <span style="color:red;">*</span></label>
                    <select name="scheme_id" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" required>
                        <?php if ($scheme_title): ?>
                            <option value="<?= $scheme_id ?>"><?= htmlspecialchars($scheme_title) ?></option>
                        <?php else: ?>
                            <option value="">-- Choose a Government Scheme --</option>
                            <?php while($s = mysqli_fetch_assoc($all_schemes)): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['title']) ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div style="flex:1; min-width: 250px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Mobile Number <span style="color:red;">*</span></label>
                    <input type="text" name="mobile" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" placeholder="10-digit mobile number" required value="<?= htmlspecialchars($mobile ?? '') ?>">
                </div>
            </div>

            <h3 style="color:#ff9933; border-bottom: 1px dashed #ddd; padding-bottom: 5px; margin-top: 25px;">2. Personal & Socio-Economic Details</h3>

            <div style="display:flex; gap:15px; margin-bottom:15px; flex-wrap: wrap;">
                <div style="flex:1; min-width: 150px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Gender <span style="color:red;">*</span></label>
                    <select name="gender" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" required>
                        <option value="">-- Select --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div style="flex:1; min-width: 180px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Date of Birth <span style="color:red;">*</span></label>
                    <input type="date" name="dob" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" required>
                </div>
                <div style="flex:1; min-width: 150px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Category <span style="color:red;">*</span></label>
                    <select name="category" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" required>
                        <option value="General">General</option>
                        <option value="OBC">OBC</option>
                        <option value="SC">SC</option>
                        <option value="ST">ST</option>
                    </select>
                </div>
                <div style="flex:1; min-width: 180px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Annual Income (₹) <span style="color:red;">*</span></label>
                    <input type="number" name="income" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" placeholder="e.g. 96000" required value="<?= htmlspecialchars($income ?? '') ?>">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px; font-weight:600;">Permanent Address (Gram Panchayat Records) <span style="color:red;">*</span></label>
                <textarea name="address" class="form-control" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing: border-box;" placeholder="Enter your full house address with ward number" required><?= htmlspecialchars($address ?? '') ?></textarea>
            </div>

            <h3 style="color:#ff9933; border-bottom: 1px dashed #ddd; padding-bottom: 5px; margin-top: 25px;">3. Document Upload (PDF / Image)</h3>

            <div style="display:flex; gap:15px; margin-bottom:25px; flex-wrap: wrap;">
                <div style="flex:1; min-width: 250px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Aadhar Card File <span style="color:red;">*</span></label>
                    <input type="file" name="aadhar_card" style="width:100%; padding:8px; border:1px dashed #aaa; border-radius:4px; background: #fafafa;" accept="image/*,application/pdf" required>
                    <small style="color:#777;">Max size: 2MB (JPG, PNG, PDF)</small>
                </div>
                <div style="flex:1; min-width: 250px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Samagra ID File <span style="color:#777; font-size:.8rem;">(Optional)</span></label>
                    <input type="file" name="samagra_id" style="width:100%; padding:8px; border:1px dashed #aaa; border-radius:4px; background: #fafafa;" accept="image/*,application/pdf">
                    <small style="color:#777;">Max size: 2MB (JPG, PNG, PDF)</small>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn" style="width:100%; padding:12px; background:#052c65; color:#fff; border:none; border-radius:4px; font-size:1.05rem; cursor:pointer; font-weight:600;">📤 Submit Application</button>
                <a href="view_schemes.php" style="padding:12px 20px; background:#eee; color:#333; text-decoration:none; border-radius:4px; font-weight:600; text-align:center; font-size:.95rem;">Cancel</a>
            </div>

        </form>
    </div>
</div>

<footer class="site-footer" style="margin-top:40px; padding:15px 0; border-top:1px solid #eee; text-align:center;">
    <div class="container">
        <div class="footer-bottom" style="font-size:.85rem; color:#666;">
            <span>© <?= date('Y') ?> Rampur Gram Panchayat | All Rights Reserved</span> | <span>🇮🇳 Digital India Initiative</span>
        </div>
    </div>
</footer>

</body>
</html>