<?php
header('Content-Type: application/json');
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'inf2pj_03';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifiez que la requête est bien en POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des données envoyées
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $defaultImage = 'image/partyIconStat.png'; // Chemin de l'image par défaut

        // Vérifiez si un fichier image a été téléchargé
        if (!empty($_FILES['new-image']['name'])) {
            $imageTmp = $_FILES['new-image']['tmp_name'];
            $imageName = basename($_FILES['new-image']['name']);
            $uploadDir = 'imagesAdmin/';
            $imagePath = $uploadDir . uniqid() . '-' . $imageName;

            if (!move_uploaded_file($imageTmp, $imagePath)) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement de l\'image.']);
                exit;
            }
        } else {
            // Utilisez l'image par défaut si aucune image n'a été téléchargée
            $imagePath = $defaultImage;
        }

        // Validation des données
        if (empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Description et utilisateur sont requis.']);
            exit;
        }

        // Données supplémentaires
        $typeId = 2; // Exemple : type de contenu fixe
        $date = date('Y-m-d H:i:s');

        // Insertion dans la base de données
        $stmt = $pdo->prepare("
            INSERT INTO contenu (Desc_contenu, Date_contenu, Photo_contenu, Id_user,Id_type_contenu) 
            VALUES (:description, :date, :image, :user_id, :type_id)
        ");
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type_id', $typeId);
        $stmt->execute();

        // Réponse JSON
        // Réponse JSON pour succès
        echo json_encode([
            'success' => true,
            'message' => 'Nouvelle statistique ajoutée avec succès.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    }
} catch (PDOException $e) {
    // Gestion des erreurs
    echo json_encode(['success' => false, 'message' => 'Erreur du serveur : ' . $e->getMessage()]);
}