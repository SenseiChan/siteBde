<?php
session_start();

// Vérification si l'utilisateur est admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit();
}

// Gestion de l'upload du fichier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $dateFichier = $_POST['date_fichier'];
    $file = $_FILES['file'];

    // Validation des extensions
    $allowedExtensions = ['xlsx']; // Extension Excel uniquement
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Format de fichier non valide.']);
        exit();
    }

    // Génération du nom du fichier
    $formattedDate = date('d-F-Y', strtotime($dateFichier));
    $fileName = "Releve-Compte-{$formattedDate}.{$extension}";
    $filePath = "docsAdmin/" . $fileName;

    // Déplacement du fichier
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=inf2pj_03;charset=utf8', 'inf2pj03', 'eMaht4aepa');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "INSERT INTO fichier (Date_fichier, Url_fichier, Id_user, Id_type_fichier)
                      VALUES (:date_fichier, :url_fichier, :id_user, 1)"; // Forcer Id_type_fichier = 1
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'date_fichier' => $dateFichier,
                'url_fichier' => $filePath,
                'id_user' => $_SESSION['user_id'],
            ]);

            echo json_encode(['success' => true, 'message' => 'Fichier ajouté avec succès !']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l’ajout en base de données.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement.']);
    }
    exit();
}

// Gestion AJAX pour récupérer les fichiers d'une année
if (isset($_GET['year'])) {
    $year = intval($_GET['year']);

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=inf2pj_03;charset=utf8', 'inf2pj03', 'eMaht4aepa');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "
            SELECT * 
            FROM fichier 
            WHERE YEAR(Date_fichier) = :year AND Id_type_fichier = 1
            ORDER BY Date_fichier DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['year' => $year]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'files' => $files]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des fichiers.']);
    }
    exit();
}

// Fonction pour récupérer les années disponibles
function getYears($pdo) {
    $query = "SELECT DISTINCT YEAR(Date_fichier) AS year FROM fichier WHERE Id_type_fichier = 1 ORDER BY year DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=inf2pj_03;charset=utf8', 'inf2pj03', 'eMaht4aepa');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des années
    $years = getYears($pdo);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion banque</title>
    <link rel="stylesheet" href="stylecss/banque.css">
</head>
<body>
    <div class="page-container">
        <?php include 'header.php'; ?>
        <main class="content">
            <div class="admin-page-container">
                <!-- Section "Relevé de compte" -->
                <div class="compte-rendus-section">
                    <h2>📂 Relevé de compte</h2>
                    <div class="add-file-container">
                        <button class="add-file-btn" data-type="1">Ajouter un relevé</button>
                    </div>
                    <div class="years">
                        <?php foreach ($years as $year): ?>
                            <a href="#" class="year-folder" data-year="<?= $year['year'] ?>">
                                <img src="image/iconFile.png" alt="Dossier">
                                <span><?= $year['year'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="compte-rendus-section">
                    <h2>💰 Caisse du BDE</h2>
                    <div class="caisse-container">
                        <p id="bde-amount">Montant actuel : <strong>3000 €</strong></p>
                    </div>
                </div>

            </div>
        </main>

        <!-- Modale pour afficher les fichiers -->
        <div id="file-modal" class="modal hidden">
            <div class="modal-content">
                <img src="image/icon_close.png" alt="Fermer" class="close-modal">
                <h3 id="modal-title">Fichiers pour l'année sélectionnée</h3>
                <ul id="file-list">
                    <!-- Les fichiers seront ajoutés ici dynamiquement -->
                </ul>
            </div>
        </div>

        <!-- Modale pour l'ajout de fichier -->
        <div id="add-file-modal" class="modal hidden">
            <div class="modal-content">
                <img src="image/icon_close.png" alt="Fermer" class="close-modal">
                <h3>Ajouter un relevé</h3>
                <form id="add-file-form" enctype="multipart/form-data">
                    <label for="date_fichier">Date :</label>
                    <input type="date" id="date_fichier" name="date_fichier" required>
                    <label for="file">Fichier (XLSX uniquement) :</label>
                    <input type="file" id="file" name="file" accept=".xlsx" required>
                    <button type="submit" class="submit-btn">Ajouter</button>
                </form>
            </div>
        </div>
        <div id="notification-container"></div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="js/banque.js"></script>
</body>
</html>
