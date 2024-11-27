<?php
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

session_start();
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Récupérer le grade de l'utilisateur s'il est connecté
$userGrade = null;
if ($userId) {
    try {
        $stmt = $pdo->prepare("SELECT Id_grade FROM utilisateur WHERE Id_user = :userId");
        $stmt->execute(['userId' => $userId]);
        $userGrade = $stmt->fetchColumn();
    } catch (PDOException $e) {
        die("Erreur lors de la récupération du grade de l'utilisateur : " . $e->getMessage());
    }
}

// Définir les classes CSS désactivées pour les grades
function getDisabledClass($userGrade, $gradeId) {
    if ($userGrade == 2) { // L'utilisateur a le grade Diamant
        return 'disabled';
    } elseif ($userGrade == 3 && in_array($gradeId, [1, 3])) { // Grade Or : bloquer Fer et Or
        return 'disabled';
    } elseif ($userGrade == 1 && $gradeId == 1) { // Grade Fer : bloquer Fer
        return 'disabled';
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique ADIL</title>
    <link rel="stylesheet" href="stylecss/styleBoutique.css">
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <section class="grades">
        <h2>Grades</h2>
        <div class="grades-container">
            <!-- Grade Fer -->
            <div class="grade-card grade-fer <?php echo getDisabledClass($userGrade, 1); ?>">
                <img src="image/lingotDeFer.png" alt="lingot de fer" width=80px>
                <h3>Fer</h3>
                <p>Fais vivre le BDE</p>
                <span class="price">5€</span>
            </div>
            <!-- Grade Diamant -->
            <div class="grade-card grade-diamant <?php echo getDisabledClass($userGrade, 2); ?>">
                <img src="image/mineraiDiamant.png" alt="minerai de diamant" width=90px>
                <h3>Diamant</h3>
                <p>Adhésion au BDE</p>
                <p>Grade premium sur le serveur Minecraft</p>
                <span class="price">13€</span>
            </div>
            <!-- Grade Or -->
            <div class="grade-card grade-or <?php echo getDisabledClass($userGrade, 3); ?>">
                <img src="image/lingotDOr.png" alt="lingot d'or" width=80px>
                <h3>Or</h3>
                <p>Adhésion au BDE</p>
                <p>Grade premium sur le serveur Minecraft</p>
                <span class="price">10€</span>
            </div>
        </div>
    </section>

    <?php
    // Section Produits Générale
    function renderProductSection($pdo, $type, $title) {
        echo "<div class='sub-section'>
            <h3>$title</h3>
            <div class='product-container'>";
        try {
            $stmt = $pdo->prepare("SELECT Id_prod, Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = :type");
            $stmt->execute(['type' => $type]);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $productId = $row['Id_prod'];
                $inCartQuantity = isset($_SESSION['cart'][$productId]['quantity']) ? $_SESSION['cart'][$productId]['quantity'] : 0;
                $isOutOfStock = $inCartQuantity >= $row['Stock_prod'];

                echo "
                <div class='product'>
                    <div class='product-image'>
                        <img src='" . htmlspecialchars($row['Photo_prod'], ENT_QUOTES) . "' alt='" . htmlspecialchars($row['Nom_prod'], ENT_QUOTES) . "' class='frame'>
                    </div>
                    <div class='product-details'>
                        <p class='name'>" . htmlspecialchars($row['Nom_prod'], ENT_QUOTES) . "</p>
                        <p class='price'>Prix : " . number_format($row['Prix_prod'], 2) . "€</p>
                        <p class='stock'>En stock : " . htmlspecialchars($row['Stock_prod'], ENT_QUOTES) . "</p>
                    </div>
                    <form method='post' action='add_to_cart.php'>
                        <input type='hidden' name='product_id' value='" . htmlspecialchars($row['Id_prod'], ENT_QUOTES) . "'>
                        <input type='hidden' name='product_name' value='" . htmlspecialchars($row['Nom_prod'], ENT_QUOTES) . "'>
                        <input type='hidden' name='product_price' value='" . htmlspecialchars($row['Prix_prod'], ENT_QUOTES) . "'>
                        <input type='hidden' name='product_image' value='" . htmlspecialchars($row['Photo_prod'], ENT_QUOTES) . "'>
                        <input type='hidden' name='product_stock' value='" . htmlspecialchars($row['Stock_prod'], ENT_QUOTES) . "'>
                        <button type='submit' class='add-to-cart-btn'" . ($isOutOfStock ? ' disabled' : '') . ">
                            Ajouter au panier
                        </button>
                    </form>
                </div>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
        }
        echo "</div></div>";
    }

    renderProductSection($pdo, 'boisson', 'Boissons');
    renderProductSection($pdo, 'snack', 'Snacks');
    renderProductSection($pdo, 'autres', 'Autres');
    ?>

    <?php if ($is_admin): ?>
        <button id="openModal" class="ajouter-produit-btn">Ajouter un produit</button>
    <?php endif; ?>
</main>
<script>
    function addToCart(productId) {
        window.location.href = `panier.php?action=add&id=${productId}`;
    }
</script>
</body>
</html>
