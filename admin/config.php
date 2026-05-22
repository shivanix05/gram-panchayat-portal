
<?php
session_start(); // Login-logout system ke liye zaroori hai

$host = "localhost";
$user = "root";
$pass = "root123";
$db   = "gram_panchayat_db";

// Database connect karne ki single line line
$conn = mysqli_connect($host, $user, $pass, $db);
 
// Agar connection fail ho toh error dikhaye aur script roke
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Helper function to redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Helper function to clean input data (basic sanitization for display/general use)
// For database interaction, prepared statements are preferred.
function clean($conn, $data) {
    // Trim whitespace, strip HTML tags, and escape special characters for SQL (though prepared statements handle this better)
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8'));
}

// Helper function to set a flash message
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Helper function to get and display a flash message
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $alert_class = ($flash['type'] === 'success') ? 'alert-success' : 'alert-error';
        $icon = ($flash['type'] === 'success') ? '✅' : '❌';
        echo "<div class='alert {$alert_class}'>{$icon} {$flash['message']}</div>";
    }
}
?>
