<?php
include 'config.php';
include 'header.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

if (isset($_POST['apply_volunteer'])) {
    $event_id = $_POST['event_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    // Upload ID
    $id_upload = $_FILES['id_upload']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["id_upload"]["name"]);

    if (move_uploaded_file($_FILES["id_upload"]["tmp_name"], $target_file)) {
        // Get the organizer ID
        $stmt = $conn->prepare("SELECT user_id FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        $organizer_id = $event['user_id'];

        $application_date = date("Y-m-d");
        $stmt = $conn->prepare("INSERT INTO volunteer_applications (event_id, user_id, full_name, email, contact_number, age, gender, address, id_upload, application_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssiisss", $event_id, $user_id, $full_name, $email, $contact_number, $age, $gender, $address, $id_upload, $application_date);

        if ($stmt->execute()) {
            // Send notification to event organizer
            $notification_message = "New volunteer application for Event " . $event_id;
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, notification_type, notification_message) VALUES (?, 'new_application', ?)");
            $stmt->bind_param("is", $organizer_id, $notification_message);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO volunteers (event_id, user_id, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("ii", $event_id, $user_id);
            if ($stmt->execute()) {
                echo "<script>
                    alert('✅ Application submitted successfully!');
                    window.location.href='events.php';
                </script>";
                exit;
            } else {
                echo "<script>
                    alert('⚠️ Failed to save volunteer record.');
                    window.location.href='events.php';
                </script>";
                exit;
            }
        } else {
            echo "<script>
                alert('❌ Error submitting application: " . $stmt->error . "');
                window.location.href='apply_volunteer.php';
            </script>";
            exit;
        }
    } 
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply as Volunteer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="volunteer-form-container">
        <h2>Apply as Volunteer</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
            <label for="full_name">Complete Name:</label>
            <input type="text" name="full_name" required>
            <br>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <br>
            <label for="gender">Contact Number:</label>
            <input type="text" name="gender" required>
            <br>
            <label for="age">Age:</label>
            <input type="number" name="age" required>
            <br>
            <label for="contact_number">Gender:</label>
            <select name="contact_number" required>
                <option value="">Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
            <br>
            <label for="address">Address:</label>
            <textarea name="address" required></textarea>
            <br>
            <label for="id_upload">Upload Resume:</label>
            <input type="file" name="id_upload" required>
            <br>
            <button type="submit" name="apply_volunteer">Apply as Volunteer</button>
        </form>
    </div>
</body>
</html>

<style>
.volunteer-form-container {
    width: 50%;
    margin: 40px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    color:#fff;
      max-height: 90vh; /* Adjust height depending on how much you want visible */
  overflow-y: auto;
  padding-right: 8px; /* space for scrollbar */
}
body{
    background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
    
}
 html, body {
  height: 107%;
  margin: 0;
  font-family: "Poppins", "Segoe UI", Inter, Roboto, Arial, sans-serif;
   background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
  color: #222;
}
.topbar {
    background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
    color: #0591f7;
}
.volunteer-form-container h2 {
    text-align: center;
    margin-bottom: 20px;
}

.volunteer-form-container label {
    display: block;
    margin-bottom: 10px;
}

.volunteer-form-container input[type="text"],
.volunteer-form-container input[type="email"],
.volunteer-form-container input[type="number"],
.volunteer-form-container select,
.volunteer-form-container textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
}

.volunteer-form-container input[type="file"] {
    margin-bottom: 20px;
}

.volunteer-form-container button[type="submit"] {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.volunteer-form-container button[type="submit"]:hover {
    background-color: #3e8e41;
}

/* ✅ RESPONSIVE STYLING (ADDED ONLY THIS PART) */
@media (max-width: 1024px) {
    .volunteer-form-container {
        width: 70%;
    }
}

@media (max-width: 768px) {
    .volunteer-form-container {
        width: 90%;
        padding: 15px;
    }
    .volunteer-form-container h2 {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .volunteer-form-container {
        width: 95%;
        margin: 20px auto;
        padding: 10px;
        box-shadow: none;
        
    }
    .volunteer-form-container input,
    .volunteer-form-container select,
    .volunteer-form-container textarea {
        font-size: 14px;
        padding: 8px;
    }
    .volunteer-form-container button[type="submit"] {
        width: 100%;
        font-size: 15px;
        padding: 10px;
        
    }
}
</style>

