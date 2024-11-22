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

// Récupérer les participants
$participantsQuery = "
    SELECT u.Id_user, u.Nom_user, u.Prenom_user 
    FROM Participer p
    JOIN Utilisateur u ON p.Id_user = u.Id_user
    WHERE p.Id_event = :eventId
";
$participantsStmt = $pdo->prepare($participantsQuery);
$participantsStmt->execute(['eventId' => $eventId]);
$participants = $participantsStmt->fetchAll(PDO::FETCH_ASSOC);

// Désinscription d'un utilisateur (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;
    $userId = $input['user_id'] ?? null;

    if ($action === 'remove_user' && $userId) {
        $deleteQuery = "DELETE FROM Participer WHERE Id_user = :userId AND Id_event = :eventId";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute(['userId' => $userId, 'eventId' => $eventId]);

        // Retourner la liste mise à jour des participants
        $participantsStmt->execute(['eventId' => $eventId]);
        $updatedParticipants = $participantsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'participants' => $updatedParticipants]);
        exit();
    }

    if ($action === 'delete_event') {
        // Vérifier s'il reste des participants
        $participantsStmt->execute(['eventId' => $eventId]);
        $remainingParticipants = $participantsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($remainingParticipants)) {
            $deleteEventQuery = "DELETE FROM Evenement WHERE Id_event = :eventId";
            $deleteEventStmt = $pdo->prepare($deleteEventQuery);
            $deleteEventStmt->execute(['eventId' => $eventId]);

            echo json_encode(['success' => true, 'message' => 'Événement supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer un événement avec des participants inscrits.']);
        }
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Action non reconnue ou données invalides.']);
    exit();
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

        <?php if (!empty($participants)): ?>
            <h2>Participants inscrits</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="participants-table">
                        <?php foreach ($participants as $participant): ?>
                            <tr data-user-id="<?= $participant['Id_user'] ?>">
                                <td><?= htmlspecialchars($participant['Nom_user']) ?></td>
                                <td><?= htmlspecialchars($participant['Prenom_user']) ?></td>
                                <td>
                                    <button class="action-btn remove-user-btn" data-user-id="<?= $participant['Id_user'] ?>">Retirer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Aucun utilisateur inscrit à cet événement.</p>
        <?php endif; ?>

        <button id="delete-event-btn" class="confirm-btn" <?= empty($participants) ? '' : 'disabled' ?>>
            Supprimer l'événement
        </button>
        <a href="events.php" class="cancel-btn">Annuler</a>
    </div>

    <div id="popup" class="popup"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteEventButton = document.getElementById('delete-event-btn');
            const participantsTable = document.getElementById('participants-table');
            const popup = document.getElementById('popup');

            const showPopup = (message, success = true) => {
                popup.textContent = message;
                popup.className = `popup ${success ? 'success' : 'error'}`;
                popup.classList.add('show');
                setTimeout(() => popup.classList.remove('show'), 3000);
            };

            const updateRemoveButtons = () => {
                const removeUserButtons = document.querySelectorAll('.remove-user-btn');

                removeUserButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const userId = button.dataset.userId;

                        fetch('', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'remove_user', user_id: userId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Met à jour le tableau
                                participantsTable.innerHTML = data.participants.map(participant => `
                                    <tr data-user-id="${participant.Id_user}">
                                        <td>${participant.Nom_user}</td>
                                        <td>${participant.Prenom_user}</td>
                                        <td>
                                            <button class="action-btn remove-user-btn" data-user-id="${participant.Id_user}">Retirer</button>
                                        </td>
                                    </tr>
                                `).join('');

                                // Réactive les boutons après la mise à jour
                                updateRemoveButtons();

                                if (data.participants.length === 0) {
                                    deleteEventButton.disabled = false; // Active le bouton supprimer
                                }

                                showPopup('Utilisateur désinscrit avec succès !', true);
                            } else {
                                showPopup(data.message || 'Erreur lors de la désinscription.', false);
                            }
                        })
                        .catch(error => showPopup('Une erreur est survenue.', false));
                    });
                });
            };

            // Initialise les boutons "Retirer" au chargement
            updateRemoveButtons();

            deleteEventButton.addEventListener('click', () => {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_event' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showPopup(data.message, true);
                        setTimeout(() => window.location.href = 'events.php', 2000);
                    } else {
                        showPopup(data.message || 'Erreur lors de la suppression.', false);
                    }
                })
                .catch(error => showPopup('Une erreur est survenue.', false));
            });
        });
    </script>
</body>
</html>