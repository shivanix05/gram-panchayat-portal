<?php
// Database connection aur session_start() ke liye config file include ki
require_once 'config.php';

// FIX: Agar admin pehle se logged in hai, toh use login page nahi dikhana hai, direct dashboard par bhejo
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = ""; // Error message ke liye empty variable

if (isset($_POST['login_btn'])) {
    // Input data ko secure banana
    $adminname = mysqli_real_escape_string($conn, $_POST['adminname']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Aapke bataye gaye table (adminpanel) aur columns ke mutabiq SQL Query
    $query = "SELECT * FROM adminpanel WHERE adminname='$adminname' AND pass='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Session mein data save karna
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_name'] = $row['adminname'];
        $_SESSION['admin_role'] = $row['role'];

        // Login hone ke baad dashboard par redirect
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // Agar naam ya password database se match nahi hua
        $error = "Incorrect Admin Name or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Digital Gram Panchayat</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: #ffffff;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 360px;
            border-top: 6px solid #0056b3; /* Govt Blue Theme */
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 5px;
            font-size: 24px;
        }
        p.subtitle {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 600;
            font-size: 14px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #0056b3;
            outline: none;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #0056b3;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background-color: #003d82;
        }
        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Panchayat Portal</h2>
    <p class="subtitle">Admin Control Panel Login</p>
    
    <!-- Error message display area -->
    <?php if(!empty($error)): ?>
        <div class="error-box"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="admin_login.php" method="POST">
        <div class="form-group">
            <label for="adminname">Admin Username</label>
            <input type="text" id="adminname" name="adminname" required placeholder="e.g. admin_sarpanch">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
        
        <button type="submit" name="login_btn" class="btn-submit">Secure Login</button>
    </form>
</div>

</body>
</html>