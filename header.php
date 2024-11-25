<?php
// Démarrer la session pour vérifier les informations utilisateur
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Chemin par défaut pour l'image de profil
$profileImage = isset($_SESSION['Photo_user']) && !empty($_SESSION['Photo_user']) 
    ? htmlspecialchars($_SESSION['Photo_user']) 
    : 'image/default.png';
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
                <a href="#">Statistique</a>
                <a href="#">Banque</a>
                <a href="#">Gestion site</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Navigation -->
            <nav>
                <ul class="nav-links">
                    <li><a href="accueil.php" class="active">Accueil</a></li>
                    <li><a href="events.php">Événements</a></li>
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
                    $profileImage = !empty($_SESSION['Photo_user']) ? $_SESSION['Photo_user'] : 'image/default.png';
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
</body>
</html>