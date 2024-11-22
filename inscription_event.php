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

// Vérifier si l'ID de l'événement est passé en paramètre
if (!isset($_GET['id'])) {
    die('ID de l\'événement non fourni.');
}

$eventId = intval($_GET['id']);

// Récupérer les informations de l'événement
$query = "
    SELECT 
        e.Id_event,
        e.Nom_event, 
        e.Desc_event, 
        e.Date_deb_event, 
        e.Heure_deb_event, 
        e.Prix_event, 
        e.Photo_event, 
        a.NomNumero_rue, 
        a.Ville 
    FROM 
        Evenement e
    JOIN 
        Adresse a ON e.Id_adr = a.Id_adr
    WHERE 
        e.Id_event = :eventId
";
$stmt = $pdo->prepare($query);
$stmt->execute(['eventId' => $eventId]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die('Événement introuvable.');
}

// Gérer le paiement (inscription)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insérer une ligne d'inscription dans une table `Inscriptions` (ou similaire)
    $insertQuery = "
        INSERT INTO Inscription (Id_user, Id_event, Date_inscription)
        VALUES (:userId, :eventId, NOW())
    ";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->execute([
        'userId' => $userId,
        'eventId' => $eventId,
    ]);

    // Redirection après inscription réussie
    header('Location: events.php?status=success');
    exit();
}

// Fonction pour formater une date complète en français
function formatFullDate($date) {
    $months = [
        'January' => 'Janvier',
        'February' => 'Février',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Août',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Décembre'
    ];

    $dateTime = new DateTime($date);
    $day = $dateTime->format('d');
    $month = $dateTime->format('F');
    $year = $dateTime->format('Y');

    $month = str_replace(array_keys($months), array_values($months), $month);

    return "$day $month $year";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription à l'événement</title>
    <link rel="stylesheet" href="stylecss/inscription_event.css">
</head>
<body>
    <div class="event-registration-container">
        <h1>Inscription à l'événement</h1>
        <div class="event-summary">
            <img src="<?= htmlspecialchars($event['Photo_event']) ?>" alt="Image de l'événement" class="event-image">
            <div class="event-details">
                <h2><?= htmlspecialchars($event['Nom_event']) ?></h2>
                <p><strong>Date :</strong> <?= htmlspecialchars(formatFullDate($event['Date_deb_event'])) ?></p>
                <p><strong>Heure :</strong> <?= htmlspecialchars(date('H:i', strtotime($event['Heure_deb_event']))) ?></p>
                <p><strong>Lieu :</strong> <?= htmlspecialchars($event['NomNumero_rue'] . ', ' . $event['Ville']) ?></p>
                <p><strong>Description :</strong> <?= htmlspecialchars($event['Desc_event']) ?></p>
                <p><strong>Prix :</strong> <?= htmlspecialchars(number_format($event['Prix_event'], 2)) ?> €</p>
            </div>
        </div>
        <form action="confirmation_event.php" method="POST">
            <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['Id_event']) ?>">
            <button type="submit" class="pay-btn">Payer</button>
        </form>
        <a href="events.php" class="cancel-link">Retour aux événements</a>
    </div>
</body>
</html>
