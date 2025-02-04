<?php
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

// Récupération des contenus de type "chiffres"
$query = $pdo->prepare("
    SELECT contenu.Id_contenu,contenu.Desc_contenu, contenu.Photo_contenu, contenu.Date_contenu 
    FROM contenu 
    JOIN typecontenu ON contenu.Id_type_contenu = typecontenu.Id_type_contenu 
    WHERE typecontenu.Type_contenu = 'Chiffres' 
    ORDER BY contenu.Date_contenu ASC
");
$query->execute();
$chiffres = $query->fetchAll(PDO::FETCH_ASSOC);


$query = $pdo->prepare("
    SELECT contenu.Id_contenu,contenu.Titre_contenu, contenu.Desc_contenu 
    FROM contenu 
    JOIN typecontenu ON contenu.Id_type_contenu = typecontenu.Id_type_contenu 
    WHERE typecontenu.Type_contenu = 'Actualite' 
    ORDER BY contenu.Date_contenu ASC
");
$query->execute();
$actualites = $query->fetchAll(PDO::FETCH_ASSOC);


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
    <title>Page Accueil</title>
    <link rel="stylesheet" href="stylecss/stylesAcceuil.css"> <!-- Lien vers le fichier CSS -->
    <script>
        // Transmettre l'ID utilisateur à JavaScript
        const userId = <?php echo json_encode($userId); ?>;
    </script>
</head>
<div id="admin-popup" class="popup hidden"></div>
<body>
    <?php include 'header.php'; ?>
    <section class="hero">
        <div class="hero-content">
            <h1>
                <span class="highlight">Vivez</span> une vie étudiante avec des <span class="highlight">Événements uniques</span> grâce à l’ADIIL !
            </h1>
            <p>Découvrez une communauté dynamique et engagée au service des étudiants.</p>
            <div class="hero-buttons">
                <a href="https://discord.gg/uGCKejSKQX" class="btn-discord" target="_blank">
                    <img src="image/discord-icon.png" alt="Discord" class="button-icon"> Discord
                </a>
                <a href="https://www.instagram.com/bdeinfolaval?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" class="btn-instagram" target="_blank">
                    <img src="image/insta-icon.png" alt="Instagram" class="button-icon"> Instagram
                </a>
            </div>
        </div>
        <div class="hero-image">
            <img src="image/planet.png" alt="Lune" class="moon"> <!-- Lune -->
            <div class="background-rectangle"></div> <!-- Rectangle violet -->
            <img src="image/mascotte.png" alt="Mascotte ADIIL">
        </div>
    </section>

    <!-- Section Quelques Chiffres -->
    <section id="stats-section" class="stats-section">
        <div class="stats-header">
            <h2 class="stats-title">Quelques chiffres</h2>
            <?php if ($is_admin): ?>
                <!-- Bouton Modifier visible uniquement pour les admins -->
                <button id="edit-stats" class="admin-button-chiffre">
                    <img src="image/pensilIconModifChiffre.png" alt="Modifier">
                </button>
            <?php endif; ?>
        </div>
        <div class="stats-container">
            <div class="stats-items">
                <?php if (!empty($chiffres)): ?>
                    <?php foreach ($chiffres as $chiffre): ?>
                        <div class="stat-item" id="stat-<?php echo $chiffre['Id_contenu']; ?>">
                            <img src="<?php echo($chiffre['Photo_contenu']); ?>" 
                                alt="Image de <?php echo htmlspecialchars($chiffre['Desc_contenu']); ?>" 
                                class="stat-icon">
                            <p><?php echo htmlspecialchars($chiffre['Desc_contenu']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun chiffre disponible pour le moment.</p>
                <?php endif; ?>
            </div>
                <!-- Bouton "+" sous les blocs -->
                <button id="add-stat" class="add-button hidden">+</button>
        </div>

        <!-- Fenêtre modale pour ajouter une nouvelle entrée -->
        <div id="add-modal" class="modal hidden">
            <div class="modal-content">
                <button id="delete-modal" class="modal-delete-button">
                    <img src="image/bin.png" alt="Supprimer">
                </button>
                <!-- Image modifiable -->
                <div class="modal-icon">
                    <img id="modal-image" src="image/partyIconStat.png" alt="Image de base" />
                </div>
                <!-- Input caché pour le téléchargement -->
                <input type="file" id="image-input" accept="image/*" class="hidden">
                <textarea id="modal-description" placeholder="Description"></textarea>
                <button id="save-modal" class="modal-save-button" onclick="addStat()">
                    <img src="image/tick.png" alt="Enregistrer">
                </button>
            </div>
        </div>
    </section>


    <section id="projets" class="projets-section">
        <div class="project-container">
            <div class="project-item">
                <img src="image/evenementPres.png" alt="Image de présentation Evénement" class="project-image">
                <div class="project-details">
                    <h3>Nos Evénements</h3>
                    <p>L’objectif de ces événements est de rassembler les différentes promotions de l’iut pour pouvoir permettre la bonne intégration de chacun.
                    Et bien sûr de profiter tous ensemble !</p>
                    <br>
                    <button class="project-button">Voir plus</button>
                </div>
            </div>
            <div class="project-item reverse">
                <img src="image/bdePres.png" alt="Image du BDE" class="project-image">
                <div class="project-details">
                    <h3>Bureau Des Etudiants</h3>
                    <p>Viens apprendre à mieux connaître les personnes qui animent et prépare tes soirées.</p>
                    <br>
                    <button class="project-button">Voir plus</button>
                </div>
            </div>
            <div class="project-item">
                <img src="image/evenementPres.png" alt="Image de la foire aux questions" class="project-image">
                <div class="project-details">
                    <h3>Foire aux Questions</h3>
                    <p>Retrouve ici certaines informations pour pouvoir répondre aux questions classiques que tu peux te poser</p>
                    <br>
                    <button class="project-button">Voir plus</button>
                </div>
            </div>
        </div>
    </section>

    <section id="latest-news-section" class="latest-news-section">
        <div class="news-header">
            <h2 class="news-title">Actualités</h2>
            <?php if ($is_admin): ?>
                <!-- Bouton Modifier visible uniquement pour les admins -->
                <button id="edit-news" class="admin-button-actua">
                    <img src="image/pensilIconModifActua.png" alt="Modifier">
                </button>
            <?php endif; ?>
        </div>
        <div class="latest-news-container">
            <div class="latest-news-items">
                <?php foreach ($actualites as $newsItem): ?>
                    <div class="latest-news-item" id="news-<?php echo $newsItem['Id_contenu']; ?>">
                        <h3><?php echo htmlspecialchars($newsItem['Titre_contenu']); ?></h3>
                        <p><?php echo htmlspecialchars($newsItem['Desc_contenu']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
                <?php if ($is_admin): ?>
                    <button id="add-news" class="add-button-actu hidden">+</button>
                <?php endif; ?>
        </div>
        <!-- Fenêtre modale pour ajouter ou modifier une actualité -->
        <div id="news-modal" class="modal hidden">
            <div class="news-modal-content">
                <button id="news-delete-modal" class="news-modal-delete-button">
                    <img src="image/bin.png" alt="Supprimer">
                </button>
                <textarea id="news-modal-titre" placeholder="Titre"></textarea>
                <textarea id="news-modal-description" placeholder="Description"></textarea>
                <button id="news-save-modal" class="news-modal-save-button" onclick="addNews()">
                    <img src="image/tick.png" alt="Enregistrer">
                </button>
            </div>
        </div>
    </section>



    <section class="testimonial-section">
        <h2>On dit ça sur le BDE DE L’ADIIL, informatique</h2>
        <div class="testimonial-container">
            <div class="testimonial-card">
                <img src="profile1.jpg" alt="Enzo Rynders" class="testimonial-avatar">
                <h3>Enzo Rynders--Vitu</h3>
                <p class="testimonial-role">Président du BDE</p>
                <p class="testimonial-text">“Le BDE ADIIL c’est avant tout une famille qui met en place de merveilleux événements qui rapprochent”.</p>
                <div class="testimonial-pagination">
                    <button class="nav-arrow prev">&lt;</button>
                    <span class="testimonial-index">1/3</span>
                    <button class="nav-arrow next">&gt;</button>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
    <script src="js/script-accueil.js"></script>
</body>
</html>