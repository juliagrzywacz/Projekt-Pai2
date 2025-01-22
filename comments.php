<?php
session_start();
require 'baza.php';
$db = getDatabaseConnection();

$postId = $_GET['post_id'] ?? null;
if (!$postId) {
    header('Location: index.php');
    exit;
}

// Obsługa sortowania
$sortOrder = isset($_GET['sort']) && $_GET['sort'] == 'oldest' ? 'ASC' : 'DESC';

// Pobieranie wszystkich komentarzy dla posta
$comments = $db->prepare("SELECT comments.content, comments.created_at, users.login 
                          FROM comments 
                          JOIN users ON comments.user_id = users.id 
                          WHERE post_id = ? 
                          ORDER BY comments.created_at $sortOrder");
$comments->execute([$postId]);
$comments = $comments->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komentarze</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Komentarze</h1>
            <nav class="menu">
                <a href="index.php">Wróć do postów</a>
            </nav>
        </header>

        <h2>Komentarze</h2>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <strong><?= htmlspecialchars($comment['login']) ?>:</strong>
                <span><?= htmlspecialchars($comment['content']) ?></span>
                <small><?= $comment['created_at'] ?></small>
            </div>
        <?php endforeach; ?>

        <!-- Opcje sortowania -->
        <p><a href="?post_id=<?= $postId ?>&sort=newest">Sortuj od najnowszych</a> | <a href="?post_id=<?= $postId ?>&sort=oldest">Sortuj od najstarszych</a></p>
    </div>
</body>
</html>
