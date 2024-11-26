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
    die("Erreur : " . $e->getMessage());
}

// Fonction pour vérifier si un utilisateur est administrateur
session_start();
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


// Message pour le formulaire d'ajout
$message = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Upload de l'image
    $targetDir = "imagesAdmin/";
    $targetFile = $targetDir . basename($_FILES["photo"]["name"]);
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO produit (Nom_prod, Prix_prod, Stock_prod, Photo_prod) 
                VALUES (:name, :price, :stock, :photo)
            ");
            $stmt->execute([
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'photo' => basename($_FILES["photo"]["name"])
            ]);
            // Retourner un message de succès
            echo "<p style='color:green;'>Produit ajouté avec succès.</p>";
        } catch (PDOException $e) {
            // Retourner un message d'erreur
            echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Erreur lors de l'upload de l'image.</p>";
    }
    exit; // Terminer le script PHP après la réponse AJAX
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique ADIL</title>
    <link rel="stylesheet" href="stylecss/styleBoutique.css">
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
                <a href="gestionMembre.php">Gestion membre</a>
                <a href="#">Statistique</a>
                <a href="#">Banque</a>
                <a href="boutique.php?gestion_site=true">Gestion site</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Navigation -->
        <nav>
            <ul class="nav-links">
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="events.php">Événements</a></li>
                <li><a href="boutique.php" class="active">Boutique</a></li>
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

<main>
    <!-- Grades Section -->
    <section id="noBlurSection" class="grades" style="padding: 80px 0px;">
        <h2>Grades</h2>
        <div style="width: 100%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

        <!-- Logo Admin -->
        <br><br><br>
        <?php if ($is_admin && isset($_GET['gestion_site']) && $_GET['gestion_site'] == 'true'): ?>
            <div class="admin-logo">
                <a href="boutique.php?gestion_site=true" id="editModeButton">
                    <img src="image/pensilIconModifChiffre.png" alt="Logo Admin" class="admin-logo-img">
                </a>
            </div>
        <?php endif; ?>

        <div class="grades-container">
            <div class="grade-card grade-fer">
                <img src="image/lingotDeFer.png" alt="lingot de fer" width=80px>
                <h3>Fer</h3>
                <p>Fais vivre le BDE</p>
                <span class="price">5€</span>
            </div>
            <div class="grade-card grade-diamant">
                <img src="image/mineraiDiamant.png" alt="minerai de diamant" width=90px >
                <h3>Diamant</h3>
                <p>Adhésion au BDE</p>
                <p>Le grade premium sur serveur Minecraft de l'ADIL</p>
                <span class="price">13€</span>
            </div>
            <div class="grade-card grade-or">
                <img src="image/lingotDOr.png" alt="lingot d'or" width=80px>
                <h3>Or</h3>
                <p>Adhésion au BDE</p>
                <p>Grade premium sur le serveur Minecraft</p>
                <span class="price">10€</span>
            </div>
        </div>
    </section>

    <!-- Consommables Section -->
    <section class="consommables">
        <h2>Consommables</h2>
        <div style="width: 100%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

        <?php if (!empty($message)) echo $message; ?>

        <!-- Section Boissons -->
        <div class="sub-section" style="padding: 30px 0px;">
            <h3>Boissons :</h3>
            <div style="width: 10%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>
            
            <!-- Logo Admin -->
            <?php if ($is_admin && isset($_GET['gestion_site']) && $_GET['gestion_site'] == 'true'): ?>
            <div class="admin-logo">
                <img src="image/pensilIconModifChiffre.png" alt="Logo Admin" class="admin-logo-img">
            </div>
            <?php endif; ?>

            <div class="product-container">
                <?php
                    try {
                        $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'boisson'");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $imageUrl = "imagesAdmin/". $row['Photo_prod'];
                            echo "
                            <div class='product'>
                                <img src='{$imageUrl}' alt='{$row['Nom_prod']}' class='frame'>
                                <p>
                                    <span class='name'>{$row['Nom_prod']}</span><br><br>
                                    Prix : {$row['Prix_prod']}€<br><br>
                                    En stock : {$row['Stock_prod']}<br><br>
                                </p>
                            </div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
                    }
                ?>
            </div>
        </div>

        <!-- Section Snacks -->
        <div class="sub-section">
            <h3>Snacks :</h3>
            <div style="width: 10%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

            <!-- Logo Admin -->
            <?php if ($is_admin && isset($_GET['gestion_site']) && $_GET['gestion_site'] == 'true'): ?>
            <div class="admin-logo">
                <img src="image/pensilIconModifChiffre.png" alt="Logo Admin" class="admin-logo-img">
            </div>
            <?php endif; ?>
            
            <div class="product-container">
                
                <?php
                try {
                    $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'snack'");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $imageUrl = "imagesAdmin/" . $row['Photo_prod'];

                        echo "
                        <div class='product'>
                            <img src='{$imageUrl}' alt='{$row['Nom_prod']}' class='frame'>
                            <p>
                                <span class='name'>{$row['Nom_prod']}</span><br><br>
                                Prix : {$row['Prix_prod']}€<br><br>
                                En stock : {$row['Stock_prod']}
                            </p>
                        </div>";
                    }
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>

        <!-- Section Autres -->
        <div class="sub-section">
            <h3>Autres :</h3>
            <div style="width: 10%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

            <!-- Logo Admin -->
            <?php if ($is_admin && isset($_GET['gestion_site']) && $_GET['gestion_site'] == 'true'): ?>
            <div class="admin-logo">
                <img src="image/pensilIconModifChiffre.png" alt="Logo Admin" class="admin-logo-img">
            </div>
            <?php endif; ?>

            <div class="product-container">
                <?php
                try {
                    $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'autres'");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $imageUrl = "imagesAdmin/" . $row['Photo_prod'];

                        echo "
                        <div class='product'>
                            <img src='{$imageUrl}' alt='{$row['Nom_prod']}' class='frame'>
                            <p>
                                <span class='name'>{$row['Nom_prod']}</span><br><br>
                                Prix : {$row['Prix_prod']}€<br><br>
                                En stock : {$row['Stock_prod']}
                            </p>
                        </div>";
                    }
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
    </section>
</main>


<script src="js/scriptBoutique.js"></script>

</body>
</html>

