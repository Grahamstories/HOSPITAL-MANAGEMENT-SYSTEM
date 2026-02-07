<?php
include "db.php";

if(isset($_POST['book'])) {
    $patient = $_POST['patient'];
    $doctor = $_POST['doctor'];
    $date = $_POST['date'];

    $conn->query("INSERT INTO appointments(patient_id,doctor_id,appointment_date,status)
                  VALUES('$patient','$doctor','$date','Pending')");
}
?>

<form method="POST">
Patient ID: <input name="patient"><br><br>
Doctor ID: <input name="doctor"><br><br>
Date: <input type="datetime-local" name="date"><br><br>

<button name="book">Book Appointment</button>
</form>
