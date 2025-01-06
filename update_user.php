<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$userId = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['tel'], $input['email'], $input['numNomRue'], $input['ville'], $input['codePostal'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

$tel = htmlspecialchars($input['tel']);
$email = htmlspecialchars($input['email']);
$numNomRue = htmlspecialchars($input['numNomRue']);
$ville = htmlspecialchars($input['ville']);
$codePostal = htmlspecialchars($input['codePostal']);

$host = 'localhost';
$dbname = 'inf2pj_03';
$username = 'inf2pj03';
$password = 'eMaht4aepa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        UPDATE utilisateur u
        JOIN adresse a ON u.Id_adr = a.Id_adr
        SET u.Tel_user = :tel,
            u.Email_user = :email,
            a.NomNumero_rue = :numNomRue,
            a.Ville = :ville,
            a.Code_postal = :codePostal
        WHERE u.Id_user = :userId
    ");
    $stmt->execute([
        ':tel' => $tel,
        ':email' => $email,
        ':numNomRue' => $numNomRue,
        ':ville' => $ville,
        ':codePostal' => $codePostal,
        ':userId' => $userId,
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune donnée mise à jour']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
