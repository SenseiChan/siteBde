<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['userId'])) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant.']);
    exit();
}

$userId = intval($data['userId']);

$host = 'localhost';
$dbname = 'inf2pj_03';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $pdo->prepare("SELECT Id_role FROM utilisateur WHERE Id_user = :userId");
    $query->execute(['userId' => $userId]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
        exit();
    }

    $newRole = $user['Id_role'] == 2 ? 1 : 2;

    // Update the role in the database
    $update = $pdo->prepare("UPDATE utilisateur SET Id_role = :newRole WHERE Id_user = :userId");
    $update->execute(['newRole' => $newRole, 'userId' => $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Rôle mis à jour avec succès.',
        'newRole' => $newRole,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>
