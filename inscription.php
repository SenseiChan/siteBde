<?php
$host = "localhost";
$dbname = "sae3";
$username = "root";
$password = "";

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

        echo "Inscription réussie !";
    } catch (PDOException $e) {
        echo "Erreur lors de l'inscription : " . $e->getMessage();
    }
}
?>
