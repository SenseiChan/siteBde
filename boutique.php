<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}



// Fonction pour vérifier si un utilisateur est administrateur
session_start();
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


// Message pour le formulaire d'ajout
$message = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $type = $_POST['type'];

    // Upload de l'image
    $targetDir = "imagesAdmin/";
    $targetFile = $targetDir . basename($_FILES["photo"]["name"]);
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO produit (Nom_prod, Prix_prod, Stock_prod, Photo_prod, Type_prod) 
                VALUES (:name, :price, :stock, :photo, :type)
            ");
            $stmt->execute([
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'photo' => basename($_FILES["photo"]["name"]),
                'type' => $type
            ]);
            // Retourner un message de succès
            echo "<p style='color:green;'>Produit ajouté avec succès.</p>";
        } catch (PDOException $e) {
            // Retourner un message d'erreur
            echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Erreur lors de l'upload de l'image.</p>";
    }
    exit; // Terminer le script PHP après la réponse AJAX
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique ADIL</title>
    <link rel="stylesheet" href="stylecss/styleBoutique.css">
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <?php
// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifiez ici si toutes les données sont présentes et valides
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $type = $_POST['type'];
    $photo = $_FILES['photo']; // Gestion de l'upload de photo

    // Simulez l'ajout du produit (vous devez ici traiter l'ajout à la base de données et l'upload de la photo)
    // Par exemple :
    // Si l'ajout est réussi, afficher un message de succès
    if ($name && $price && $stock && $type && $photo) {
        // Traitez l'ajout ici (vérifiez si tout est valide et effectuez l'upload de l'image)
        // Exemple : $result = ajouterProduit($name, $price, $stock, $type, $photo);

        // Si le traitement est réussi, afficher un message de succès
        $message = "Produit ajouté avec succès !";
    } else {
        // Si une erreur survient, affichez un message d'erreur
        $message = "Erreur dans l'ajout du produit.";
    }
}
?>


<!-- Zone de message (affichée après la soumission) -->
<?php if ($message): ?>
    <div id="message">
        <?php echo $message; ?>
    </div>
<?php endif; ?>
    <script>
        // Gestion des événements pour le pop-up
        const modal = document.getElementById("myModal");
        const openBtn = document.getElementById("openModal");
        const closeBtn = document.getElementById("closeModal");

        // Afficher la fenêtre modale
        openBtn.onclick = () => {
            modal.style.display = "flex";
        };

        // Fermer la fenêtre modale
        closeBtn.onclick = () => {
            modal.style.display = "none";
        };

        // Fermer la fenêtre en cliquant à l'extérieur
        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>
    <!-- Grades Section -->
    <section id="noBlurSection" class="grades" style="padding: 80px 0px;">
        <h2>Grades</h2>
        <div style="width: 100%; height: 100%; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

        <!-- Logo Admin -->
        <br><br><br>
        <?php if ($is_admin && isset($_GET['gestion_site']) && $_GET['gestion_site'] == 'true'): ?>
            <div class="admin-logo">
                <a href="boutique.php?gestion_site=true" id="editModeButton">
                    <img src="image/pensilIconModifChiffre.png" alt="Logo Admin" class="admin-logo-img">
                </a>
            </div>
        <?php endif; ?>

        <div class="grades-container">
            <div class="grade-card grade-fer">
                <img src="image/lingotDeFer.png" alt="lingot de fer" width=80px>
                <h3>Fer</h3>
                <p>Fais vivre le BDE</p>
                <span class="price">5€</span>
            </div>
            <div class="grade-card grade-diamant">
                <img src="image/mineraiDiamant.png" alt="minerai de diamant" width=90px >
                <h3>Diamant</h3>
                <p>Adhésion au BDE</p>
                <p>Le grade premium sur serveur Minecraft de l'ADIL</p>
                <span class="price">13€</span>
            </div>
            <div class="grade-card grade-or">
                <img src="image/lingotDOr.png" alt="lingot d'or" width=80px>
                <h3>Or</h3>
                <p>Adhésion au BDE</p>
                <p>Grade premium sur le serveur Minecraft</p>
                <span class="price">10€</span>
            </div>
        </div>
    </section>

    <!-- Consommables Section -->
    <section class="consommables">
        <h2>Consommables</h2>
        <div style="width: 100%; height: 0px; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

       

<!-- Pop-Up (fenêtre modale) -->
<div id="myModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" id="closeModal">&times;</span>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="product_id">
            <input type="text" name="name" id="name" placeholder="Nom du produit" required>
            <input type="number" name="price" id="price" placeholder="Prix du produit" required>
            <input type="number" name="stock" id="stock" placeholder="Stock du produit" required>
            <input type="text" name="type" id="type" placeholder="Type du produit" required pattern="^(boisson|snack|autres)$" title="Veuillez entrer 'boisson', 'snack' ou 'autres'.">
            <input type="file" name="photo" id="photo" required>
            <button type="submit">Sauvegarder</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des événements pour le pop-up
        const modal = document.getElementById("myModal");
        const openBtn = document.getElementById("openModal");
        const closeBtn = document.getElementById("closeModal");

        // Afficher la fenêtre modale
        openBtn.onclick = function() {
            modal.style.display = "flex";
        };

        // Fermer la fenêtre modale
        closeBtn.onclick = function() {
            modal.style.display = "none";
        };

        // Fermer la fenêtre en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    });
</script>

        <!-- Section Boissons -->
        <div class="sub-section" style="padding: 30px 0px;">
            <h3>Boissons :</h3>
            <div style="width: 100%; height: 0px; border: 3px #AC6CFF solid; border-radius: 15px;"></div>
            
            <!-- Logo Admin -->
            <?php if ($is_admin && isset($_GET['gestion_site']) && $_GET['gestion_site'] == 'true'): ?>
            <div class="admin-logo">
                <img src="image/pensilIconModifChiffre.png" alt="Logo Admin" class="admin-logo-img">
            </div>
            <?php endif; ?>

            <div class="product-container">
                <?php
                    try {
                        $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'boisson'");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $imageUrl = "imagesAdmin/". $row['Photo_prod'];
                            echo "
                            <div class='product'>
                                <img src='{$imageUrl}' alt='{$row['Nom_prod']}' class='frame'>
                                <p>
                                    <span class='name'>{$row['Nom_prod']}</span><br><br>
                                    Prix : {$row['Prix_prod']}€<br><br>
                                    En stock : {$row['Stock_prod']}<br><br>
                                </p>
                            </div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
                    }
                ?>
            </div>
        </div>

        <!-- Section Snacks -->
        <div class="sub-section">
            <h3>Snacks :</h3>
            <div style="width: 100%; height: 0px; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

            <!-- Logo Admin -->
            <?php if ($is_admin && isset($_GET['gestion_site']) && $_GET['gestion_site'] == 'true'): ?>
            <div class="admin-logo">
                <img src="image/pensilIconModifChiffre.png" alt="Logo Admin" class="admin-logo-img">
            </div>
            <?php endif; ?>
            
            <div class="product-container">
                
                <?php
                try {
                    $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'snack'");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $imageUrl = "imagesAdmin/" . $row['Photo_prod'];

                        echo "
                        <div class='product'>
                            <img src='{$imageUrl}' alt='{$row['Nom_prod']}' class='frame'>
                            <p>
                                <span class='name'>{$row['Nom_prod']}</span><br><br>
                                Prix : {$row['Prix_prod']}€<br><br>
                                En stock : {$row['Stock_prod']}
                            </p>
                        </div>";
                    }
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>

        <!-- Section Autres -->
        <div class="sub-section">
            <h3>Autres :</h3>
            <div style="width: 100%; height: 0px; border: 3px #AC6CFF solid; border-radius: 15px;"></div>

            <!-- Logo Admin -->
            <?php if ($is_admin && isset($_GET['gestion_site']) && $_GET['gestion_site'] == 'true'): ?>
            <div class="admin-logo">
                <img src="image/pensilIconModifChiffre.png" alt="Logo Admin" class="admin-logo-img">
            </div>
            <?php endif; ?>

            <div class="product-container">
                <?php
                try {
                    $stmt = $pdo->query("SELECT Nom_prod, Photo_prod, Prix_prod, Stock_prod FROM produit WHERE Type_prod = 'autres'");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $imageUrl = "imagesAdmin/" . $row['Photo_prod'];

                        echo "
                        <div class='product'>
                            <img src='{$imageUrl}' alt='{$row['Nom_prod']}' class='frame'>
                            <p>
                                <span class='name'>{$row['Nom_prod']}</span><br><br>
                                Prix : {$row['Prix_prod']}€<br><br>
                                En stock : {$row['Stock_prod']}
                            </p>
                        </div>";
                    }
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
         <!-- Bouton Ajouter un produit, déplacé après la liste des produits -->
    <?php if ($is_admin): ?>
        <button id="openModal" class="ajouter-produit-btn">
            <h5>Ajouter un produit</h5>
        </button>
    <?php endif; ?>
    </section>
</main>


<script src="js/scriptBoutique.js"></script>

</body>
</html>

