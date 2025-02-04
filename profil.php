<?php
session_start(); // Démarrer la session

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit(); // Redirection si non connecté
}

// Détermine si l'utilisateur est administrateur
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Récupération de l'ID utilisateur via GET ou SESSION
if ($is_admin && isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Si l'utilisateur est admin et qu'un ID est passé via GET, utiliser cet ID
    $userId = intval($_GET['id']);
} else {
    // Sinon, utiliser l'ID utilisateur connecté
    $userId = $_SESSION['user_id'];
}

// Connexion à la base de données
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

// Récupération des informations utilisateur
$userQuery = $pdo->prepare("
    SELECT 
        u.Nom_user, 
        u.Prenom_user, 
        u.Tel_user, 
        u.Email_user, 
        u.Photo_user, 
        u.Id_role,
        g.Nom_grade,
        a.NomNumero_rue, 
        a.Ville, 
        a.Code_postal
    FROM utilisateur u
    LEFT JOIN grade g ON u.Id_grade = g.Id_grade
    LEFT JOIN adresse a ON u.Id_adr = a.Id_adr
    WHERE u.Id_user = :id
");
$userQuery->execute(['id' => $userId]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit();
}

// Le reste du code pour récupérer événements, badges, etc., reste inchangé
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

function getUserEvents($pdo, $userId, $month, $year) {
    // Récupérer les événements auxquels l'utilisateur participe
    $eventQuery = $pdo->prepare("
        SELECT e.Nom_event AS name, e.Desc_event AS description, e.Date_deb_event AS date, e.Heure_deb_event AS time
        FROM evenement e
        JOIN participer p ON e.Id_event = p.Id_event
        WHERE p.Id_user = :userId
        AND MONTH(e.Date_deb_event) = :month
        AND YEAR(e.Date_deb_event) = :year
    ");
    $eventQuery->execute([
        'userId' => $userId,
        'month' => $month,
        'year' => $year
    ]);
    $events = $eventQuery->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les événements créés par l'utilisateur dans son agenda
    $calendarQuery = $pdo->prepare("
        SELECT c.Nom_calend AS name, c.Desc_calend AS description, DATE(c.DateHeure_calend) AS date, TIME(c.DateHeure_calend) AS time
        FROM calendrier c
        WHERE c.Id_user = :userId
        AND MONTH(c.DateHeure_calend) = :month
        AND YEAR(c.DateHeure_calend) = :year
    ");
    $calendarQuery->execute([
        'userId' => $userId,
        'month' => $month,
        'year' => $year
    ]);
    $calendarEvents = $calendarQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fusionner les événements des deux tables
    return array_merge($events, $calendarEvents);
}
$userEvents = getUserEvents($pdo, $userId, $currentMonth, $currentYear);

$eventsByDay = [];
foreach ($userEvents as $event) {
    $day = (int) date('j', strtotime($event['date']));
    $eventsByDay[$day][] = $event;
}

$transactionQuery = $pdo->prepare("
    SELECT 
        t.Montant_trans, 
        t.Date_trans, 
        t.Id_event, 
        t.Id_prod, 
        t.Id_grade,
        COALESCE(e.Nom_event, p.Nom_prod, g.Nom_grade) AS Transaction_desc
    FROM transactions t
    LEFT JOIN evenement e ON t.Id_event = e.Id_event
    LEFT JOIN produit p ON t.Id_prod = p.Id_prod
    LEFT JOIN grade g ON t.Id_grade = g.Id_grade
    WHERE t.Id_user = :id
    ORDER BY t.Date_trans DESC
    LIMIT 3
");
$transactionQuery->execute(['id' => $userId]);
$transactions = $transactionQuery->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données des badges
$badgesQuery = $pdo->prepare("
    SELECT b.Id_badge, b.Nom_badge, b.Desc_badge, b.Photo_badge
    FROM decrocher d
    JOIN badge b ON d.Id_badge = b.Id_badge
    WHERE d.Id_user = :id AND d.Afficher_badge = 1
    LIMIT 3
");
$badgesQuery->execute(['id' => $userId]);
$badges = $badgesQuery->fetchAll(PDO::FETCH_ASSOC);

// Extraire uniquement les IDs des badges affichés
$badgeIds = array_column($badges, 'Id_badge');

$allBadgesQuery = $pdo->query("
    SELECT Id_badge, Nom_badge, Desc_badge, Photo_badge
    FROM badge
");
$allBadges = $allBadgesQuery->fetchAll(PDO::FETCH_ASSOC);

$userBadgesQuery = $pdo->prepare("
    SELECT Id_badge
    FROM decrocher
    WHERE Id_user = :userId
");
$userBadgesQuery->execute(['userId' => $userId]);
$userBadges = $userBadgesQuery->fetchAll(PDO::FETCH_COLUMN, 0);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile-pic'])) {
    $file = $_FILES['profile-pic'];

    // Vérifier que c'est une image
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo "Type de fichier non valide.";
        exit;
    }

    // Déplacer le fichier
    $uploadDir = 'imagesAdmin/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = $userId . '_' . time() . '_' . basename($file['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Mettre à jour la BDD
        $updateQuery = $pdo->prepare("
            UPDATE utilisateur 
            SET Photo_user = :photo 
            WHERE Id_user = :id
        ");
        $updateQuery->execute([
            ':photo' => $filePath,
            ':id' => $userId
        ]);

        // Actualiser la session si besoin
        $_SESSION['Photo_user'] = $filePath;

        // Rediriger pour éviter le rechargement du formulaire
        header("Location: profil.php");
        exit;
    } else {
        echo "Erreur lors du téléchargement de l'image.";
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Page Profil</title>
  <link rel="stylesheet" href="stylecss/styleProf.css"> <!-- Lien vers le fichier CSS -->
</head>
<body>
  <?php
  $originalUserId = $userId; // Sauvegarder l'ID initial
  include 'header.php';
  $userId = $originalUserId; // Restaurer après inclusion
  ?>
  <main class="blur-target">
    <br><br><br>
        <div class="profile-header">
            <div class="profile-title">
                <h1><?= htmlspecialchars($user['Prenom_user'] . ' ' . $user['Nom_user']) ?></h1>
                <p><?= $user['Id_role'] == 2 ? 'Admin' : 'Membre' ?></p>
                <?php

                if ($is_admin && $userId !== $_SESSION['user_id']): ?>
                    <button id="toggle-role-btn" data-user-id="<?= htmlspecialchars($userId) ?>" 
                            class="toggle-role-btn">
                        <?= $user['Id_role'] == 2 ? 'Rétrograder en Membre' : 'Promouvoir en Admin' ?>
                    </button>
                <?php endif; ?>
            </div>
            <div class="profile-picture">
                <form id="profile-pic-form" enctype="multipart/form-data" method="post">
                    <label for="profile-pic-input">
                        <img src="<?= htmlspecialchars($user['Photo_user']) ?>" alt="Profil" class="profile-icon" id="profile-pic-preview">
                    </label>
                    <input type="file" id="profile-pic-input" name="profile-pic" accept="image/*" style="display: none;">
                </form>
            </div>
        </div>

        <hr>

        <div class="profile-content">
            <!-- Agenda -->
            <div class="calendar">
              <div class="calendar-header">
                  <button onclick="changeMonth(-1)">&#8592;</button>
                  <h3><?= date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)) ?></h3>
                  <button onclick="changeMonth(1)">&#8594;</button>
                  <button class="add-event-btn">+ Ajouter</button>
              </div>
              <div class="calendar-grid">
                    <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                        <div class="calendar-day">
                            <span class="day-number"><?= $day ?></span>
                            <?php if (isset($eventsByDay[$day])): ?>
                                <div class="events">
                                    <?php foreach ($eventsByDay[$day] as $event): ?>
                                        <div class="event">
                                            <strong><?= htmlspecialchars($event['name']) ?></strong>
                                            <p><?= htmlspecialchars($event['description']) ?></p>
                                            <small><?= htmlspecialchars($event['time']) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>

              </div>
            <!-- Informations -->
            <div class="right">
              <div class="profile-section">
                  <h3>Informations</h3>
                  <div class="profile-info">
                    <p>Téléphone : <?= htmlspecialchars($user['Tel_user'] ?? 'Non renseigné') ?></p>
                    <p>Email : <?= htmlspecialchars($user['Email_user'] ?? 'Non renseigné') ?></p>
                    <p>Adresse : <?= htmlspecialchars($user['NomNumero_rue'] ?? 'Non renseigné') ?> <?= htmlspecialchars($user['Code_postal'] ?? 'Non renseigné') ?> <?= htmlspecialchars($user['Ville'] ?? 'Non renseigné') ?></p>
                  </div>
                  <button class="edit-info-btn">Éditer les Informations</button>
              </div>

              <!-- Historique -->
              <div class="profile-section">
                <h3>Historique</h3>
                <ul>
                    <?php foreach ($transactions as $transaction): ?>
                        <li>
                            <?= htmlspecialchars($transaction['Transaction_desc'] . ' - ' . $transaction['Montant_trans'] . '€ - ' . date('d/m/Y', strtotime($transaction['Date_trans']))) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button class="view-history">Accéder à l'historique</button>
              </div>

              <!-- Badges -->
              <div class="profile-section badges">
                  <h3>Badges</h3>
                  <div class="badge-list">
                      <?php foreach ($badges as $badge): ?>
                          <img src="<?= htmlspecialchars($badge['Photo_badge']) ?>" alt="<?= htmlspecialchars($badge['Nom_badge']) ?>">
                      <?php endforeach; ?>
                  </div>
                  <button class="view-badges">Voir tous les badges</button>
              </div>
            </div>
        </div>
    </main>
    <!-- Modal -->
    <div class="modal hidden">
        <h2>Informations</h2>
        <div class="modal-body">
            <label for="tel">Téléphone</label>
            <input type="text" id="tel" value="<?= htmlspecialchars($user['Tel_user'] ?? ''); ?>">

            <label for="email">Email</label>
            <input type="email" id="email" value="<?= htmlspecialchars($user['Email_user'] ?? ''); ?>">

            <label for="numNomRue">Numéro et Nom de rue</label>
            <input type="text" id="numNomRue" value="<?= htmlspecialchars($user['NomNumero_rue'] ?? ''); ?>">

            <label for="ville">Ville</label>
            <input type="text" id="ville" value="<?= htmlspecialchars($user['Ville'] ?? ''); ?>">

            <label for="codePostal">Code Postal</label>
            <input type="text" id="codePostal" value="<?= htmlspecialchars($user['Code_postal'] ?? ''); ?>">
        </div>
        <div class="modal-footer">
            <button class="save-info-btn">Enregistrer</button>
            <button class="close-modal">Fermer</button>
        </div>
    </div>

    <div class="history-modal hidden">
        <div class="history-header">
            <h2>Historique</h2>
            <button class="close-history-modal">X</button>
        </div>
        <div class="history-search">
            <input type="text" id="transaction-search" placeholder="Rechercher par nom..." />
        </div>
        <div class="history-content">
        </div>
    </div>
    <div class="badge-modal hidden">
        <h2>Badges</h2>
        <div class="modal-body">
            <div class="badge-category">
                <h3>Année :</h3>
                <div class="badges">
                    <?php foreach (array_slice($allBadges, 0, 3) as $badge): ?>
                        <?php 
                            $isOwned = in_array($badge['Id_badge'], $userBadges); // Badge possédé
                            $hasTick = in_array($badge['Id_badge'], $badgeIds); // Badge avec tick
                            $class = $isOwned ? '' : 'blur'; // Classe "blur" si non possédé
                        ?>
                        <div class="badge <?= $class ?>">
                            <img src="<?= htmlspecialchars($badge['Photo_badge']) ?>" alt="<?= htmlspecialchars($badge['Nom_badge']) ?>">
                            <?php if ($hasTick): ?>
                                <div class="tick-mark"></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="badge-category">
                <h3>Taux de participations :</h3>
                <div class="badges">
                    <?php foreach (array_slice($allBadges, 3, 5) as $badge): ?>
                        <?php 
                            $isOwned = in_array($badge['Id_badge'], $userBadges); // Badge possédé
                            $hasTick = in_array($badge['Id_badge'], $badgeIds); // Badge avec tick
                            $class = $isOwned ? '' : 'blur'; // Classe "blur" si non possédé
                        ?>
                        <div class="badge <?= $class ?>">
                            <img src="<?= htmlspecialchars($badge['Photo_badge']) ?>" alt="<?= htmlspecialchars($badge['Nom_badge']) ?>">
                            <?php if ($hasTick): ?>
                                <div class="tick-mark"></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="badge-category">
                <h3>Grades :</h3>
                <div class="badges">
                    <?php foreach (array_slice($allBadges, 8, 3) as $badge): ?>
                        <?php 
                            $isOwned = in_array($badge['Id_badge'], $userBadges); // Badge possédé
                            $hasTick = in_array($badge['Id_badge'], $badgeIds); // Badge avec tick
                            $class = $isOwned ? '' : 'blur'; // Classe "blur" si non possédé
                        ?>
                        <div class="badge <?= $class ?>">
                            <img src="<?= htmlspecialchars($badge['Photo_badge']) ?>" alt="<?= htmlspecialchars($badge['Nom_badge']) ?>">
                            <?php if ($hasTick): ?>
                                <div class="tick-mark"></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <button class="close-badge-modal">X</button>
    </div>
        <div class="modal-add-event hidden">
        <h2>Ajouter un événement</h2>
        <form id="add-event-form">
            <label for="event-name">Nom de l'événement</label>
            <input type="text" id="event-name" name="event-name" required>

            <label for="event-date">Date</label>
            <input type="date" id="event-date" name="event-date" required>

            <label for="event-time">Heure</label>
            <input type="time" id="event-time" name="event-time" required>

            <label for="event-desc">Description</label>
            <textarea id="event-desc" name="event-desc" rows="4" required></textarea>

            <div class="modal-footer">
                <button type="submit" class="save-event-btn">Enregistrer</button>
                <button type="button" class="close-modal-event">Fermer</button>
            </div>
        </form>
    </div>
    <script src="js/scriptProf.js"></script>
</body>
</html>