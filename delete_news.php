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

    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['id'])) {
        $id = intval($input['id']);
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = :id");
        $stmt->execute(['id' => $id]);

        echo json_encode(['success' => $stmt->rowCount() > 0]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>
