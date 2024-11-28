<?php
session_start();

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: accueil.php");
    exit();
}

$eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Récupérer les informations de l'événement
try {
    $eventQuery = $pdo->prepare("
        SELECT Nom_event, Date_deb_event 
        FROM Evenement 
        WHERE Id_event = :eventId
    ");
    $eventQuery->execute(['eventId' => $eventId]);
    $event = $eventQuery->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        die("Événement introuvable.");
    }
} catch (PDOException $e) {
    die('Erreur lors de la récupération des informations de l\'événement : ' . $e->getMessage());
}

// Récupérer les participants
try {
    $participantsQuery = $pdo->prepare("
        SELECT u.Nom_user, u.Prenom_user, u.Email_user
        FROM Utilisateur u
        JOIN Participer p ON u.Id_user = p.Id_user
        WHERE p.Id_event = :eventId
    ");
    $participantsQuery->execute(['eventId' => $eventId]);
    $participants = $participantsQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors de la récupération des participants : ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - <?= htmlspecialchars($event['Nom_event']) ?></title>
    <link rel="stylesheet" href="stylecss/voir_participant.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <h1>Participants de l'événement : <?= htmlspecialchars($event['Nom_event']) ?></h1>

        <?php if (empty($participants)): ?>
            <p>Aucun participant inscrit pour cet événement.</p>
        <?php else: ?>
            <div class="participants-table">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $participant): ?>
                            <tr>
                                <td><?= htmlspecialchars($participant['Nom_user']) ?></td>
                                <td><?= htmlspecialchars($participant['Prenom_user']) ?></td>
                                <td><?= htmlspecialchars($participant['Email_user']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <!-- Bouton Retour -->
        <div class="back-button-container">
            <a href="events.php" class="back-button">Retour</a>
        </div>
    </main>
</body>
</html>
