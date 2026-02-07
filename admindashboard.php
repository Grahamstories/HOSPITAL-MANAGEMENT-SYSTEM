<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!$conn) {
    die("Database connection failed.");
}

$create_msg = '';
$create_err = '';

// Handle user deletion
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id !== $_SESSION['id']) { // prevent self-deletion
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $del_id);
        if ($stmt->execute()) {
            $create_msg = 'User deleted successfully.';
        } else {
            $create_err = 'Failed to delete user.';
        }
    } else {
        $create_err = 'Cannot delete your own account.';
    }
}

// Handle admin-created users
if (isset($_POST['create_user'])) {
    $c_name = trim($_POST['name']);
    $c_email = trim($_POST['email']);
    $c_password = $_POST['password'];
    $c_role = isset($_POST['role']) && in_array($_POST['role'], ['admin','doctor','receptionist']) ? $_POST['role'] : 'receptionist';

    if ($c_name === '' || $c_email === '' || $c_password === '') {
        $create_err = 'Please fill all required fields.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $c_email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $create_err = 'Email is already registered.';
        } else {
            $hash = password_hash($c_password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?, ?, ?, ?)");
            $ins->bind_param('ssss', $c_name, $c_email, $hash, $c_role);
            if ($ins->execute()) {
                $create_msg = 'User created successfully.';
            } else {
                $create_err = 'Failed to create user.';
            }
        }
    }
}

$pRes = $conn->query("SELECT COUNT(*) total FROM patients");
$p = $pRes ? $pRes->fetch_assoc() : ['total' => 0];
$dRes = $conn->query("SELECT COUNT(*) total FROM doctors");
$d = $dRes ? $dRes->fetch_assoc() : ['total' => 0];
$aRes = $conn->query("SELECT COUNT(*) total FROM appointments");
$a = $aRes ? $aRes->fetch_assoc() : ['total' => 0];
$uRes = $conn->query("SELECT id, name, email, role FROM users ORDER BY role DESC, name ASC");
$users = $uRes ? $uRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - HMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #0a0a0a; color: #00ff00; font-family: 'Courier New', monospace; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #00ffff; padding-bottom: 15px; }
        h1 { font-size: 28px; text-shadow: 0 0 10px #00ff00; margin: 0; }
        .logout-btn { background: #ff6600; color: #0a0a0a; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .logout-btn:hover { background: #ff8822; box-shadow: 0 0 20px rgba(255,102,0,0.5); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #0d0d0d; border: 2px solid #00ffff; border-radius: 6px; padding: 20px; text-align: center; box-shadow: 0 0 10px rgba(0,255,255,0.2); }
        .stat-number { font-size: 36px; color: #00ff00; font-weight: bold; text-shadow: 0 0 15px #00ff00; }
        .stat-label { color: #00ffff; margin-top: 8px; }
        .section { background: #0d0d0d; border: 2px solid #00ffff; border-radius: 6px; padding: 20px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,255,255,0.2); }
        .section h3 { color: #00ff00; text-shadow: 0 0 10px #00ff00; margin-top: 0; }
        form { display: grid; gap: 12px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        input, select { background: #0a0a0a; color: #00ff00; border: 1px solid #00ffff; padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; }
        input:focus, select:focus { outline: none; border-color: #ff6600; box-shadow: 0 0 15px rgba(255,102,0,0.3); }
        button { background: #ff6600; color: #0a0a0a; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #ff8822; box-shadow: 0 0 20px rgba(255,102,0,0.5); }
        .msg { padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        .msg.success { background: rgba(0,255,0,0.1); color: #00ff00; border: 1px solid #00ff00; }
        .msg.error { background: rgba(255,0,0,0.1); color: #ff6666; border: 1px solid #ff6666; }
        .users-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .users-table th { background: #1a1a1a; color: #00ffff; padding: 12px; text-align: left; border-bottom: 2px solid #00ff00; }
        .users-table td { padding: 12px; border-bottom: 1px solid #00ffff; }
        .users-table tr:hover { background: #1a1a1a; }
        .role-badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .role-admin { background: #ff6600; color: #0a0a0a; }
        .role-doctor { background: #00ffff; color: #0a0a0a; }
        .role-receptionist { background: #00ff00; color: #0a0a0a; }
        .delete-link { color: #ff6666; text-decoration: none; cursor: pointer; }
        .delete-link:hover { text-decoration: underline; color: #ff8888; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Admin Dashboard</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if ($create_err) echo "<div class='msg error'>" . htmlspecialchars($create_err) . "</div>"; ?>
    <?php if ($create_msg) echo "<div class='msg success'>" . htmlspecialchars($create_msg) . "</div>"; ?>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $p['total']; ?></div>
            <div class="stat-label">Patients</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $d['total']; ?></div>
            <div class="stat-label">Doctors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $a['total']; ?></div>
            <div class="stat-label">Appointments</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($users); ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>

    <div class="section">
        <h3>Create User Account</h3>
        <p style="color: #00ffff; font-size: 12px; margin-bottom: 15px;">Create as many accounts as needed. Form clears after each successful creation.</p>
        <form method="POST" action="">
            <div class="form-row">
                <input type="text" name="name" placeholder="Full name" required value="<?php echo isset($_POST['name']) && $create_err ? htmlspecialchars($_POST['name']) : ''; ?>">
                <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) && $create_err ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="receptionist" <?php echo isset($_POST['role']) && $_POST['role'] === 'receptionist' && $create_err ? 'selected' : ''; ?>>Receptionist</option>
                <option value="doctor" <?php echo isset($_POST['role']) && $_POST['role'] === 'doctor' && $create_err ? 'selected' : ''; ?>>Doctor</option>
                <option value="admin" <?php echo isset($_POST['role']) && $_POST['role'] === 'admin' && $create_err ? 'selected' : ''; ?>>Admin</option>
            </select>
            <button type="submit" name="create_user">Create Account</button>
        </form>
    </div>

    <div class="section">
        <h3>Manage Users</h3>
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['id']): ?>
                            <a class="delete-link" onclick="return confirm('Delete user: <?php echo htmlspecialchars($user['name']); ?>?');" href="?delete_id=<?php echo $user['id']; ?>">Delete</a>
                        <?php else: ?>
                            <span style="color:#00ffff;">(You)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
