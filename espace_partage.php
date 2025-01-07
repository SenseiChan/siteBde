<?php
session_start();

// Variable pour stocker les erreurs
$errors = [];

// Vérification si l'utilisateur est admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit();
}

// Gestion de l'upload du fichier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $typeFichier = $_POST['type_fichier'];
    $dateFichier = $_POST['date_fichier'];
    $file = $_FILES['file'];

    // Validation des extensions
    $allowedExtensions = ['docx', 'pdf'];
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array($extension, $allowedExtensions)) {
        $errors[] = 'Format de fichier non valide.';
    }

    // Génération du nom du fichier
    $formattedDate = date('d-F-Y', strtotime($dateFichier));
    $typeName = $typeFichier == 2 ? 'Reunion' : 'Evenement';
    $fileName = "Compte-Rendu-{$typeName}-{$formattedDate}.{$extension}";
    $filePath = "docsAdmin/" . $fileName;

    // Déplacement du fichier
    if (empty($errors) && !move_uploaded_file($file['tmp_name'], $filePath)) {
        $errors[] = 'Erreur lors du téléchargement.';
    }

    // Si pas d'erreur, on enregistre dans la base de données
    if (empty($errors)) {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=inf2pj_03;charset=utf8', 'inf2pj03', 'eMaht4aepa');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "INSERT INTO fichier (Date_fichier, Url_fichier, Id_user, Id_type_fichier)
                      VALUES (:date_fichier, :url_fichier, :id_user, :type_fichier)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'date_fichier' => $dateFichier,
                'url_fichier' => $filePath,
                'id_user' => $_SESSION['user_id'],
                'type_fichier' => $typeFichier,
            ]);

            $errors[] = 'Fichier ajouté avec succès !'; // Message de succès
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de l’ajout en base de données.';
        }
    }
    exit();
}

// Gestion AJAX pour récupérer les fichiers d'une année
if (isset($_GET['year']) && isset($_GET['type'])) {
    $year = intval($_GET['year']);
    $type = intval($_GET['type']);

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=inf2pj_03;charset=utf8', 'inf2pj03', 'eMaht4aepa');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "
            SELECT * 
            FROM fichier 
            WHERE YEAR(Date_fichier) = :year AND Id_type_fichier = :type
            ORDER BY Date_fichier DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['year' => $year, 'type' => $type]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'files' => $files]);
    } catch (PDOException $e) {
        $errors[] = 'Erreur lors de la récupération des fichiers.';
    }
    exit();
}

// Fonction pour récupérer les années disponibles
function getYears($pdo, $type) {
    $query = "SELECT DISTINCT YEAR(Date_fichier) AS year FROM fichier WHERE Id_type_fichier = :type ORDER BY year DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=inf2pj_03;charset=utf8', 'inf2pj03', 'eMaht4aepa');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des années
    $yearsReunion = getYears($pdo, 2);
    $yearsEvenement = getYears($pdo, 3);
} catch (PDOException $e) {
    $errors[] = 'Erreur de connexion : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Rendus</title>
    <link rel="stylesheet" href="stylecss/admin_compte_rendus.css">
</head>
<body>
    <div class="page-container">
        <?php include 'header.php'; ?>
        <main class="content">
            <div class="admin-page-container">
                <!-- Section "Compte rendus de réunion" -->
                <div class="compte-rendus-section">
                    <h2>📂 Compte rendus de réunion</h2>
                    <div class="add-file-container">
                        <button class="add-file-btn" data-type="2">Ajouter un compte rendu</button>
                    </div>
                    <div class="years">
                        <?php foreach ($yearsReunion as $year): ?>
                            <a href="#" class="year-folder" data-year="<?= $year['year'] ?>" data-type="2">
                                <img src="image/iconFile.png" alt="Dossier">
                                <span><?= $year['year'] . '-' . ($year['year'] + 1) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Section "Compte rendus des événements" -->
                <div class="compte-rendus-section">
                    <h2>📂 Compte rendus des événements</h2>
                    <div class="add-file-container">
                        <button class="add-file-btn" data-type="3">Ajouter un compte rendu</button>
                    </div>
                    <div class="years">
                        <?php foreach ($yearsEvenement as $year): ?>
                            <a href="#" class="year-folder" data-year="<?= $year['year'] ?>" data-type="3">
                                <img src="image/iconFile.png" alt="Dossier">
                                <span><?= $year['year'] . '-' . ($year['year'] + 1) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- Section d'affichage des erreurs ou messages -->
        <div id="notification-container">
            <?php if (!empty($errors)): ?>
                <div class="notification">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

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
                <h3>Ajouter un compte rendu</h3>
                <form id="add-file-form" enctype="multipart/form-data">
                    <label for="date_fichier">Date :</label>
                    <input type="date" id="date_fichier" name="date_fichier" required>
                    <label for="file">Fichier (DOCX ou PDF) :</label>
                    <input type="file" id="file" name="file" accept=".docx,.pdf" required>
                    <input type="hidden" id="type_fichier" name="type_fichier">
                    <button type="submit" class="submit-btn">Ajouter</button>
                </form>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="js/admin_compte_rendus.js"></script>
</body>
</html>
