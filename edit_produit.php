<?php
session_start();

// Vérifier si l'utilisateur est administrateur
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if (!$is_admin) {
    header("Location: accueil.php");
    exit();
}

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

// Initialisation des variables
$error = '';
$success = '';
$product = null;
$productId = $_GET['id'] ?? null;

if ($productId) {
    // Récupérer les informations du produit
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE Id_prod = :id");
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $error = "Produit introuvable.";
    }
} else {
    $error = "ID de produit manquant.";
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $type = $_POST['type'] ?? '';

    if (!empty($name) && !empty($price) && !empty($stock) && !empty($type)) {
        // Vérifier si une nouvelle image a été téléchargée
        if (!empty($_FILES['product_image']['name'])) {
            $imagePath = 'uploads/' . basename($_FILES['product_image']['name']);
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $imagePath)) {
                // Requête avec mise à jour de l'image
                $sql = "UPDATE produit 
                        SET Nom_prod = :name, 
                            Prix_prod = :price, 
                            Stock_prod = :stock, 
                            Type_prod = :type, 
                            Photo_prod = :image 
                        WHERE Id_prod = :productId";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':price', $price, PDO::PARAM_STR);
                $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
                $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                $stmt->bindParam(':image', $imagePath, PDO::PARAM_STR);
                $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            } else {
                $error = "Erreur lors de l'upload de l'image.";
            }
        } else {
            // Requête sans mise à jour de l'image
            $sql = "UPDATE produit 
                    SET Nom_prod = :name, 
                        Prix_prod = :price, 
                        Stock_prod = :stock, 
                        Type_prod = :type 
                    WHERE Id_prod = :productId";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_STR);
            $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
        }

        // Exécution de la requête
        if ($stmt->execute()) {
            $success = "Produit mis à jour avec succès.";
            // Mettre à jour les données du produit
            $product['Nom_prod'] = $name;
            $product['Prix_prod'] = $price;
            $product['Stock_prod'] = $stock;
            $product['Type_prod'] = $type;
            if (isset($imagePath)) {
                $product['Photo_prod'] = $imagePath;
            }
        } else {
            $error = "Erreur lors de la mise à jour : " . implode(", ", $stmt->errorInfo());
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Produit</title>
    <link rel="stylesheet" href="stylecss/styleEdit.css">
</head>
<body>
    <main>
        <h1>Modifier le Produit</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <?php if ($product): ?>
            <form method="post" enctype="multipart/form-data">
                <div class="form-container">
                    <div class="form-group">
                        <label>Image :</label>
                        <div class="image-container">
                            <img src="<?= htmlspecialchars($product['Photo_prod']) ?>" alt="Produit">
                            <input type="file" name="product_image" id="product_image">
                            <label for="product_image" class="change-image">Cliquez pour changer l'image</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name">Nom :</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['Nom_prod'], ENT_QUOTES) ?>">
                    </div>

                    <div class="form-group">
                        <label for="price">Prix (€) :</label>
                        <input type="text" id="price" name="price" value="<?= htmlspecialchars($product['Prix_prod'], ENT_QUOTES) ?>">
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock :</label>
                        <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($product['Stock_prod'], ENT_QUOTES) ?>">
                    </div>

                    <div class="form-group">
                        <label for="type">Type :</label>
                        <select id="type" name="type">
                            <option value="Boisson" <?= $product['Type_prod'] === 'Boisson' ? 'selected' : '' ?>>Boisson</option>
                            <option value="Snack" <?= $product['Type_prod'] === 'Snack' ? 'selected' : '' ?>>Snack</option>
                            <option value="Autres" <?= $product['Type_prod'] === 'Autres' ? 'selected' : '' ?>>Autres</option>
                        </select>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="boutique.php" class="btn btn-danger">Annuler</a>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
