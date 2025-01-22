<?php
session_start();
require 'baza.php';
$db = getDatabaseConnection();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user'])) {
    header('Location: index.php');  // Jeśli nie jest zalogowany, przekieruj na stronę główną
    exit;
}

// Zmiana nazwy użytkownika
if (isset($_POST['submit_change_name'])) {
    $new_name = $_POST['new_name'];
    $stmt = $db->prepare("UPDATE users SET login = ? WHERE id = ?");
    $stmt->execute([$new_name, $_SESSION['user']['id']]);
    $_SESSION['user']['login'] = $new_name;  // Zaktualizuj nazwę w sesji
    header('Location: settings.php');  // Przekierowanie do strony ustawień po zmianie
    exit;
}

// Zmiana hasła użytkownika
if (isset($_POST['submit_change_password'])) {
    $new_password = $_POST['new_password'];
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $_SESSION['user']['id']]);
    header('Location: settings.php');  // Przekierowanie po zmianie hasła
    exit;
}

// Wylogowanie użytkownika
if (isset($_GET['logout'])) {
    session_destroy(); 
    header('Location: index.php');  // Przekierowanie na stronę główną po wylogowaniu
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ustawienia - Platforma Mikroblogowa</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Ustawienia użytkownika</h1>
            <nav>
                <a href="index.php">Strona główna</a> <!-- Link powrotny do strony z postami -->
            </nav>
        </header>

        <h2>Zmiana danych</h2>

        <!-- Formularz zmiany nazwy -->
        <form method="post">
            <label for="new_name">Nowy login:</label>
            <input type="text" name="new_name" id="new_name" value="<?= htmlspecialchars($_SESSION['user']['login']) ?>" required><br>
            <button type="submit" name="submit_change_name">Zmień nazwę</button>
        </form>

        <!-- Formularz zmiany hasła -->
        <form method="post" style="margin-bottom: 30px;"> <!-- Większa przerwa między formularzami -->
            <label for="new_password">Nowe hasło:</label>
            <input type="password" name="new_password" id="new_password" required><br>
            <button type="submit" name="submit_change_password">Zmień hasło</button>
        </form>

        <!-- Wylogowanie -->
        <form method="get">
            <button type="submit" name="logout" value="1">Wyloguj</button>
        </form>
    </div>
</body>
</html>
