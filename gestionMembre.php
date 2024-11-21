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
<header>
    <div class="header-container">
        <!-- Logo -->
        <div class="logo">
            <img src="image/logoAdiil.png" alt="Logo BDE">
        </div>

        <div class="dropdown">
            <button class="dropdown-toggle">Admin</button>
            <div class="dropdown-menu">
                <a href="#">Espace partagé</a>
                <a href="#">Gestion membre</a>
                <a href="#">Statistique</a>
                <a href="#">Banque</a>
                <a href="#">Gestion site</a>
            </div>
        </div>

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
            if ($userId != null):
                // Utilisateur connecté
                $profileImage = !empty($_SESSION['Photo_user']) ? $_SESSION['Photo_user'] : 'image/ppBaptProf.jpg';
            ?>
                <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profil" class="profile-icon">
                <form action="logout.php" method="post" class="logout-form">
                    <button type="submit" class="logout-button">Se déconnecter</button>
                </form>
                <img src="image/logoPanier.png" alt="Panier" class="cartIcon">
            <?php endif; ?>
        </div>
    </div>
</header>
</body>
</html>
