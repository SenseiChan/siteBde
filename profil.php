<?php
session_start(); // Démarrer la session

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Vérifie si l'utilisateur est connecté et admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil</title>
  <link rel="stylesheet" href="stylecss/styleProf.css">
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
                <a href="#">Espace partagé</a>
                <a href="#">Gestion membre</a>
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
                    <li><a href="evenements.php">Événements</a></li>
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
  <?php
    // Variables dynamiques pour le profil
    $nom = "Erwan";
    $role = "Membre (LE ROLE)";
    $photoProfil = "image/ppBaptProf.jpg";
    $calendarURL = "https://calendar.google.com/calendar/embed?src=tomyflach%40gmail.com&ctz=UTC";
  ?>
  <div class="background">
    <div class="top-bar"></div>
    <div class="center-box">
      <div class="profile-header">
        <div class="profile-info">
          <h1><?php echo $nom; ?></h1>
          <p><?php echo $role; ?></p>
        </div>
        <div class="profile-picture">
          <img src="<?php echo $photoProfil; ?>" alt="Photo de profil">
        </div>
      </div>

      <!-- Conteneur pour le calendrier et les boxes à droite -->
      <div class="content-row">
        <!-- Section Agenda (calendrier) -->
        <div class="agenda">
          <h2>Agenda</h2>
          <iframe src="<?php echo $calendarURL; ?>" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>
        </div>

        <!-- Conteneur des 3 boxes à droite -->
        <div class="content-column">
          <div class="right-box informations">
            <h2>Informations</h2>
            <p>Contenu des informations.</p>
          </div>
          <div class="right-box historique">
            <h2>Historique</h2>
            <p>Contenu de l'historique.</p>
          </div>
          <div class="right-box badges">
            <h2>Badges</h2>
            <p>Contenu des badges.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
