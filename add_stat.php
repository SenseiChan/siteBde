<?php
header('Content-Type: application/json');
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la requête est POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new-image']) && isset($_POST['description'])) {
        $desc = htmlspecialchars($_POST['description']); // Échapper la description
        $userId = $_SESSION['user_id']; // ID de l'utilisateur connecté
        $typeId = 2; // ID du type de contenu
        $date = date('Y-m-d H:i:s'); // Date du jour au format SQL

        // Vérifier que le fichier a été uploadé correctement
        if ($_FILES['new-image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['new-image'];
            $imageName = basename($image['name']);
            $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION)); // Extension du fichier

            // Vérifier que le fichier est un PNG ou un JPG
            $allowedExtensions = ['png', 'jpg', 'jpeg'];
            if (!in_array($imageExtension, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Format de fichier invalide. Seuls les PNG et JPG sont acceptés.']);
                exit;
            }

            // Générer un nom unique pour éviter les conflits
            $uniqueImageName = uniqid('image_', true) . '.' . $imageExtension;

            // Définir le dossier cible pour les uploads
            $targetDir = __DIR__ . '/imagesAdmin/';
            $targetFile = $targetDir . $uniqueImageName;

            // Vérifier si le dossier existe, sinon le créer
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Déplacer le fichier uploadé dans le dossier cible
            if (move_uploaded_file($image['tmp_name'], $targetFile)) {
                // Insérer dans la base de données avec les champs requis
                $imagePath = "imagesAdmin/" . $uniqueImageName; // Chemin à enregistrer dans la base
                $stmt = $pdo->prepare("
                    INSERT INTO Contenu (Date_contenu, Desc_contenu, Photo_contenu, Id_user, Id_type_contenu) 
                    VALUES (:date, :desc, :image, :user_id, :type_id)
                ");
                $stmt->bindParam(':date', $date);
                $stmt->bindParam(':desc', $desc);
                $stmt->bindParam(':image', $imagePath);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':type_id', $typeId);
                $stmt->execute();

                echo json_encode(['success' => true, 'message' => 'Image ajoutée avec succès !']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement de l\'image.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload de l\'image.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données invalides ou requête incorrecte.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur générale : ' . $e->getMessage()]);
}
