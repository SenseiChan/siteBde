<?php
$currentPage = 'events';

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
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('MMMM yyyy');
    return ucfirst($formatter->format(new DateTime($date)));
}

// Fonction pour formater les dates en français
function formatDate($date) {
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('dd MMMM yyyy');
    return $formatter->format(new DateTime($date));
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
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="events.php" class="active">Événements</a></li>
                    <li><a href="boutique.php">Boutique</a></li>
                    <li><a href="bde.php">BDE</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </nav>

            <!-- Boutons et Panier -->
            <div class="header-buttons">
                <button class="connectButtonHeader">Se connecter</button>
                <button class="registerButtonHeader">S'inscrire</button>
                <img src="image/logoPanier.png" alt="Panier" class="cartIcon">
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
