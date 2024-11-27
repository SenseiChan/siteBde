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
    $name = $_POST['Nom_prod'] ?? '';
    $price = $_POST['Prix_prod'] ?? '';
    $stock = $_POST['Stock_prod'] ?? '';
    $type = $_POST['Type_prod'] ?? '';

    if (!empty($name) && !empty($price) && !empty($stock) && !empty($type)) {
        // Vérifier si une nouvelle image a été téléchargée
        if (!empty($_FILES['Photo_prod']['name'])) {
            $targetDir = 'imagesAdmin/';
            $fileName = basename($_FILES['Photo_prod']['name']);
            $targetFile = $targetDir . $fileName;

            // Déplacer le fichier uploadé vers le répertoire cible
            if (move_uploaded_file($_FILES['Photo_prod']['tmp_name'], $targetFile)) {
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
                $stmt->bindParam(':image', $targetFile, PDO::PARAM_STR);
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
            header("Location: boutique.php");
            exit();
        } else {
            $error = "Erreur lors de la mise à jour.";
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
    <title>Modifier un Produit</title>
    <link rel="stylesheet" href="stylecss/styleEdit.css">
</head>
<body>
    <div class="edit-product-container">
        <h1><?= htmlspecialchars($product['Nom_prod']) ?></h1>
        <div class="header-icons">
            <a href="delete_product.php?id=<?= htmlspecialchars($product['Id_prod']) ?>" class="delete-icon">
                <img src="image/bin.png" alt="Supprimer">
            </a>
            <a href="boutique.php" class="close-btn">
                <img src="image/icon_close.png" alt="Fermer">
            </a>
        </div>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-content">
                <div class="image-container">
                    <label for="Photo_prod" class="image-label">
                        <img src="<?= htmlspecialchars($product['Photo_prod']) ?>" alt="Photo du produit" id="product-image">
                    </label>
                    <input type="file" name="Photo_prod" id="Photo_prod" accept=".jpg, .jpeg, .png" hidden>
                </div>
                <div class="form-details">
                    <div class="form-group">
                        <label for="Nom_prod">Nom :</label>
                        <input type="text" name="Nom_prod" id="Nom_prod" value="<?= htmlspecialchars($product['Nom_prod']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="Prix_prod">Prix (€) :</label>
                        <input type="number" name="Prix_prod" id="Prix_prod" value="<?= htmlspecialchars($product['Prix_prod']) ?>" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="Stock_prod">Stock :</label>
                        <input type="number" name="Stock_prod" id="Stock_prod" value="<?= htmlspecialchars($product['Stock_prod']) ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="Type_prod">Type :</label>
                        <select name="Type_prod" id="Type_prod" required>
                            <option value="Boisson" <?= $product['Type_prod'] === 'Boisson' ? 'selected' : '' ?>>Boisson</option>
                            <option value="Snack" <?= $product['Type_prod'] === 'Snack' ? 'selected' : '' ?>>Snack</option>
                            <option value="Autres" <?= $product['Type_prod'] === 'Autres' ? 'selected' : '' ?>>Autres</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="save-btn">Sauvegarder</button>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('Photo_prod').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('product-image').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
