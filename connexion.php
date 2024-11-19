<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = ""; // Remplacez par votre mot de passe MySQL
$dbname = "sae"; // Remplacez par le nom de votre base de données

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifiez si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer les valeurs du formulaire
    $email = htmlspecialchars($_POST['email']);
    $mdp = htmlspecialchars($_POST['mdp']);

    // Vérifiez que les champs ne sont pas vides
    if (empty($email) || empty($mdp)) {
        echo "Veuillez remplir tous les champs.";
    } else {
        // Préparer la requête pour trouver l'utilisateur
        $sql = "SELECT Mdp_user, Prenom_user, Nom_user, Id_user FROM utilisateur WHERE Email_user = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Erreur lors de la préparation de la requête : " . $conn->error);
        }

        // Lier les paramètres
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Obtenir le résultat
        $result = $stmt->get_result();

        // Vérifiez si un utilisateur a été trouvé
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $storedMdp = $user['Mdp_user']; // Récupérer le mot de passe stocké dans la base de données

            // Afficher le mot de passe haché
            echo "Mot de passe haché trouvé dans la base de données : " . htmlspecialchars($storedMdp) . "<br>";

            // Vérifiez le mot de passe
            if (password_verify($mdp, $storedMdp)) {
                echo "Connexion réussie. Bienvenue, " . htmlspecialchars($user['Prenom_user']) . " " . htmlspecialchars($user['Nom_user']) . " !";

                // Démarrer une session et rediriger vers une page protégée (par exemple, un tableau de bord)
                session_start();
                $_SESSION['user_id'] = $user['Id_user'];
                $_SESSION['user_name'] = $user['Prenom_user'] . " " . $user['Nom_user'];

                // Redirige l'utilisateur vers une page sécurisée après la connexion réussie
                header("Location: dashboard.php"); // Remplacez par la page de votre choix
                exit();
            } else {
                echo "Mot de passe incorrect.";
            }
        } else {
            echo "Aucun utilisateur trouvé avec cet email.";
        }

        // Fermer la requête
        $stmt->close();
    }
}

// Fermer la connexion
$conn->close();
?>
