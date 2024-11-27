<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_POST['product_image'];
    $productStock = $_POST['product_stock'];

    // Ajoutez correctement le produit au panier
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => htmlspecialchars_decode($productName, ENT_QUOTES), // Decode HTML entities for names with apostrophes
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
