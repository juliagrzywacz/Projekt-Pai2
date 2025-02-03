<?php
session_start();
require 'baza.php';
$db = getDatabaseConnection();

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$posts = [];

if (!empty($query)) {
    $queryLike = "%$query%";

    $stmt = $db->prepare("
        SELECT DISTINCT posts.id, posts.content, posts.created_at, posts.user_id, users.login 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        LEFT JOIN hashtags ON posts.id = hashtags.post_id
        WHERE posts.content LIKE :query 
        OR users.login LIKE :query 
        OR hashtags.hashtag LIKE :query
        ORDER BY posts.created_at DESC
    ");
    $stmt->bindParam(':query', $queryLike, PDO::PARAM_STR);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wyniki wyszukiwania</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Wyniki wyszukiwania</h1>
        <a href="index.php">🔙 Powrót</a>

        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <strong><?= htmlspecialchars($post['login']) ?>:</strong>
                    <p><?= htmlspecialchars($post['content']) ?></p>
                    <small><?= htmlspecialchars($post['created_at']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Brak wyników dla "<?= htmlspecialchars($_GET['query']) ?>".</p>
        <?php endif; ?>
    </div>
</body>
</html>
