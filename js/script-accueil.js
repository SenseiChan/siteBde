document.addEventListener('DOMContentLoaded', () => {
    const editButton = document.getElementById('edit-stats');
    const statsSection = document.getElementById('stats-section');
    const pageElements = document.querySelectorAll('body > *:not(#stats-section)');
    const addButton = document.getElementById('add-stat');

    // Créer le bouton "Revenir"
    const backButton = document.createElement('button');
    backButton.id = 'back-to-normal';
    backButton.textContent = 'Enregistrer';
    backButton.style.display = 'none'; // Masquer par défaut
    backButton.classList.add('admin-button-chiffre'); // Utiliser les styles du bouton admin
    statsSection.querySelector('.stats-header').appendChild(backButton);

    editButton.addEventListener('click', () => {
        // Ajouter l'effet de flou sur tout sauf la section
        addButton.classList.remove('hidden');
        pageElements.forEach(element => element.classList.add('blur-effect'));
        statsSection.classList.add('highlight');

        // Ajouter les boutons de suppression
        document.querySelectorAll('.stat-item').forEach(item => {
            const deleteButton = document.createElement('button');
            deleteButton.classList.add('delete-icon');
            deleteButton.setAttribute('data-id', item.id.replace('stat-', ''));
            deleteButton.innerHTML = '<img src="image/bin.png" alt="Supprimer">';
            item.appendChild(deleteButton);

            // Ajouter l'événement de suppression
            deleteButton.addEventListener('click', function () {
                const statId = this.getAttribute('data-id');
                const statElement = document.getElementById(`stat-${statId}`);
                statElement.remove(); // Suppression visuelle

                // Suppression dans la base de données
                fetch('delete_stat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: statId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Erreur lors de la suppression !');
                    }
                })
                .catch(error => console.error('Erreur:', error));
            });
        });

        // Masquer le bouton "Modifier" et afficher "Enregistrer"
        editButton.style.display = 'none';
        backButton.style.display = 'inline-block';
    });

    backButton.addEventListener('click', () => {
        // Retirer l'effet de flou
        pageElements.forEach(element => element.classList.remove('blur-effect'));
        statsSection.classList.remove('highlight');

        // Supprimer les boutons de suppression
        document.querySelectorAll('.delete-icon').forEach(button => {
            button.remove();
        });

        // Réafficher le bouton "Modifier" et masquer "Enregistrer"
        editButton.style.display = 'inline-block';
        backButton.style.display = 'none';
        addButton.classList.add('hidden'); // Affiche le bouton "+"
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const addButton = document.getElementById('add-stat');
    const modal = document.getElementById('add-modal');
    const closeModal = document.getElementById('delete-modal');
    const saveModal = document.getElementById('save-modal');
    const modalImage = document.getElementById('modal-image');
    const imageInput = document.getElementById('image-input');

    // Ouvrir la modale
    addButton.addEventListener('click', () => {
        modal.classList.remove('hidden');
        document.body.classList.add('blur');
    });

    // Fermer la modale
    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
        document.body.classList.remove('blur');
    });

    // Sauvegarde des données via le bouton "tick"
    saveModal.addEventListener('click', () => {
        const description = document.getElementById('modal-description').value;

        // Préparation des données à envoyer
        const formData = new FormData();
        formData.append('description', description);

        // Ajout de l'image si elle a été modifiée
        if (imageInput.files.length > 0) {
            formData.append('new-image', imageInput.files[0]);
        }

        // Ajout de l'ID utilisateur
        formData.append('user-id', userId);

        // Requête AJAX pour sauvegarder les données
        fetch('add_stat.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ajouté avec succès !');
                location.reload(); // Recharge la page pour afficher le nouveau bloc
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => console.error('Erreur :', error));
    });

    // Ouvrir le sélecteur de fichiers en cliquant sur l'image
    modalImage.addEventListener('click', () => {
        imageInput.click(); // Déclenche le sélecteur de fichiers
    });

    // Met à jour l'image affichée lorsque l'utilisateur sélectionne un fichier
    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                modalImage.src = e.target.result; // Affiche l'image sélectionnée dans la modale
            };
            reader.readAsDataURL(file); // Charge l'image pour affichage
        }
    });
});
