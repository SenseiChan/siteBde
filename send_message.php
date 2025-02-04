<?php
session_start();

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);

    if (empty($message)) {
        header("Location: chat_admin.php");
        exit();
    }

    $host = 'localhost';
    $dbname = 'inf2pj_03';
    $username = 'inf2pj03';
    $password = 'eMaht4aepa';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO chat (Id_user, Desc_mess) VALUES (:userId, :message)");
        $stmt->execute([
            ':userId' => $userId,
            ':message' => $message
        ]);

        header("Location: chat_admin.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur de base de données : " . $e->getMessage());
    }
} else {
    header("Location: chat_admin.php");
    exit();
}
?>
