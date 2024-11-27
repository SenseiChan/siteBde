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

// Vérifier si un grade est déjà dans le panier
function isGradeInCart() {
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $item) {
            if (in_array($id, ['grade_fer', 'grade_diamant', 'grade_or'])) {
                return true; // Un grade est déjà présent dans le panier
            }
        }
    }
    return false;
}

// Définir les classes CSS désactivées pour les grades
function getDisabledClass($userGrade, $gradeId) {
    if ($userGrade == 2) { // L'utilisateur a le grade Diamant
        return 'disabled'; // Tous les grades sont bloqués
    } elseif ($userGrade == 3 && in_array($gradeId, [1, 3])) { // L'utilisateur a le grade Or
        return 'disabled'; // Fer et Or sont bloqués
    } elseif ($userGrade == 1 && $gradeId == 1) { // L'utilisateur a le grade Fer
        return 'disabled'; // Fer est bloqué
    }
    return ''; // Aucun blocage
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
    <!-- Affichage d'un message d'erreur si un grade est déjà dans le panier -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <section class="grades">
        <h2>Grades</h2>
        <div class="grades-container">
            <!-- Grade Fer -->
            <form method="post" action="add_to_cart.php" class="grade-card grade-fer <?= getDisabledClass($userGrade, 1) ?> <?= isGradeInCart() ? 'disabled' : '' ?>">
                <input type="hidden" name="product_id" value="grade_fer">
                <input type="hidden" name="product_name" value="Fer">
                <input type="hidden" name="product_price" value="5.00">
                <input type="hidden" name="product_image" value="image/lingotDeFer.png">
                <input type="hidden" name="product_stock" value="1">
                <div onclick="if (!this.parentElement.classList.contains('disabled')) this.parentElement.submit();">
                    <img src="image/lingotDeFer.png" alt="Lingot de fer" width="80">
                    <h3>Fer</h3>
                    <p>Fais vivre le BDE</p>
                    <span class="price">5€</span>
                </div>
            </form>

            <!-- Grade Diamant -->
            <form method="post" action="add_to_cart.php" class="grade-card grade-diamant <?= getDisabledClass($userGrade, 2) ?> <?= isGradeInCart() ? 'disabled' : '' ?>">
                <input type="hidden" name="product_id" value="grade_diamant">
                <input type="hidden" name="product_name" value="Diamant">
                <input type="hidden" name="product_price" value="13.00">
                <input type="hidden" name="product_image" value="image/mineraiDiamant.png">
                <input type="hidden" name="product_stock" value="1">
                <div onclick="if (!this.parentElement.classList.contains('disabled')) this.parentElement.submit();">
                    <img src="image/mineraiDiamant.png" alt="Minerai de diamant" width="90">
                    <h3>Diamant</h3>
                    <p>Adhésion au BDE</p>
                    <p>Grade premium sur le serveur Minecraft</p>
                    <span class="price">13€</span>
                </div>
            </form>

            <!-- Grade Or -->
            <form method="post" action="add_to_cart.php" class="grade-card grade-or <?= getDisabledClass($userGrade, 3) ?> <?= isGradeInCart() ? 'disabled' : '' ?>">
                <input type="hidden" name="product_id" value="grade_or">
                <input type="hidden" name="product_name" value="Or">
                <input type="hidden" name="product_price" value="10.00">
                <input type="hidden" name="product_image" value="image/lingotDOr.png">
                <input type="hidden" name="product_stock" value="1">
                <div onclick="if (!this.parentElement.classList.contains('disabled')) this.parentElement.submit();">
                    <img src="image/lingotDOr.png" alt="Lingot d'or" width="80">
                    <h3>Or</h3>
                    <p>Adhésion au BDE + avantages</p>
                    <p>Grade premium sur le serveur Minecraft</p>
                    <span class="price">10€</span>
                </div>
            </form>
        </div>
    </section>

    <?php
    // Section Produits Générale
    function renderProductSection($pdo, $type, $title) {
        global $is_admin; // Utilisation de la variable $is_admin

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
                    </form>";

                // Ajout du bouton de modification pour les administrateurs
                if ($is_admin) {
                    echo "
                    <a href='edit_produit.php?id=" . htmlspecialchars($productId, ENT_QUOTES) . "' class='edit-icon'>
                        <img src='image/icon_modify.png' alt='Modifier' class='icon-modify'>
                    </a>";
                }

                echo "</div>";
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
</body>
</html>
