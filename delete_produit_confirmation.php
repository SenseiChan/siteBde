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

// Vérification de l'ID du produit
$productId = $_GET['id'] ?? null;

if ($productId) {
    // Récupérer les informations du produit
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE Id_prod = :id");
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['error_message'] = "Produit introuvable.";
        header("Location: boutique.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "ID de produit manquant.";
    header("Location: boutique.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de suppression</title>
    <link rel="stylesheet" href="stylecss/styleDelete.css">
</head>
<body>
    <div class="delete-confirmation-container">
        <h1>Confirmation de suppression</h1>
        <p>Êtes-vous sûr de vouloir supprimer le produit suivant ?</p>

        <div class="product-info">
            <div class="image-wrapper">
                <img src="<?= htmlspecialchars($product['Photo_prod'], ENT_QUOTES) ?>" alt="Image du produit">
            </div>
            <p><strong>Nom :</strong> <?= htmlspecialchars($product['Nom_prod'], ENT_QUOTES) ?></p>
            <p><strong>Prix :</strong> <?= htmlspecialchars($product['Prix_prod'], ENT_QUOTES) ?> €</p>
            <p><strong>Stock :</strong> <?= htmlspecialchars($product['Stock_prod'], ENT_QUOTES) ?></p>
            <p><strong>Type :</strong> <?= htmlspecialchars($product['Type_prod'], ENT_QUOTES) ?></p>
        </div>

        <div class="confirmation-actions">
            <a href="delete_produit.php?id=<?= htmlspecialchars($product['Id_prod'], ENT_QUOTES) ?>" class="confirm-btn">Confirmer</a>
            <a href="boutique.php" class="cancel-btn">Annuler</a>
        </div>
    </div>
</body>
</html>
