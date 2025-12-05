<?php
include 'config.php';

if (isset($_POST['content']) && isset($_POST['post_id'])) {
    $content = $_POST['content'];
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    $query = "INSERT INTO comments (post_id, user_id, content) VALUES ('$post_id', '$user_id', '$content')";
    $result = $conn->query($query);

    if ($result) {
        header("Location: index.php");
    } else {
        echo "Error creating comment";
    }
}
?>
