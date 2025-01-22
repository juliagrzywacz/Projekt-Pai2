<?php
session_start();
require 'baza.php';
$db = getDatabaseConnection();

// Pobieranie postów
$posts = $db->query("SELECT posts.id, posts.content, posts.created_at, users.login 
                     FROM posts 
                     JOIN users ON posts.user_id = users.id 
                     ORDER BY posts.created_at DESC 
                     LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// Obsługa dodawania komentarzy
if (isset($_POST['submit_comment']) && isset($_SESSION['user'])) {
    $commentContent = $_POST['comment_content'];
    $postId = $_POST['post_id'];
    $userId = $_SESSION['user']['id'];

    // Zapisz komentarz do bazy
    $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $userId, $commentContent]);

    // Przekierowanie na tę samą stronę, aby zaktualizować widok
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platforma Mikroblogowa</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Platforma Mikroblogowa</h1>
            <div class="menu-toggle" onclick="toggleMenu()">☰</div>
            <nav class="menu">
                <?php if (isset($_SESSION['user'])): ?>
                    <p><a href="settings.php"><?= htmlspecialchars($_SESSION['user']['login']) ?></a></p>
                <?php else: ?>
                    <a href="login.php">Logowanie</a>
                    <a href="register.php">Rejestracja</a>
                <?php endif; ?>
            </nav>
        </header>

        <?php if (isset($_SESSION['user'])): ?>
            <!-- Dodawanie postów -->
            <form action="post.php" method="post">
                <textarea name="content" rows="4" cols="50" maxlength="280" required placeholder="Napisz coś..."></textarea><br>
                <button type="submit" name="add_post">Dodaj Wpis</button>
            </form>
        <?php endif; ?>

        <h2>Ostatnie wpisy</h2>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <strong><?= htmlspecialchars($post['login']) ?>:</strong>
                <p><?= htmlspecialchars($post['content']) ?></p>
                <small><?= $post['created_at'] ?></small>

                <!-- Wyświetlanie komentarzy -->
                <h3>Komentarze:</h3>
                <?php
                $postId = $post['id'];
                $comments = $db->prepare("SELECT comments.content, comments.created_at, users.login 
                                          FROM comments 
                                          JOIN users ON comments.user_id = users.id 
                                          WHERE post_id = ? ORDER BY comments.created_at DESC LIMIT 3");
                $comments->execute([$postId]);
                $comments = $comments->fetchAll(PDO::FETCH_ASSOC);

                foreach ($comments as $comment): ?>
                    <div class="comment">
                        <strong><?= htmlspecialchars($comment['login']) ?>:</strong>
                        <span><?= htmlspecialchars($comment['content']) ?></span>
                        <small><?= $comment['created_at'] ?></small>
                    </div>
                <?php endforeach; ?>

                <?php if (count($comments) >= 3): ?>
                    <p><a href="comments.php?post_id=<?= $postId ?>">Pokaż więcej komentarzy...</a></p>
                <?php endif; ?>

                <!-- Formularz dodawania komentarza -->
                <?php if (isset($_SESSION['user'])): ?>
                    <form method="post">
                        <input type="hidden" name="post_id" value="<?= $postId ?>">
                        <textarea name="comment_content" rows="3" cols="50" maxlength="280" required placeholder="Napisz komentarz..."></textarea><br>
                        <button type="submit" name="submit_comment">Dodaj Komentarz</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
