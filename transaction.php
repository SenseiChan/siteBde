<?php 
session_start();
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if (!$is_admin) {
    header("Location: accueil.php");
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

// Traitement du changement d'état de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $transaction_id = $_POST['transaction_id'];
    $payer_status = isset($_POST['payer']) ? 1 : 0;  // Si la case est cochée, on met à 1 ("Payé"), sinon à 0 ("Non payé")
    
    // Mise à jour de la base de données
    $sql = "UPDATE Transactions SET Payer_trans = :payer_status WHERE Id_trans = :transaction_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':payer_status', $payer_status, PDO::PARAM_INT);
    $stmt->bindParam(':transaction_id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
}

// Récupération des transactions
$sql = "SELECT 
            t.Id_trans, 
            t.Montant_trans, 
            t.Date_trans, 
            t.Qte_trans, 
            t.Payer_trans, 
            u.Nom_user, 
            u.Prenom_user, 
            p.Nom_paie
        FROM Transactions t
        INNER JOIN Utilisateur u ON t.Id_user = u.Id_user
        INNER JOIN Paiement p ON t.Id_paie = p.Id_paie
        ORDER BY t.Date_trans DESC";

$transactions = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylecss/styleTransaction.css">
    <title>Transaction</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Liste des Transactions</h1>
        <table>
            <thead>
                <tr>
                    <th>Montant</th>
                    <th>Date</th>
                    <th>Quantité</th>
                    <th>Utilisateur</th>
                    <th>Moyen de Paiement</th>
                    <th>Payé</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['Montant_trans']) ?> €</td>
                            <td><?= htmlspecialchars(date('d M Y', strtotime($transaction['Date_trans']))) ?></td>
                            <td><?= htmlspecialchars($transaction['Qte_trans']) ?></td>
                            <td><?= htmlspecialchars($transaction['Nom_user'] . ' ' . $transaction['Prenom_user']) ?></td>
                            <td><?= htmlspecialchars($transaction['Nom_paie']) ?></td>
                            <td>
                                <!-- Cliquez sur 'Oui' ou 'Non' pour changer l'état -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="transaction_id" value="<?= htmlspecialchars($transaction['Id_trans']) ?>">
                                    <input type="hidden" name="payer" value="<?= $transaction['Payer_trans'] ? 0 : 1 ?>">
                                    <button type="submit" name="update_payment" class="pay-button <?= $transaction['Payer_trans'] ? 'oui' : 'non' ?>">
                                        <?= $transaction['Payer_trans'] ? 'Oui' : 'Non' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Aucune transaction trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
