<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sae;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Récupération des données de l'événement
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    die('ID de l\'événement non spécifié.');
}

$query = "
    SELECT e.Id_event, e.Nom_event, e.Desc_event, e.Date_deb_event, e.Heure_deb_event, e.Prix_event, e.Photo_event,
           a.NomNumero_rue, a.Code_postal, a.Ville, a.Id_adr
    FROM Evenement e
    LEFT JOIN Adresse a ON e.Id_adr = a.Id_adr
    WHERE e.Id_event = :id
";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $eventId]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die('Événement introuvable.');
}

// Gestion du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['Nom_event'] ?? '';
    $description = $_POST['Desc_event'] ?? '';
    $date = $_POST['Date_deb_event'] ?? '';
    $time = $_POST['Heure_deb_event'] ?? '';
    $price = $_POST['Prix_event'] ?? '';
    $address = $_POST['NomNumero_rue'] ?? '';
    $postalCode = $_POST['Code_postal'] ?? '';
    $city = $_POST['Ville'] ?? '';

    // Validation du champ "Prix" pour s'assurer qu'il ne contient que des chiffres
    if (!is_numeric($price) || $price < 0) {
        die('Le prix doit être un nombre positif.');
    }

    // Gestion de l'upload de l'image avec validation
    $imagePath = $event['Photo_event']; // Garde le chemin de l'image actuelle par défaut
    if (!empty($_FILES['Photo_event']['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($_FILES['Photo_event']['name'], PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            $targetDir = "imagesAdmin/";
            $targetFile = $targetDir . basename($_FILES['Photo_event']['name']);

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

    // Mise à jour des données de l'événement
    $updateQuery = "
        UPDATE Evenement
        SET Nom_event = :name, Desc_event = :description, Date_deb_event = :date, Heure_deb_event = :time,
            Prix_event = :price, Photo_event = :image, Id_adr = :addressId
        WHERE Id_event = :id
    ";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'date' => $date,
        'time' => $time,
        'price' => $price,
        'image' => $imagePath,
        'addressId' => $addressId,
        'id' => $eventId,
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
    <title>Modifier un événement</title>
    <link rel="stylesheet" href="stylecss/edit_event.css">
</head>
<body>
    <div class="edit-event-container">
        <h1><?= htmlspecialchars($event['Nom_event']) ?></h1>
        <div class="header-icons">
            <a href="delete_event.php?id=<?= htmlspecialchars($event['Id_event']) ?>" class="delete-icon">
                <img src="image/bin.png" alt="Supprimer">
            </a>
            <a href="events.php" class="close-btn">
                <img src="image/icon_close.png" alt="Fermer">
            </a>
        </div>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-content">
                <div class="image-container">
                    <label for="Photo_event" class="image-label">
                        <img src="<?= htmlspecialchars($event['Photo_event']) ?>" alt="Photo de l'événement" id="event-image">
                    </label>
                    <input type="file" name="Photo_event" id="Photo_event" accept=".jpg, .jpeg, .png" hidden>
                </div>
                <div class="form-details">
                    <div class="form-group">
                        <label for="Nom_event">Nom :</label>
                        <input type="text" name="Nom_event" id="Nom_event" value="<?= htmlspecialchars($event['Nom_event']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="Date_deb_event">Date :</label>
                        <input type="date" name="Date_deb_event" id="Date_deb_event" value="<?= htmlspecialchars($event['Date_deb_event']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="Heure_deb_event">Heure :</label>
                        <input type="time" name="Heure_deb_event" id="Heure_deb_event" value="<?= htmlspecialchars($event['Heure_deb_event']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="NomNumero_rue">Adresse :</label>
                        <input type="text" name="NomNumero_rue" id="NomNumero_rue" value="<?= htmlspecialchars($event['NomNumero_rue'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="Code_postal">Code Postal :</label>
                        <input type="text" name="Code_postal" id="Code_postal" value="<?= htmlspecialchars($event['Code_postal'] ?? '') ?>" pattern="^\d{5}$" title="Le code postal doit contenir exactement 5 chiffres." required>
                    </div>
                    <div class="form-group">
                        <label for="Ville">Ville :</label>
                        <input type="text" name="Ville" id="Ville" value="<?= htmlspecialchars($event['Ville'] ?? '') ?>" pattern="^[A-Za-zÀ-ÿ\s\-]+$" title="La ville doit uniquement contenir des lettres." required>
                    </div>
                    <div class="form-group">
                        <label for="Prix_event">Prix (€) :</label>
                        <input type="number" name="Prix_event" id="Prix_event" value="<?= htmlspecialchars($event['Prix_event']) ?>" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="Desc_event">Description :</label>
                        <textarea name="Desc_event" id="Desc_event"><?= htmlspecialchars($event['Desc_event']) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="save-btn">Sauvegarder</button>
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
