<?php
require_once 'config.php';

// Fetch scheme count for stats
$scheme_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM schemes"))['c'] ?? 0;
$user_count   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'] ?? 0;
$complaint_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM complaints WHERE status='Resolved'"))['c'] ?? 0;

// Fetch latest 3 schemes for homepage
$schemes_res = mysqli_query($conn, "SELECT * FROM schemes ORDER BY created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Gram Panchayat Portal | E-Government Services</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏛️</text></svg>">
</head>
<body>

<?php include("header.php"); ?>

<!-- ── Hero ───────────────────────────────────────────────── -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-badge">🌐 Digital India Initiative</div>
        <h2>Aapki Panchayat,<br>Ab Aapki Mutthi Mein</h2>
        <p>File complaints, apply for certificates, and explore government schemes — all from the comfort of your home.</p>
        <div class="hero-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user_dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard →</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary btn-lg">📝 Register Now</a>
                <a href="login.php"    class="btn btn-outline btn-lg">🔑 Already Registered?</a>
            <?php endif; ?>
        </div>
        <div class="hero-stats">
            <div class="stat-item"><span class="num"><?= $user_count ?>+</span><span class="label">Registered Villagers</span></div>
            <div class="stat-item"><span class="num"><?= $scheme_count ?>+</span><span class="label">Active Schemes</span></div>
            <div class="stat-item"><span class="num"><?= $complaint_count ?>+</span><span class="label">Complaints Resolved</span></div>
            <div class="stat-item"><span class="num">24/7</span><span class="label">Online Services</span></div>
        </div>
    </div>
</section>

<div class="tricolor-strip"></div>

<!-- ── Services ───────────────────────────────────────────── -->
<section class="section" id="services">
    <div class="container">
        <div class="section-header">
            <h2>🔧 Our Digital Services</h2>
            <p>Sabhi sarkari seva ab ek jagah par — tez, transparent aur aasaan</p>
            <div class="section-divider"></div>
        </div>
        <div class="grid grid-4">
            <a href="<?= isset($_SESSION['user_id']) ? 'file_complaint.php' : 'login.php' ?>" class="service-card">
                <div class="service-icon icon-saffron">📝</div>
                <h3>File a Complaint</h3>
                <p>Road, bijli, paani — apni samasya register karein</p>
            </a>
            <a href="view_schemes.php" class="service-card">
                <div class="service-icon icon-green">📋</div>
                <h3>View Schemes</h3>
                <p>Sarkar ki nayi yojanaon ki jaankaari paein</p>
            </a>
            <a href="<?= isset($_SESSION['user_id']) ? 'apply_certificate.php' : 'login.php' ?>" class="service-card">
                <div class="service-icon icon-navy">🏆</div>
                <h3>Apply Certificate</h3>
                <p>Birth, Death ya Income Certificate ke liye apply karein</p>
            </a>
            <a href="<?= isset($_SESSION['user_id']) ? 'my_complaints.php' : 'login.php' ?>" class="service-card">
                <div class="service-icon icon-saffron">🔍</div>
                <h3>Track Status</h3>
                <p>Apni complaint aur certificate ki sthiti dekhein</p>
            </a>
        </div>
    </div>
</section>

<!-- ── Latest Schemes ──────────────────────────────────────── -->
<?php if (mysqli_num_rows($schemes_res) > 0): ?>
<section class="section section-alt" >
    <div class="container">
        <div class="section-header" id="schemes">
            <h2>📋 Nayi Sarkari Yojanaen</h2>
            <p>Recently announced government schemes for our villagers</p>
            <div class="section-divider"></div>
        </div>
        <div class="grid grid-3">
            <?php while ($s = mysqli_fetch_assoc($schemes_res)): ?>
            <div class="scheme-card">
                <div class="scheme-card-top">
                    <h3><?= htmlspecialchars($s['title']) ?></h3>
                    <div class="date">📅 Launch: <?= date('d M Y', strtotime($s['launch_date'])) ?></div>
                </div>
                <div class="scheme-card-body">
                    <p><?= htmlspecialchars(substr($s['description'], 0, 120)) ?>…</p>
                    <div class="elig-label">✅ Eligibility</div>
                    <p><?= htmlspecialchars(substr($s['eligibility'], 0, 80)) ?>…</p>
                    <a href="view_schemes.php" class="btn btn-navy btn-sm mt-2">Read More →</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-3">
            <a href="view_schemes.php" class="btn btn-primary">View All Schemes →</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── About ───────────────────────────────────────────────── -->
<section class="section" id="about">
    <div class="container">
        <div class="grid grid-2" style="align-items:center;gap:48px;">
            <div>
                <div class="hero-badge" style="background:var(--saffron-light);color:var(--saffron);border:1px solid var(--saffron);">About Our Panchayat</div>
                <h2 style="font-family:'Baloo 2',cursive;font-size:2rem;color:var(--navy);margin:16px 0 12px;">Sagar Gram Panchayat</h2>
                <p style="color:var(--gray-600);line-height:1.8;margin-bottom:16px;">
                    Rampur Gram Panchayat, Madhya Pradesh ka ek adarsh gram panchayat hai jo Digital India ke sapne ko saakar karne mein aage hai.
                    Hamare Sarpanch ke netritv mein, hum apne 5,000+ nagarikon ko behtar sarkari seva dene ke liye prabaddh hain.
                </p>
                <div class="grid grid-2" style="gap:16px;margin-top:24px;">
                    <div class="card card-body" style="text-align:center;">
                        <div style="font-size:2rem;margin-bottom:6px;">🏘️</div>
                        <div style="font-family:'Baloo 2',cursive;font-weight:800;font-size:1.4rem;color:var(--navy);">5,000+</div>
                        <div style="font-size:.85rem;color:var(--gray-600);">Population</div>
                    </div>
                    <div class="card card-body" style="text-align:center;">
                        <div style="font-size:2rem;margin-bottom:6px;">🏠</div>
                        <div style="font-family:'Baloo 2',cursive;font-weight:800;font-size:1.4rem;color:var(--navy);">1,200+</div>
                        <div style="font-size:.85rem;color:var(--gray-600);">Households</div>
                    </div>
                </div>
            </div>
            <div>
                <div class="announcement-bar" style="border-radius:var(--radius);margin-bottom:16px;">
                    📢 <strong>Gram Sabha Notice:</strong> Monthly meeting on 20th June 2025 at 10 AM — All residents are requested to attend.
                </div>
                <div class="card">
                    <div class="card-header-strip saffron"></div>
                    <div class="card-body">
                        <h3 style="font-family:'Baloo 2',cursive;font-weight:700;color:var(--navy);margin-bottom:12px;">👤 Sarpanch Message</h3>
                        <p style="font-size:.9rem;color:var(--gray-600);line-height:1.8;font-style:italic;">
                            "Hamare is Digital Panchayat Portal ke through, main chahta hoon ki har nagrik ghar baithe apni samasya register kar sake aur sarkari yojanaon ka labh utha sake. Yahi hamara Digital India ka sapna hai."
                        </p>
                        <p style="margin-top:12px;font-weight:700;color:var(--navy);">— Shri Ramesh Patel, Sarpanch</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Contact ─────────────────────────────────────────────── -->
<section class="section section-alt" id="contact">
    <div class="container">
        <div class="section-header">
            <h2>📞 Contact Us</h2>
            <p>Kisi bhi sahayata ke liye humse sampark karein</p>
            <div class="section-divider"></div>
        </div>
        <div class="grid grid-3">
            <div class="card card-body" style="text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:12px;">📍</div>
                <h3 style="font-family:'Baloo 2',cursive;color:var(--navy);margin-bottom:6px;">Address</h3>
                <p style="font-size:.88rem;color:var(--gray-600);">Gram Panchayat Bhawan,<br>Sagar Village, Tehsil Pathria,<br>District Sagar, MP — 462001</p>
            </div>
            <div class="card card-body" style="text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:12px;">📞</div>
                <h3 style="font-family:'Baloo 2',cursive;color:var(--navy);margin-bottom:6px;">Phone</h3>
                <p style="font-size:.88rem;color:var(--gray-600);">Sarpanch: +91 98765 43210<br>Office: 07777-09888<br>Helpline: 1800-000-000 (Free)</p>
            </div>
            <div class="card card-body" style="text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:12px;">🕐</div>
                <h3 style="font-family:'Baloo 2',cursive;color:var(--navy);margin-bottom:6px;">Office Hours</h3>
                <p style="font-size:.88rem;color:var(--gray-600);">Monday – Saturday<br>9:00 AM – 5:00 PM<br>Sunday: Closed</p>
            </div>
        </div>
    </div>
</section>

<!-- ── Footer ─────────────────────────────────────────────── -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>🏛️ Digital Gram Panchayat Portal</h3>
                <p>A Digital India initiative to bring government services closer to every citizen. Built with transparency, accountability, and ease of access.</p>
               
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">🏠 Home</a></li>
                    <li><a href="view_schemes.php">📋 Schemes</a></li>
                    <li><a href="register.php">📝 Register</a></li>
                    <li><a href="login.php">🔑 Login</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Services</h4>
                <ul>
                    <li><a href="#">📝 File Complaint</a></li>
                    <li><a href="#">🏆 Certificates</a></li>
                    <li><a href="#">📋 Schemes</a></li>
                    <li><a href="#">📞 Contact</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> sagar Gram Panchayat | All Rights Reserved</span>
            <span>Made with ❤️ under Digital India Programme 🇮🇳</span>
        </div>
    </div>
</footer>

</body>
</html>