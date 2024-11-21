<?php
$currentPage = 'events';

session_start(); // Démarrage de la session

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Vérifier si l'utilisateur est administrateur
$isAdmin = false;

if ($userId) {
    $roleQuery = $pdo->prepare('SELECT Id_role FROM Utilisateur WHERE Id_user = :userId');
    $roleQuery->execute(['userId' => $userId]);
    $userRole = $roleQuery->fetch(PDO::FETCH_ASSOC);

    if ($userRole && $userRole['Id_role'] == 2) {
        $isAdmin = true;
    }
}

// Requête pour récupérer tous les événements
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
    ORDER BY 
        e.Date_deb_event ASC
";
$events = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Diviser les événements en "à venir" et "passés"
$currentDate = date('Y-m-d');
$upcomingEvents = [];
$pastEvents = [];

foreach ($events as $event) {
    if ($event['Date_deb_event'] >= $currentDate) {
        $upcomingEvents[] = $event;
    } else {
        $pastEvents[] = $event;
    }
}

// Fonction pour formater les mois en français
function formatMonthYear($date) {
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
    $monthYear = $dateTime->format('F Y'); // Par exemple : "December 2024"
    
    // Traduire en français
    return str_replace(array_keys($months), array_values($months), $monthYear);
}

// Regrouper les événements par mois
function groupEventsByMonth($events) {
    $grouped = [];
    foreach ($events as $event) {
        $monthYear = formatMonthYear($event['Date_deb_event']);
        $grouped[$monthYear][] = $event;
    }
    return $grouped;
}

$upcomingEventsGrouped = groupEventsByMonth($upcomingEvents);
$pastEventsGrouped = groupEventsByMonth($pastEvents);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Événements - BDE ADIIL</title>
  <link rel="stylesheet" href="stylecss/styles_events.css">
</head>
<body>
    <header>
        <div class="header-container">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <img src="image/logoAdiil.png" alt="Logo ADIIL">
            </a>

            <!-- Navigation -->
            <nav>
                <ul class="nav-links">
                    <li><a href="accueil.php">Accueil</a></li>
                    <li><a href="events.php" class="active">Événements</a></li>
                    <li><a href="boutique.php">Boutique</a></li>
                    <li><a href="bde.php">BDE</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </nav>

            <!-- Boutons / Profil -->
            <div class="header-buttons">
                <?php if ($userId): ?>
                    <!-- Utilisateur connecté -->
                    <img src="<?= htmlspecialchars(!empty($_SESSION['Photo_user']) ? $_SESSION['Photo_user'] : 'image/ppBaptProf.jpg') ?>" alt="Profil" class="profile-icon">
                    <form action="logout.php" method="post" class="logout-form">
                        <button type="submit" class="logout-button">Se déconnecter</button>
                    </form>
                    <img src="image/logoPanier.png" alt="Panier" class="cartIcon">
                <?php else: ?>
                    <!-- Boutons si non connecté -->
                    <a href="connexion.html" class="connectButtonHeader">Se connecter</a>
                    <a href="inscription.html" class="registerButtonHeader">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </header>     

  <main>
    <section class="events">
      <div class="tabs-container">
        <div class="tabs">
            <a href="events.php" class="tab active">Événements</a>
            <a href="calendrier.php" class="tab">Calendrier</a>
        </div>
      </div>

      <!-- Boutons administrateurs -->
      <?php if ($isAdmin): ?>
          <div class="admin-buttons">
              <a href="add_event.php" class="add-event-btn">+ Ajouter un événement</a>
          </div>
      <?php endif; ?>

      <!-- Événements à venir -->
      <?php foreach ($upcomingEventsGrouped as $month => $events): ?>
        <div class="month-section">
          <h3><?= htmlspecialchars($month) ?></h3>
          <?php foreach ($events as $event): ?>
          <div class="event-card">
            <!-- Bouton Modifier -->
            <div class="edit-btn-container">
              <?php if ($isAdmin): ?>
                <a href="edit_event.php?id=<?= htmlspecialchars($event['Id_event']) ?>" class="edit-event-btn">
                  <img src="image/icon_modify.png" alt="Modifier">
                  Modifier
                </a>
              <?php endif; ?>
            </div>
            <img src="<?= htmlspecialchars($event['Photo_event']) ?>" alt="Photo de l'événement">
            <div class="event-info">
              <h4><?= htmlspecialchars($event['Nom_event']) ?></h4>
              <div class="event-details-grid">
                  <div class="event-date">
                      <img src="image/Calendar.png" alt="Calendrier" class="icon">
                      <span><?= htmlspecialchars(formatMonthYear($event['Date_deb_event'])) ?></span>
                  </div>
                  <div class="event-time">
                      <img src="image/Clock.png" alt="Horloge" class="icon">
                      <span><?= htmlspecialchars(date('H:i', strtotime($event['Heure_deb_event']))) ?></span>
                  </div>
                  <div class="event-location">
                      <img src="image/Localisation.png" alt="Localisation" class="icon">
                      <span><?= htmlspecialchars($event['NomNumero_rue'] . ', ' . $event['Ville']) ?></span>
                  </div>
              </div>
              <p><?= htmlspecialchars($event['Desc_event']) ?></p>

              <button class="register-btn">S'inscrire</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <!-- Evenements passes -->
      <?php if (!empty($pastEventsGrouped)): ?>
        <div class="past-events">
            <h2>Événements passés</h2>
            <?php foreach ($pastEventsGrouped as $month => $events): ?>
                <div class="month-section">
                    <h3><?= htmlspecialchars($month) ?></h3>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card past-event-card">
                            <img src="<?= htmlspecialchars($event['Photo_event']) ?>" alt="Photo de l'événement">
                            <div class="event-info">
                                <h4><?= htmlspecialchars($event['Nom_event']) ?></h4>
                                <div class="event-details-grid">
                                    <div class="event-date">
                                        <img src="image/Calendar.png" alt="Calendrier" class="icon">
                                        <span><?= htmlspecialchars(formatMonthYear($event['Date_deb_event'])) ?></span>
                                    </div>
                                    <div class="event-time">
                                        <img src="image/Clock.png" alt="Horloge" class="icon">
                                        <span><?= htmlspecialchars(date('H:i', strtotime($event['Heure_deb_event']))) ?></span>
                                    </div>
                                    <div class="event-location">
                                        <img src="image/Localisation.png" alt="Localisation" class="icon">
                                        <span><?= htmlspecialchars($event['NomNumero_rue'] . ', ' . $event['Ville']) ?></span>
                                    </div>
                                </div>
                                <p><?= htmlspecialchars($event['Desc_event']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    </section>
  </main>
</body>
</html>