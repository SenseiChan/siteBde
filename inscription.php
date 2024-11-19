<?php
$host = "localhost";
$dbname = "sae";
$username = "root";
$password = "";

$message = ''; // Variable pour stocker les messages de succès ou d'erreur

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $promo = $_POST['promo'] ?? '';
    $mdp = $_POST['mdp'] ?? '';
    $rue = $_POST['rue'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $codePostal = $_POST['codePostal'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    $mdp_hache = password_hash($mdp, PASSWORD_DEFAULT);

    try {
        // Insérer l'adresse dans la table Adresse
        $stmt_adr = $pdo->prepare("INSERT INTO Adresse (NomNumero_rue, Code_postal, Ville) VALUES (?, ?, ?)");
        $stmt_adr->execute([$rue, $codePostal, $ville]);
        $id_adr = $pdo->lastInsertId(); // Récupérer l'ID de l'adresse insérée

        // Insérer l'utilisateur dans la table Utilisateur
        $stmt_user = $pdo->prepare("INSERT INTO Utilisateur 
            (Nom_user, Prenom_user, Mdp_user, Date_crea_user, Dern_connexion, Tel_user, Email_user, Photo_user, Id_role, Id_adr, Annee_promo) 
            VALUES (?, ?, ?, NOW(), NOW(), ?, ?, 'default.jpg', 1, ?, ?)");
        $stmt_user->execute([$nom, $prenom, $mdp_hache, $telephone, $email, $id_adr, $promo]);

        $message = "Inscription réussie !";  // Message de succès
    } catch (PDOException $e) {
        $message = "Erreur lors de l'inscription : " . $e->getMessage();  // Message d'erreur
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body>

<!-- Affichage du message de succès ou d'erreur -->
<?php if ($message): ?>
    <div style="margin: 10px; padding: 10px; background-color: #f4f4f4; border: 1px solid #ccc; border-radius: 5px;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- Formulaire d'inscription -->
<form method="POST" action="">
    <label for="prenom">Prénom:</label><br>
    <input type="text" id="prenom" name="prenom" required><br><br>

    <label for="nom">Nom:</label><br>
    <input type="text" id="nom" name="nom" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>

    <label for="promo">Année de promotion:</label><br>
    <input type="number" id="promo" name="promo"><br><br>

    <label for="mdp">Mot de passe:</label><br>
    <input type="password" id="mdp" name="mdp" required><br><br>

    <label for="rue">Rue:</label><br>
    <input type="text" id="rue" name="rue" required><br><br>

    <label for="ville">Ville:</label><br>
    <input type="text" id="ville" name="ville" required><br><br>

    <label for="codePostal">Code postal:</label><br>
    <input type="text" id="codePostal" name="codePostal" required><br><br>

    <label for="telephone">Numéro de téléphone:</label><br>
    <input type="text" id="telephone" name="telephone" required><br><br>

    <input type="submit" value="S'inscrire">
</form>

</body>
</html>
