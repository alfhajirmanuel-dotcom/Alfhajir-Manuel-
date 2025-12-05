<?php
include 'config.php';
include 'header.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// Fetch user
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_id = $user['id'];

// Check if application ID is provided
if (!isset($_GET['id'])) {
  echo "<div style='padding:40px; color:#fff;'>Invalid request.</div>";
  exit;
}

$app_id = intval($_GET['id']);

// Fetch application details
$stmt = $conn->prepare("
  SELECT volunteers.*, events.title AS event_title, events.date, events.time
  FROM volunteers 
  JOIN events ON volunteers.event_id = events.id
  WHERE volunteers.id = ? AND volunteers.user_id = ?
");
$stmt->bind_param("ii", $app_id, $user_id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();

if (!$app) {
  echo "<div style='padding:40px; color:#fff;'>Application not found.</div>";
  exit;
}

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $notes = trim($_POST['notes']);
  $status = $app['status']; // Users can’t change status directly

  // Optional: handle file upload
  $file_path = $app['resume_path'];
  if (!empty($_FILES['resume']['name'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $target_file = $target_dir . basename($_FILES["resume"]["name"]);

    if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
      $file_path = $target_file;
    }
  }

  $stmt = $conn->prepare("UPDATE volunteers SET notes = ?, resume_path = ? WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ssii", $notes, $file_path, $app_id, $user_id);
  $stmt->execute();

  echo "<script>alert('Your application has been updated successfully.');window.location.href='my_applications.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit My Application</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<style>
body {
  background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
  color: #fff;
  font-family: "Poppins", sans-serif;
}
.container {
  max-width: 700px;
  margin-top: 50px;
  background: rgba(255,255,255,0.05);
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
label { font-weight: 600; }
input, textarea {
  background: #2d2a42;
  border: none;
  color: #fff;
  border-radius: 8px;
  padding: 10px;
  width: 100%;
  margin-bottom: 15px;
}
.btn-save {
  background: linear-gradient(90deg, #007bff, #00c6ff);
  border: none;
  padding: 10px 18px;
  color: #fff;
  border-radius: 12px;
  transition: all 0.2s ease;
}
.btn-save:hover {
  background: linear-gradient(90deg, #0056b3, #00a6ff);
}
.status-badge {
  padding: 6px 12px;
  border-radius: 10px;
  font-weight: 600;
}
.status-badge.pending { background: #c1940a33; color: #ffd64f; }
.status-badge.accepted { background: #0a6b1b33; color: #05f75e; }
.status-badge.rejected { background: #5a0a0a33; color: #f55; }
</style>
</head>
<body>

<div class="container">
  <h3>📝 Edit Application for <?php echo htmlspecialchars($app['event_title']); ?></h3>
  <p><strong>Date:</strong> <?php echo htmlspecialchars($app['date']); ?> | 
     <strong>Time:</strong> <?php echo htmlspecialchars($app['time']); ?></p>
  <p><strong>Status:</strong> 
     <span class="status-badge <?php echo strtolower($app['status']); ?>">
       <?php echo ucfirst($app['status']); ?>
     </span>
  </p>

  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label for="notes">Notes / Message:</label>
      <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($app['notes'] ?? ''); ?></textarea>
    </div>

    <div class="form-group">
      <label for="resume">Upload Updated Resume (optional):</label>
      <input type="file" id="resume" name="resume">
      <?php if (!empty($app['resume_path'])): ?>
        <small>Current file: <a href="<?php echo htmlspecialchars($app['resume_path']); ?>" target="_blank" style="color:#00c6ff;">View</a></small>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn-save">💾 Save Changes</button>
    <a href="my_applications.php" class="btn btn-secondary ml-2">⬅ Back</a>
  </form>
</div>

</body>
</html>
