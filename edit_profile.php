<?php
session_start();
include 'config.php';
include 'header.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (isset($_POST['update'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, gender = ? WHERE username = ?");
    $stmt->bind_param("ssss", $full_name, $email, $gender, $username);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: profile.php");
    } else {
        $error = 'Error updating profile';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="edit-profile-container">
        <h2>Edit Profile</h2>
        <form action="" method="post">
            <div class="input-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>">
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>">
            </div>
            <div class="input-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="male" <?php if ($user['gender'] == 'male') echo 'selected'; ?>>Male</option>
                    <option value="female" <?php if ($user['gender'] == 'female') echo 'selected'; ?>>Female</option>
                    <option value="other" <?php if ($user['gender'] == 'other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
            <button type="submit" name="update">Update Profile</button>
        </form>
    </div>

    <script src="script.js"></script>
</body>
</html>

<style>
body {
    background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
}
.edit-profile-container {
    width: 80%;
    margin: 40px auto;
    padding: 20px;
    background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
    border: 1px solid #0591f7;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    color:#fff;
}
.topbar {
    background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
    color: #0591f7;
}
.input-group {
    margin-bottom: 20px;
}

.input-group label {
    display: block;
    margin-bottom: 10px;
}

.input-group input[type="text"], .input-group input[type="email"] {
    width: 100%;
    height: 40px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.input-group select {
    width: 100%;
    height: 40px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

button[type="submit"] {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    background-color: #337ab7;
    color: #fff;
    cursor: pointer;
}
</style>
<script>
// Add event listener to form submission
document.querySelector('form').addEventListener('submit', function(event) {
    // Validate form fields
    var fullName = document.getElementById('full_name').value.trim();
    var email = document.getElementById('email').value.trim();

    if (fullName === '' || email === '') {
        alert('Please fill out all fields.');
        event.preventDefault();
    }
});
</script>