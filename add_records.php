<?php
include "db.php";

$aid = $_GET['aid'];

if(isset($_POST['save'])) {
    $diag = $_POST['diagnosis'];
    $notes = $_POST['notes'];

    // Save medical record
    $conn->query("INSERT INTO records(appointment_id,diagnosis,notes)
                  VALUES('$aid','$diag','$notes')");

    // ✅ Update appointment status
    $conn->query("UPDATE appointments SET status='Completed' WHERE id='$aid'");

    echo "Record saved and appointment marked as completed!";
}
?>

<h3>Add Medical Record</h3>

<form method="POST">
Diagnosis:<br>
<textarea name="diagnosis"></textarea><br><br>

Notes:<br>
<textarea name="notes"></textarea><br><br>

<button name="save">Save</button>
</form>

