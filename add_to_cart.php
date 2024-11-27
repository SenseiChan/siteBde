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

    // Vérification si le produit est un grade
    $isGrade = in_array($productId, ['grade_fer', 'grade_diamant', 'grade_or']);

    // Vérification si un grade est déjà dans le panier
    if ($isGrade) {
        foreach ($_SESSION['cart'] as $id => $item) {
            if (in_array($id, ['grade_fer', 'grade_diamant', 'grade_or'])) {
                // Si un autre grade est déjà dans le panier, redirigez avec un message d'erreur
                $_SESSION['error_message'] = "Vous ne pouvez avoir qu'un seul grade dans votre panier.";
                header('Location: boutique.php');
                exit;
            }
        }
    }

    // Ajouter l'article au panier
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => $productName,
            'price' => $productPrice,
            'image' => $productImage,
            'stock' => $productStock,
            'quantity' => 1,
        ];
    }

    // Redirection après l'ajout
    header('Location: panier.php');
    exit;
}
?>
