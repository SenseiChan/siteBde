<?php
session_start();

// Vérifie si l'utilisateur est administrateur
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Redirige si l'utilisateur n'est pas admin
if (!$is_admin) {
    header("Location: accueil.php");
    exit();
}

$host = 'localhost';
$dbname = 'inf2pj_03';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fetch messages
$messagesQuery = $pdo->query("
    SELECT c.Desc_mess, c.Date_mess, u.Prenom_user, u.Nom_user
    FROM chat c
    JOIN utilisateur u ON c.Id_user = u.Id_user
    ORDER BY c.Date_mess DESC
");
$messages = $messagesQuery->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Accueil</title>
    <link rel="stylesheet" href="stylecss/styleChat.css"> <!-- Lien vers le fichier CSS -->
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <div class="chat-container">
            <h1>Chat des Admins</h1>
            <div class="chat-box">
                <?php foreach ($messages as $message): ?>
                    <div class="chat-message">
                        <p><strong><?= htmlspecialchars($message['Prenom_user'] . ' ' . $message['Nom_user']) ?></strong> - <?= date('d/m/Y H:i', strtotime($message['Date_mess'])) ?></p>
                        <p><?= htmlspecialchars($message['Desc_mess']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <form action="send_message.php" method="post" class="chat-form">
                <textarea name="message" rows="3" placeholder="Tapez votre message..." required></textarea>
                <button type="submit">Envoyer</button>
            </form>
        </div>
    </main>
    
</body>
</html>