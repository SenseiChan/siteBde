<?php
session_start();

// Vérifier si l'utilisateur est administrateur
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if (!$is_admin) {
    header("Location: index.php");
    exit();
}

$host = 'localhost';
$dbname = 'inf2pj_03';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['Nom_prod'] ?? '';
    $price = $_POST['Prix_prod'] ?? '';
    $stock = $_POST['Stock_prod'] ?? '';
    $type = $_POST['Type_prod'] ?? '';
    $imagePath = 'imagesAdmin/default_product.png'; // Image par défaut

    // Validation des champs
    if (!empty($name) && is_numeric($price) && $price > 0 && is_numeric($stock) && $stock >= 0 && !empty($type)) {
        // Gestion de l'upload d'image
        if (!empty($_FILES['Photo_prod']['name'])) {
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileExtension = strtolower(pathinfo($_FILES['Photo_prod']['name'], PATHINFO_EXTENSION));

            if (in_array($fileExtension, $allowedExtensions)) {
                $targetDir = "imagesAdmin/";
                $targetFile = $targetDir . uniqid() . '.' . $fileExtension;

                if (move_uploaded_file($_FILES['Photo_prod']['tmp_name'], $targetFile)) {
                    $imagePath = $targetFile;
                } else {
                    $error = "Erreur lors du téléchargement de l'image.";
                }
            } else {
                $error = "Format d'image non valide. Seuls les formats JPG, JPEG et PNG sont autorisés.";
            }
        }

        // Insérer le produit dans la base de données
        $query = "
            INSERT INTO produit (Nom_prod, Prix_prod, Stock_prod, Type_prod, Photo_prod)
            VALUES (:name, :price, :stock, :type, :image)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'stock' => $stock,
            'type' => $type,
            'image' => $imagePath,
        ]);

        header("Location: boutique.php");
        exit();
    } else {
        $error = "Veuillez remplir tous les champs correctement.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit</title>
    <link rel="stylesheet" href="stylecss/add_produit.css">
</head>
<body>
    <div class="add-product-container">
        <h1>Ajouter un produit</h1>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-content">
                <div class="image-container">
                    <label for="Photo_prod" class="image-label">
                        <img src="image/default_product.png" alt="Image par défaut" id="product-image">
                    </label>
                    <input type="file" name="Photo_prod" id="Photo_prod" accept=".jpg, .jpeg, .png" hidden>
                </div>
                <div class="form-details">
                    <div class="form-group">
                        <label for="Nom_prod">Nom :</label>
                        <input type="text" name="Nom_prod" id="Nom_prod" required>
                    </div>
                    <div class="form-group">
                        <label for="Prix_prod">Prix (€) :</label>
                        <input type="number" name="Prix_prod" id="Prix_prod" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="Stock_prod">Stock :</label>
                        <input type="number" name="Stock_prod" id="Stock_prod" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="Type_prod">Type :</label>
                        <select name="Type_prod" id="Type_prod" required>
                            <option value="Boisson">Boisson</option>
                            <option value="Snack">Snack</option>
                            <option value="Autres">Autres</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="save-btn">Ajouter</button>
                <a href="boutique.php" class="cancel-btn">Annuler</a>
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
