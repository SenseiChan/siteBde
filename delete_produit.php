<?php
session_start();

// Vérifier si l'utilisateur est administrateur
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if (!$is_admin) {
    header("Location: accueil.php");
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
    try {
        // Récupérer les informations du produit pour suppression de l'image
        $stmt = $pdo->prepare("SELECT Photo_prod FROM produit WHERE Id_prod = :id");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Supprimer l'image associée si elle existe
            if (!empty($product['Photo_prod']) && file_exists($product['Photo_prod'])) {
                unlink($product['Photo_prod']); // Supprime le fichier image
            }

            // Supprimer le produit de la base de données
            $stmt = $pdo->prepare("DELETE FROM produit WHERE Id_prod = :id");
            $stmt->execute(['id' => $productId]);

            // Rediriger vers la boutique avec un message de succès
            $_SESSION['success_message'] = "Le produit a été supprimé avec succès.";
            header("Location: boutique.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Produit introuvable.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "ID de produit manquant.";
    header("Location: boutique.php");
    exit();
}
?>
