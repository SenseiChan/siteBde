<?php
session_start();

// Vérification si le panier est vide
if (empty($_SESSION['cart'])) {
    header('Location: panier.php');
    exit();
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'inf2pj_03';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
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

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];

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
    ) VALUES (
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

    $current_date = date('Y-m-d H:i:s');
    $payer_trans = $payment_method_id == 1 ? 1 : 0;

    foreach ($_SESSION['cart'] as $product_id => $product) {
        $product_grade = null;
        $montant_trans = $product['price'] * $product['quantity'];
        $qte_trans = $product['quantity'];
        $id_event = null;

        // Vérifier si le produit est un grade
        if (in_array($product_id, ['grade_diamant', 'grade_or', 'grade_fer'])) {
            $product_grade = match ($product_id) {
                'grade_diamant' => 3,
                'grade_or' => 2,
                'grade_fer' => 1,
                default => null
            };
            $product_id = null; // Pas d'ID produit pour un grade

            // Mise à jour du grade de l'utilisateur dans la table utilisateur
            $updateGradeSql = "UPDATE utilisateur SET Id_grade = :id_grade WHERE Id_user = :id_user";
            $updateGradeStmt = $pdo->prepare($updateGradeSql);
            $updateGradeStmt->execute([
                ':id_grade' => $product_grade,
                ':id_user' => $user_id,
            ]);
        }

        // Vérifier si le produit est un événement
        if (strpos($product_id, 'event_') === 0) {
            $id_event = str_replace('event_', '', $product_id);
            $product_id = null; // Pas d'ID produit pour un événement
        }

        // Exécuter la transaction
        $stmt->execute([
            ':montant_trans' => $montant_trans,
            ':date_trans' => $current_date,
            ':qte_trans' => $qte_trans,
            ':payer_trans' => $payer_trans,
            ':id_promo' => $_SESSION['promo_id'] ?? null,
            ':id_grade' => $product_grade,
            ':id_event' => $id_event,
            ':id_prod' => $product_id,
            ':id_user' => $user_id,
            ':id_paie' => $payment_method_id,
        ]);

        // Ajouter l'utilisateur à la table de participation pour un événement
        if ($id_event !== null) {
            $check_participation = $pdo->prepare("
                SELECT COUNT(*) 
                FROM participer 
                WHERE Id_user = :id_user AND Id_event = :id_event
            ");
            $check_participation->execute([
                ':id_user' => $user_id,
                ':id_event' => $id_event,
            ]);

            // Insérer uniquement si l'utilisateur n'est pas encore inscrit
            if ($check_participation->fetchColumn() == 0) {
                $participationSql = "INSERT INTO participer (Id_user, Id_event) VALUES (:id_user, :id_event)";
                $participationStmt = $pdo->prepare($participationSql);
                $participationStmt->execute([
                    ':id_user' => $user_id,
                    ':id_event' => $id_event,
                ]);
            }
        }

        // Décrémenter le stock du produit si ce n'est pas un grade ou un événement
        if ($product_id !== null) {
            $updateStockSql = "UPDATE produit SET Stock_prod = Stock_prod - :quantity WHERE Id_prod = :product_id";
            $updateStockStmt = $pdo->prepare($updateStockSql);
            $updateStockStmt->execute([
                ':quantity' => $product['quantity'],
                ':product_id' => $product_id,
            ]);
        }
    }

    // Suppression du panier après paiement
    unset($_SESSION['cart']);

    // Suppression de la réduction et de l'ID promo
    unset($_SESSION['promoReduction']);
    unset($_SESSION['promo_id']);

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
