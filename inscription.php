<?php
// Connexion à la base de données
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

$conn->set_charset("utf8mb4"); // Facultatif, pour gérer les caractères spéciaux
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connection successful<br>";
}


// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération des données du formulaire
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $promo = $_POST['promo'] ?? '';
    $mdp = $_POST['mdp'] ?? '';
    $rue = $_POST['rue'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $codePostal = $_POST['codePostal'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    // Hachage du mot de passe
    $mdp_hache = password_hash($mdp, PASSWORD_DEFAULT);

    try {
        // Insérer l'adresse dans la table Adresse
        $stmt_adr = $pdo->prepare("INSERT INTO Adresse (NomNumero_rue, Code_postal, Ville) VALUES (?, ?, ?)");
        $stmt_adr->execute([$rue, $codePostal, $ville]);
        $id_adr = $pdo->lastInsertId(); // Récupérer l'ID de l'adresse insérée

        // Insérer l'utilisateur dans la table Utilisateur
        $stmt_user = $pdo->prepare("INSERT INTO Utilisateur 
            (Nom_user, Prenom_user, Mdp_user, Date_crea_user, Dern_connexion, Tel_user, Email_user, Photo_user, Id_role, Id_adr) 
            VALUES (?, ?, ?, NOW(), NOW(), ?, ?, 'default.jpg', 1, ?)");
        $stmt_user->execute([$nom, $prenom, $mdp_hache, $telephone, $email, $id_adr]);

        echo "Inscription réussie !";
    } catch (PDOException $e) {
        echo "Erreur lors de l'inscription : " . $e->getMessage();
    }
}
?>
