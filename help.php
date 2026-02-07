<?php
// Help page - no login required
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Help - Hospital Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), linear-gradient(135deg, #667eea 0%, #764ba2 100%) center/cover no-repeat;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(255,255,255,0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 0 10px rgba(227, 0, 255, 0.3);
        }
        h2 {
            color: #764ba2;
            border-bottom: 3px solid #ffb900;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .section {
            margin: 20px 0;
            line-height: 1.8;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            background: rgba(102, 126, 234, 0.1);
            padding: 12px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        .faq {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #ff5956;
        }
        .faq strong {
            color: #764ba2;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
        }
        .back-link a:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hospital Management System - Help & FAQ</h1>
        
        <div class="section">
            <h2>About the Application</h2>
            <p>The Hospital Management System is a comprehensive platform designed to streamline hospital operations and manage patient care efficiently. Our system helps healthcare providers manage patients, appointments, medical records, and doctors' schedules seamlessly.</p>
        </div>

        <div class="section">
            <h2>Key Features</h2>
            <ul class="feature-list">
                <li><strong>Patient Management:</strong> Register and manage patient information, medical history, and contact details.</li>
                <li><strong>Doctor Management:</strong> Maintain a directory of doctors with their specializations.</li>
                <li><strong>Appointment Scheduling:</strong> Book and track patient appointments with doctors.</li>
                <li><strong>Medical Records:</strong> Store and retrieve medical records, diagnoses, and treatment notes.</li>
                <li><strong>User Roles:</strong> Different access levels for admin, doctors, and reception staff.</li>
                <li><strong>Photo Upload:</strong> Upload and manage patient photos for identification.</li>
            </ul>
        </div>

        <div class="section">
            <h2>Getting Started</h2>
            <ol>
                <li><strong>Login:</strong> Enter your email and password to access the system.</li>
                <li><strong>Dashboard:</strong> View your dashboard based on your role (Admin, Doctor, or Receptionist).</li>
                <li><strong>Navigate:</strong> Use the navigation options to access different modules.</li>
                <li><strong>Manage Data:</strong> Add, edit, or view patient and appointment information.</li>
            </ol>
        </div>

        <div class="section">
            <h2>Frequently Asked Questions</h2>
            
            <div class="faq">
                <strong>Q: How do I reset my password?</strong><br>
                A: Click on the "Forgot Password" button on the login page and enter your email address. An admin will assist you with password reset.
            </div>

            <div class="faq">
                <strong>Q: How do I upload a patient photo?</strong><br>
                A: Go to the Patients section and select "Add Patient". You can upload a JPG, PNG, or GIF image (max 2MB).
            </div>

            <div class="faq">
                <strong>Q: What are the different user roles?</strong><br>
                A: <br>
                - <strong>Admin:</strong> Full access to the system, can create users and manage all data.<br>
                - <strong>Doctor:</strong> Can view appointments and add medical records.<br>
                - <strong>Receptionist:</strong> Can manage patient information and book appointments.
            </div>

            <div class="faq">
                <strong>Q: How do I book an appointment?</strong><br>
                A: Go to the Appointments section, select a patient and doctor, choose a date/time, and confirm. The appointment status will be "Pending" until confirmed.
            </div>

            <div class="faq">
                <strong>Q: Can I edit patient information after creation?</strong><br>
                A: Yes, you can view patient details and edit information from the Patients page (permissions depend on your role).
            </div>

            <div class="faq">
                <strong>Q: Who should I contact for technical support?</strong><br>
                A: Contact your system administrator or the hospital IT department for technical assistance.
            </div>
        </div>

        <div class="section">
            <h2>System Requirements</h2>
            <p>
                This application requires a modern web browser (Chrome, Firefox, Safari, or Edge) with JavaScript enabled. 
                The system is mobile-friendly and can be accessed from desktop, tablet, or smartphone devices.
            </p>
        </div>

        <div class="section">
            <h2>Security & Privacy</h2>
            <p>
                All patient data is securely stored and password-protected. Only authorized personnel can access sensitive medical information. 
                Please ensure you log out after each session and never share your login credentials.
            </p>
        </div>

        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>
