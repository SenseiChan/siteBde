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
