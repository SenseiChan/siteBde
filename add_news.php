<?php
header('Content-Type: application/json');
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifiez que la requête est bien en POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des données envoyées via FormData
        $titre = isset($_POST['titre']) ? trim($_POST['titre']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;

        // Récupération de l'ID utilisateur depuis la session
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Validation des données
        if (empty($titre) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Titre et description sont requis.']);
            exit;
        }
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié.']);
            exit;
        }

        // Données supplémentaires
        $typeId = 1; // Exemple : type de contenu fixe
        $date = date('Y-m-d H:i:s'); // Date du jour

        // Insertion dans la base de données
        $stmt = $pdo->prepare("
            INSERT INTO Contenu (Date_contenu, Desc_contenu, Titre_contenu, Id_user, Id_type_contenu) 
            VALUES (:date, :desc, :titre, :user_id, :type_id)
        ");
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type_id', $typeId);
        $stmt->execute();

        // Réponse JSON
        // Réponse JSON pour succès
        echo json_encode([
            'success' => true,
            'message' => 'Nouvelle actualité ajoutée avec succès.'
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    }
} catch (PDOException $e) {
    // Gestion des erreurs
    echo json_encode(['success' => false, 'message' => 'Erreur du serveur : ' . $e->getMessage()]);
}