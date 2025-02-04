<?php
$host = 'localhost';
$dbname = 'inf2pj_03';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

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

    if (!empty($prenom) && !empty($nom) && !empty($email) && !empty($promo) && !empty($mdp) && !empty($rue) && !empty($ville) && !empty($codePostal) && !empty($telephone)) {
        
        $mdp_hache = password_hash($mdp, PASSWORD_DEFAULT);

        if ($promo === 'NULL') {
            $promo = NULL; // NULL sera inséré dans la base de données
        }

        // Démarrage d'une transaction
        mysqli_begin_transaction($conn);

        try {
            $sql_adr = "INSERT INTO adresse (NomNumero_rue, Code_postal, Ville) 
                        VALUES ('$rue', '$codePostal', '$ville')";
            
            if (!mysqli_query($conn, $sql_adr)) {
                throw new Exception("Erreur lors de l'insertion dans Adresse : " . mysqli_error($conn));
            }
            $id_adr = mysqli_insert_id($conn);
            $sql_user = "INSERT INTO utilisateur 
                        (Nom_user, Prenom_user, Mdp_user, Date_crea_user, Dern_connexion, Tel_user, Email_user, Photo_user, Id_role, Id_adr, Annee_promo) 
                        VALUES ('$nom', '$prenom', '$mdp_hache', NOW(), NOW(), '$telephone', '$email', 'image/default.png', 1, $id_adr, '$promo')";
            
            if (!mysqli_query($conn, $sql_user)) {
                throw new Exception("Erreur lors de l'insertion dans Utilisateur : " . mysqli_error($conn));
            }

            $Id_user = mysqli_insert_id($conn);

            // Ajout dans la table "decrocher"
            if ($promo == '1') {
                $sql_promo = "INSERT INTO decrocher (Id_user, Id_badge, Afficher_badge) VALUES ($Id_user, 1, 2)";    
                if (!mysqli_query($conn, $sql_promo)) {
                    throw new Exception("Erreur lors de l'insertion dans Utilisateur : " . mysqli_error($conn));
                }
            }
            elseif ($promo == '2') {
                $sql_promo = "INSERT INTO decrocher (Id_user, Id_badge, Afficher_badge) VALUES ($Id_user, 2, 2)";    
                if (!mysqli_query($conn, $sql_promo)) {
                    throw new Exception("Erreur lors de l'insertion dans Utilisateur : " . mysqli_error($conn));
                }
            }
            elseif ($promo == '3') {
                $sql_promo = "INSERT INTO decrocher (Id_user, Id_badge, Afficher_badge) VALUES ($Id_user, 3, 2)";    
                if (!mysqli_query($conn, $sql_promo)) {
                    throw new Exception("Erreur lors de l'insertion dans Utilisateur : " . mysqli_error($conn));
                }
            }

            mysqli_commit($conn);
            header("Location: connexion.html");
            echo "Inscription réussie !";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo $e->getMessage();
        }

    } else {
        echo "Veuillez remplir tous les champs.";
    }
}

mysqli_close($conn);
?>
