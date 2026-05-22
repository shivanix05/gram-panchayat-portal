<?php
// Session check aur database connection ke liye config file include ki
require_once 'config.php'; 

// Admin session check ko admin_dashboard.php ke sath match kiya
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Check kiya ki URL me ID pass hui hai ya nahi
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_schemes.php");
    exit();
}

$scheme_id = (int)$_GET['id'];

// Sidebar counts ke liye query executing - Safely checking rows
$pending_comp_res = mysqli_query($conn, "SELECT COUNT(*) c FROM complaints WHERE status='Pending'");
$pending_comp = $pending_comp_res ? mysqli_fetch_assoc($pending_comp_res)['c'] : 0;

$pending_certs_res = mysqli_query($conn, "SELECT COUNT(*) c FROM certificates WHERE status='Pending'");
$pending_certs = $pending_certs_res ? mysqli_fetch_assoc($pending_certs_res)['c'] : 0;

// ── UPDATE OPERATION (CRUD - Update) ───────────────────────────
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    
    $title       = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $eligibility = mysqli_real_escape_string($conn, $_POST['eligibility'] ?? '');
    $launch_date = mysqli_real_escape_string($conn, $_POST['launch_date'] ?? '');

    if (!empty($title) && !empty($description) && !empty($eligibility) && !empty($launch_date)) {
        $stmt = mysqli_prepare($conn, "UPDATE schemes SET title = ?, description = ?, eligibility = ?, launch_date = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $eligibility, $launch_date, $scheme_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Success hone par wapas manage_schemes.php par redirect kar do
            header("Location: manage_schemes.php?status=updated");
            exit();
        } else {
            $errors[] = "Database ko update karne me dikkat aayi: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors[] = "Kripya sabhi fields ko dhyan se bharein.";
    }
}

// ── READ CURRENT DATA (Pehle se save data nikalna) ─────────────
$scheme_query = mysqli_prepare($conn, "SELECT * FROM schemes WHERE id = ?");
mysqli_stmt_bind_param($scheme_query, "i", $scheme_id);
mysqli_stmt_execute($scheme_query);
$result = mysqli_stmt_get_result($scheme_query);
$scheme_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($scheme_query);

// Agar wo ID database me na mile to wapas bhej do
if (!$scheme_data) {
    header("Location: manage_schemes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Scheme | Gram Panchayat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="gov-ribbon"><div class="container"><span>🇮🇳 Digital Gram Panchayat Portal — Sarpanch Admin Panel</span></div></div>
<header class="site-header">
    <div class="container header-inner">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem" class="header-logo">
        <div class="header-text">
            <h1>🏛️ Gram Panchayat — Admin Panel</h1>
            <p>Yojana Sanshodhan (Update) | <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></p>
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
            <a href="manage_schemes.php">Manage Schemes</a> <span class="sep">›</span>
            <span>Update Scheme</span>
        </div>

        <div class="table-wrapper mb-3" style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; border-top: 4px solid var(--navy, #052c65);">
            <div class="page-title-bar" style="border-bottom: 1px solid #eee; margin-bottom: 20px; padding-bottom: 10px;">
                <div>
                    <h2 style="margin-top:0; color: var(--navy, #052c65);">✏️ Yojana Ki Jankari Badlein</h2>
                    <p style="color:#666; margin:0;">Make updates to "<?= htmlspecialchars($scheme_data['title']) ?>"</p>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="background:#fce8e6; color:#c5221f; padding:12px; border-radius:4px; margin-bottom:20px; border-left:4px solid #c5221f;">
                    <?php foreach ($errors as $err): ?>
                        ❌ <?= htmlspecialchars($err) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="update_scheme.php?id=<?= $scheme_id ?>">
                <input type="hidden" name="action" value="update">
                
                <div class="form-row" style="display:flex; gap:15px; margin-bottom:18px; flex-wrap: wrap;">
                    <div class="form-group" style="flex:2; min-width: 280px;">
                        <label style="display:block; margin-bottom:6px; font-weight:bold; color: #333;">Scheme Title <span style="color:red;">*</span></label>
                        <input type="text" name="title" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size: .95rem;" 
                               value="<?= htmlspecialchars($scheme_data['title']) ?>" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width: 180px;">
                        <label style="display:block; margin-bottom:6px; font-weight:bold; color: #333;">Launch Date <span style="color:red;">*</span></label>
                        <input type="date" name="launch_date" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size: .95rem;" 
                               value="<?= htmlspecialchars($scheme_data['launch_date']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom:18px;">
                    <label style="display:block; margin-bottom:6px; font-weight:bold; color: #333;">Description <span style="color:red;">*</span></label>
                    <textarea name="description" class="form-control" rows="5" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size: .95rem; line-height: 1.5;" required><?= htmlspecialchars($scheme_data['description']) ?></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom:25px;">
                    <label style="display:block; margin-bottom:6px; font-weight:bold; color: #333;">Eligibility Criteria <span style="color:red;">*</span></label>
                    <textarea name="eligibility" class="form-control" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size: .95rem; line-height: 1.5;" required><?= htmlspecialchars($scheme_data['eligibility']) ?></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-green" style="padding:10px 24px; font-weight:bold; cursor:pointer; background: #137333; color: white; border: none; border-radius: 4px;">💾 Save Changes</button>
                    <a href="manage_schemes.php" class="btn" style="padding:10px 20px; font-weight:bold; text-decoration: none; background: #eee; color: #333; border-radius: 4px; font-size: .92rem; text-align: center; display: inline-block;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

<footer class="site-footer" style="margin-top:40px;"><div class="container"><div class="footer-bottom" style="display:flex; justify-content:space-between; font-size: .85rem; opacity: 0.8;">
    <span>© <?= date('Y') ?> Rampur Gram Panchayat — Admin Panel</span>
    <span>🇮🇳 Digital India</span>
</div></div></footer>
</body>
</html>