<?php
session_start();

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Vérifier si l'utilisateur est connecté
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$userId) {
    header('Location: connexion.html');
    exit();
}

// Vérifier si l'ID de l'événement est passé via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = intval($_POST['event_id']);

    // Vérification si l'utilisateur est déjà inscrit
    $checkQuery = "
        SELECT * 
        FROM Participer 
        WHERE Id_user = :userId AND Id_event = :eventId
    ";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute(['userId' => $userId, 'eventId' => $eventId]);

    if ($checkStmt->rowCount() > 0) {
        $alreadyRegistered = true;
    } else {
        // Insérer dans la table Participer
        $insertQuery = "
            INSERT INTO Participer (Id_user, Id_event)
            VALUES (:userId, :eventId)
        ";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            'userId' => $userId,
            'eventId' => $eventId,
        ]);

        $alreadyRegistered = false;
    }
} else {
    header('Location: events.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription</title>
    <link rel="stylesheet" href="stylecss/confirmation_event.css">
</head>
<body>
    <div class="confirmation-container">
        <?php if ($alreadyRegistered): ?>
            <h1>Vous êtes déjà inscrit à cet événement !</h1>
            <p>Vous pouvez consulter vos inscriptions dans votre espace utilisateur.</p>
        <?php else: ?>
            <h1>Inscription réussie !</h1>
            <div class="success-animation">✔</div>
            <p>Vous êtes bien inscrit à l'événement.</p>
        <?php endif; ?>
        <a href="events.php?success=true" class="back-btn">Retour aux événements</a>
    </div>
</body>
</html>
