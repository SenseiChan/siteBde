<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

function convertDriveLink($link) {
    // Vérifie si le lien contient "drive.google.com"
    if (strpos($link, 'drive.google.com') !== false) {
        // Extrais l'ID du fichier à partir du lien Google Drive
        preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $link, $matches);
        if (isset($matches[1])) {
            $fileId = $matches[1];
            // Retourne le lien au format "thumbnail"
            return "https://drive.google.com/thumbnail?id=" . $fileId;
        }
    }
    // Si le lien ne correspond pas au format attendu, retourne le lien d'origine
    return $link;
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

/*
// Exemple de requête pour récupérer le rôle
session_start();

$query = $pdo->prepare("
    SELECT role.Id_role, role.Nom_role
    FROM utilisateur
    JOIN role ON utilisateur.Id_role = role.Id_role
    WHERE utilisateur.Id_user = :id
");
$query->execute(['id' => $_SESSION['Id_user']]);
$role = $query->fetch(PDO::FETCH_ASSOC);

// Stocke dans la session si l'utilisateur est admin
if ($role && $role['Id_role'] == 2) {
    $_SESSION['is_admin'] = true;
} else {
    $_SESSION['is_admin'] = false;
}
    */
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page avec Header</title>
    <link rel="stylesheet" href="style/styles.css"> <!-- Lien vers le fichier CSS -->
</head>
<body>
    <header>
        <div class="header-container">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <img src="image/logoAdiil.png" alt="Logo ADIIL">
            </a>

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
                <button class="connectButtonHeader">Se connecter</button>
                <button class="registerButtonHeader">S'inscrire</button>
                <img src="image/logoPanier.png" alt="Panier" class="cartIcon">
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>
                <span class="highlight">Vivez</span> une vie étudiante avec des <span class="highlight">Événements uniques</span> grâce à l’ADIIL !
            </h1>
            <p>Découvrez une communauté dynamique et engagée au service des étudiants.</p>
            <div class="hero-buttons">
                <a href="#" class="btn-discord">
                    <img src="image/discord-icon.png" alt="Discord" class="button-icon"> Discord
                </a>
                <a href="#" class="btn-instagram">
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
            <button id="edit-stats" class="admin-button-chiffre">
                <img src="image/pensilIconModifChiffre.png" alt="Modifier">
            </button>
        </div>
        <div class="stats-container">
            <div class="stats-items">
                <?php if (!empty($chiffres)): ?>
                    <?php foreach ($chiffres as $chiffre): ?>
                        <div class="stat-item" id="stat-<?php echo $chiffre['Id_contenu']; ?>">
                            <img src="<?php echo convertDriveLink($chiffre['Photo_contenu']); ?>" 
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
                <button id="save-modal" class="modal-save-button">
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

    <section class="latest-news-section">
        <div class="news-header">
            <h2 class="news-title">Actualité</h2>
            <?//php if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']): ?>
                <button class="admin-button-actua">
                    <img src="image/pensilIconModifActua.png" alt="Modifier" />
                </button>
            <?//php endif; ?>
        </div>
        <div class="latest-news-container">
            <?php if (!empty($actualites)): ?>
                <?php foreach ($actualites as $actualite): ?>
                    <div class="latest-news-item">
                        <h3><?php echo htmlspecialchars($actualite['Titre_contenu']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($actualite['Desc_contenu'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune actualité disponible pour le moment.</p>
            <?php endif; ?>
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
    <script src="js/script-accueil.js"></script>
</body>
</html>