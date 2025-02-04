<?php
// Définir le fuseau horaire pour garantir des dates correctes
date_default_timezone_set('Europe/Paris');

session_start();

// Vérifier si l'utilisateur est administrateur
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if (!$is_admin) {
    header("Location: index.php");
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
    die("Erreur : " . $e->getMessage());
}

// Ajouter une nouvelle promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_promo'])) {
    $nom_promo = trim($_POST['nom_promo']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $pourcentage = intval($_POST['pourcentage']);

    // Validation des champs
    if (!empty($nom_promo) && !empty($date_debut) && !empty($date_fin) && $pourcentage > 0 && $pourcentage <= 100) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO promotion (Nom_promo, Date_deb_promo, Date_fin_promo, Pourcentage_promo)
                VALUES (:nom_promo, :date_debut, :date_fin, :pourcentage)
            ");
            $stmt->execute([
                'nom_promo' => $nom_promo,
                'date_debut' => $date_debut,
                'date_fin' => $date_fin,
                'pourcentage' => $pourcentage,
            ]);
            $success_message = "La promotion a été ajoutée avec succès.";
        } catch (PDOException $e) {
            $error_message = "Erreur lors de l'ajout de la promotion : " . $e->getMessage();
        }
    } else {
        $error_message = "Veuillez remplir tous les champs correctement.";
    }
}

// Supprimer une promotion
if (isset($_POST['delete_promo_id'])) {
    $promo_id = intval($_POST['delete_promo_id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM promotion WHERE Id_promo = :id");
        $stmt->execute(['id' => $promo_id]);
        $success_message = "La promotion a été supprimée avec succès.";
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression de la promotion : " . $e->getMessage();
    }
}

// Récupérer les promotions et séparer en deux catégories
$promotions_en_cours = [];
$promotions_autres = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM promotion ORDER BY Date_fin_promo DESC");
    $stmt->execute();
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentDate = new DateTime('now', new DateTimeZone('Europe/Paris'));

    foreach ($promotions as $promo) {
        $startDate = new DateTime($promo['Date_deb_promo'], new DateTimeZone('Europe/Paris'));
        $endDate = new DateTime($promo['Date_fin_promo'], new DateTimeZone('Europe/Paris'));

        if ($currentDate >= $startDate && $currentDate <= $endDate) {
            $promotions_en_cours[] = $promo; // En cours
        } else {
            $promotions_autres[] = $promo; // Expirées ou en attente
        }
    }
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des promotions : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des promotions</title>
    <link rel="stylesheet" href="stylecss/add_promo.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <h1>Gestion des promotions</h1>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Formulaire d'ajout de promotion -->
        <form method="POST" class="promo-form">
            <div class="form-group">
                <label for="nom_promo">Nom de la promotion :</label>
                <input type="text" id="nom_promo" name="nom_promo" required>
            </div>
            <div class="form-group">
                <label for="date_debut">Date de début :</label>
                <input type="datetime-local" id="date_debut" name="date_debut" required>
            </div>
            <div class="form-group">
                <label for="date_fin">Date de fin :</label>
                <input type="datetime-local" id="date_fin" name="date_fin" required>
            </div>
            <div class="form-group">
                <label for="pourcentage">Pourcentage de la promotion :</label>
                <input type="number" id="pourcentage" name="pourcentage" min="1" max="100" required>
            </div>
            <button type="submit" class="submit-btn">Ajouter la promotion</button>
        </form>

        <!-- Promotions en cours -->
        <h2>Promotions en cours</h2>
        <div class="promo-table">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Pourcentage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promotions_en_cours)): ?>
                        <tr>
                            <td colspan="5">Aucune promotion en cours.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promotions_en_cours as $promo): ?>
                            <tr>
                                <td><?= htmlspecialchars($promo['Nom_promo']) ?></td>
                                <td><?= (new DateTime($promo['Date_deb_promo']))->format('d M Y à H:i') ?></td>
                                <td><?= (new DateTime($promo['Date_fin_promo']))->format('d M Y à H:i') ?></td>
                                <td><?= htmlspecialchars($promo['Pourcentage_promo']) ?>%</td>
                                <td>
                                    <form method="post" class="delete-form">
                                        <input type="hidden" name="delete_promo_id" value="<?= htmlspecialchars($promo['Id_promo']) ?>">
                                        <button type="submit" class="delete-btn">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Promotions en attente ou expirées -->
        <h2>Promotions en attente ou expirées</h2>
        <div class="promo-table">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Pourcentage</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promotions_autres)): ?>
                        <tr>
                            <td colspan="6">Aucune promotion en attente ou expirée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promotions_autres as $promo): ?>
                            <?php
                            $currentDate = new DateTime('now', new DateTimeZone('Europe/Paris'));
                            $startDate = new DateTime($promo['Date_deb_promo'], new DateTimeZone('Europe/Paris'));
                            $endDate = new DateTime($promo['Date_fin_promo'], new DateTimeZone('Europe/Paris'));
                            $status = ($currentDate < $startDate) ? 'En attente' : 'Expirée';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($promo['Nom_promo']) ?></td>
                                <td><?= $startDate->format('d M Y à H:i') ?></td>
                                <td><?= $endDate->format('d M Y à H:i') ?></td>
                                <td><?= htmlspecialchars($promo['Pourcentage_promo']) ?>%</td>
                                <td><?= $status ?></td>
                                <td>
                                    <form method="post" class="delete-form">
                                        <input type="hidden" name="delete_promo_id" value="<?= htmlspecialchars($promo['Id_promo']) ?>">
                                        <button type="submit" class="delete-btn">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
