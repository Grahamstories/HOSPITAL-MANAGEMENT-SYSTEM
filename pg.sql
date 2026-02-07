USERS
 id
 name
 email
 password
 role

PATIENTS
 id
 name
 age
 gender
 contact

DOCTORS
 id
 name
 specialization

APPOINTMENTS
 id
 patient_id  ----> PATIENTS.id
 doctor_id   ----> DOCTORS.id
 appointment_date
 status

RECORDS
 id
 appointment_id ---> APPOINTMENTS.id
 diagnosis
 notes
