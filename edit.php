<?php
session_start();
require 'baza.php';
$db = getDatabaseConnection();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Rozróżnienie, czy edytowany jest post, czy komentarz
if (isset($_GET['post_id'])) {
    $postId = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);

    // Pobieranie postu z bazy
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$postId, $_SESSION['user']['id']]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: index.php');
        exit;
    }

    $editType = 'post';
    $content = $post['content'];

} elseif (isset($_GET['comment_id'])) {
    $commentId = filter_input(INPUT_GET, 'comment_id', FILTER_VALIDATE_INT);

    // Pobieranie komentarza z bazy
    $stmt = $db->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user']['id']]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        header('Location: index.php');
        exit;
    }

    $editType = 'comment';
    $content = $comment['content'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING));

    if (!empty($content)) {
        if ($editType === 'post') {
            $stmt = $db->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$content, $postId, $_SESSION['user']['id']]);
        } elseif ($editType === 'comment') {
            $stmt = $db->prepare("UPDATE comments SET content = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$content, $commentId, $_SESSION['user']['id']]);
        }

        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edycja</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="edit-container">
        <h1>Edycja <?= $editType === 'post' ? 'Postu' : 'Komentarza' ?></h1>
        <form action="edit.php?<?= $editType === 'post' ? 'post_id' : 'comment_id' ?>=<?= $editType === 'post' ? $post['id'] : $comment['id'] ?>" method="post">
            <textarea name="content" rows="4" cols="50" maxlength="280" required><?= htmlspecialchars($content) ?></textarea><br>
            <button type="submit">Zapisz zmiany</button>
        </form>
        <a href="index.php">Powrót</a>
    </div>
</body>
</html>
