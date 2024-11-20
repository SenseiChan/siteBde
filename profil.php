<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil</title>
  <link rel="stylesheet" href="stylecss/styleProf.css">
</head>
<body>
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
