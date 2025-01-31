<?php
session_start();

require 'baza.php';
$db = getDatabaseConnection();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['like'])) {
    $postId = $_POST['post_id'];
    $userId = $_SESSION['user']['id'];

    // Sprawdzenie, czy użytkownik już polubił ten post
    $stmt = $db->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);
    $like = $stmt->fetch();

    if ($like) {
        // Użytkownik już polubił, więc usuwamy polubienie
        $stmt = $db->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
    } else {
        // Użytkownik jeszcze nie polubił, więc dodajemy polubienie
        $stmt = $db->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$postId, $userId]);
    }
}

header('Location: index.php');
exit;
?>