<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header('Location: login.php');
    exit;
}

$doctor_id = isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : null;
if (!$doctor_id) {
    echo "<p>No doctor selected. Please log in as a doctor.</p>";
    exit;
}

// Handle appointment status update
if (isset($_GET['action']) && isset($_GET['id']) && in_array($_GET['action'], ['accept', 'reject'])) {
    $apt_id = (int)$_GET['id'];
    $new_status = $_GET['action'] === 'accept' ? 'accepted' : 'rejected';
    
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
    $stmt->bind_param('sii', $new_status, $apt_id, $doctor_id);
    $stmt->execute();
}

$result = $conn->query("SELECT * FROM appointments WHERE doctor_id='" . $conn->real_escape_string($doctor_id) . "' ORDER BY appointment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>My Appointments</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #0a0a0a; color: #00ff00; font-family: 'Courier New', monospace; padding: 20px; }
        .header { display: flex; justify-content: center; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #00ffff; padding-bottom: 15px; position: relative; }
        h2 { font-size: 28px; text-shadow: 0 0 15px #00ff00, 0 0 30px #00ff00; margin: 0; color: #00ff00; font-weight: bold; }
        .logout-btn { background: #ff6600; color: #0a0a0a; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; position: absolute; right: 0; }
        .logout-btn:hover { background: #ff8822; box-shadow: 0 0 20px rgba(255,102,0,0.5); }
        .container { background: #0d0d0d; border: 2px solid #00ffff; border-radius: 6px; padding: 20px; box-shadow: 0 0 10px rgba(0,255,255,0.2); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1a1a1a; color: #00ffff; padding: 12px; text-align: left; border-bottom: 2px solid #00ff00; }
        td { padding: 12px; border-bottom: 1px solid #00ffff; }
        tr:hover { background: #1a1a1a; }
        a { color: #ff6600; text-decoration: none; }
        a:hover { color: #ff8822; text-decoration: underline; }
        .btn-group { display: flex; gap: 8px; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; display: inline-block; cursor: pointer; border: none; }
        .btn-accept { background: #00ff00; color: #0a0a0a; }
        .btn-accept:hover { background: #22ff22; box-shadow: 0 0 10px rgba(0,255,0,0.5); }
        .btn-reject { background: #ff3333; color: #fff; }
        .btn-reject:hover { background: #ff5555; box-shadow: 0 0 10px rgba(255,51,51,0.5); }
        .btn-record { background: #ff6600; color: #0a0a0a; }
        .btn-record:hover { background: #ff8822; box-shadow: 0 0 10px rgba(255,102,0,0.5); }
        .status-pending { color: #ffff00; font-weight: bold; }
        .status-accepted { color: #00ff00; font-weight: bold; }
        .status-rejected { color: #ff3333; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>My Appointments</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="container">
        <table>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { 
                $status_class = 'status-' . $row['status'];
                $can_change = $row['status'] === 'pending';
            ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                <td><span class="<?= $status_class ?>"><?= ucfirst(htmlspecialchars($row['status'])) ?></span></td>
                <td>
                    <div class="btn-group">
                        <?php if ($can_change): ?>
                            <a class="btn btn-accept" href="?action=accept&id=<?= $row['id'] ?>">Accept</a>
                            <a class="btn btn-reject" href="?action=reject&id=<?= $row['id'] ?>">Reject</a>
                        <?php endif; ?>
                        <a class="btn btn-record" href="add_records.php?aid=<?= urlencode($row['id']) ?>">Add Record</a>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>