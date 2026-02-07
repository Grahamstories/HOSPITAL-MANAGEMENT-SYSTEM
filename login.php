<?php
session_start();
// show errors during debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "db.php";

// prefer 'akram-huseyn' image if present in uploads folder
$preferred = null;
$found = glob(__DIR__ . '/uploads/akram-huseyn.*');
if ($found && count($found) > 0) {
    $preferred = 'uploads/' . basename($found[0]);
}

// choose most recent uploaded patient photo for login background (fallback to default)
$bg = null;
// check column exists before selecting to avoid fatal errors
$col = $conn->query("SHOW COLUMNS FROM patients LIKE 'photo'");
if ($col && $col->num_rows > 0) {
    $bgRes = $conn->query("SELECT photo FROM patients WHERE photo IS NOT NULL AND photo <> '' ORDER BY id DESC LIMIT 1");
    if ($bgRes && $bgRes->num_rows > 0) {
        $row = $bgRes->fetch_assoc();
        $bg = $row['photo'];
    }
} else {
    // column missing: try to add it so future requests can use it (safe on local dev)
    try {
        $conn->query("ALTER TABLE patients ADD COLUMN photo VARCHAR(255) NULL");
    } catch (Exception $e) {
        // ignore - if ALTER fails, we'll simply fall back to default
    }
}

$bgUrl = $preferred ? $preferred : ($bg ? $bg : 'path/to/your/background.jpg');

// Registration is handled by admins from the Admin Dashboard. Only keep login handling here.

// Login handling (supports hashed or legacy plaintext passwords)
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $stored = $user['password'];
        $ok = false;
        if (password_verify($password, $stored)) $ok = true;
        // fallback for existing plaintext passwords
        if (!$ok && $password === $stored) $ok = true;

        if ($ok) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['doctor_id'] = $user['id'];

            if ($user['role'] == 'admin') { header("Location: admindashboard.php"); exit; }
            elseif ($user['role'] == 'doctor') { header("Location: records.php"); exit; }
            elseif ($user['role'] == 'receptionist') { header("Location: receptionist.php"); exit; }
        } else {
            $error = 'Invalid credentials.';
        }
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hospital Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* dynamic background comes from PHP $bgUrl */
        body { 
            background: #0a0a0a;
            display:flex;flex-direction:column;justify-content:center;align-items:center;min-height:100vh;margin:0;padding:20px;font-family:Arial,Helvetica,sans-serif;
        }
        h2 {
            color: #00ff00;
            font-size: 2.5em;
            text-shadow: 0 0 20px #00ff00, 0 0 40px #00ff00, 0 0 60px #0088ff;
            margin-bottom: 40px;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        h3 {
            color: #00ffff;
            text-shadow: 0 0 15px #00ffff, 0 0 30px #0088ff;
            font-size: 1.5em;
            margin-top: 0;
            font-family: 'Courier New', monospace;
        }
        .border {
            background: #1a1a2e !important;
            border: 2px solid #00ffff !important;
            box-shadow: 0 0 20px #00ffff, 0 0 40px #0088ff, inset 0 0 20px rgba(0,255,255,0.1) !important;
        }
    </style>
</head>
<body>

<h2>Hospital Management System</h2>

    <div class="box box-input">
        <div >
        <h3>LOGIN</h3>
        <?php if (isset($error)) echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>
        <form method="POST" action="">
            <input class="input" type="email" name="email" placeholder="Email" required><br>
            <input class="input" type="password" name="password" placeholder="Password" required><br>
            <input type="submit" name="login" value="Login">
            <div style="margin-top:16px;display:flex;gap:10px;justify-content:center;">
                <a href="forgot_password.php" style="background:#ff6600;color:#000;padding:8px 16px;border-radius:6px;text-decoration:none;font-size:0.9em;font-weight:bold;box-shadow:0 0 15px #ff6600;border:2px solid #ff6600;">Forgot Password</a>
                <a href="help.php" style="background:#00ffff;color:#000;padding:8px 16px;border-radius:6px;text-decoration:none;font-size:0.9em;font-weight:bold;box-shadow:0 0 15px #00ffff;border:2px solid #00ffff;">Help</a>
            </div>
        </form>
        </div>
    </div>

</body>
</html>
