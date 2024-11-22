<?php
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=Sae;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Fonction pour vérifier si l'utilisateur est administrateur
function isAdmin($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['is_admin'] ?? 0; // Retourne 1 si admin, 0 sinon
}

// Simulez l'ID de l'utilisateur connecté (à remplacer par votre système d'authentification)
$currentUserId = 1; // ID fictif pour l'exemple
$isAdmin = isAdmin($currentUserId, $pdo);

// Traitement de l'ajout de produit si le formulaire est soumis
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Gestion de l'upload de l'image
    $targetDir = "image/";
    $targetFile = $targetDir . basename($_FILES["photo"]["name"]);
    $uploadOk = 1;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO produit (Nom_prod, Type_prod, Prix_prod, Stock_prod, Photo_prod) 
                VALUES (:name, :type, :price, :stock, :photo)
            ");
            $stmt->execute([
                'name' => $name,
                'type' => $type,
                'price' => $price,
                'stock' => $stock,
                'photo' => basename($_FILES["photo"]["name"])
            ]);
            $message = "<p style='color:green;'>Produit ajouté avec succès.</p>";
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
        }
    } else {
        $message = "<p style='color:red;'>Échec de l'upload de l'image.</p>";
    }
}

// Récupération des produits existants
try {
    $stmt = $pdo->query("SELECT * FROM produit ORDER BY Type_prod");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
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
    <section class="consommables">
        <h2>Consommables</h2>
        <?php if (!empty($message)) echo $message; ?>

        <div class="product-container">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <img src="image/<?php echo htmlspecialchars($product['Photo_prod']); ?>" alt="Produit">
                    <p>
                        <span class="name"><?php echo htmlspecialchars($product['Nom_prod']); ?></span><br>
                        Type : <?php echo htmlspecialchars($product['Type_prod']); ?><br>
                        Prix : <?php echo htmlspecialchars($product['Prix_prod']); ?> €<br>
                        Stock : <?php echo htmlspecialchars($product['Stock_prod']); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Section pour ajouter des produits (seulement pour les admins) -->
        <?php if ($isAdmin): ?>
            <div class="add-product">
                <h3>Ajouter un consommable</h3>
                <form action="" method="POST" enctype="multipart/form-data" class="add-product-form">
                    <label for="name">Nom du produit :</label>
                    <input type="text" id="name" name="name" required>

                    <label for="type">Type :</label>
                    <select id="type" name="type" required>
                        <option value="boisson">Boisson</option>
                        <option value="snack">Snack</option>
                        <option value="autres">Autres</option>
                    </select>

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
    </section>
</main>
</body>
</html>
