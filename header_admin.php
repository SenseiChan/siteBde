<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Header avec Menu Déroulant</title>
  <link rel="stylesheet" href="styles_headeradmin.css">
</head>
<body>
  <header>
    <div class="header-container">
      <!-- Logo -->
      <a href="index.php" class="logo">
        <img src="image/logoAdiil.png" alt="Logo ADIIL">
      </a>

      <!-- Menu Admin -->
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

      <!-- Navigation -->
      <nav>
        <ul class="nav-links">
          <li><a href="index.php" class="active">Accueil</a></li>
          <li><a href="events.php">Événements</a></li>
          <li><a href="boutique.php">Boutique</a></li>
          <li><a href="bde.php">BDE</a></li>
          <li><a href="faq.php">FAQ</a></li>
        </ul>
      </nav>

      <!-- Boutons et Panier -->
      <div class="header-buttons">
        <!-- Icône utilisateur -->
        <img src="image/icon_user.png" alt="Icône utilisateur" class="user-icon">

        <!-- Icône Panier -->
        <img src="image/logoPanier.png" alt="Panier" class="cartIcon">
      </div>
    </div>
  </header>
</body>
</html>
