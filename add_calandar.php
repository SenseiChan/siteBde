<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$host = 'localhost';
$dbname = 'inf2pj_03';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user_id'];
    $eventName = $_POST['event-name'];
    $eventDate = $_POST['event-date'];
    $eventTime = $_POST['event-time'];
    $eventDesc = $_POST['event-desc'];

    $dateTime = $eventDate . ' ' . $eventTime;

    $stmt = $pdo->prepare("INSERT INTO calendrier (Nom_calend, DateHeure_calend, Desc_calend, Id_user) VALUES (:name, :datetime, :desc, :user_id)");
    $stmt->execute([
        ':name' => $eventName,
        ':datetime' => $dateTime,
        ':desc' => $eventDesc,
        ':user_id' => $userId,
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>
