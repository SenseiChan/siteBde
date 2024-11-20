<?php
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['mdp'])) {
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['mdp']);

        // Requête pour récupérer l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE Email_user = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Mdp_user'])) {
            // Définir les informations de l'utilisateur dans la session
            $_SESSION['user_id'] = $user['Id_user'];
            $_SESSION['role_id'] = $user['Id_role']; // Utilisé pour vérifier si admin
            $_SESSION['email'] = $user['Email_user'];
            $_SESSION['nom_user'] = $user['Nom_user'];
            $_SESSION['prenom_user'] = $user['Prenom_user'];

            // Déterminer si l'utilisateur est admin
            $_SESSION['is_admin'] = ($user['Id_role'] == 2);

            // Mettre à jour la dernière connexion
            $updateStmt = $pdo->prepare("UPDATE utilisateur SET Dern_connexion = NOW() WHERE Id_user = :id");
            $updateStmt->execute(['id' => $user['Id_user']]);

            // Rediriger vers la page d'accueil
            header("Location: accueil.php");
            exit();
        } else {
            // Erreur d'authentification
            $error = "Adresse e-mail ou mot de passe incorrect.";
            header("Location: connexion.html?error=" . urlencode($error));
            exit();
        }
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}