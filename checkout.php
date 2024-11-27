<?php
session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: boutique.php');
    exit();
}

// Simulez une validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logique pour enregistrer la commande dans la base de données
    unset($_SESSION['cart']); // Vider le panier après validation
    header('Location: confirmation_checkout.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation</title>
</head>
<body>
<main>
    <h1>Confirmation d'achat</h1>
    <form method="POST">
        <p>Êtes-vous sûr de vouloir valider votre commande ?</p>
        <button type="submit">Valider</button>
        <a href="panier.php">Retour au panier</a>
    </form>
</main>
</body>
</html>
