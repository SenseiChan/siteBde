<?php
header('Content-Type: application/json');
session_start();

$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['title'], $input['description'])) {
            $title = htmlspecialchars($input['title']);
            $description = htmlspecialchars($input['description']);
            $userId = $_SESSION['user_id']; // ID de l'utilisateur connecté

            $stmt = $pdo->prepare("INSERT INTO news (title, description, user_id) VALUES (:title, :description, :user_id)");
            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'user_id' => $userId,
            ]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>
