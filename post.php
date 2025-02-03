<?php
session_start();
require 'baza.php';
$db = getDatabaseConnection();

if (isset($_POST['add_post']) && isset($_SESSION['user'])) {
    $content = $_POST['content'];
    $userId = $_SESSION['user']['id'];

    if (strlen($content) == 0 || strlen($content) > 280) {
        echo "Treść wpisu musi mieć od 1 do 280 znaków.";
        exit;
    }

    $stmt = $db->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->execute([$userId, $content]);
    header('Location: index.php');
    exit;

    // Wyszukiwanie hasztagów
    preg_match_all('/#\w+/', $content, $matches);
    $hashtags = $matches[0];

    foreach ($hashtags as $hashtag) {
        $stmt = $db->prepare("INSERT INTO hashtags (post_id, hashtag) VALUES (?, ?)");
        $stmt->execute([$postId, $hashtag]);
    }
}
?>
