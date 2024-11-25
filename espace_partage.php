<?php
session_start(); // Démarrage de la session pour vérifier les droits d'accès

// Vérification si l'utilisateur est admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: accueil.php'); // Redirection si l'utilisateur n'est pas admin
    exit();
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
    <?php include 'header.php'; // Inclure le header ?>

    <main>
        <div class="admin-page-container">
            <div class="compte-rendus">
                <!-- Section "Compte rendus de réunion" -->
                <div class="compte-rendus-section">
                    <h2>Compte rendus de réunion</h2>
                    <div class="years">
                        <?php
                        // Générer dynamiquement les années
                        for ($year = 2024; $year >= 2017; $year--) {
                            $nextYear = $year + 1;
                            echo "
                                <a href='compte_rendus.php?type=reunion&year={$year}-{$nextYear}' class='year-folder'>
                                    <img src='image/iconFile.png' alt='Dossier' class='folder-icon'>
                                    <span>{$year}-{$nextYear}</span>
                                </a>
                            ";
                        }
                        ?>
                    </div>
                </div>

                <!-- Divider -->
                <div class="divider"></div>

                <!-- Section "Compte rendus des événements" -->
                <div class="compte-rendus-section">
                    <h2>Compte rendus des événements</h2>
                    <div class="years">
                        <?php
                        // Générer dynamiquement les années pour les événements
                        for ($year = 2024; $year >= 2017; $year--) {
                            $nextYear = $year + 1;
                            echo "
                                <a href='compte_rendus.php?type=evenement&year={$year}-{$nextYear}' class='year-folder'>
                                    <img src='image/iconFile.png' alt='Dossier' class='folder-icon'>
                                    <span>{$year}-{$nextYear}</span>
                                </a>
                            ";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; // Inclure le footer ?>
</body>
</html>
