<?php
session_start();

// Vérification si l'utilisateur est admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: accueil.php');
    exit();
}

// Gestion AJAX pour récupérer les fichiers par année et type
if (isset($_GET['action']) && $_GET['action'] === 'get_files') {
    if (isset($_GET['year']) && isset($_GET['type'])) {
        $year = intval($_GET['year']);
        $type = intval($_GET['type']);

        try {
            $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "
                SELECT Url_fichier 
                FROM Fichier 
                WHERE Id_type_fichier = :type 
                AND YEAR(Date_fichier) = :year
                ORDER BY Date_fichier DESC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['type' => $type, 'year' => $year]);

            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($files);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Erreur de connexion à la base de données.']);
        }
    } else {
        echo json_encode(['error' => 'Paramètres manquants.']);
    }
    exit();
}

// Gestion de l'ajout de fichier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $dateFichier = $_POST['date_fichier'];
    $file = $_FILES['file'];
    $typeFichier = 1; // Pour les relevés de compte

    // Validation des extensions de fichier
    $allowedExtensions = ['xlsx'];
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array($extension, $allowedExtensions)) {
        echo "<script>alert('Format de fichier non valide.'); window.location.href = 'banque.php';</script>";
        exit();
    }

    // Génération du nom du fichier et du chemin
    $formattedDate = date('d-F-Y', strtotime($dateFichier));
    $fileName = "Releve-Compte-{$formattedDate}.{$extension}";
    $filePath = "docsAdmin/" . $fileName;

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérification si le fichier existe déjà
        $queryCheck = "SELECT COUNT(*) FROM Fichier WHERE Url_fichier = :url_fichier";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->execute(['url_fichier' => $filePath]);
        $fileExists = $stmtCheck->fetchColumn();

        if ($fileExists > 0) {
            echo "<script>alert('Ce fichier existe déjà dans la base de données.'); window.location.href = 'banque.php';</script>";
            exit();
        }

        // Déplacement du fichier téléchargé
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $query = "INSERT INTO Fichier (Date_fichier, Url_fichier, Id_user, Id_type_fichier) 
                      VALUES (:date_fichier, :url_fichier, :id_user, :type_fichier)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'date_fichier' => $dateFichier,
                'url_fichier' => $filePath,
                'id_user' => $_SESSION['user_id'],
                'type_fichier' => $typeFichier,
            ]);

            echo "<script>alert('Fichier ajouté avec succès !'); window.location.href = 'banque.php';</script>";
        } else {
            echo "<script>alert('Erreur lors du téléchargement du fichier.'); window.location.href = 'banque.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de l\'ajout dans la base de données.'); window.location.href = 'banque.php';</script>";
    }
    exit();
}

// Récupération des années pour les dossiers
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function getYears($pdo, $type) {
        $query = "SELECT DISTINCT YEAR(Date_fichier) AS year FROM Fichier WHERE Id_type_fichier = :type ORDER BY year DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $yearsReleve = getYears($pdo, 1);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevé de compte</title>
    <link rel="stylesheet" href="stylecss/banque.css"> <!-- Le fichier de style -->
</head>
<body>
<div class="page-container">
    <?php include 'header.php'; ?>
    
    <main class="content">
        
        <!-- Section "Relevé de compte" -->
        <div class="compte-rendus-section">
            <h2>📂 Relevé de compte</h2>
            <div class="add-file-container">
                <button class="add-file-btn" id="add-releve">Ajouter un relevé de compte</button>
            </div>
            <div class="years">
                <?php if (!empty($yearsReleve)): ?>
                    <?php foreach ($yearsReleve as $year): ?>
                        <a href="#" class="year-folder" data-year="<?= $year['year'] ?>" data-type="1">
                            <img src="image/iconFile.png" alt="Dossier">
                            <span><?= $year['year'] ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun relevé disponible.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section "Caisse du BDE" -->
        <div class="compte-rendus-section">
            <h2>💰 Caisse du BDE</h2>
            <div class="finance-container">
                <div class="finance-item">
                    <h3>Compte en banque :</h3>
                    <p class="amount">300 000 €</p>
                </div>
                <div class="finance-item">
                    <h3>Compte en Paypal :</h3>
                    <p class="amount">300 000 €</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Modale pour les fichiers -->
    <div id="file-modal" class="modal hidden">
        <div class="modal-content">
            <img src="image/icon_close.png" alt="Fermer" class="close-modal">
            <h3 id="file-modal-title"></h3>
            <ul id="file-list"></ul>
        </div>
    </div>

    <!-- Modale pour ajouter un fichier -->
    <div id="add-file-modal" class="modal hidden">
        <div class="modal-content">
            <img src="image/icon_close.png" alt="Fermer" class="close-add-modal">
            <h3>Ajouter un relevé de compte</h3>
            <form id="add-file-form" method="post" enctype="multipart/form-data">
                <label for="date_fichier">Date :</label>
                <input type="date" id="date_fichier" name="date_fichier" required>
                <label for="file">Fichier (XLSX uniquement) :</label>
                <input type="file" id="file" name="file" accept=".xlsx" required>
                <button type="submit" class="submit-btn">Ajouter</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</div>
<script src="js/banque.js"></script>
</body>
</html>
