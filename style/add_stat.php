<?php
header('Content-Type: application/json');
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new-image']) && isset($_POST['new-desc'])) {
        $desc = htmlspecialchars($_POST['new-desc']);
        $image = $_FILES['new-image'];
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($image["name"]);
        move_uploaded_file($image["tmp_name"], $targetFile);

        $stmt = $pdo->prepare("INSERT INTO contenu (Photo_contenu, Desc_contenu) VALUES (:image, :desc)");
        $stmt->bindParam(':image', $targetFile);
        $stmt->bindParam(':desc', $desc);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>
