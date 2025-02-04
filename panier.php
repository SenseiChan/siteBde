<?php
session_start();

if (isset($_SESSION['error_message'])): ?>
    <div class="error-message">
        <?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES) ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif;

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: connexion.html");
    exit;
}

// Initialisation du panier s'il n'existe pas encore
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
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

$promoReduction = 0; // Réduction par défaut

// Vérifier si l'utilisateur a modifié les quantités via les boutons
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $productId = $_POST['product_id'];

        if (isset($_SESSION['cart'][$productId])) {
            // Vérifier si le produit est un événement
            $isEvent = strpos($productId, 'event_') === 0;

            if (!$isEvent) { // Ne pas permettre l'incrémentation pour les événements
                if ($action === 'increment') {
                    // Incrémenter la quantité uniquement si elle est inférieure au stock
                    if ($_SESSION['cart'][$productId]['quantity'] < $_SESSION['cart'][$productId]['stock']) {
                        $_SESSION['cart'][$productId]['quantity']++;
                    }
                } elseif ($action === 'decrement') {
                    // Décrémenter la quantité
                    $_SESSION['cart'][$productId]['quantity']--;

                    // Si la quantité atteint 0, supprimer le produit du panier
                    if ($_SESSION['cart'][$productId]['quantity'] <= 0) {
                        unset($_SESSION['cart'][$productId]);
                    }
                }
            }

            if ($action === 'remove') {
                // Supprimer le produit ou l'événement du panier
                unset($_SESSION['cart'][$productId]);
            }
        }
    }

    if (isset($_POST['promo_code'])) {
        $promoCode = trim($_POST['promo_code']);

        // Vérifiez si le code promo existe et est valide
        $stmt = $pdo->prepare("
            SELECT Pourcentage_promo, Date_deb_promo, Date_fin_promo 
            FROM promotion 
            WHERE Nom_promo = :promoCode
        ");
        $stmt->execute(['promoCode' => $promoCode]);
        $promo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($promo) {
            $currentDate = date('Y-m-d H:i:s');
            if ($currentDate >= $promo['Date_deb_promo'] && $currentDate <= $promo['Date_fin_promo']) {
                $_SESSION['promoReduction'] = (int) $promo['Pourcentage_promo'];
                $_SESSION['promo_id'] = $promo['Id_promo'] ?? null; // Si l'ID promo est disponible
            } else {
                $error = "Le code promo est expiré ou non valide.";
            }
        } else {
            $error = "Le code promo est invalide.";
        }
    }
}

// Calcul du total
$total = 0;
foreach ($_SESSION['cart'] as $product) {
    $total += $product['quantity'] * $product['price'];
}

// Ajout : Appliquez la réduction si un code promo est valide
$promoReduction = $_SESSION['promoReduction'] ?? 0;
if ($promoReduction > 0) {
    $total = $total - ($total * ($promoReduction / 100));
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
        <?php else: ?>
            <?php foreach ($_SESSION['cart'] as $productId => $product): ?>
                <?php if (strpos($productId, 'event_') === 0): // Si c'est un événement ?>
                    <div class="cart-item event-item">
                        <img src="<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>" 
                            alt="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>" 
                            class="product-image">
                        <div class="cart-details">
                            <h3>Événement : <?= htmlspecialchars($product['name'], ENT_QUOTES) ?></h3>
                            <p><strong>Prix :</strong> <?= htmlspecialchars(number_format($product['price'], 2)) ?> €</p>
                        </div>
                        <form method="post" class="remove-form">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId, ENT_QUOTES) ?>">
                            <button type="submit" name="action" value="remove" class="remove-btn">
                                <img src="image/bin.png" alt="Supprimer">
                            </button>
                        </form>
                    </div>
                <?php else: // Si c'est un produit standard ?>
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>" 
                            alt="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>" 
                            class="product-image">
                        <div class="cart-details">
                            <h3><?= htmlspecialchars($product['name'], ENT_QUOTES) ?></h3>
                            <div class="quantity-controls">
                                <form method="post" class="quantity-form">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId, ENT_QUOTES) ?>">
                                    <button type="submit" name="action" value="decrement" class="decrement-btn">-</button>
                                    <span class="quantity"><?= htmlspecialchars($product['quantity'], ENT_QUOTES) ?></span>
                                    <button type="submit" name="action" value="increment" class="increment-btn" 
                                        <?= $product['quantity'] >= $product['stock'] ? 'disabled' : '' ?>>+</button>
                                </form>
                            </div>
                            <p class="price"><?= number_format($product['quantity'] * $product['price'], 2) ?> €</p>
                        </div>
                        <form method="post" class="remove-form">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId, ENT_QUOTES) ?>">
                            <button type="submit" name="action" value="remove" class="remove-btn">
                                <img src="image/bin.png" alt="Supprimer">
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>

        <?php if (!empty($_SESSION['cart'])): ?>
            <!-- Zone Code promo -->
            <form method="post" class="promo-form">
                <label for="promo_code">Code promo :</label>
                <input type="text" name="promo_code" id="promo_code" placeholder="Entrez votre code promo">
                <button type="submit">Appliquer</button>
            </form>
            <?php if (!empty($error)): ?>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="cart-total">
            <h2>Total : <?= htmlspecialchars(number_format($total, 2)) ?> €</h2>
            <?php if ($promoReduction > 0): ?>
                <p>Réduction appliquée : <?= $promoReduction ?>%</p>
            <?php endif; ?>
            <div class="action-buttons">
                <a href="boutique.php" class="return-to-shop-btn">Retour à la boutique</a>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <form method="post" action="payement.php">
                        <button type="submit" class="pay-button">Payer</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
