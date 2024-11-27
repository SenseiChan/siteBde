document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.show-participants-btn');

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.dataset.eventId;
            const participantsList = document.getElementById(`participants-list-${eventId}`);

            if (participantsList.style.display === 'none' || !participantsList.style.display) {
                // Affiche la liste des participants
                fetchParticipants(eventId, participantsList);
                participantsList.style.display = 'block';
            } else {
                // Cache la liste des participants
                participantsList.style.display = 'none';
            }
        });
    });
});

// Fonction pour récupérer les participants via AJAX
function fetchParticipants(eventId, container) {
    fetch(`events.php?eventId=${eventId}`)  // Utilise le même fichier PHP pour récupérer les participants
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = 'Aucun participant trouvé pour cet événement.';
                return;
            }

            let html = '<table><thead><tr><th>Nom</th><th>Prénom</th></tr></thead><tbody>';
            data.forEach(participant => {
                html += `<tr><td>${participant.Nom_user}</td><td>${participant.Prenom_user}</td>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des participants:', error);
            container.innerHTML = 'Erreur de récupération des participants';
        });
}

