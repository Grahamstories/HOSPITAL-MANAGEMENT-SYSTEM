<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'receptionist') {
    header("Location: login.php");
    exit;
}

$msg = '';
$err = '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'appointments';

// Create appointment
if (isset($_POST['create_apt'])) {
    $patient_id = (int)$_POST['patient_id'];
    $doctor_id = (int)$_POST['doctor_id'];
    $apt_date = $_POST['appointment_date'];
    
    if ($patient_id && $doctor_id && $apt_date) {
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param('iis', $patient_id, $doctor_id, $apt_date);
        if ($stmt->execute()) {
            $msg = 'Appointment created successfully.';
        } else {
            $err = 'Failed to create appointment.';
        }
    } else {
        $err = 'Please fill all required fields.';
    }
}

// Get patients, doctors, appointments
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
$doctors = $conn->query("SELECT id, name FROM doctors ORDER BY name");
$appointments = $conn->query("SELECT a.id, p.name as patient_name, d.name as doctor_name, a.appointment_date, a.status FROM appointments a JOIN patients p ON a.patient_id = p.id JOIN doctors d ON a.doctor_id = d.id ORDER BY a.appointment_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receptionist Dashboard - HMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #0a0a0a; color: #00ff00; font-family: 'Courier New', monospace; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #00ffff; padding-bottom: 15px; }
        h1 { font-size: 28px; text-shadow: 0 0 10px #00ff00; margin: 0; }
        .logout-btn { background: #ff6600; color: #0a0a0a; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .logout-btn:hover { background: #ff8822; box-shadow: 0 0 20px rgba(255,102,0,0.5); }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #00ffff; padding-bottom: 15px; }
        .tab-btn { background: #0d0d0d; border: 1px solid #00ffff; color: #00ff00; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        .tab-btn.active { background: #ff6600; color: #0a0a0a; border-color: #ff6600; }
        .tab-btn:hover { background: #1a1a1a; }
        .section { background: #0d0d0d; border: 2px solid #00ffff; border-radius: 6px; padding: 20px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,255,255,0.2); }
        .section h3 { color: #00ff00; text-shadow: 0 0 10px #00ff00; margin-top: 0; }
        form { display: grid; gap: 12px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        input, select, textarea { background: #0a0a0a; color: #00ff00; border: 1px solid #00ffff; padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #ff6600; box-shadow: 0 0 15px rgba(255,102,0,0.3); }
        button { background: #ff6600; color: #0a0a0a; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #ff8822; box-shadow: 0 0 20px rgba(255,102,0,0.5); }
        .msg { padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        .msg.success { background: rgba(0,255,0,0.1); color: #00ff00; border: 1px solid #00ff00; }
        .msg.error { background: rgba(255,0,0,0.1); color: #ff6666; border: 1px solid #ff6666; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { background: #1a1a1a; color: #00ffff; padding: 12px; text-align: left; border-bottom: 2px solid #00ff00; }
        .table td { padding: 12px; border-bottom: 1px solid #00ffff; }
        .table tr:hover { background: #1a1a1a; }
        .patient-card { background: #1a1a1a; border: 1px solid #00ffff; border-radius: 4px; padding: 15px; margin-bottom: 10px; cursor: pointer; }
        .patient-card:hover { background: #252525; box-shadow: 0 0 10px rgba(0,255,255,0.3); }
        .patient-name { font-size: 16px; color: #00ff00; font-weight: bold; }
        .patient-email { font-size: 12px; color: #00ffff; margin-top: 5px; }
        .status-pending { color: #ffff00; font-weight: bold; }
        .status-accepted { color: #00ff00; font-weight: bold; }
        .status-rejected { color: #ff3333; font-weight: bold; }
        .hidden { display: none; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Receptionist Dashboard</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if ($msg) echo "<div class='msg success'>" . htmlspecialchars($msg) . "</div>"; ?>
    <?php if ($err) echo "<div class='msg error'>" . htmlspecialchars($err) . "</div>"; ?>

    <div class="tabs">
        <button class="tab-btn <?php echo $tab === 'appointments' ? 'active' : ''; ?>" onclick="switchTab('appointments')">Create Appointment</button>
        <button class="tab-btn <?php echo $tab === 'patients' ? 'active' : ''; ?>" onclick="switchTab('patients')">View Patients</button>
        <button class="tab-btn <?php echo $tab === 'all_apts' ? 'active' : ''; ?>" onclick="switchTab('all_apts')">All Appointments</button>
    </div>

    <!-- Create Appointment Tab -->
    <div id="appointments" class="section <?php echo $tab !== 'appointments' ? 'hidden' : ''; ?>">
        <h3>Create New Appointment</h3>
        <form method="POST" action="">
            <!-- Patient Identification -->
            <div style="background: #1a1a1a; border: 1px solid #00ffff; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                <h4 style="color: #ff6600; margin-top: 0;">Patient Identification</h4>
                <div class="form-row">
                    <div>
                        <label style="color: #00ffff; margin-bottom: 5px; display: block;">Search Patient by Name</label>
                        <input type="text" id="patientSearch" placeholder="Type patient name..." autocomplete="off">
                        <div id="patientDropdown" style="position: absolute; background: #0d0d0d; border: 1px solid #00ffff; border-radius: 4px; margin-top: 2px; z-index: 10; width: 300px; max-height: 200px; overflow-y: auto; display: none;"></div>
                        <input type="hidden" id="patient_id" name="patient_id" required>
                        <div id="selectedPatient" style="color: #00ff00; margin-top: 5px; font-weight: bold;"></div>
                    </div>
                    <div>
                        <label style="color: #00ffff; margin-bottom: 5px; display: block;">Patient ID (Auto-filled)</label>
                        <input type="text" id="display_patient_id" placeholder="Auto-filled when patient selected" readonly style="background: #0a0a0a; color: #00ffff; cursor: not-allowed;">
                    </div>
                </div>
            </div>

            <!-- Appointment Details -->
            <div style="background: #1a1a1a; border: 1px solid #00ffff; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                <h4 style="color: #ff6600; margin-top: 0;">Appointment Details</h4>
                <div class="form-row">
                    <div>
                        <label style="color: #00ffff; margin-bottom: 5px; display: block;">Select Doctor</label>
                        <select name="doctor_id" required>
                            <option value="">-- Choose a Doctor --</option>
                            <?php while ($doctor = $doctors->fetch_assoc()): ?>
                                <option value="<?php echo $doctor['id']; ?>"><?php echo htmlspecialchars($doctor['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label style="color: #00ffff; margin-bottom: 5px; display: block;">Appointment Date & Time</label>
                        <input type="datetime-local" name="appointment_date" required>
                    </div>
                </div>
            </div>

            <!-- Visit Information -->
            <div style="background: #1a1a1a; border: 1px solid #00ffff; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                <h4 style="color: #ff6600; margin-top: 0;">Reason for Visit</h4>
                <div>
                    <label style="color: #00ffff; margin-bottom: 5px; display: block;">Chief Complaint / Reason for Appointment</label>
                    <textarea name="chief_complaint" placeholder="e.g., Fever, Back pain, Follow-up, General checkup..." style="width: 100%; height: 80px; resize: vertical;"></textarea>
                </div>
                <div class="form-row" style="margin-top: 12px;">
                    <div>
                        <label style="color: #00ffff; margin-bottom: 5px; display: block;">Symptoms</label>
                        <textarea name="symptoms" placeholder="e.g., Headache, Cough, Dizziness..." style="width: 100%; height: 80px; resize: vertical;"></textarea>
                    </div>
                    <div>
                        <label style="color: #00ffff; margin-bottom: 5px; display: block;">Duration of Symptoms</label>
                        <input type="text" name="symptom_duration" placeholder="e.g., 3 days, 2 weeks...">
                    </div>
                </div>
                <div style="margin-top: 12px;">
                    <label style="color: #00ffff; margin-bottom: 5px; display: block;">Additional Notes</label>
                    <textarea name="notes" placeholder="Any additional information (allergies, medications, previous conditions, etc.)" style="width: 100%; height: 80px; resize: vertical;"></textarea>
                </div>
            </div>

            <button type="submit" name="create_apt" style="width: 100%; padding: 12px; font-size: 16px;">Create Appointment</button>
        </form>
    </div>

    <!-- View Patients Tab -->
    <div id="patients" class="section <?php echo $tab !== 'patients' ? 'hidden' : ''; ?>">
        <h3>Patient Directory & History</h3>
        <input type="text" id="searchInput" placeholder="Search patients by name..." style="width: 100%; margin-bottom: 20px;">
        <div id="patientsList">
            <?php 
            $patients->data_seek(0);
            while ($patient = $patients->fetch_assoc()): 
                $patient_id = $patient['id'];
                $apt_count = $conn->query("SELECT COUNT(*) total FROM appointments WHERE patient_id = $patient_id")->fetch_assoc();
                $records_count = $conn->query("SELECT COUNT(*) total FROM records WHERE patient_id = $patient_id")->fetch_assoc();
            ?>
            <div class="patient-card" onclick="togglePatientDetails(this, <?php echo $patient_id; ?>)">
                <div class="patient-name"><?php echo htmlspecialchars($patient['name']); ?></div>
                <div style="color: #00ffff; font-size: 12px; margin-top: 8px;">
                    📅 Appointments: <?php echo $apt_count['total']; ?> | 📋 Medical Records: <?php echo $records_count['total']; ?>
                </div>
            </div>
            <div id="details-<?php echo $patient_id; ?>" class="hidden" style="background: #252525; border-left: 3px solid #ff6600; padding: 15px; margin-bottom: 10px; border-radius: 4px;">
                <h4 style="color: #ff6600;">Appointment History</h4>
                <table class="table" style="font-size: 12px;">
                    <tr>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                    <?php 
                    $apts = $conn->query("SELECT a.appointment_date, d.name, a.status FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.patient_id = $patient_id ORDER BY a.appointment_date DESC");
                    while ($apt = $apts->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($apt['name']); ?></td>
                        <td><?php echo htmlspecialchars($apt['appointment_date']); ?></td>
                        <td><span class="status-<?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <h4 style="color: #ff6600; margin-top: 15px;">Medical Records</h4>
                <table class="table" style="font-size: 12px;">
                    <tr>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                    <?php 
                    $recs = $conn->query("SELECT r.date_created, d.name, r.notes FROM records r JOIN doctors d ON r.doctor_id = d.id WHERE r.patient_id = $patient_id ORDER BY r.date_created DESC LIMIT 5");
                    if ($recs && $recs->num_rows > 0) {
                        while ($rec = $recs->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rec['name']); ?></td>
                            <td><?php echo htmlspecialchars($rec['date_created']); ?></td>
                            <td><?php echo htmlspecialchars(substr($rec['notes'], 0, 50)) . '...'; ?></td>
                        </tr>
                        <?php endwhile;
                    } else {
                        echo "<tr><td colspan='3' style='text-align: center; color: #00ffff;'>No medical records yet</td></tr>";
                    }
                    ?>
                </table>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- All Appointments Tab -->
    <div id="all_apts" class="section <?php echo $tab !== 'all_apts' ? 'hidden' : ''; ?>">
        <h3>All Appointments</h3>
        <table class="table">
            <tr>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date & Time</th>
                <th>Status</th>
            </tr>
            <?php while ($apt = $appointments->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                <td><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                <td><?php echo htmlspecialchars($apt['appointment_date']); ?></td>
                <td><span class="status-<?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.getElementById('appointments').classList.add('hidden');
            document.getElementById('patients').classList.add('hidden');
            document.getElementById('all_apts').classList.add('hidden');
            
            // Show selected tab
            document.getElementById(tabName).classList.remove('hidden');
            
            // Update button styles
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Update URL
            window.history.replaceState({}, '', '?tab=' + tabName);
        }

        function togglePatientDetails(card, patientId) {
            const details = document.getElementById('details-' + patientId);
            details.classList.toggle('hidden');
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const search = this.value.toLowerCase();
            const patients = document.querySelectorAll('.patient-card');
            patients.forEach(patient => {
                const text = patient.textContent.toLowerCase();
                patient.style.display = text.includes(search) ? 'block' : 'none';
            });
        });

        // Patient search dropdown functionality
        const patientSearch = document.getElementById('patientSearch');
        const patientDropdown = document.getElementById('patientDropdown');
        const patientInput = document.getElementById('patient_id');
        const selectedPatient = document.getElementById('selectedPatient');

        const allPatients = <?php 
            $patients->data_seek(0);
            $patientsArray = [];
            while ($p = $patients->fetch_assoc()) {
                $patientsArray[] = $p;
            }
            echo json_encode($patientsArray);
        ?>;

        patientSearch.addEventListener('keyup', function() {
            const search = this.value.toLowerCase();
            
            if (search.length === 0) {
                patientDropdown.style.display = 'none';
                return;
            }

            const matches = allPatients.filter(p => 
                p.name.toLowerCase().includes(search)
            );

            if (matches.length === 0) {
                patientDropdown.innerHTML = '<div style="padding: 10px; color: #ff6666;">No patients found</div>';
                patientDropdown.style.display = 'block';
                return;
            }

            patientDropdown.innerHTML = matches.map(p => 
                `<div onclick="selectPatient(${p.id}, '${p.name.replace(/'/g, "\\'")}'); event.stopPropagation();" style="padding: 10px; border-bottom: 1px solid #00ffff; cursor: pointer; color: #00ff00;">
                    ${p.name}
                </div>`
            ).join('');
            patientDropdown.style.display = 'block';
        });

        function selectPatient(id, name) {
            patientInput.value = id;
            patientSearch.value = name;
            selectedPatient.textContent = '✓ Selected: ' + name;
            patientDropdown.style.display = 'none';
        }

        document.addEventListener('click', function(e) {
            if (e.target !== patientSearch && e.target !== patientDropdown) {
                patientDropdown.style.display = 'none';
            }
        });
    </script>

</body>
</html>
