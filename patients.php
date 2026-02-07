<?php
include "db.php";

// Ensure patients table has a 'photo' column (adds it if missing)
$col = $conn->query("SHOW COLUMNS FROM patients LIKE 'photo'");
if ($col && $col->num_rows == 0) {
    $conn->query("ALTER TABLE patients ADD COLUMN photo VARCHAR(255) NULL");
}

if (isset($_POST['add'])) {
    // sanitize inputs
    $name = $conn->real_escape_string($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact']);

    $photoPath = null; // will store path if upload succeeds

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $tmp = $_FILES['photo']['tmp_name'];
        $orig = basename($_FILES['photo']['name']);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type. Allowed: jpg,jpeg,png,gif";
        } elseif ($_FILES['photo']['size'] > $maxSize) {
            $error = "File too large. Max 2MB.";
        } else {
            if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
            $newName = 'uploads/' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($tmp, __DIR__ . '/' . $newName)) {
                $photoPath = $newName;
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    }

    $photoSql = $photoPath ? "'" . $conn->real_escape_string($photoPath) . "'" : "NULL";

    $conn->query("INSERT INTO patients(name,age,gender,contact,photo) VALUES('$name',$age,'$gender','$contact',$photoSql)");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Patients</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;padding:20px;background:#f7f7f7}
        .card{background:#fff;padding:16px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.08);max-width:800px;margin:auto}
        input,select{padding:8px;margin:6px 0;width:100%;box-sizing:border-box}
        img.avatar{width:80px;height:80px;object-fit:cover;border-radius:6px}
        table{width:100%;border-collapse:collapse;margin-top:16px}
        th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
    </style>
</head>
<body>
<div class="card">
    <h2>Add Patient</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Name</label>
        <input type="text" name="name" required>

        <label>Age</label>
        <input type="number" name="age" min="0" required>

        <label>Gender</label>
        <select name="gender">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label>Contact</label>
        <input type="text" name="contact">

        <label>Photo (jpg, png, gif) - max 2MB</label>
        <input type="file" name="photo" accept="image/*">

        <button type="submit" name="add">Add Patient</button>
    </form>

    <h2>Patients</h2>
    <table>
        <thead>
            <tr><th>Photo</th><th>Name</th><th>Age</th><th>Gender</th><th>Contact</th></tr>
        </thead>
        <tbody>
        <?php
        $res = $conn->query("SELECT * FROM patients ORDER BY id DESC");
        while ($p = $res->fetch_assoc()) {
            $photo = $p['photo'] ? htmlspecialchars($p['photo']) : 'https://via.placeholder.com/80?text=No+Photo';
            echo "<tr>";
            echo "<td><img class='avatar' src='" . $photo . "' alt='photo'></td>";
            echo "<td>" . htmlspecialchars($p['name']) . "</td>";
            echo "<td>" . htmlspecialchars($p['age']) . "</td>";
            echo "<td>" . htmlspecialchars($p['gender']) . "</td>";
            echo "<td>" . htmlspecialchars($p['contact']) . "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>