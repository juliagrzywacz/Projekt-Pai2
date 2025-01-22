<?php
session_start();
require 'baza.php';

$db = getDatabaseConnection();

// Wylogowanie użytkownika
if (isset($_GET['logout'])) {
    session_destroy(); 
    header('Location: zad1.php');  // Przekierowanie na stronę logowania
    exit;
}

// Rejestracja użytkownika
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rejestracja
    if (isset($_POST['submit_register'])) {
        $login = $_POST['login'];
        $password = $_POST['password'];

        // Sprawdzanie, czy login już istnieje
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetchColumn() > 0) {
            echo "Login jest już zajęty.";
            exit;
        }

        // Zapisanie użytkownika do bazy
        $stmt = $db->prepare("INSERT INTO users (login, password_hash) VALUES (?, ?)");
        $stmt->execute([$login, password_hash($password, PASSWORD_DEFAULT)]);
        header('Location: zad1.php');
        exit;
    }

    // Logowanie
    if (isset($_POST['submit_login'])) {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        // Sprawdzanie, czy użytkownik istnieje
        $stmt = $db->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Sprawdzenie hasła
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = ['id' => $user['id'], 'login' => $user['login']];
                header('Location: zad1.php');
                exit;
            } else {
                echo "Błąd: Nieprawidłowe hasło.";
            }
        } else {
            echo "Błąd: Użytkownik nie istnieje.";
        }
    }

    // Dodawanie postu
    if (isset($_POST['add_post']) && isset($_SESSION['user'])) {
        $content = $_POST['content'];
        $userId = $_SESSION['user']['id'];

        // Sprawdzanie długości treści
        if (strlen($content) == 0 || strlen($content) > 280) {
            echo "Treść wpisu musi mieć od 1 do 280 znaków.";
            exit;
        }

        // Zapisanie postu
        $stmt = $db->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->execute([$userId, $content]);

        // Przekierowanie po dodaniu postu
        header('Location: zad1.php');
        exit;
    }
}

// Pobieranie ostatnich wpisów z bazy danych
$posts = $db->query("SELECT posts.id, posts.content, posts.created_at, users.login 
                     FROM posts 
                     JOIN users ON posts.user_id = users.id 
                     ORDER BY posts.created_at DESC 
                     LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platforma Mikroblogowa</title>
</head>
<body>
    <h1>Witaj na Platformie Mikroblogowej</h1>

    <?php if (isset($_SESSION['user'])): ?>
        <p>Zalogowano jako: <?= htmlspecialchars($_SESSION['user']['login']) ?> (<a href="?logout=1">Wyloguj</a>)</p>
        
        <!-- Formularz dodawania posta -->
        <form method="post">
            <textarea name="content" rows="4" cols="50" maxlength="280" required></textarea><br>
            <button type="submit" name="add_post">Dodaj Wpis</button>
        </form>
    <?php else: ?>
        <h2>Zaloguj się</h2>
        <form method="post">
            <input type="text" name="login" placeholder="Login" required><br>
            <input type="password" name="password" placeholder="Hasło" required><br>
            <button type="submit" name="submit_login">Zaloguj</button>
        </form>

        <h2>Rejestracja</h2>
        <form method="post">
            <input type="text" name="login" placeholder="Login" required><br>
            <input type="password" name="password" placeholder="Hasło" required><br>
            <button type="submit" name="submit_register">Zarejestruj</button>
        </form>
    <?php endif; ?>

    <h2>Ostatnie wpisy</h2>
    <?php foreach ($posts as $post): ?>
        <div>
            <strong><?= htmlspecialchars($post['login']) ?>:</strong>
            <p><?= htmlspecialchars($post['content']) ?></p>
            <small><?= $post['created_at'] ?></small>
        </div>
        <hr>
    <?php endforeach; ?>
</body>
</html>
