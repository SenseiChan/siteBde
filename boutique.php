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

// Message pour le formulaire d'ajout
$message = "";

// Gestion de l'ajout d'un produit
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
            $message = "<p style='color:green;'>Produit ajouté avec succès.</p>";
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
        }
    } else {
        $message = "<p style='color:red;'>Erreur lors de l'upload de l'image.</p>";
    }
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
        <a href="index.php" class="logo">
            <img src="image/logoAdiil.png" alt="Logo ADIIL">
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
            <img src="image/logoPanier.png" alt="Panier" class="cartIcon">
        </div>
    </div>
</header>
<main>
    <!-- Grades Section -->
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

    <!-- Consommables Section -->
    <section class="consommables">
        <h2>Consommables</h2>
        <div style="width: 100%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

        <?php if (!empty($message)) echo $message; ?>

        <!-- Formulaire d'ajout pour les admins -->
        <?php if ($is_admin): ?>
        <div class="add-product">
            <h3>Ajouter un consommable</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="add-product-form">
                <label for="name">Nom du produit :</label>
                <input type="text" id="name" name="name" required>

                <label for="price">Prix (€) :</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>

                <label for="stock">Stock :</label>
                <input type="number" id="stock" name="stock" min="0" required>

                <label for="photo">Photo du produit :</label>
                <input type="file" id="photo" name="photo" accept="image/*" required>

                <button type="submit" class="registerButtonHeader">Ajouter</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Affichage des produits -->
        <div class="product-container">
            <h3>Tous les produits :</h3>
            <?php
            try {
                // Récupération de tous les produits
                $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $imageUrl = "imagesAdmin/". htmlspecialchars($row['Photo_prod']);
                    echo "
                    <div class='product'>
                        <img src='{$imageUrl}' alt='" . htmlspecialchars($row['Nom_prod']) . "' class='frame'>
                        <p>
                            <span class='name'>" . htmlspecialchars($row['Nom_prod']) . "</span><br><br>
                            Prix : " . htmlspecialchars($row['Prix_prod']) . "€<br><br>
                            En stock : " . htmlspecialchars($row['Stock_prod']) . "
                        </p>
                    </div>";
                }
            } catch (PDOException $e) {
                echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </section>
</main>
</body>
</html>
