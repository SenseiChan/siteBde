<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = ""; // Remplacez par votre mot de passe MySQL
$dbname = "sae3"; // Remplacez par le nom de votre base de données

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifiez si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $mdp = trim($_POST['mdp']);

    // Vérifiez que les champs ne sont pas vides
    if (empty($email) || empty($mdp)) {
        echo "Veuillez remplir tous les champs.";
    } else {
        // Préparer la requête pour trouver l'utilisateur
        $sql = "SELECT * FROM Utilisateurs WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Erreur lors de la préparation de la requête : " . $conn->error);
        }

        // Lier les paramètres
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Obtenir le résultat
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Vérifiez le mot de passe
            if (password_verify($mdp, $user['mdp'])) {
                echo "Connexion réussie. Bienvenue, " . htmlspecialchars($user['prenom']) . " " . htmlspecialchars($user['nom']) . " !";

                // Ici, vous pouvez rediriger vers une autre page ou démarrer une session utilisateur
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['prenom'] . " " . $user['nom'];
                header("Location: dashboard.php"); // Remplacez par votre page après connexion
                exit();
            } else {
                echo "Mot de passe incorrect.";
            }
        } else {
            echo "Aucun utilisateur trouvé avec cet email.";
        }

        $stmt->close();
    }
}

// Fermer la connexion
$conn->close();
?>
