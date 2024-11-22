<?php
session_start(); // Démarrer la session

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


// Vérifie si l'utilisateur est connecté et admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Redirige si l'utilisateur n'est pas connecté
if ($userId == null) {
    header("Location: accueil.php");
    exit(); // Assurez-vous de terminer le script après la redirection
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

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

// Détermine le mois et l'année actuels ou sélectionnés
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : date('m');

// Calcul des jours dans le mois
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

function getUserEvents($pdo, $userId, $month, $year) {
  $query = $pdo->prepare("
      SELECT e.Nom_event, e.Desc_event, e.Date_deb_event, e.Heure_deb_event, e.Prix_event, e.Photo_event
      FROM evenement e
      JOIN participer p ON e.Id_event = p.Id_event
      WHERE p.Id_user = :userId
      AND MONTH(e.Date_deb_event) = :month
      AND YEAR(e.Date_deb_event) = :year
  ");
  $query->execute([
      'userId' => $userId,
      'month' => $month,
      'year' => $year
  ]);

  return $query->fetchAll(PDO::FETCH_ASSOC);
}


// Appel de la fonction pour récupérer les événements
$userEvents = getUserEvents($pdo, $userId, $currentMonth, $currentYear);

// Vérification pour éviter des erreurs si aucun événement n'est récupéré
if (!is_array($userEvents)) {
    $userEvents = [];
}

// Organisation des événements par jour
$eventsByDay = [];
foreach ($userEvents as $event) { // Utilisation correcte de $userEvents
    $day = (int) date('j', strtotime($event['Date_deb_event']));
    $eventsByDay[$day][] = $event;
}

// Récupération des 3 dernières transactions avec leurs détails
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

// Récupération des badges de l'utilisateur
$badgesQuery = $pdo->prepare("
    SELECT b.Nom_badge, b.Desc_badge, b.Photo_badge
    FROM decrocher d
    JOIN badge b ON d.Id_badge = b.Id_badge
    WHERE d.Id_user = :id AND d.Afficher_badge = 1
    LIMIT 3
");
$badgesQuery->execute(['id' => $userId]);
$badges = $badgesQuery->fetchAll(PDO::FETCH_ASSOC);

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
  <header class="blur-target">
    <div class="header-container">
        <!-- Logo -->
        <div class="logo">
            <img src="image/logoAdiil.png" alt="Logo BDE">
        </div>

        <!-- Menu Admin -->
        <?php if ($is_admin): ?>
        <div class="dropdown">
            <button class="dropdown-toggle">Admin</button>
            <div class="dropdown-menu">
            <a href="#">Espace partagé</a>
            <a href="gestionMembre.php">Gestion membre</a>
            <a href="#">Statistique</a>
            <a href="#">Banque</a>
            <a href="#">Gestion site</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Navigation -->
        <nav>
            <ul class="nav-links">
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="boutique.php">Boutique</a></li>
                <li><a href="bde.php">BDE</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </nav>

        <!-- Boutons / Profil -->
        <div class="header-buttons">
            <?php
            if ($userId!=null):
            ?>
                <img src="<?= htmlspecialchars($user['Photo_user'] ?? 'image/default-profile.png') ?>" alt="Profil" class="profile-icon">
                <form action="logout.php" method="post" class="logout-form">
                    <button type="submit" class="logout-button">Se déconnecter</button>
                </form>
                <img src="image/logoPanier.png" alt="Panier" class="cartIcon">
            <?php endif; ?>
        </div>
      </div>
  </header>

  <main class="blur-target">
    <br><br><br>
        <div class="profile-header">
            <div class="profile-title">
                <h1><?= htmlspecialchars($user['Prenom_user'] . ' ' . $user['Nom_user']) ?></h1>
                <p><?= htmlspecialchars($user['Nom_grade'] ?? 'Membre') ?></p>
            </div>
            <div class="profile-picture">
                <img src="<?= htmlspecialchars($user['Photo_user'] ?? 'image/default-profile.png') ?>" alt="Photo de profil">
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
              </div>
              <div class="calendar-grid">
                  <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                      <div class="calendar-day">
                          <span class="day-number"><?= $day ?></span>
                          <?php if (isset($eventsByDay[$day])): ?>
                              <div class="events">
                                  <?php foreach ($eventsByDay[$day] as $event): ?>
                                      <div class="event">
                                          <strong><?= htmlspecialchars($event['Nom_event']) ?></strong>
                                          <p><?= htmlspecialchars($event['Desc_event']) ?></p>
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
            <!-- Transactions will be dynamically loaded here -->
        </div>
    </div>
    <script src="js/scriptProf.js"></script>
</body>
</html>