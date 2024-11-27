<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sae";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
}

// Récupérer les 10 derniers événements avec leurs informations
$query_last_events = "SELECT E.Nom_event, E.Prix_event, COUNT(P.Id_user) AS nb_participants 
                      FROM Evenement E 
                      LEFT JOIN Participer P ON E.Id_event = P.Id_event
                      GROUP BY E.Id_event
                      ORDER BY E.Date_deb_event DESC 
                      LIMIT 10";

$result_last_events = $mysqli->query($query_last_events);

// Récupérer les données pour le graphique des événements (nom, prix, nombre de participants)
$event_labels = [];
$event_prices = [];
$event_participants = [];

if ($result_last_events->num_rows > 0) {
    while ($row = $result_last_events->fetch_assoc()) {
        $event_labels[] = $row['Nom_event'];
        $event_prices[] = $row['Prix_event'];
        $event_participants[] = $row['nb_participants'];
    }
} else {
    echo "Aucun événement trouvé.";
}

// Récupération du type de produit sélectionné
$type_prod = isset($_GET['type_prod']) ? $_GET['type_prod'] : '';

// Requête SQL pour récupérer les statistiques par type de produit
$sql = "SELECT Type_prod, Nom_prod, Stock_prod, Prix_prod FROM Produit";

// Si un type est sélectionné, filtrer par ce type
if (!empty($type_prod)) {
    $sql .= " WHERE Type_prod = '" . $mysqli->real_escape_string($type_prod) . "'";
}

// Exécution de la requête
$result = $mysqli->query($sql);

// Préparation des données pour le graphique
$labels = [];
$stock = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['Nom_prod'];  // Nom des produits
        $stock[] = $row['Stock_prod']; // Stock des produits
    }
} else {
    echo "Aucune donnée trouvée.";
}

// Requête SQL pour récupérer les utilisateurs, leur année de promotion et leur rôle
$sql_roles = "SELECT U.Annee_promo, R.Nom_role, COUNT(U.Id_user) AS nb_utilisateurs
              FROM Utilisateur U
              JOIN Role R ON U.Id_role = R.Id_role
              GROUP BY U.Annee_promo, R.Nom_role
              ORDER BY U.Annee_promo DESC";

$result_roles = $mysqli->query($sql_roles);

// Préparation des données pour le tableau
$roles_data = [];
if ($result_roles->num_rows > 0) {
    while ($row = $result_roles->fetch_assoc()) {
        $roles_data[$row['Annee_promo']][$row['Nom_role']] = $row['nb_utilisateurs'];
    }
} else {
    echo "Aucune donnée trouvée pour les rôles.";
}

// Calculer le nombre d'événements et le gain total depuis le début de l'année scolaire
$query_events_gain = "
    SELECT 
    COUNT(DISTINCT E.Id_event) AS total_events,
    SUM(E.Prix_event * IFNULL(P.nb_participants, 0)) AS total_gain
    FROM Evenement E
    LEFT JOIN (SELECT Id_event, COUNT(Id_user) AS nb_participants FROM Participer GROUP BY Id_event) P ON E.Id_event = P.Id_event
";


$result_events_gain = $mysqli->query($query_events_gain);

// Initialisation des variables
$total_events = 0;
$total_gain = 0;

if ($result_events_gain->num_rows > 0) {
    $row = $result_events_gain->fetch_assoc();
    $total_events = $row['total_events'];
    $total_gain = $row['total_gain'];
} else {
    echo "Aucun événement depuis le début de l'année scolaire.";
}

?>

<!DOCTYPE html>
<html lang="fr">
<?php include 'header.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Produits</title>
    <!-- Lien vers le fichier CSS externe -->
    <link rel="stylesheet" href="stylecss/styleStat.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">  
    <h1>Statistiques des Produits</h1>
    
    <!-- Sélectionner le type de produit -->
    <form method="GET" action="">
        <label for="type_prod">Choisir un type de produit :</label>
        <select name="type_prod" id="type_prod">
            <option value="">Tous les types</option>
            <option value="Boisson" <?php if ($type_prod == 'Boisson') echo 'selected'; ?>>Boisson</option>
            <option value="Snack" <?php if ($type_prod == 'Snack') echo 'selected'; ?>>Snack</option>
            <option value="Autres" <?php if ($type_prod == 'Autres') echo 'selected'; ?>>Autres</option>
        </select>
        <button type="submit">Filtrer</button>
    </form>
    
    <!-- Tableau des statistiques -->
    <table>
        <thead>
            <tr>
                <th>Nom Produit</th>
                <th>Stock</th>
                <th>Prix (€)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['Nom_prod']) . "</td>
                            <td>" . htmlspecialchars($row['Stock_prod']) . "</td>
                            <td>" . number_format($row['Prix_prod'], 2, ',', ' ') . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Aucune donnée disponible</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Graphique des stocks -->
    <canvas id="statChart"></canvas>
</div>

<script>
    // Données pour le graphique des stocks
    const data = {
        labels: <?php echo json_encode($labels); ?>, // Noms des produits
        datasets: [{
            label: 'Stock des Produits',
            data: <?php echo json_encode($stock); ?>, // Stocks des produits
            backgroundColor: '#AC6CFF', // Couleur des barres
            borderColor: '#9B55E3', // Couleur des bordures
            borderWidth: 1
        }]
    };

       // Configuration du graphique
       const config = {
        type: 'bar', // Type de graphique (barres)
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#FFFFFF' // Couleur des légendes
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#FFFFFF' // Couleur des étiquettes de l'axe X
                    }
                },
                y: {
                    ticks: {
                        color: '#FFFFFF' // Couleur des étiquettes de l'axe Y
                    }
                }
            }
        }
    };

    // Création du graphique
    const ctx = document.getElementById('statChart').getContext('2d');
    new Chart(ctx, config);
</script>

<div class="container">
    <h1>Statistiques des Événements</h1>

    <!-- Graphique des 10 derniers événements -->
    <canvas id="eventChart"></canvas>

    <!-- Tableau des événements -->
    <table>
        <thead>
            <tr>
                <th>Nom de l'Événement</th>
                <th>Prix (€)</th>
                <th>Participants</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_last_events->num_rows > 0) {
                while ($row = $result_last_events->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['Nom_event']) . "</td>
                            <td>" . number_format($row['Prix_event'], 2, ',', ' ') . "</td>
                            <td>" . $row['nb_participants'] . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Aucune donnée disponible</td></tr>";
            }
            ?>
        </tbody>
    </table>

</div>

<script>
// Données pour le graphique des 10 derniers événements
const eventData = {
    labels: <?php echo json_encode($event_labels); ?>, // Noms des événements
    datasets: [{
        label: 'Nombre de Participants',
        data: <?php echo json_encode($event_participants); ?>, // Nombre de participants
        backgroundColor: '#AC6CFF',
        borderColor: '#9B55E3',
        borderWidth: 1
    }]
};

// Configuration du graphique des événements
const eventConfig = {
    type: 'bar',
    data: eventData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: '#FFFFFF'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#FFFFFF'
                }
            },
            y: {
                ticks: {
                    color: '#FFFFFF'
                }
            }
        }
    }
};

// Création du graphique des événements
const ctx_event = document.getElementById('eventChart').getContext('2d');
new Chart(ctx_event, eventConfig);
</script>

<!-- Graphique du nombre d'événements et du gain total -->
<div class="container">
    <h2>Événements et Gain Total depuis le début de l'année scolaire</h2>
    <canvas id="eventGainChart"></canvas>
</div>

<script>
    // Données pour le graphique des événements et gain
    const eventGainData = {
        labels: ['Événements', 'Gain Total (€)'],
        datasets: [{
            label: 'Statistiques',
            data: [<?php echo $total_events; ?>, <?php echo $total_gain; ?>],
            backgroundColor: ['#AC6CFF', '#AC6CFF'], // Couleurs pour les barres (vert pour les événements, rouge pour le gain)
            borderColor: ['#6b16da50', '#6b16da50'], // Couleurs des bordures des barres
            borderWidth: 3
        }]
    };

    // Configuration du graphique
    const eventGainConfig = {
        type: 'bar', // Type de graphique (barres)
        data: eventGainData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#FFFFFF' // Couleur des légendes
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#FFFFFF' // Couleur des étiquettes de l'axe X
                    }
                },
                y: {
                    ticks: {
                        color: '#FFFFFF' // Couleur des étiquettes de l'axe Y
                    }
                }
            }
        }
    };

    // Création du graphique
    const ctx_event_gain = document.getElementById('eventGainChart').getContext('2d');
    new Chart(ctx_event_gain, eventGainConfig);
</script>


<!-- Trait violet de séparation -->
<hr style="border: 1px solid #9B55E3; margin-top: 40px;">

<!-- Tableau des utilisateurs par année et rôle -->
<h2>Nombre d'Utilisateurs par Année et Rôle</h2>
<table>
    <thead>
        <tr>
            <th>Année de Promotion</th>
            <th>Rôle</th>
            <th>Nombre d'Utilisateurs</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (!empty($roles_data)) {
        foreach ($roles_data as $annee_promo => $roles) {
            foreach ($roles as $role_name => $nb_users) {
                // Vérifie si l'année de promotion est NULL
                $annee_promo_display = (is_null($annee_promo) || $annee_promo === '') ? 'Non spécifié' : $annee_promo;

                echo "<tr>
                        <td>" . htmlspecialchars($annee_promo_display) . "</td>
                        <td>" . htmlspecialchars($role_name) . "</td>
                        <td>" . $nb_users . "</td>
                      </tr>";
            }
        }
    } else {
        echo "<tr><td colspan='3'>Aucune donnée disponible pour les rôles.</td></tr>";
    }
    ?>
    </tbody>
</table>

</div>

<script>
// Données pour le graphique des stocks
const data = {
    labels: <?php echo json_encode($labels); ?>, // Noms des produits
    datasets: [{
        label: 'Stock des Produits',
        data: <?php echo json_encode($stock); ?>, // Stocks des produits
        backgroundColor: '#AC6CFF', // Couleur des barres
        borderColor: '#9B55E3', // Couleur des bordures
        borderWidth: 1
    }]
};

// Configuration du graphique
const config = {
    type: 'bar', // Type de graphique (barres)
    data: data,
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: '#FFFFFF' // Couleur des légendes
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#FFFFFF' // Couleur des étiquettes de l'axe X
                }
            },
            y: {
                ticks: {
                    color: '#FFFFFF' // Couleur des étiquettes de l'axe Y
                }
            }
        }
    }
};

// Création du graphique
const ctx = document.getElementById('statChart').getContext('2d');
new Chart(ctx, config);
</script>

<?php include 'footer.php'; ?>
</body>
</html>


