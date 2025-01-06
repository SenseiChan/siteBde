<?php
session_start();

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=inf2pj_03;charset=utf8', 'inf2pj03', 'eMaht4aepa');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Vérification du rôle de l'utilisateur
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$userId) {
    header('Location: connexion.html');
    exit();
}

$roleQuery = $pdo->prepare('SELECT Id_role FROM Utilisateur WHERE Id_user = :userId');
$roleQuery->execute(['userId' => $userId]);
$userRole = $roleQuery->fetch(PDO::FETCH_ASSOC);

if (!$userRole || $userRole['Id_role'] != 2) {
    die('Accès refusé. Seuls les administrateurs peuvent ajouter un événement.');
}

// Gestion du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['Nom_event'] ?? '';
    $description = $_POST['Desc_event'] ?? '';
    $date = $_POST['Date_deb_event'] ?? '';
    $time = $_POST['Heure_deb_event'] ?? '';
    $price = $_POST['Prix_event'] ?? '';
    $address = $_POST['NomNumero_rue'] ?? '';
    $postalCode = $_POST['Code_postal'] ?? '';
    $city = $_POST['Ville'] ?? '';
    $imagePath = 'image/evenementPres.png'; // Image par défaut

    // Validation des données côté serveur
    if (!preg_match('/^\d{5}$/', $postalCode)) {
        die("Le code postal doit contenir exactement 5 chiffres.");
    }

    if (!preg_match('/^[A-Za-zÀ-ÿ\s\-]+$/', $city)) {
        die("La ville doit uniquement contenir des lettres.");
    }

    // Gestion de l'upload d'image
    if (!empty($_FILES['Photo_event']['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($_FILES['Photo_event']['name'], PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            $targetDir = "imagesAdmin/";
            $targetFile = $targetDir . uniqid() . '.' . $fileExtension;

            if (move_uploaded_file($_FILES['Photo_event']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            } else {
                echo "Erreur lors du téléchargement de l'image.";
            }
        } else {
            echo "Format d'image non valide. Seuls les formats JPG et PNG sont autorisés.";
        }
    }

    // Vérifier si l'adresse existe déjà
    $checkAddressQuery = "
        SELECT Id_adr
        FROM Adresse
        WHERE NomNumero_rue = :address AND Code_postal = :postalCode AND Ville = :city
    ";
    $checkStmt = $pdo->prepare($checkAddressQuery);
    $checkStmt->execute([
        'address' => $address,
        'postalCode' => $postalCode,
        'city' => $city,
    ]);
    $addressData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    // Si l'adresse n'existe pas, la créer
    if (!$addressData) {
        $insertAddressQuery = "
            INSERT INTO Adresse (NomNumero_rue, Code_postal, Ville)
            VALUES (:address, :postalCode, :city)
        ";
        $insertStmt = $pdo->prepare($insertAddressQuery);
        $insertStmt->execute([
            'address' => $address,
            'postalCode' => $postalCode,
            'city' => $city,
        ]);
        $addressId = $pdo->lastInsertId();
    } else {
        $addressId = $addressData['Id_adr'];
    }

    // Insérer l'événement
    $insertEventQuery = "
        INSERT INTO Evenement (Nom_event, Desc_event, Date_deb_event, Heure_deb_event, Prix_event, Photo_event, Id_adr)
        VALUES (:name, :description, :date, :time, :price, :image, :addressId)
    ";
    $insertStmt = $pdo->prepare($insertEventQuery);
    $insertStmt->execute([
        'name' => $name,
        'description' => $description,
        'date' => $date,
        'time' => $time,
        'price' => $price,
        'image' => $imagePath,
        'addressId' => $addressId,
    ]);

    header('Location: events.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un événement</title>
    <link rel="stylesheet" href="stylecss/add_event.css">
</head>
<body>
    <div class="add-event-container">
        <h1>Ajouter un événement</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-content">
                <div class="image-container">
                    <label for="Photo_event" class="image-label">
                        <img src="image/evenementPres.png" alt="Image par défaut" id="event-image">
                    </label>
                    <input type="file" name="Photo_event" id="Photo_event" accept=".jpg, .jpeg, .png" hidden>
                </div>
                <div class="form-details">
                    <div class="form-group">
                        <label for="Nom_event">Nom :</label>
                        <input type="text" name="Nom_event" id="Nom_event" required>
                    </div>
                    <div class="form-group">
                        <label for="Date_deb_event">Date :</label>
                        <input type="date" name="Date_deb_event" id="Date_deb_event" required>
                    </div>
                    <div class="form-group">
                        <label for="Heure_deb_event">Heure :</label>
                        <input type="time" name="Heure_deb_event" id="Heure_deb_event" required>
                    </div>
                    <div class="form-group">
                        <label for="NomNumero_rue">Adresse :</label>
                        <input type="text" name="NomNumero_rue" id="NomNumero_rue" required>
                    </div>
                    <div class="form-group">
                        <label for="Code_postal">Code Postal :</label>
                        <input type="text" name="Code_postal" id="Code_postal" pattern="^\d{5}$" title="Le code postal doit contenir exactement 5 chiffres." required>
                    </div>
                    <div class="form-group">
                        <label for="Ville">Ville :</label>
                        <input type="text" name="Ville" id="Ville" pattern="^[A-Za-zÀ-ÿ\s\-]+$" title="La ville doit uniquement contenir des lettres." required>
                    </div>
                    <div class="form-group">
                        <label for="Prix_event">Prix (€) :</label>
                        <input type="number" name="Prix_event" id="Prix_event" required>
                    </div>
                    <div class="form-group">
                        <label for="Desc_event">Description :</label>
                        <textarea name="Desc_event" id="Desc_event" required></textarea>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="save-btn">Ajouter</button>
                <a href="events.php" class="cancel-btn">Annuler</a>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('Photo_event').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('event-image').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
