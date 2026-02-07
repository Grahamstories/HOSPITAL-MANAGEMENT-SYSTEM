<?php
// One-time password reset script - RUN ONLY ON LOCALHOST and DELETE AFTER USE
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('Forbidden');
}

include 'db.php';

$email = 'samuelwasswa72@gmail.com';
$newPassword = 'wasswa'; // change this if you prefer
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param('ss', $hash, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Password for $email updated. New password: $newPassword\n";
    echo "Please delete this file (set_admin_pw.php) after logging in.";
} else {
    echo "No rows updated - check that the email exists.\n";
    echo "You can view users at verify_admin.php";
}
?>