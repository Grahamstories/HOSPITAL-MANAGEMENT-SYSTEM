<?php
include 'db.php';

$name = 'WASSWA SAMUEL';
$email = 'samuelwasswa72@gmail.com';
$password = password_hash('wasswa', PASSWORD_DEFAULT);
$role = 'admin';

$stmt = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $name, $email, $password, $role);
$stmt->execute();

echo $stmt->affected_rows ? 'Admin created' : 'error: ' . $stmt->error;
?>