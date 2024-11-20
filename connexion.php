<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "sae"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars($_POST['email']);
    $mdp = htmlspecialchars($_POST['mdp']);

    if (empty($email) || empty($mdp)) {
        echo "Veuillez remplir tous les champs.";
    } else {
        $email = $conn->real_escape_string($email);

        $sql = "SELECT Mdp_user, Prenom_user, Nom_user, Id_user FROM utilisateur WHERE Email_user = '$email'";
        $result = $conn->query($sql);

        if ($result === false) {
            die("Erreur lors de l'exécution de la requête : " . $conn->error);
        }

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $storedMdp = $user['Mdp_user'];

            if (password_verify($mdp, $storedMdp)) {
                echo "Connexion réussie. Bienvenue, " . htmlspecialchars($user['Prenom_user']) . " " . htmlspecialchars($user['Nom_user']) . " !";

                session_start();
                $_SESSION['user_id'] = $user['Id_user'];
                $_SESSION['user_name'] = $user['Prenom_user'] . " " . $user['Nom_user'];
                header("Location: profil.html");
                exit();
            } else {
                echo "Mot de passe incorrect.";
            }
        } else {
            echo "Aucun utilisateur trouvé avec cet email.";
        }
    }
}

$conn->close();
?>
