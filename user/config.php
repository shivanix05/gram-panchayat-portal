
<?php
// Agar session pehle se chal raha hai to ignore karo, nahi to start karo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection Settings (Apne server ke hisab se check kar lena)
$host = "localhost";
$user = "root";
$pass = "root123";
$db   = "gram_panchayat_db"; // Aapki database ka jo bhi naam ho

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>