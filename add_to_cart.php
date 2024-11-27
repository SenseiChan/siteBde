<?php
session_start();

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Vérifiez si les données POST sont présentes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_POST['product_image'];
    $productStock = $_POST['product_stock'];

    // Si le produit est déjà dans le panier, incrémentez la quantité
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
    } else {
        // Sinon, ajoutez le produit au panier avec une quantité initiale de 1
        $_SESSION['cart'][$productId] = [
            'name' => $productName,
            'price' => $productPrice,
            'image' => $productImage,
            'stock' => $productStock,
            'quantity' => 1,
        ];
    }
}

// Redirigez vers la page panier
header('Location: panier.php');
exit();
?>
