<?php
include 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

if (isset($_POST['accept_volunteer'])) {
    $volunteer_id = $_POST['volunteer_id'];
    $stmt = $conn->prepare("UPDATE volunteers SET status = 'accepted' WHERE id = ?");
    $stmt->bind_param("i", $volunteer_id);
    $stmt->execute();
    header("Location: events.php");
}

if (isset($_POST['reject_volunteer'])) {
    $volunteer_id = $_POST['volunteer_id'];
    $stmt = $conn->prepare("UPDATE volunteers SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $volunteer_id);
    $stmt->execute();
    header("Location: events.php");
}
?>