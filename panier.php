<?php
session_start();

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Vérification de l'action
$action = isset($_GET['action']) ? $_GET['action'] : null;

// Initialisation du panier
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ajouter un produit au panier
if ($action === 'add' && isset($_GET['id'])) {
    $productId = intval($_GET['id']);

    // Récupérer les informations du produit
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE Id_prod = :id");
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (isset($_SESSION['cart'][$productId])) {
            // Incrémente la quantité si déjà dans le panier
            $_SESSION['cart'][$productId]['quantity']++;
        } else {
            // Ajoute le produit au panier
            $_SESSION['cart'][$productId] = [
                'id' => $productId,
                'name' => $product['Nom_prod'],
                'price' => $product['Prix_prod'],
                'image' => $product['Photo_prod'],
                'stock' => $product['Stock_prod'],
                'quantity' => 1
            ];
        }
    }
    header('Location: panier.php');
    exit();
}

// Supprimer un produit du panier
if ($action === 'remove' && isset($_GET['id'])) {
    $productId = intval($_GET['id']);
    unset($_SESSION['cart'][$productId]);
    header('Location: panier.php');
    exit();
}

// Mettre à jour les quantités
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['quantities'] as $productId => $quantity) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = max(1, intval($quantity)); // Minimum 1
        }
    }
    header('Location: panier.php');
    exit();
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
    <h1>Votre Panier</h1>
    <?php if (!empty($_SESSION['cart'])): ?>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="50">
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td><?= number_format($item['price'], 2) ?>€</td>
                            <td>
                                <input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                            </td>
                            <td><?= number_format($item['price'] * $item['quantity'], 2) ?>€</td>
                            <td>
                                <a href="panier.php?action=remove&id=<?= $item['id'] ?>" class="remove-btn">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="update-btn">Mettre à jour le panier</button>
        </form>
        <div class="cart-summary">
            <h2>Total : 
                <?= number_format(array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $_SESSION['cart'])), 2) ?>€
            </h2>
            <a href="checkout.php" class="checkout-btn">Passer à la caisse</a>
        </div>
    <?php else: ?>
        <p>Votre panier est vide.</p>
        <a href="boutique.php" class="back-btn">Retour à la boutique</a>
    <?php endif; ?>
</main>

</body>
</html>
