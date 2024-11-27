<?php
session_start();

// Si le panier est vide, rediriger vers la page d'accueil ou panier
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
$user_id = $_SESSION['user_id']; // Assurez-vous que l'utilisateur est connecté et que l'ID utilisateur est stocké

// Calcul du montant total et de la quantité
foreach ($_SESSION['cart'] as $product_id => $product) {
    $total_amount += $product['price'] * $product['quantity'];
}

// Récupération du grade de l'utilisateur
$sql_grade = "SELECT Id_grade FROM Utilisateur WHERE Id_user = :id_user";
$stmt_grade = $pdo->prepare($sql_grade);
$stmt_grade->bindParam(':id_user', $user_id, PDO::PARAM_INT);
$stmt_grade->execute();
$id_grade = $stmt_grade->fetchColumn(); // Récupère l'Id_grade (ou null si pas trouvé)

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

    // Préparer la requête d'insertion pour les transactions
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
    $payer_trans = 1; // Transaction réglée

    // Boucle pour insérer chaque produit comme une transaction
    foreach ($_SESSION['cart'] as $product_id => $product) {
        $montant_trans = $product['price'] * $product['quantity']; // Prix total pour ce produit
        $qte_trans = $product['quantity']; // Quantité pour ce produit

        // Liaison des variables aux colonnes
        $stmt->bindParam(':montant_trans', $montant_trans);
        $stmt->bindParam(':date_trans', $current_date);
        $stmt->bindParam(':qte_trans', $qte_trans);
        $stmt->bindParam(':payer_trans', $payer_trans);
        $stmt->bindParam(':id_promo', $id_promo);
        $stmt->bindParam(':id_grade', $id_grade);
        $stmt->bindParam(':id_event', $id_event);
        $stmt->bindParam(':id_prod', $product_id); // ID du produit
        $stmt->bindParam(':id_user', $user_id);
        $stmt->bindParam(':id_paie', $payment_method_id);

        // Exécution de la requête
        $stmt->execute();
    }

    // Suppression du panier après paiement
    unset($_SESSION['cart']);

    // Redirection après paiement
    header('Location: boutique.php');
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
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
        <form method="POST" action="payement.php">
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
                    Paiement par Cheque
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

            <!-- Bouton de soumission -->
            <button type="submit" class="btn-pay">Valider le paiement</button>
        </form>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
