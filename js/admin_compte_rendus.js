document.addEventListener('DOMContentLoaded', () => {
    const addFileButtons = document.querySelectorAll('.add-file-btn');
    const yearFolders = document.querySelectorAll('.year-folder');
    const addFileModal = document.getElementById('add-file-modal'); // Modale pour l'ajout de fichier
    const fileModal = document.getElementById('file-modal'); // Modale pour afficher les fichiers
    const closeButtons = document.querySelectorAll('.close-modal'); // Boutons de fermeture
    const addFileForm = document.getElementById('add-file-form'); // Formulaire d'ajout de fichier
    const fileList = document.getElementById('file-list'); // Liste des fichiers
    const modalTitle = document.getElementById('modal-title'); // Titre de la modale de fichiers

    // Vérifier que les éléments existent avant de les manipuler
    if (!addFileModal || !fileModal || !addFileForm) {
        console.error('Certains éléments nécessaires ne sont pas trouvés dans le DOM.');
        return;
    }

    // Fonction pour afficher les notifications
    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;

        container.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Ouvrir la modale pour ajouter un fichier
    addFileButtons.forEach(button => {
        button.addEventListener('click', () => {
            const type = button.dataset.type;
            if (addFileModal) {
                document.getElementById('type_fichier').value = type; // Assigner le type
                addFileModal.classList.remove('hidden');
            } else {
                console.error('addFileModal introuvable.');
            }
        });
    });

    // Fermer les modales
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (addFileModal) addFileModal.classList.add('hidden');
            if (fileModal) fileModal.classList.add('hidden');
        });
    });

    // Soumission du formulaire pour ajouter un fichier
    addFileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(addFileForm);

        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (data.success) {
                showNotification(data.message || 'Fichier ajouté avec succès.', 'success');
                setTimeout(() => location.reload(), 1000); // Recharger la page après un délai
            } else {
                showNotification(data.message || 'Erreur lors de l’ajout du fichier.', 'error');
            }
        } catch (error) {
            showNotification('Une erreur est survenue.', 'error');
        }
    });

    // Ouvrir la modale pour consulter les fichiers
    yearFolders.forEach(folder => {
        folder.addEventListener('click', async (event) => {
            event.preventDefault();

            const year = folder.dataset.year;
            const type = folder.dataset.type;

            try {
                const response = await fetch(`?year=${year}&type=${type}`);
                const data = await response.json();

                if (data.success) {
                    if (modalTitle) modalTitle.textContent = `Fichiers pour ${year}-${parseInt(year) + 1}`;
                    if (fileList) {
                        fileList.innerHTML = '';
                        if (data.files.length > 0) {
                            data.files.forEach(file => {
                                const listItem = document.createElement('li');
                                const link = document.createElement('a');
                                link.href = file.Url_fichier;
                                link.textContent = file.Url_fichier.split('/').pop();
                                link.target = '_blank';
                                listItem.appendChild(link);
                                fileList.appendChild(listItem);
                            });
                        } else {
                            fileList.innerHTML = '<li>Aucun fichier trouvé pour cette année.</li>';
                        }
                    }
                    fileModal.classList.remove('hidden');
                } else {
                    showNotification(data.message || 'Erreur lors de la récupération des fichiers.', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la récupération des fichiers.', 'error');
            }
        });
    });
});