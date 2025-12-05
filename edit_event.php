<?php 
session_start(); 
include 'config.php'; 
include 'header.php'; 

if (!isset($_SESSION['username'])) { 
  header("Location: login"); 
  exit; 
} 

if (isset($_GET['event_id'])) { 
  $event_id = $_GET['event_id']; 
  $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?"); 
  $stmt->bind_param("i", $event_id); 
  $stmt->execute(); 
  $result = $stmt->get_result(); 
  $event = $result->fetch_assoc(); 

  if (!$event) { 
    $_SESSION['error'] = "Event not found";
    header("Location: my_events.php"); 
    exit; 
  } 

  if (isset($_POST['update'])) { 
    $title = $_POST['title']; 
    $description = $_POST['description']; 
    $date = $_POST['date']; 
    $time = $_POST['time']; 

    // Image upload 
    if ($_FILES['image']['name']) { 
      $image_name = $_FILES['image']['name']; 
      $image_tmp = $_FILES['image']['tmp_name']; 
      $image_size = $_FILES['image']['size']; 
      $image_type = $_FILES['image']['type']; 

      // Validate image 
      $allowed_types = array('image/jpeg', 'image/png', 'image/gif'); 
      if (!in_array($image_type, $allowed_types)) { 
        $_SESSION['error'] = "Invalid image type";
        header("Location: edit_event.php?event_id=$event_id"); 
        exit; 
      } 

      // Upload image 
      $upload_dir = 'uploads/'; 
      $new_image_name = uniqid() . '.' . pathinfo($image_name, PATHINFO_EXTENSION); 
      $upload_path = $upload_dir . $new_image_name; 
      if (move_uploaded_file($image_tmp, $upload_path)) {
        // Update image in database 
        $stmt = $conn->prepare("UPDATE events SET image = ? WHERE id = ?"); 
        $stmt->bind_param("si", $new_image_name, $event_id); 
        $stmt->execute(); 
      } else {
        $_SESSION['error'] = "Failed to upload image";
        header("Location: edit_event.php?event_id=$event_id"); 
        exit; 
      }
    } 

    // Update event 
    $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, date = ?, time = ? WHERE id = ?"); 
    $stmt->bind_param("ssssi", $title, $description, $date, $time, $event_id); 
    if ($stmt->execute()) {
      $_SESSION['success'] = "Event updated successfully";
      header("Location: my_events.php"); 
      exit; 
    } else {
      $_SESSION['error'] = "Failed to update event";
      header("Location: edit_event.php?event_id=$event_id"); 
      exit; 
    }
  } 
} else { 
  $_SESSION['error'] = "Event ID not provided";
  header("Location: my_events.php"); 
  exit; 
} 

if (isset($_SESSION['success'])) {
  echo "<p style='color: green'>" . $_SESSION['success'] . "</p>";
  unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
  echo "<p style='color: red'>" . $_SESSION['error'] . "</p>";
  unset($_SESSION['error']);
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-height: 130vh; /* Adjust height depending on how much you want visible */
            overflow-y: auto;
            padding-right: 8px; /* space for scrollbar */
        }
          .topbar {
            background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
            color: #0591f7;
        }
    
          html, body {
           height: 107%;
           margin: 0;
           font-family: "Poppins", "Segoe UI", Inter, Roboto, Arial, sans-serif;
           background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
           color: #222;
         }
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group input[type="file"] {
            padding: 0;
        }

        .form-group button[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-group button[type="submit"]:hover {
            background-color: #3e8e41;
        }

        .image-preview {
            width: 100px;
            height: 100px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Event</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo $event['title']; ?>">
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo $event['description']; ?></textarea>
            </div>
            <div class="form-group">
    <label for="date">Date:</label>
    <input type="date" id="date" name="date" value="<?php echo $event['date']; ?>">
</div>
<div class="form-group">
    <label for="time">Time:</label>
    <input type="time" id="time" name="time" value="<?php echo $event['time']; ?>">
</div>
<div class="form-group">
    <label for="image">Image:</label>
    <input type="file" id="image" name="image">
    <?php if ($event['image']) { ?>
        <img src="uploads/<?php echo $event['image']; ?>" class="image-preview">
    <?php } ?>
</div>
<button type="submit" name="update">Update</button>
</form>
</div>
</body>
</html>
