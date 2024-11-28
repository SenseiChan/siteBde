<?php
$currentPage = 'events';

session_start(); // Démarrage de la session

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Vérifie si l'utilisateur est connecté et admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Vérifie si le message de succès doit être affiché
$showSuccessPopup = isset($_GET['success']) && $_GET['success'] === 'true';

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Vérifier si l'utilisateur est administrateur
$isAdmin = false;

/////////////////////////////////////////////////////////////////////////////////////////

function getParticipants($pdo, $eventId) {
  $query = "SELECT u.Nom_user, u.Prenom_user, u.Email_user
            FROM Utilisateur u
            JOIN Participer p ON u.Id_user = p.Id_user
            WHERE p.Id_event = :eventId";

  $stmt = $pdo->prepare($query);
  $stmt->execute(['eventId' => $eventId]);
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Debugging
  if (empty($result)) {
      error_log("Aucun participant trouvé pour l'événement ID: $eventId");
  }

  return $result; // Retourne les participants sous forme de tableau associatif
}

if (isset($_GET['eventId'])) {
  $eventId = $_GET['eventId'];
  error_log("Récupération des participants pour l'événement ID: " . $eventId);
  $participants = getParticipants($pdo, $eventId);
  error_log("Participants récupérés: " . print_r($participants, true));
  echo json_encode($participants);
  exit; // Fin de l'exécution du script
}

/////////////////////////////////////////////////////////////////////////////////////////

if ($userId) {
    $roleQuery = $pdo->prepare('SELECT Id_role FROM Utilisateur WHERE Id_user = :userId');
    $roleQuery->execute(['userId' => $userId]);
    $userRole = $roleQuery->fetch(PDO::FETCH_ASSOC);

    if ($userRole && $userRole['Id_role'] == 2) {
        $isAdmin = true;
    }
}

// Limiter les événements passés aux 3 plus récents
if (!empty($pastEvents)) {
  usort($pastEvents, function ($a, $b) {
      return strtotime($b['Date_deb_event']) - strtotime($a['Date_deb_event']);
  });

  // Garder uniquement les 3 derniers événements passés
  $pastEvents = array_slice($pastEvents, 0, 3);

  // Regrouper à nouveau les 3 derniers événements par mois
  $pastEventsGrouped = groupEventsByMonth($pastEvents);
}

// Fonction pour vérifier si un utilisateur est inscrit à un événement
function isUserRegistered($pdo, $userId, $eventId) {
    $query = "SELECT COUNT(*) FROM Participer WHERE Id_user = :userId AND Id_event = :eventId";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'userId' => $userId,
        'eventId' => $eventId,
    ]);
    return $stmt->fetchColumn() > 0;
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

// Limiter les événements passés aux 3 plus récents
if (!empty($pastEvents)) {
    usort($pastEvents, function ($a, $b) {
        return strtotime($b['Date_deb_event']) - strtotime($a['Date_deb_event']);
    });

    // Garder uniquement les 3 derniers événements passés
    $pastEvents = array_slice($pastEvents, 0, 3);

    // Regrouper à nouveau les 3 derniers événements par mois
    $pastEventsGrouped = groupEventsByMonth($pastEvents);
}

// Regrouper les événements à venir par mois
$upcomingEventsGrouped = groupEventsByMonth($upcomingEvents);


// Fonction pour formater les mois en français (pour les sections)
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
    $month = $dateTime->format('F');
    $year = $dateTime->format('Y');

    $month = str_replace(array_keys($months), array_values($months), $month);

    return "$month $year";
}

// Fonction pour formater une date complète en français (pour les containers)
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
  <script src="js/scriptEvent.js" defer></script>
</head>
<?php if ($showSuccessPopup): ?>
    <div id="success-popup" class="popup">
        Inscription réussie !
    </div>
<?php endif; ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const popup = document.getElementById('success-popup');
        if (popup) {
            // Affiche le pop-up
            popup.classList.add('show');
            
            // Cache le pop-up après 3 secondes
            setTimeout(() => {
                popup.classList.remove('show');
            }, 3000);
        }
    });
</script>
<body>
<?php include 'header.php'; ?>

  <main>
    <section class="events">
      <div class="tabs-container">
        <div class="tabs">
            <a href="events.php" class="tab active">Événements</a>
            <a href="calendrier.php" class="tab">Calendrier</a>
        </div>
      </div>

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
            <div class="edit-btn-container">
              <?php if ($isAdmin): ?>
                <a href="edit_event.php?id=<?= htmlspecialchars($event['Id_event']) ?>" class="edit-event-btn">
                  <img src="image/icon_modify.png" alt="Modifier"> Modifier
                </a>
              <?php endif; ?>
            </div>
            <img src="<?= htmlspecialchars($event['Photo_event']) ?>" alt="Photo de l'événement">
            <div class="event-info">
              <h4><?= htmlspecialchars($event['Nom_event']) ?></h4>
              <div class="event-details-grid">
                  <div class="event-date">
                      <img src="image/Calendar.png" alt="Calendrier">
                      <span><?= htmlspecialchars(formatFullDate($event['Date_deb_event'])) ?></span>
                  </div>
                  <div class="event-time">
                      <img src="image/Clock.png" alt="Horloge">
                      <span><?= htmlspecialchars(date('H:i', strtotime($event['Heure_deb_event']))) ?></span>
                  </div>
                  <div class="event-location">
                      <img src="image/Localisation.png" alt="Localisation">
                      <span><?= htmlspecialchars($event['NomNumero_rue'] . ', ' . $event['Ville']) ?></span>
                  </div>
              </div>
              <p><?= htmlspecialchars($event['Desc_event']) ?></p>
              <?php if (isUserRegistered($pdo, $userId, $event['Id_event'])): ?>
                <button class="register-btn disabled" disabled>Déjà inscrit</button>
              <?php else: ?>
                <a href="inscription_event.php?id=<?= htmlspecialchars($event['Id_event']) ?>" class="register-btn">S'inscrire</a>
              <?php endif; ?>

              <!---------------------------------------------------------------------------------------------->
              <br>
              <?php if ($isAdmin): ?>
                <div class="participants-button-container">
                    <a href="voir_participant.php?id=<?= htmlspecialchars($event['Id_event']) ?>" class="show-participants-btn">Voir les participants</a>
                </div>

              <?php endif; ?>
              <!---------------------------------------------------------------------------------------------->

            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <!-- Événements passés -->
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
                          <img src="image/Calendar.png" alt="Calendrier">
                          <span><?= htmlspecialchars(formatFullDate($event['Date_deb_event'])) ?></span>
                      </div>
                      <div class="event-time">
                          <img src="image/Clock.png" alt="Horloge">
                          <span><?= htmlspecialchars(date('H:i', strtotime($event['Heure_deb_event']))) ?></span>
                      </div>
                      <div class="event-location">
                          <img src="image/Localisation.png" alt="Localisation">
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
  <?php include 'footer.php'; ?>
</body>
</html>