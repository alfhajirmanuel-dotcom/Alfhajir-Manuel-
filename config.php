<?php
$conn = new mysqli("localhost", "msfkwifi", "@Superimba1", "msfkwifi_testing");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>