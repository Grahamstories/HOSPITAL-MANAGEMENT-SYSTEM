<?php
include 'db.php';

$res = $conn->query("SELECT id, name, email, role FROM users ORDER BY id DESC LIMIT 20");
if (!$res) {
    echo 'Query failed: ' . $conn->error;
    exit;
}

echo "<h2>Users</h2>";
echo "<table border=1 cellpadding=6><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($r = $res->fetch_assoc()) {
    echo "<tr><td>" . htmlspecialchars($r['id']) . "</td><td>" . htmlspecialchars($r['name']) . "</td><td>" . htmlspecialchars($r['email']) . "</td><td>" . htmlspecialchars($r['role']) . "</td></tr>";
}
echo "</table>";
?>