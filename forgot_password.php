<?php
include "db.php";

$message = '';
if (isset($_POST['send'])) {
    $email = trim($_POST['email']);
    
    if ($email === '') {
        $error = 'Please enter your email.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $message = "A password reset request has been sent to your email. Please contact the admin.";
            // TODO: Send email notification to admin with user request
        } else {
            $error = 'Email not found in the system.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)), linear-gradient(135deg, #667eea 0%, #764ba2 100%) center/cover no-repeat;
            display:flex;flex-direction:column;justify-content:center;align-items:center;min-height:100vh;margin:0;padding:20px;font-family:Arial,Helvetica,sans-serif;
        }
        h2 {
            color: #fff;
            font-size: 2em;
            text-shadow: 0 0 20px rgba(227, 0, 255, 0.6);
            margin-bottom: 30px;
        }
        .container {
            background: rgba(0,0,0,0.7);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(227, 0, 255, 0.3);
            max-width: 400px;
            width: 100%;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid rgba(227, 0, 255, 0.4);
            border-radius: 6px;
            background: rgba(255,255,255,0.1);
            color: white;
            box-sizing: border-box;
        }
        input::placeholder {
            color: rgba(255,255,255,0.6);
        }
        button {
            width: 100%;
            padding: 10px;
            background: #ff5956;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 10px;
        }
        button:hover {
            background: #e84543;
        }
        .message {
            color: #4caf50;
            margin: 10px 0;
            text-align: center;
        }
        .error {
            color: #ff5956;
            margin: 10px 0;
            text-align: center;
        }
        a {
            color: #ffb900;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 15px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Forgot Password</h2>
    <div class="container">
        <?php if (isset($message)) echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
        
        <form method="POST" action="">
            <label style="color:#fff;">Enter your email address:</label>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit" name="send">Send Password Reset Request</button>
        </form>
        
        <a href="login.php">← Back to Login</a>
    </div>
</body>
</html>
