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
$quantity = 0;
$user_id = $_SESSION['user_id'];

// Calcul du montant total et de la quantité totale
foreach ($_SESSION['cart'] as $product_id => $product) {
    $total_amount += $product['price'] * $product['quantity'];
    $quantity += $product['quantity'];
}

// Ajout : Application de la réduction si un code promo est valide
$promoReduction = $_SESSION['promoReduction'] ?? 0; // Utilise la réduction stockée dans la session
if ($promoReduction > 0) {
    $total_amount = $total_amount - ($total_amount * ($promoReduction / 100));
}



// Récupération du grade de l'utilisateur si nécessaire
$stmt_grade = $pdo->prepare("SELECT Id_grade FROM Utilisateur WHERE Id_user = :id_user");
$stmt_grade->bindParam(':id_user', $user_id, PDO::PARAM_INT);
$stmt_grade->execute();
$id_grade = $stmt_grade->fetchColumn();

// Vérification si l'utilisateur existe
$stmt_check_user = $pdo->prepare("SELECT COUNT(*) FROM Utilisateur WHERE Id_user = :id_user");
$stmt_check_user->bindParam(':id_user', $user_id, PDO::PARAM_INT);
$stmt_check_user->execute();

if (!$stmt_check_user->fetchColumn()) {
    die("Erreur : L'utilisateur avec l'ID $user_id n'existe pas dans la base de données.");
}

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

// Ajout : Inclure l'ID promo si applicable
$id_promo = $_SESSION['promo_id'] ?? null; // Récupérer l'ID promo stocké dans la session

    $current_date = date('Y-m-d H:i:s');
    
    if ($payment_method_id == 1) {
        $payer_trans = 1;
    }else{
        $payer_trans = 0;
    }


    $grade_mapping = [
        'grade_diamant' => 3,
        'grade_or' => 2,
        'grade_fer' => 1,
    ];
    
    foreach ($_SESSION['cart'] as $product_id => $product) {
        $product_grade = NULL;
        $montant_trans = $product['price'] * $product['quantity'];
        $qte_trans = $product['quantity'];
        
        if ($product_id == 'grade_diamant') {
            $product_id = NULL;
            $product_grade = 3;

            $stmt->bindParam(':montant_trans', $montant_trans);
            $stmt->bindParam(':date_trans', $current_date);
            $stmt->bindParam(':qte_trans', $qte_trans);
            $stmt->bindParam(':payer_trans', $payer_trans);
            $stmt->bindParam(':id_promo', $id_promo, PDO::PARAM_INT);
            $stmt->bindValue(':id_grade', $product_grade, PDO::PARAM_INT);
            $stmt->bindParam(':id_event', $id_event, PDO::PARAM_INT);
            $stmt->bindParam(':id_prod', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_user', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_paie', $payment_method_id, PDO::PARAM_INT);
        } else if ($product_id == 'grade_or') {
            $product_id = NULL;
            $product_grade = 2;

            $stmt->bindParam(':montant_trans', $montant_trans);
            $stmt->bindParam(':date_trans', $current_date);
            $stmt->bindParam(':qte_trans', $qte_trans);
            $stmt->bindParam(':payer_trans', $payer_trans);
            $stmt->bindParam(':id_promo', $id_promo, PDO::PARAM_INT);
            $stmt->bindValue(':id_grade', $product_grade, PDO::PARAM_INT);
            $stmt->bindParam(':id_event', $id_event, PDO::PARAM_INT);
            $stmt->bindParam(':id_prod', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_user', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_paie', $payment_method_id, PDO::PARAM_INT);
        } else if ($product_id == 'grade_diamant') {
            $product_id = NULL;
            $product_grade = 1;

            $stmt->bindParam(':montant_trans', $montant_trans);
            $stmt->bindParam(':date_trans', $current_date);
            $stmt->bindParam(':qte_trans', $qte_trans);
            $stmt->bindParam(':payer_trans', $payer_trans);
            $stmt->bindParam(':id_promo', $id_promo, PDO::PARAM_INT);
            $stmt->bindValue(':id_grade', $product_grade, PDO::PARAM_INT);
            $stmt->bindParam(':id_event', $id_event, PDO::PARAM_INT);
            $stmt->bindParam(':id_prod', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_user', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_paie', $payment_method_id, PDO::PARAM_INT);
        } else {
        $stmt->bindParam(':montant_trans', $montant_trans);
        $stmt->bindParam(':date_trans', $current_date);
        $stmt->bindParam(':qte_trans', $qte_trans);
        $stmt->bindParam(':payer_trans', $payer_trans);
        $stmt->bindParam(':id_promo', $id_promo, PDO::PARAM_INT);
        $stmt->bindValue(':id_grade', $product_grade, $product_grade === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':id_event', $id_event, PDO::PARAM_INT);
        $stmt->bindParam(':id_prod', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_user', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_paie', $payment_method_id, PDO::PARAM_INT);
    
        
    }
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
