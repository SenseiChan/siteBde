<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sae";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupération des statistiques
$sql = "SELECT Type_prod, COUNT(*) AS total, SUM(Prix_prod * Stock_prod) AS valeur_totale 
        FROM Produit 
        GROUP BY Type_prod";
$result = $conn->query($sql);

// Préparation des données pour le graphique
$labels = [];
$total = [];
$valeur_totale = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['Type_prod'];
        $total[] = $row['total'];
        $valeur_totale[] = $row['valeur_totale'];
    }
} else {
    // Gérer le cas où il n'y a pas de données dans la base de données
    echo "Aucune donnée trouvée.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Produits</title>
    <link rel="stylesheet" href="stylecss/styleStat.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">
        <h1>Statistiques des Produits</h1>
        
        <!-- Tableau des statistiques -->
        <table>
            <thead>
                <tr>
                    <th>Type de Produit</th>
                    <th>Total Produits</th>
                    <th>Valeur Totale (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['Type_prod']) . "</td>
                                <td>" . htmlspecialchars($row['total']) . "</td>
                                <td>" . number_format($row['valeur_totale'], 2, ',', ' ') . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Aucune donnée disponible</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Graphique -->
        <canvas id="statChart"></canvas>
        
        <!-- Bouton Exporter en CSV -->
        <button id="export">Exporter en CSV</button>
    </div>

    <script>
        // Données du graphique
        const data = {
            labels: <?php echo json_encode($labels); ?>, // Types de produits
            datasets: [{
                label: 'Total Produits',
                data: <?php echo json_encode($total); ?>, // Total des produits par type
                backgroundColor: '#AC6CFF', // Couleur des barres
                borderColor: '#9B55E3', // Couleur des bordures
                borderWidth: 1
            },{
                label: 'Valeur Totale (€)',
                data: <?php echo json_encode($valeur_totale); ?>, // Valeur totale des produits
                backgroundColor: '#46EACF', // Couleur des barres
                borderColor: '#229583', // Couleur des bordures
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

        // Exportation des données en CSV
        document.getElementById('export').addEventListener('click', function () {
            const table = document.querySelector('table');
            let csvContent = "data:text/csv;charset=utf-8,";

            // Extraire les en-têtes
            const headers = Array.from(table.querySelectorAll('thead th'))
                .map(th => th.textContent)
                .join(",");
            csvContent += headers + "\n";

            // Extraire les lignes du tableau
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const values = Array.from(row.querySelectorAll('td'))
                    .map(td => td.textContent)
                    .join(",");
                csvContent += values + "\n";
            });

            // Télécharger le fichier CSV
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "statistiques_produits.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
    <script src="js/scriptStat.js"></script>
    <?php include 'footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
