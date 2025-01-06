<?php
// Connexion à la base de données dans 'bde.php'
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations BDE</title>
    <link rel="stylesheet" href="stylecss/styleInfoBde.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- Barre Bleue -->
    <div class="topBleu"></div>

    <!-- Introduction -->
    <div class="center-box">
        <h2 id="equipe">L'équipe</h2>
        <p id="presEquipe">L’équipe du BDE est composée, cette année, de 6 membres avec chacun des fonctions différentes</p>
    </div>

    <!-- Mascotte 
    <div class="moderation_img">
        <img src="image/moderation.png" alt="mascotte IUT">
    </div>
    

    <!-- Membres de l'Équipe -->
    <div class="team-section">
        <!-- Président -->
        <div class="team-president">
            <img src="imagesAdmin/Enzo_adiil.jpg" alt="Enzo">
            <h3>Enzo RYNDERS--VITU</h3>
            <p>Président</p>
        </div>

        <!-- Secrétaire & Trésorier -->
        <div class="row">
            <div class="team-member">
                <img src="imagesAdmin/gemino_adiil.jpg" alt="Gémino">
                <h3>Gémino RUFFAULT--RAVENEL</h3>
                <p>Secrétaire</p>
            </div>
            <div class="team-member">
                <img src="imagesAdmin/tom_adiil.jpg" alt="Tom">
                <h3>Tom YSOPE</h3>
                <p>Trésorier</p>
            </div>
        </div>

        <!-- Chauffeur, Événementiel, Communication -->
        <div class="row">
            <div class="team-member">
                <img src="imagesAdmin/Axel_adiil.jpg" alt="Axel">
                <h3>Axel GALLARD</h3>
                <p>Chauffeur</p>
            </div>
            <div class="team-member">
                <img src="imagesAdmin/julien_adiil.jpg" alt="Julien">
                <h3>Julien DAUVERGNE</h3>
                <p>Événementiel</p>
            </div>
            <div class="team-member">
                <img src="imagesAdmin/Mathis_adiil.jpg" alt="Mathis">
                <h3>Mathis LE NÔTRE</h3>
                <p>Chargé de communication</p>
            </div>
        </div>
    </div>
    <br>
    <?php include 'footer.php'; ?>
</body>
</html>
