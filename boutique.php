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
        <a href="index.php" class="logo">
            <img src="imagesAdmin/logoAdiil.png" alt="Logo ADIIL">
        </a>
        <nav>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Accueil</a></li>
                <li><a href="events.php">Événements</a></li>
                <li><a href="boutique.php">Boutique</a></li>
                <li><a href="bde.php">BDE</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </nav>
        <div class="header-buttons">
            <button class="connectButtonHeader">Se connecter</button>
            <button class="registerButtonHeader">S'inscrire</button>
            <img src="imagesAdmin/logoPanier.png" alt="Panier" class="cartIcon">
        </div>
    </div>
</header>
<main>
    <section class="grades" style="padding: 80px 0px;">
        <h2>Grades</h2>
        <div style="width: 100%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>
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
    
    <section class="consommables">
        <h2>Consommables</h2>
        <div style="width: 100%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

        <!-- Section Boissons -->
        <div class="sub-section" style="padding: 30px 0px;">
            <h3>Boissons :</h3>
            <div style="width: 10%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>
            <div class="product-container">
                <?php
                // Connexion à la base de données
                $host = 'localhost';
                $dbname = 'Sae';
                $username = 'root';
                $password = '';

                try {
                    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Récupération des boissons
                    $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'boisson'");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $imageUrl = "image/" . $row['Nom_prod'];

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

        <!-- Section Snacks -->
        <div class="sub-section">
            <h3>Snacks :</h3>
            <div style="width: 10%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>
            <div class="product-container">
                <?php
                try {
                    // Récupération des snacks
                    $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'snack'");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $imageUrl = "image/" . $row['Nom_prod'];

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
            <div style="width: 10%; height: 100%; border: 25px #AC6CFF solid; border-radius: 15px;"></div>
            <div class="product-container">
                <?php
                try {
                    // Récupération des produits "Autres"
                    $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'autres'");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $imageUrl = "image/" . $row['Nom_prod'];

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
</body>
</html>