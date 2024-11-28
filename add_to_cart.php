<?php
session_start();

// Initialisation du panier s'il n'existe pas encore
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Vérifier si des données POST ont été envoyées
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_POST['product_image'];
    $productStock = $_POST['product_stock'];

    // Vérification si le produit est un événement
    $isEvent = strpos($productId, 'event_') === 0;

    // Empêcher d'ajouter deux fois le même événement
    if ($isEvent && isset($_SESSION['cart'][$productId])) {
        $_SESSION['error_message'] = "Cet événement est déjà dans votre panier.";
        header('Location: panier.php');
        exit;
    }

    // Ajouter l'article ou l'événement au panier
    $_SESSION['cart'][$productId] = [
        'name' => $productName,
        'price' => $productPrice,
        'image' => $productImage,
        'stock' => $productStock,
        'quantity' => 1,
    ];

    // Redirection directe vers le panier
    header('Location: panier.php');
    exit;
}
?>
