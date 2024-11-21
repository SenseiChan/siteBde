<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Vérification de l'ID de l'événement
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    die('ID de l\'événement non spécifié.');
}

// Vérifier si l'événement existe
$query = "
    SELECT e.Nom_event, e.Date_deb_event, a.NomNumero_rue, a.Code_postal, a.Ville 
    FROM Evenement e
    LEFT JOIN Adresse a ON e.Id_adr = a.Id_adr
    WHERE e.Id_event = :id
";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $eventId]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die('Événement introuvable.');
}

// Suppression après confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    try {
        // Supprimer l'événement
        $deleteEventQuery = "DELETE FROM Evenement WHERE Id_event = :id";
        $deleteStmt = $pdo->prepare($deleteEventQuery);
        $deleteStmt->execute(['id' => $eventId]);

        // Redirection vers la page des événements
        header('Location: events.php?delete=success');
        exit();
    } catch (Exception $e) {
        die('Erreur lors de la suppression de l\'événement : ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un événement</title>
    <link rel="stylesheet" href="stylecss/delete_event.css">
</head>
<body>
    <div class="confirmation-container">
        <h1>Confirmer la suppression</h1>
        <p>Êtes-vous sûr de vouloir supprimer l'événement suivant ?</p>
        <div class="event-details">
            <h3><?= htmlspecialchars($event['Nom_event'] ?? 'Nom inconnu') ?></h3>
            <p><strong>Date :</strong> <?= htmlspecialchars($event['Date_deb_event'] ?? 'Non spécifiée') ?></p>
            <p><strong>Adresse :</strong> 
                <?= htmlspecialchars($event['NomNumero_rue'] ?? 'Non spécifiée') ?>, 
                <?= htmlspecialchars($event['Code_postal'] ?? 'Non spécifié') ?> 
                <?= htmlspecialchars($event['Ville'] ?? 'Non spécifiée') ?>
            </p>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" class="confirm-btn">Oui, supprimer</button>
            <a href="events.php" class="cancel-btn">Annuler</a>
        </form>
    </div>
</body>
</html>
