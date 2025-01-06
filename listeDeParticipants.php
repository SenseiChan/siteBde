<?php
session_start();

if (!isset($_GET['eventId'])) {
    die("ID de l'événement manquant.");
}

$eventId = $_GET['eventId'];

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=inf2pj_03;charset=utf8', 'inf2pj03', 'eMaht4aepa');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Récupérer les informations de l'événement
$stmtEvent = $pdo->prepare("SELECT Nom_event FROM evenement WHERE Id_event = :eventId");
$stmtEvent->execute(['eventId' => $eventId]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Événement introuvable.");
}

// Récupérer la liste des participants
$stmt = $pdo->prepare("
    SELECT u.Prenom_user, u.Nom_user, u.Email_user 
    FROM participer p
    JOIN utilisateur u ON p.Id_user = u.Id_user
    WHERE p.Id_event = :eventId
");
$stmt->execute(['eventId' => $eventId]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - <?= htmlspecialchars($event['Nom_event']) ?></title>
    <link rel="stylesheet" href="stylecss/styles_events.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <h1>Participants pour l'événement : <?= htmlspecialchars($event['Nom_event']) ?></h1>
        <table>
            <thead>
                <tr>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participants as $participant): ?>
                    <tr>
                        <td><?= htmlspecialchars($participant['Prenom_user']) ?></td>
                        <td><?= htmlspecialchars($participant['Nom_user']) ?></td>
                        <td><?= htmlspecialchars($participant['Email_user']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

            <a href="events.php" class="button-back">Retour aux événements</a>

    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
