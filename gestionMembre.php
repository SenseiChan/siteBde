<?php
session_start(); // Démarrer la session

// Vérifie si l'utilisateur est connecté
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Vérifie si l'utilisateur est administrateur
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Redirige si l'utilisateur n'est pas admin
if (!$is_admin) {
    header("Location: accueil.php");
    exit(); // Assurez-vous de terminer le script après la redirection
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

// Récupération des administrateurs
$adminQuery = $pdo->query("
    SELECT u.Id_user, u.Nom_user, u.Prenom_user, u.Photo_user, g.Nom_grade
    FROM utilisateur u
    LEFT JOIN grade g ON u.Id_grade = g.Id_grade
    WHERE u.Id_role = 2
");
$administrateurs = $adminQuery->fetchAll(PDO::FETCH_ASSOC);

// Récupération des membres
$membreQuery = $pdo->prepare("
    SELECT u.Id_user, u.Nom_user, u.Prenom_user, u.Photo_user, g.Nom_grade, u.Annee_promo
    FROM utilisateur u
    LEFT JOIN grade g ON u.Id_grade = g.Id_grade
    WHERE u.Id_role != 2
    AND (:search IS NULL OR u.Prenom_user LIKE :search)
    ORDER BY u.Annee_promo ASC
");
$searchParam = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : null;
$membreQuery->bindValue(':search', $searchParam, PDO::PARAM_STR);
$membreQuery->execute();
$membres = $membreQuery->fetchAll(PDO::FETCH_ASSOC);

// Organisation des membres par année
$membresParAnnee = [];
foreach ($membres as $membre) {
    $anneePromo = $membre['Annee_promo'] ?? 'Inconnue'; // Gestion des valeurs nulles
    if (!isset($membresParAnnee[$anneePromo])) {
        $membresParAnnee[$anneePromo] = [];
    }
    $membresParAnnee[$anneePromo][] = $membre;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Membre</title>
    <link rel="stylesheet" href="stylecss/styleGestionMembre.css"> <!-- Lien vers le fichier CSS -->
</head>
<body>
<?php include 'header.php'; ?>
    <main>
        <br><br><br>
        <div class="admin-section">
            <h2>Administrateurs</h2>
            <div class="admin-container">
                <?php foreach ($administrateurs as $admin): ?>
                    <a href="profil.php?id=<?= htmlspecialchars($admin['Id_user']) ?>">
                    <div class="user-card">
                            <div class="user-info">
                                <p><?= htmlspecialchars($admin['Prenom_user']) . ' ' . htmlspecialchars($admin['Nom_user']); ?></p>
                                <p class="grade"><?= htmlspecialchars($admin['Nom_grade'] ?? ''); ?></p>
                            </div>
                            <img src="<?= htmlspecialchars($admin['Photo_user']); ?>" alt="Photo de profil">
                    </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="member-section">
            <div class="member-header">
                <h2>Membres</h2>
                <form id="search-form" method="GET" action="gestionMembre.php" class="search-form">
                    <input type="text" name="search" placeholder="Rechercher par prénom..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">Rechercher</button>
                </form>
            </div>
            <div class="promo-container">
                <?php foreach ($membresParAnnee as $year => $members): ?>
                    <div class="promo-column">
                        <h3>Année <?= htmlspecialchars($year); ?></h3>
                        <div class="promo-members">
                            <?php foreach ($members as $member): ?>
                                <a href="profil.php?id=<?= htmlspecialchars($member['Id_user']); ?>">
                                <div class="user-card">
                                        <div class="user-info">
                                            <p><?= htmlspecialchars($member['Prenom_user']) . ' ' . htmlspecialchars($member['Nom_user']); ?></p>
                                            <p class="grade"><?= htmlspecialchars($member['Nom_grade'] ?? ''); ?></p>
                                        </div>
                                        <img src="<?= htmlspecialchars($member['Photo_user']); ?>" alt="Photo de profil">
                                </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>


    <footer class="site-footer">
        <div class="footer-content">
            <p>
                Copyright ©. Tous droits réservés.
                <a href="#">Mentions légales et CGU</a> | <a href="#">Politique de confidentialité</a>
            </p>
            <div class="footer-icons">
                <a href="#" aria-label="Discord">
                    <img src="image/discordIconFooter.png" alt="Discord">
                </a>
                <a href="#" aria-label="Instagram">
                    <img src="image/instIconFooter.png" alt="Instagram">
                </a>
            </div>
        </div>
    </footer>
</body>
</html>
