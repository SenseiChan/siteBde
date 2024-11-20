<?php
$currentPage = 'calendrier';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.html");
    exit();
}

// Connexion à la base de données
$dsn = 'mysql:host=localhost;dbname=sae;charset=utf8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Gestion du mois sélectionné
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$startDate = strtotime($currentMonth . '-01');
$endDate = strtotime(date('Y-m-t', $startDate));

// Récupération des événements
$query = $pdo->prepare("SELECT * FROM Calendrier WHERE DateHeure_calend BETWEEN :start AND :end ORDER BY DateHeure_calend");
$query->execute([
    ':start' => date('Y-m-d H:i:s', $startDate),
    ':end' => date('Y-m-d H:i:s', $endDate),
]);
$events = $query->fetchAll();

// Organisation des événements par jour
$eventByDay = [];
foreach ($events as $event) {
    $date = date('Y-m-d', strtotime($event['DateHeure_calend']));
    $eventByDay[$date][] = $event;
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
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">
                <img src="image/logoAdiil.png" alt="Logo ADIIL">
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="events.php" class="active">Événements</a></li>
                    <li><a href="boutique.php">Boutique</a></li>
                    <li><a href="bde.php">BDE</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </nav>
            <div class="header-buttons">
                <img src="image/icon_user.png" alt="Icône utilisateur" class="user-icon">
                <img src="image/logoPanier.png" alt="Panier" class="cartIcon">
            </div>
        </div>
    </header>

    <!-- Onglets -->
    <div class="tabs-container">
        <div class="tabs">
            <a href="events.php" class="tab <?php if($currentPage === 'events') echo 'active'; ?>">Événements</a>
            <a href="calendrier.php" class="tab <?php if($currentPage === 'calendrier') echo 'active'; ?>">Calendrier</a>
        </div>
        <div class="icontri">
            <img src="image/icon_tri.png" alt="Menu">
        </div>
    </div>

    <!-- Calendrier -->
    <main>
        <div class="calendar-container">
            <h2>Calendrier des événements</h2>
            <form action="" method="GET" class="month-selector">
                <label for="month">Choisir un mois :</label>
                <input type="month" id="month" name="month" value="<?= date('Y-m', $startDate); ?>">
                <button type="submit">Afficher</button>
            </form>
            <table class="calendar">
                <thead>
                    <tr>
                        <th>Lun</th>
                        <th>Mar</th>
                        <th>Mer</th>
                        <th>Jeu</th>
                        <th>Ven</th>
                        <th>Sam</th>
                        <th>Dim</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $currentDay = $startDate;
                    echo '<tr>';
                    for ($i = 1; $i < date('N', $currentDay); $i++) echo '<td></td>';
                    while ($currentDay <= $endDate) {
                        $day = date('Y-m-d', $currentDay);
                        if (date('N', $currentDay) == 1 && $currentDay != $startDate) echo '</tr><tr>';
                        echo '<td>';
                        echo date('j', $currentDay);
                        if (isset($eventByDay[$day])) {
                            foreach ($eventByDay[$day] as $event) {
                                echo '<div class="event">';
                                echo '<span>' . date('H:i', strtotime($event['DateHeure_calend'])) . '</span> - ';
                                echo htmlspecialchars($event['Nom_calend']);
                                echo '</div>';
                            }
                        }
                        echo '</td>';
                        $currentDay = strtotime('+1 day', $currentDay);
                    }
                    for ($i = date('N', $currentDay); $i <= 7 && $i != 1; $i++) echo '<td></td>';
                    echo '</tr>';
                    ?>
                </tbody>
            </table>
        </div>
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
