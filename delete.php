<?php
session_start();
require 'baza.php';
$db = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_type'], $_POST['id'], $_SESSION['user'])) {
    $deleteType = $_POST['delete_type'];
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $userId = $_SESSION['user']['id'];

    if ($deleteType === 'post') {
        $stmt = $db->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
    } elseif ($deleteType === 'comment') {
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
    }

    header('Location: index.php');
    exit;
}
header('Location: index.php');
exit;
