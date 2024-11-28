<?php
// Démarrer la session pour vérifier les informations utilisateur
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Récupération des informations utilisateur
$photoQuery = $pdo->prepare("
    SELECT  
        u.Photo_user
    FROM utilisateur u
    WHERE u.Id_user = :id
");
$photoQuery->execute(['id' => $userId]);
$userPhoto = $photoQuery->fetch(PDO::FETCH_ASSOC);

// Chemin par défaut pour l'image de profil
$profileImage = !empty($userPhoto['Photo_user']) ? $userPhoto['Photo_user'] : 'image/default.png';


// Fonction pour vérifier si une page est active
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="stylecss/header.css"> <!-- Lien vers le fichier header.css -->
</head>
<body>
<header>
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
                <a href="espace_partage.php">Espace partagé</a>
                <a href="gestionMembre.php">Gestion membre</a>
                <a href="statistique.php">Statistique</a>
                <a href="banque.php">Banque</a>
                <a href="chat_admin.php">Chat Administrateur</a>
                <a href="transaction.php">Transactions</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Navigation -->
        <nav>
            <ul class="nav-links">
                <li><a href="accueil.php" class="<?= isActive('accueil.php') ?>">Accueil</a></li>
                <li><a href="events.php" class="<?= isActive('events.php') ?>">Événements</a></li>
                <li><a href="boutique.php" class="<?= isActive('boutique.php') ?>">Boutique</a></li>
                <li><a href="bde.php" class="<?= isActive('bde.php') ?>">BDE</a></li>
                <li><a href="faq.php" class="<?= isActive('faq.php') ?>">FAQ</a></li>
            </ul>
        </nav>

        <!-- Boutons / Profil -->
        <div class="header-buttons">
            <?php if ($userId != null): ?>
                <!-- Utilisateur connecté -->
                <a href="profil.php"><img src="<?= htmlspecialchars($profileImage) ?>" alt="Profil" class="profile-icon"></a>
                <form action="logout.php" method="post" class="logout-form">
                    <button type="submit" class="logout-button">Se déconnecter</button>
                </form>
                <a href="panier.php"><img src="image/logoPanier.png" alt="Panier" class="cartIcon"></a>
            <?php else: ?>
                <!-- Boutons si non connecté -->
                <a href="connexion.html" class="connectButtonHeader">Se connecter</a>
                <a href="inscription.html" class="registerButtonHeader">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</header>
</body>
</html>
