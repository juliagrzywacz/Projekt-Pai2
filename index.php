<?php
// Rozpoczynamy sesję tylko raz
session_start();

require 'baza.php';
$db = getDatabaseConnection();

// Pobieranie postów
$posts = $db->query("SELECT posts.id, posts.content, posts.created_at, posts.user_id, users.login FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie komentarzy dla postów
$comments = [];
foreach ($posts as $post) {
    $stmt = $db->prepare("SELECT comments.id, comments.content, comments.created_at, comments.user_id, users.login FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC LIMIT 3");
    $stmt->execute([$post['id']]);
    $comments[$post['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obsługa dodawania komentarzy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'], $_SESSION['user'])) {
    $commentContent = trim(filter_input(INPUT_POST, 'comment_content', FILTER_SANITIZE_STRING));
    $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $userId = $_SESSION['user']['id'];

    if (!empty($commentContent) && $postId) {
        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $userId, $commentContent]);
    }

    header('Location: index.php');
    exit;
}

// Pobieranie postów konkretnego użytkownika
$viewUser = isset($_GET['view_user']) ? $_GET['view_user'] : null;
if ($viewUser) {
    $posts = $db->prepare("SELECT posts.id, posts.content, posts.created_at, posts.user_id, users.login FROM posts JOIN users ON posts.user_id = users.id WHERE users.login = ? ORDER BY posts.created_at DESC");
    $posts->execute([$viewUser]);
    $posts = $posts->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLAB</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>
                <img src="img/cheers.png" alt="Logo Cheers" class="logo"> BLAB
            </h1>
            <nav>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="settings.php"><?= htmlspecialchars($_SESSION['user']['login']) ?></a>

                <?php else: ?>
                    <a href="login.php">Logowanie</a>
                    <a href="register.php">Rejestracja</a>
                <?php endif; ?>
            </nav>
        </header>

        <?php if (isset($_SESSION['user'])): ?>
            <form action="post.php" method="post">
                <textarea name="content" rows="4" maxlength="280" required placeholder="Napisz coś..."></textarea>
                <button type="submit" name="add_post">Dodaj Wpis</button>
            </form>
        <?php endif; ?>

        <h2>Ostatnie wpisy</h2>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <strong><?= htmlspecialchars($post['login']) ?>:</strong>
                <p><?= htmlspecialchars($post['content']) ?></p>
                <small><?= htmlspecialchars($post['created_at']) ?></small>
                
                <div class="likes">
                    <form action="like.php" method="post">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" name="like" class="like-button">
                            <img src="img/like.png" alt="Lubię to" class="like-icon">
                        </button>
                    </form>
                    <span class="like-count">
                        <?php
                        $likes = $db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
                        $likes->execute([$post['id']]);
                        $likeCount = $likes->fetchColumn();
                        echo $likeCount;
                        ?>
                    </span>
                </div>


                <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] === $post['user_id']): ?>
                    <div class="options">
                        <button class="options-btn">...</button>
                        <div class="options-menu">
                            <form action="edit.php" method="get">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit">Edytuj</button>
                            </form>
                            <form action="delete.php" method="post">
                                <input type="hidden" name="delete_type" value="post">
                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                <button type="submit" name="delete">Usuń</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="comments">
                    <?php $postComments = $comments[$post['id']] ?? []; ?>
                    <?php foreach ($postComments as $comment): ?>
                        <div class="comment">
                            <strong><?= htmlspecialchars($comment['login']) ?>:</strong>
                            <p><?= htmlspecialchars($comment['content']) ?></p>
                            <small><?= htmlspecialchars($comment['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>

                    <a href="comments.php?post_id=<?= $post['id'] ?>">Pokaż wszystkie komentarze</a>
                </div>

                <?php if (isset($_SESSION['user'])): ?>
                    <form action="index.php" method="post">
                        <textarea name="comment_content" rows="2" maxlength="280" required placeholder="Dodaj komentarz..."></textarea>
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" name="submit_comment">Dodaj komentarz</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if (empty($posts)): ?>
            <p>Brak postów do wyświetlenia.</p>
        <?php endif; ?>
    </div>
</body>

</html>