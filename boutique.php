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

$message = "";

// Gestion des ajouts/modifications produits par l'admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $type = $_POST['type'];

    $targetDir = "imagesAdmin/";
    $targetFile = $targetDir . basename($_FILES["photo"]["name"]);
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO produit (Nom_prod, Prix_prod, Stock_prod, Photo_prod, Type_prod) 
                VALUES (:name, :price, :stock, :photo, :type)
            ");
            $stmt->execute([
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'photo' => basename($_FILES["photo"]["name"]), // Stock uniquement le nom de fichier
                'type' => $type
            ]);
            $message = "<p style='color:green;'>Produit ajouté avec succès.</p>";
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
        }
    } else {
        $message = "<p style='color:red;'>Erreur lors de l'upload de l'image.</p>";
    }
    exit;
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
            <div class="grade-card grade-fer">
                <img src="image/lingotDeFer.png" alt="lingot de fer" width=80px>
                <h3>Fer</h3>
                <p>Fais vivre le BDE</p>
                <span class="price">5€</span>
            </div>
            <div class="grade-card grade-diamant">
                <img src="image/mineraiDiamant.png" alt="minerai de diamant" width=90px >
                <h3>Diamant</h3>
                <p>Adhésion au BDE</p>
                <p>Grade premium sur le serveur Minecraft</p>
                <span class="price">13€</span>
            </div>
            <div class="grade-card grade-or">
                <img src="image/lingotDOr.png" alt="lingot d'or" width=80px>
                <h3>Or</h3>
                <p>Adhésion au BDE</p>
                <p>Grade premium sur le serveur Minecraft</p>
                <span class="price">10€</span>
            </div>
        </div>
    </section>

    <?php
    // Section Boissons
    echo "<div class='sub-section'>
        <h3>Boissons</h3>
        <div class='product-container'>";
    try {
        $stmt = $pdo->query("SELECT Id_prod, Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'boisson'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "
            <div class='product'>
                <div class='product-image'>
                    <img src='{$row['Photo_prod']}' alt='{$row['Nom_prod']}' class='frame'>
                </div>
                <div class='product-details'>
                    <p class='name'>{$row['Nom_prod']}</p>
                    <p class='price'>Prix : " . number_format($row['Prix_prod'], 2) . "€</p>
                    <p class='stock'>En stock : {$row['Stock_prod']}</p>
                </div>
                <form method='post' action='add_to_cart.php'>
                    <input type='hidden' name='product_id' value='{$row['Id_prod']}'>
                    <input type='hidden' name='product_name' value='{$row['Nom_prod']}'>
                    <input type='hidden' name='product_price' value='{$row['Prix_prod']}'>
                    <input type='hidden' name='product_image' value='{$row['Photo_prod']}'>
                    <input type='hidden' name='product_stock' value='{$row['Stock_prod']}'>
                    <button type='submit' class='add-to-cart-btn'>Ajouter au panier</button>
                </form>
            </div>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
    }
    echo "</div></div>";

    // Section Snacks
    echo "<div class='sub-section'>
        <h3>Snacks</h3>
        <div class='product-container'>";
    try {
        $stmt = $pdo->query("SELECT Id_prod, Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'snack'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "
            <div class='product'>
                <div class='product-image'>
                    <img src='{$row['Photo_prod']}' alt='{$row['Nom_prod']}' class='frame'>
                </div>
                <div class='product-details'>
                    <p class='name'>{$row['Nom_prod']}</p>
                    <p class='price'>Prix : " . number_format($row['Prix_prod'], 2) . "€</p>
                    <p class='stock'>En stock : {$row['Stock_prod']}</p>
                </div>
                <form method='post' action='add_to_cart.php'>
                    <input type='hidden' name='product_id' value='{$row['Id_prod']}'>
                    <input type='hidden' name='product_name' value='{$row['Nom_prod']}'>
                    <input type='hidden' name='product_price' value='{$row['Prix_prod']}'>
                    <input type='hidden' name='product_image' value='{$row['Photo_prod']}'>
                    <input type='hidden' name='product_stock' value='{$row['Stock_prod']}'>
                    <button type='submit' class='add-to-cart-btn'>Ajouter au panier</button>
                </form>
            </div>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
    }
    echo "</div></div>";

    // Section Autres
    echo "<div class='sub-section'>
        <h3>Autres</h3>
        <div class='product-container'>";
    try {
        $stmt = $pdo->query("SELECT Id_prod, Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'autres'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "
            <div class='product'>
                <div class='product-image'>
                    <img src='{$row['Photo_prod']}' alt='{$row['Nom_prod']}' class='frame'>
                </div>
                <div class='product-details'>
                    <p class='name'>{$row['Nom_prod']}</p>
                    <p class='price'>Prix : " . number_format($row['Prix_prod'], 2) . "€</p>
                    <p class='stock'>En stock : {$row['Stock_prod']}</p>
                </div>
                <form method='post' action='add_to_cart.php'>
                    <input type='hidden' name='product_id' value='{$row['Id_prod']}'>
                    <input type='hidden' name='product_name' value='{$row['Nom_prod']}'>
                    <input type='hidden' name='product_price' value='{$row['Prix_prod']}'>
                    <input type='hidden' name='product_image' value='{$row['Photo_prod']}'>
                    <input type='hidden' name='product_stock' value='{$row['Stock_prod']}'>
                    <button type='submit' class='add-to-cart-btn'>Ajouter au panier</button>
                </form>
            </div>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
    }
    echo "</div></div>";
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
