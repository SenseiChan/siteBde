<?php
session_start(); // Démarrage de la session pour vérifier les droits d'accès

// Vérification si l'utilisateur est admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: accueil.php'); // Redirection si l'utilisateur n'est pas admin
    exit();
}

// Gestion du tri
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc'; // Valeur par défaut : décroissant
$nextOrder = $order === 'asc' ? 'desc' : 'asc'; // Alternance entre croissant et décroissant

// Gestion de l'API AJAX pour récupérer les fichiers
if (isset($_GET['year']) && isset($_GET['type'])) {
    $yearRange = $_GET['year']; // Année au format "2024-2025"
    $type = $_GET['type']; // Type de fichier : 2 = réunion, 3 = événement

    // Extraction des bornes des années
    [$startYear, $endYear] = explode('-', $yearRange);

    try {
        // Connexion à la base de données
        $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupération des fichiers correspondant
        $query = "
            SELECT * 
            FROM Fichier 
            WHERE Id_type_fichier = :type 
            AND YEAR(Date_fichier) BETWEEN :startYear AND :endYear
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'type' => $type,
            'startYear' => $startYear,
            'endYear' => $endYear,
        ]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retour des données JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'files' => $files]);
        exit();
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . $e->getMessage()]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Rendus</title>
    <link rel="stylesheet" href="stylecss/admin_compte_rendus.css"> <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="page-container">
        <?php include 'header.php'; // Inclure le header ?>

        <main class="content">
            <div class="admin-page-container">
                <!-- Bouton de tri -->
                <div class="sort-container">
                    <a href="?order=<?= $nextOrder ?>" class="sort-button">
                        <img src="image/icon_tri.png" alt="Trier" class="sort-icon">
                        Trier par : <?= $order === 'asc' ? 'Z-A' : 'A-Z' ?>
                    </a>
                </div>

                <!-- Section "Compte rendus de réunion" -->
                <div class="compte-rendus-section">
                    <h2>📂 Compte rendus de réunion</h2>
                    <div class="years">
                        <?php
                        // Générer dynamiquement les années en fonction du tri
                        $years = [];
                        for ($year = 2024; $year >= 2017; $year--) {
                            $nextYear = $year + 1;
                            $years[] = "{$year}-{$nextYear}";
                        }

                        if ($order === 'asc') {
                            sort($years); // Tri croissant
                        } else {
                            rsort($years); // Tri décroissant
                        }

                        foreach ($years as $yearRange) {
                            echo "
                                <a href='#' class='year-folder' data-year='{$yearRange}' data-type='2'>
                                    <img src='image/iconFile.png' alt='Dossier' class='folder-icon'>
                                    <span>{$yearRange}</span>
                                </a>
                            ";
                        }
                        ?>
                    </div>
                </div>

                <!-- Section "Compte rendus des événements" -->
                <div class="compte-rendus-section">
                    <h2>📂 Compte rendus des événements</h2>
                    <div class="years">
                        <?php
                        foreach ($years as $yearRange) {
                            echo "
                                <a href='#' class='year-folder' data-year='{$yearRange}' data-type='3'>
                                    <img src='image/iconFile.png' alt='Dossier' class='folder-icon'>
                                    <span>{$yearRange}</span>
                                </a>
                            ";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modale -->
        <div id="file-modal" class="modal hidden">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h3 id="modal-title">Fichiers pour l'année sélectionnée</h3>
                <ul id="file-list">
                    <!-- Les fichiers seront ajoutés ici dynamiquement -->
                </ul>
            </div>
        </div>

        <?php include 'footer.php'; // Inclure le footer ?>
    </div>

    <!-- Script JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('file-modal');
            const closeModal = document.querySelector('.close-modal');
            const fileList = document.getElementById('file-list');
            const modalTitle = document.getElementById('modal-title');

            document.querySelectorAll('.year-folder').forEach(folder => {
                folder.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const year = folder.dataset.year;
                    const type = folder.dataset.type;

                    // Requête AJAX pour récupérer les fichiers
                    try {
                        const response = await fetch(`?year=${year}&type=${type}`);
                        const data = await response.json();

                        if (data.success) {
                            modalTitle.textContent = `Fichiers pour ${year}`;
                            fileList.innerHTML = '';

                            if (data.files.length > 0) {
                                data.files.forEach(file => {
                                    const listItem = document.createElement('li');
                                    listItem.innerHTML = `
                                        <a href="${file.Url_fichier}" target="_blank">${file.Url_fichier.split('/').pop()}</a>
                                    `;
                                    fileList.appendChild(listItem);
                                });
                            } else {
                                fileList.innerHTML = '<li>Aucun fichier disponible.</li>';
                            }

                            modal.classList.remove('hidden');
                        } else {
                            alert(data.message || 'Une erreur est survenue.');
                        }
                    } catch (error) {
                        alert('Erreur lors de la récupération des fichiers.');
                    }
                });
            });

            closeModal.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        });
    </script>
</body>
</html>
