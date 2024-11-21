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

// Requête pour récupérer tous les événements
$query = "
    SELECT 
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


// Fonction pour formater les dates en français
function formatDate($date) {
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
    $dayMonthYear = $dateTime->format('d F Y'); // Par exemple : "18 December 2024"
    
    // Traduire en français
    return str_replace(array_keys($months), array_values($months), $dayMonthYear);
}


// Fonction pour convertir un lien Google Drive
function convertDriveLink($link) {
    if (strpos($link, 'drive.google.com') !== false) {
        preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $link, $matches);
        if (isset($matches[1])) {
            $fileId = $matches[1];
            return "https://drive.google.com/thumbnail?id=" . $fileId;
        }
    }
    return $link;
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
                <?php
                if ($userId!=null):
                    // Utilisateur connecté
                    $profileImage = !empty($_SESSION['Photo_user']) ? $_SESSION['Photo_user'] : 'image/ppBaptProf.jpg';
                ?>
                    <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profil" class="profile-icon">
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
            <a href="events.php" class="tab <?php if($currentPage === 'events') echo 'active'; ?>">Événements</a>
            <a href="calendrier.php" class="tab <?php if($currentPage === 'calendrier') echo 'active'; ?>">Calendrier</a>
        </div>
        <div class="icontri">
            <img src="image/icon_tri.png" alt="Menu">
        </div>
      </div>

      <!-- Événements à venir -->
      <?php foreach ($upcomingEventsGrouped as $month => $events): ?>
        <div class="month-section">
          <h3><?= htmlspecialchars($month) ?></h3>
          <?php foreach ($events as $event): ?>
          <div class="event-card">
            <img src="<?= htmlspecialchars(convertDriveLink($event['Photo_event'])) ?>" alt="Photo de l'événement">
            
            <div class="event-info">
              <h4><?= htmlspecialchars($event['Nom_event']) ?></h4>
              <div class="event-details-grid">
                  <div class="event-date">
                      <img src="image/Calendar.png" alt="Calendrier" class="icon">
                      <span><?= formatDate($event['Date_deb_event']) ?></span>
                  </div>
                  <div class="event-time">
                      <img src="image/Clock.png" alt="Horloge" class="icon">
                      <span><?= date('H\hi', strtotime($event['Heure_deb_event'])) ?></span>
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

      <!-- Événements passés -->
      <?php if (!empty($pastEventsGrouped)): ?>
        <div class="month-section">
          <h3>Événements passés</h3>
          <?php foreach ($pastEventsGrouped as $month => $events): ?>
            <div class="past-month">
              <h4><?= htmlspecialchars($month) ?></h4>
              <?php foreach ($events as $event): ?>
              <div class="event-card">
                <img src="<?= htmlspecialchars(convertDriveLink($event['Photo_event'])) ?>" alt="Photo de l'événement">
                <div class="event-info">
                  <h4><?= htmlspecialchars($event['Nom_event']) ?></h4>
                  <div class="event-details-grid">
                      <div class="event-date">
                          <img src="image/Calendar.png" alt="Calendrier" class="icon">
                          <span><?= formatDate($event['Date_deb_event']) ?></span>
                      </div>
                      <div class="event-time">
                          <img src="image/Clock.png" alt="Horloge" class="icon">
                          <span><?= date('H\hi', strtotime($event['Heure_deb_event'])) ?></span>
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

  <!-- Footer -->
  <footer class="site-footer">
      <div class="footer-content">
          <p>
              Copyright ©. Tous droits réservés.
              <a href="#">Mentions légales et CGU</a> | <a href="#">Politique de confidentialité</a>
          </p>
          <div class="footer-icons">
              <a href="#" aria-label="Discord">
                  <img src="images/discordIconFooter.png" alt="Discord">
              </a>
              <a href="#" aria-label="Instagram">
                  <img src="images/instIconFooter.png" alt="Instagram">
              </a>
          </div>
      </div>
  </footer>
</body>
</html>