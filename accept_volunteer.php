<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['volunteer_id']) && isset($_GET['event_id'])) {
    $volunteer_id = $_GET['volunteer_id'];
    $event_id = $_GET['event_id'];

    $stmt = $conn->prepare("UPDATE volunteers SET status = 'accepted' WHERE id = ? AND event_id = ?");
    $stmt->bind_param("ii", $volunteer_id, $event_id);
    $stmt->execute();

    header("Location: my_events.php");
    exit;
} else {
    header("Location: my_events.php");
    exit;
}
?>
