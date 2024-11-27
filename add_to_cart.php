<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifiez si toutes les informations nécessaires sont fournies
    $requiredFields = ['product_id', 'product_name', 'product_price', 'product_image', 'product_stock'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            die("Erreur : une information requise ($field) est manquante.");
        }
    }

    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_POST['product_image'];
    $productStock = $_POST['product_stock'];

    // Ajoutez correctement le produit au panier
    if (isset($_SESSION['cart'][$productId])) {
        // Si l'élément est déjà dans le panier, ne pas dépasser le stock maximal
        if ($_SESSION['cart'][$productId]['quantity'] < $productStock) {
            $_SESSION['cart'][$productId]['quantity']++;
        }
    } else {
        // Ajoutez le produit au panier
        $_SESSION['cart'][$productId] = [
            'name' => htmlspecialchars($productName, ENT_QUOTES), // Évitez les problèmes de caractères spéciaux
            'price' => $productPrice,
            'image' => $productImage,
            'stock' => $productStock,
            'quantity' => 1,
        ];
    }

    // Redirige vers la page panier ou boutique
    header('Location: panier.php');
    exit;
}
?>
