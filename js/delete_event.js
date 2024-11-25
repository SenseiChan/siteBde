document.addEventListener('DOMContentLoaded', () => {
    const deleteEventButton = document.getElementById('delete-event-btn');
    const participantsTable = document.getElementById('participants-table');
    const popup = document.getElementById('popup');

    const showPopup = (message, success = true) => {
        popup.textContent = message;
        popup.className = `popup ${success ? 'success' : 'error'}`;
        popup.classList.add('show');
        setTimeout(() => popup.classList.remove('show'), 3000);
    };

    const updateRemoveButtons = () => {
        const removeUserButtons = document.querySelectorAll('.remove-user-btn');

        removeUserButtons.forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.dataset.userId;

                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'remove_user', user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Met à jour le tableau
                        participantsTable.innerHTML = data.participants.map(participant => `
                            <tr data-user-id="${participant.Id_user}">
                                <td>${participant.Nom_user}</td>
                                <td>${participant.Prenom_user}</td>
                                <td>
                                    <button class="action-btn remove-user-btn" data-user-id="${participant.Id_user}">Retirer</button>
                                </td>
                            </tr>
                        `).join('');

                        // Réactive les boutons après la mise à jour
                        updateRemoveButtons();

                        if (data.participants.length === 0) {
                            deleteEventButton.disabled = false; // Active le bouton supprimer
                        }

                        showPopup('Utilisateur désinscrit avec succès !', true);
                    } else {
                        showPopup(data.message || 'Erreur lors de la désinscription.', false);
                    }
                })
                .catch(() => showPopup('Une erreur est survenue.', false));
            });
        });
    };

    // Initialise les boutons "Retirer" au chargement
    updateRemoveButtons();

    deleteEventButton.addEventListener('click', () => {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_event' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPopup(data.message, true);
                setTimeout(() => window.location.href = 'events.php', 1000);
            } else {
                showPopup(data.message || 'Erreur lors de la suppression.', false);
            }
        })
        .catch(() => showPopup('Une erreur est survenue.', false));
    });
});
