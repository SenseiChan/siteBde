<?php
$currentPage = 'calendrier';

session_start();

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Vérifie si l'utilisateur est connecté et admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Connexion à la base de données
$dsn = 'mysql:host=localhost;dbname=inf2pj_03;charset=utf8';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Gestion du mois et de l'année sélectionnés
if (isset($_GET['month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['month'])) {
    $selectedYear = substr($_GET['month'], 0, 4); // Année
    $selectedMonth = substr($_GET['month'], 5, 2); // Mois
} else {
    $selectedYear = date('Y');
    $selectedMonth = date('m');
}

// Définir les limites de début et de fin pour le mois sélectionné
$startDate = "$selectedYear-$selectedMonth-01";
$endDate = date('Y-m-t', strtotime($startDate));

// Récupérer les événements pour le mois sélectionné
$query = $pdo->prepare("
    SELECT e.Nom_event, e.Date_deb_event, e.Heure_deb_event, a.NomNumero_rue, a.Ville
    FROM evenement e
    JOIN adresse a ON e.Id_adr = a.Id_adr
    WHERE e.Date_deb_event BETWEEN :startDate AND :endDate
    ORDER BY e.Date_deb_event
");
$query->execute([
    ':startDate' => $startDate,
    ':endDate' => $endDate
]);
$events = $query->fetchAll();

// Organiser les événements par jour
$eventsByDay = [];
foreach ($events as $event) {
    $day = date('j', strtotime($event['Date_deb_event'])); // Jour du mois
    $eventsByDay[$day][] = $event;
}

// Fonction pour générer le calendrier
function generateCalendar($year, $month, $eventsByDay) {
    $daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    $firstDayOfMonth = strtotime("$year-$month-01");
    $totalDays = date('t', $firstDayOfMonth); // Nombre total de jours dans le mois
    $startDayOfWeek = date('N', $firstDayOfMonth); // Jour de la semaine du 1er jour (1 = Lundi)

    // Affichage du calendrier
    echo '<table class="calendar">';
    echo '<thead><tr>';
    foreach ($daysOfWeek as $day) {
        echo "<th>$day</th>";
    }
    echo '</tr></thead>';
    echo '<tbody><tr>';

    // Remplir les cellules vides avant le premier jour du mois
    for ($i = 1; $i < $startDayOfWeek; $i++) {
        echo '<td class="empty"></td>';
    }

    // Remplir les jours du mois
    for ($day = 1; $day <= $totalDays; $day++) {
        $currentDay = str_pad($day, 2, '0', STR_PAD_LEFT);
        echo '<td>';
        echo "<div class='day-number'>$day</div>";

        // Afficher les événements pour ce jour
        if (isset($eventsByDay[$day])) {
            foreach ($eventsByDay[$day] as $event) {
                echo "<div class='event'>";
                echo "<span class='event-time'>" . date('H:i', strtotime($event['Heure_deb_event'])) . "</span> ";
                echo "<span class='event-name'>" . htmlspecialchars($event['Nom_event']) . "</span>";
                echo "</div>";
            }
        }

        echo '</td>';

        // Nouvelle ligne après Dimanche
        if (date('N', strtotime("$year-$month-$currentDay")) == 7) {
            echo '</tr>';
            if ($day < $totalDays) {
                echo '<tr>';
            }
        }
    }

    // Remplir les cellules vides après le dernier jour du mois
    $lastDayOfWeek = date('N', strtotime("$year-$month-$totalDays"));
    for ($i = $lastDayOfWeek; $i < 7; $i++) {
        echo '<td class="empty"></td>';
    }

    echo '</tr></tbody>';
    echo '</table>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des événements</title>
    <link rel="stylesheet" href="stylecss/styles_calendrier.css">
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Onglets -->
    <div class="tabs-container">
        <div class="tabs">
            <a href="events.php" class="tab">Événements</a>
            <a href="calendrier.php" class="tab active">Calendrier</a>
        </div>
    </div>

    <!-- Sélecteur de mois -->
    <main>
        <div class="calendar-container">
            <h2>Calendrier des événements</h2>
            <form action="" method="GET" class="month-selector">
                <label for="month">Choisir un mois :</label>
                <input type="month" id="month" name="month" value="<?= htmlspecialchars($selectedYear . '-' . $selectedMonth) ?>" class="styled-month">
                <button type="submit" class="styled-button">Afficher</button>
            </form>
            <?php generateCalendar($selectedYear, $selectedMonth, $eventsByDay); ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
