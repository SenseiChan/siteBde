<?php
session_start();

// Si le panier est vide, rediriger vers la page d'accueil ou panier
if (empty($_SESSION['cart'])) {
    header('Location: panier.php');
    exit();
}

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
$user_id = $_SESSION['user_id']; // Assurez-vous que l'ID de l'utilisateur est stocké dans la session

// Calcul du montant total et de la quantité
foreach ($_SESSION['cart'] as $product_id => $product) {
    $total_amount += $product['price'] * $product['quantity'];
    $quantity += $product['quantity'];
}

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];

    // Si une promo existe, vous pouvez récupérer l'ID de la promo ici (sinon, laissez null)
    $id_promo = isset($_SESSION['promo_id']) ? $_SESSION['promo_id'] : null;

    // Exemple d'ID de grade, ajustez selon la logique de l'application
    $id_grade = 1;  // Remplacez par la logique appropriée pour récupérer l'ID du grade de l'utilisateur

    // ID d'événement, s'il y en a un
    $id_event = isset($_SESSION['event_id']) ? $_SESSION['event_id'] : null;

    // ID du produit (ou de l'événement), à extraire du panier
    $id_prod = null;
    if ($id_event === null) {
        // Si c'est un produit et non un événement
        $id_prod = $_SESSION['cart'][key($_SESSION['cart'])]['id_prod'];
    }

    // Insertion de la transaction dans la base de données
    $sql = "INSERT INTO Transactions (Montant_trans, Date_trans, Qte_trans, Id_promo, Id_grade, Id_event, Id_prod, Id_user, Id_paie)
            VALUES (:montant_trans, :date_trans, :qte_trans, :id_promo, :id_grade, :id_event, :id_prod, :id_user, :id_paie)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':montant_trans', $total_amount);
    $stmt->bindParam(':date_trans', date('Y-m-d H:i:s'));
    $stmt->bindParam(':qte_trans', $quantity);
    $stmt->bindParam(':id_promo', $id_promo);
    $stmt->bindParam(':id_grade', $id_grade);
    $stmt->bindParam(':id_event', $id_event);
    $stmt->bindParam(':id_prod', $id_prod);
    $stmt->bindParam(':id_user', $user_id);
    $stmt->bindParam(':id_paie', $payment_method == 'carte' ? 1 : 2); // 1 pour carte, 2 pour espèces
    $stmt->execute();

    // Récupérer l'ID de la transaction insérée
    $transaction_id = $pdo->lastInsertId();

    // Mise à jour de la colonne Payer_trans
    $sql_update = "UPDATE Transactions SET Payer_trans = 1 WHERE Id_trans = :transaction_id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':transaction_id', $transaction_id);
    $stmt_update->execute();

    // Redirection après paiement
    header('Location: confirmation.php');
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
        <form method="POST">
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

            <button type="submit" class="btn-pay">Valider le paiement</button>
        </form>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
