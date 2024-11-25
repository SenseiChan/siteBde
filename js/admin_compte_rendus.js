document.addEventListener('DOMContentLoaded', () => {
    const addFileButtons = document.querySelectorAll('.add-file-btn');
    const yearFolders = document.querySelectorAll('.year-folder');
    const addFileModal = document.getElementById('add-file-modal');
    const fileModal = document.getElementById('file-modal');
    const closeButtons = document.querySelectorAll('.close-modal');
    const addFileForm = document.getElementById('add-file-form');
    const fileList = document.getElementById('file-list');
    const modalTitle = document.getElementById('modal-title');

    // Ouvrir la modale pour ajouter un fichier
    addFileButtons.forEach(button => {
        button.addEventListener('click', () => {
            const type = button.dataset.type;
            document.getElementById('type_fichier').value = type;
            addFileModal.classList.remove('hidden');
        });
    });

    // Fermer les modales
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            addFileModal.classList.add('hidden');
            fileModal.classList.add('hidden');
        });
    });

    // Ouvrir la modale pour consulter les fichiers
    yearFolders.forEach(folder => {
        folder.addEventListener('click', async (event) => {
            event.preventDefault();

            const year = folder.dataset.year;
            const type = folder.dataset.type;

            // Requête AJAX pour récupérer les fichiers
            try {
                const response = await fetch(`?year=${year}&type=${type}`);
                const data = await response.json();

                if (data.success) {
                    // Mise à jour du titre de la modale
                    modalTitle.textContent = `Fichiers pour ${year}-${parseInt(year) + 1}`;

                    // Remplir la liste des fichiers
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

                    // Afficher la modale
                    fileModal.classList.remove('hidden');
                } else {
                    alert(data.message || 'Erreur lors de la récupération des fichiers.');
                }
            } catch (error) {
                console.error('Erreur lors de la récupération des fichiers:', error);
                alert('Une erreur est survenue lors de la récupération des fichiers.');
            }
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
                alert(data.message || 'Fichier ajouté avec succès.');
                location.reload(); // Recharger la page pour mettre à jour les dossiers
            } else {
                alert(data.message || 'Erreur lors de l’ajout du fichier.');
            }
        } catch (error) {
            console.error('Erreur lors de l’ajout du fichier:', error);
            alert('Une erreur est survenue lors de l’ajout du fichier.');
        }
    });
});
