<?php session_start(); include 'config.php'; include 'header.php'; if (!isset($_SESSION['username'])) { header("Location: login.php"); } $username = $_SESSION['username']; if (isset($_POST['change_password'])) { $current_password = $_POST['current_password']; $new_password = $_POST['new_password']; $confirm_password = $_POST['confirm_password']; $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?"); $stmt->bind_param("s", $username); $stmt->execute(); $result = $stmt->get_result(); $user = $result->fetch_assoc(); if ($current_password === $user['password']) { if ($new_password === $confirm_password) { $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?"); $stmt->bind_param("ss", $new_password, $username); $stmt->execute(); $success = "Password changed successfully!"; } else { $error = "New passwords do not match."; } } else { $error = "Current password is incorrect."; } } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
  <div class="change-password-container">
    <h2><i class="fa-solid fa-lock"></i> Change Password</h2>
    <?php if (isset($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php elseif (isset($success)): ?>
      <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    <form action="" method="post">
      <div class="input-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password">
      </div>
      <div class="input-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password">
      </div>
      <div class="input-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password">
      </div>
      <button type="submit" name="change_password">Change Password</button>
    </form>
  </div>
 
</body>
</html>

<style>
body{
    background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
}
.change-password-container {
  max-width: 400px;
  margin: 40px auto;
  padding: 20px;
  background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
  border: 1px solid #e0e7ef;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  color:#fff;
}

.change-password-container h2 {
  margin-top: 0;
}

.input-group {
  margin-bottom: 20px;
}

.input-group label {
  display: block;
  margin-bottom: 10px;
}

.input-group input {
  width: 100%;
  height: 40px;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

button[type="submit"] {
  background-color: #4CAF50;
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button[type="submit"]:hover {
  background-color: #3e8e41;
}

.error-message {
  background-color: #f44336;
  color: #fff;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 20px;
}

.success-message {
  background-color: #8BC34A;
  color: #fff;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 20px;
}

.settings-links ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.settings-links li {
  margin-bottom: 10px;
}

.settings-links a {
  text-decoration: none;
  color: #337ab7;
}

.settings-links a:hover {
  color: #23527c;
}

.settings-links i {
  margin-right: 10px;
}
 .topbar {
    background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
    color: #0591f7;
}
</style>