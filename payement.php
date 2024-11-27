<?php
session_start();

// Vérification si le panier est vide
if (empty($_SESSION['cart'])) {
    header('Location: panier.php');
    exit();
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Initialisation des variables
$total_amount = 0;
$quantity = 0; // Initialisation pour la quantité totale
$user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté

// Calcul du montant total et de la quantité totale
foreach ($_SESSION['cart'] as $product_id => $product) {
    $total_amount += $product['price'] * $product['quantity'];
    $quantity += $product['quantity'];
}

// Vérification si l'utilisateur existe
$stmt_check_user = $pdo->prepare("SELECT COUNT(*) FROM Utilisateur WHERE Id_user = :id_user");
$stmt_check_user->bindParam(':id_user', $user_id, PDO::PARAM_INT);
$stmt_check_user->execute();

if (!$stmt_check_user->fetchColumn()) {
    die("Erreur : L'utilisateur avec l'ID $user_id n'existe pas dans la base de données.");
}

// Récupération du grade de l'utilisateur
$sql_grade = "SELECT Id_grade FROM Utilisateur WHERE Id_user = :id_user";
$stmt_grade = $pdo->prepare($sql_grade);
$stmt_grade->bindParam(':id_user', $user_id, PDO::PARAM_INT);
$stmt_grade->execute();
$id_grade = $stmt_grade->fetchColumn();

// Débogage pour vérifier la valeur de $id_grade
if ($id_grade === false) {
    $id_grade = null; // Si aucun grade trouvé, on le définit explicitement sur NULL
}
error_log("ID Grade récupéré : " . ($id_grade ?? "NULL"));

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];

    // Gestion des IDs (Promo, Event)
    $id_promo = $_SESSION['promo_id'] ?? null;
    $id_event = $_SESSION['event_id'] ?? null;

    // Mapper le moyen de paiement sur l'ID
    $payment_method_id = match ($payment_method) {
        'carte' => 1,
        'espece' => 2,
        'cheque' => 3,
        'paypal' => 4,
        'virement' => 5,
        default => throw new Exception("Moyen de paiement invalide.")
    };

    // Préparation de l'insertion dans la table Transactions
    $sql = "INSERT INTO Transactions (
                Montant_trans, 
                Date_trans, 
                Qte_trans, 
                Payer_trans, 
                Id_promo, 
                Id_grade, 
                Id_event, 
                Id_prod, 
                Id_user, 
                Id_paie
            )
            VALUES (
                :montant_trans, 
                :date_trans, 
                :qte_trans, 
                :payer_trans, 
                :id_promo, 
                :id_grade, 
                :id_event, 
                :id_prod, 
                :id_user, 
                :id_paie
            )";
    $stmt = $pdo->prepare($sql);

    $current_date = date('Y-m-d H:i:s'); // Date actuelle
    
    // Indicateur de transaction réglée (payée immédiatement ou non)
    $payer_trans = $payment_method_id === 1 ? 1 : 0;

    // Boucle pour chaque produit dans le panier
    foreach ($_SESSION['cart'] as $product_id => $product) {
        // Vérification si le produit existe
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Produit WHERE Id_prod = :product_id");
        $stmt_check->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_check->execute();

        if (!$stmt_check->fetchColumn()) {
            die("Erreur : Produit avec l'ID $product_id introuvable dans la base.");
        }

        $montant_trans = $product['price'] * $product['quantity'];
        $qte_trans = $product['quantity'];

        // Vérification si le produit existe dans la table produit
        $sql_check_product = "SELECT COUNT(*) FROM produit WHERE Id_prod = :id_prod";
        $stmt_check_product = $pdo->prepare($sql_check_product);
        $stmt_check_product->bindParam(':id_prod', $product_id, PDO::PARAM_INT);
        $stmt_check_product->execute();
        $product_exists = $stmt_check_product->fetchColumn() > 0;

        if (!$product_exists) {
            continue; // Saute ce produit si non trouvé
        }

        // Liaison des paramètres pour insertion
        $stmt->bindParam(':montant_trans', $montant_trans);
        $stmt->bindParam(':date_trans', $current_date);
        $stmt->bindParam(':qte_trans', $qte_trans);
        $stmt->bindParam(':payer_trans', $payer_trans);
        $stmt->bindParam(':id_promo', $id_promo, PDO::PARAM_INT);
        
        // Gestion explicite de id_grade (peut être NULL)
        if ($id_grade === null) {
            $stmt->bindValue(':id_grade', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':id_grade', $id_grade, PDO::PARAM_INT);
        }

        $stmt->bindParam(':id_event', $id_event, PDO::PARAM_INT);
        $stmt->bindParam(':id_prod', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_user', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_paie', $payment_method_id, PDO::PARAM_INT);

        $stmt->execute();
    }

    // Suppression du panier après paiement
    unset($_SESSION['cart']);

    // Redirection après le paiement
    header('Location: boutique.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylecss/stylePayement.css">
    <title>Paiement</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Choisissez votre moyen de paiement</h1>

        <!-- Affichage du montant total et de la quantité -->
        <h2>Montant Total: <?= number_format($total_amount, 2) ?> €</h2>
        <h3>Quantité totale: <?= $quantity ?> produit(s)</h3>

        <!-- Formulaire de sélection de paiement -->
        <form method="POST" action="payement.php" class="payment-form">
            <div class="payment-option">
                <label>
                    <input type="radio" name="payment_method" value="carte" required>
                    Paiement par Carte Bancaire
                </label>
            </div>

            <div class="payment-option">
                <label>
                    <input type="radio" name="payment_method" value="espece" required>
                    Paiement en Espèces
                </label>
            </div>

            <div class="payment-option">
                <label>
                    <input type="radio" name="payment_method" value="cheque" required>
                    Paiement par Chèque
                </label>
            </div>

            <div class="payment-option">
                <label>
                    <input type="radio" name="payment_method" value="paypal" required>
                    Paiement par PayPal
                </label>
            </div>

            <div class="payment-option">
                <label>
                    <input type="radio" name="payment_method" value="virement" required>
                    Paiement par Virement Bancaire
                </label>
            </div>

            <!-- Boutons de soumission et retour au panier -->
            <div class="button-group">
                <button type="submit" class="btn-pay">Valider le paiement</button>
                <a href="panier.php" class="btn-cart">Retour au panier</a>
            </div>
        </form>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
