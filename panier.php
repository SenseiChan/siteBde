<?php
session_start();

// Initialisation du panier s'il n'existe pas encore
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Vérifier si l'utilisateur a modifié les quantités via les boutons
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $productId = $_POST['product_id'];

    if (isset($_SESSION['cart'][$productId])) {
        if ($action === 'increment') {
            // Incrémenter la quantité (vérification du stock possible ici)
            $_SESSION['cart'][$productId]['quantity']++;
        } elseif ($action === 'decrement') {
            // Décrémenter la quantité, mais pas en dessous de 1
            $_SESSION['cart'][$productId]['quantity'] = max(1, $_SESSION['cart'][$productId]['quantity'] - 1);
        } elseif ($action === 'remove') {
            // Supprimer le produit du panier
            unset($_SESSION['cart'][$productId]);
        }
    }
}

// Calcul du total
$total = 0;
foreach ($_SESSION['cart'] as $product) {
    $total += $product['quantity'] * $product['price'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <link rel="stylesheet" href="stylecss/stylePanier.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Panier :</h1>
        <div class="cart-container">
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Votre panier est vide.</p>
            <a href="boutique.php" class="return-btn">Retour à la boutique</a>
        <?php else: ?>
            <?php foreach ($_SESSION['cart'] as $productId => $product): ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    <div class="cart-details">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p>Prix : <?= htmlspecialchars(number_format($product['price'], 2)) ?> €</p>
                        <div class="quantity-controls">
                            <form method="post" class="quantity-form">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId) ?>">
                                <button type="submit" name="action" value="decrement" class="decrement-btn">-</button>
                                <span class="quantity"><?= htmlspecialchars($product['quantity']) ?></span>
                                <button type="submit" name="action" value="increment" class="increment-btn">+</button>
                            </form>
                        </div>
                        <form method="post" class="remove-form">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId) ?>">
                            <button type="submit" name="action" value="remove" class="remove-btn">🗑</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
        <div class="cart-total">
            <h2>Total : <?= htmlspecialchars(number_format($total, 2)) ?> €</h2>
            <?php if (!empty($_SESSION['cart'])): ?>
                <button class="pay-btn">Payer</button>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
