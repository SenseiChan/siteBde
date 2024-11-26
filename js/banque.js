document.addEventListener('DOMContentLoaded', function () {
    // Afficher les fichiers pour une année spécifique
    document.querySelectorAll('.year-folder').forEach(folder => {
        folder.addEventListener('click', function (e) {
            e.preventDefault();
            const year = this.dataset.year;
            const type = this.dataset.type;

            fetch(`banque.php?action=get_files&year=${year}&type=${type}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const fileList = document.getElementById('file-list');
                        fileList.innerHTML = ''; // Réinitialise la liste
                        data.files.forEach(file => {
                            const li = document.createElement('li');
                            li.innerHTML = `<a href="${file.Url_fichier}" target="_blank">${file.Url_fichier}</a>`;
                            fileList.appendChild(li);
                        });
                        document.getElementById('file-modal-title').textContent = `Fichiers pour ${year}`;
                        document.getElementById('file-modal').classList.remove('hidden');
                    } else {
                        alert(data.message || 'Aucun fichier trouvé.');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la récupération des fichiers.');
                });
        });
    });

    // Ouvrir la modale pour ajouter un fichier
    document.getElementById('add-releve').addEventListener('click', function () {
        document.getElementById('add-file-modal').classList.remove('hidden');
    });

    // Fermer les modales
    document.querySelectorAll('.close-modal, .close-add-modal').forEach(closeBtn => {
        closeBtn.addEventListener('click', function () {
            this.closest('.modal').classList.add('hidden');
        });
    });

    // Gestion de l'ajout d'un fichier
    document.getElementById('add-file-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('banque.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout du fichier.');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout du fichier.');
            });
    });
});

function openFileModal(files, yearTitle) {
    const fileModal = document.getElementById("file-modal");
    const fileList = document.getElementById("file-list");
    const modalTitle = document.getElementById("file-modal-title");

    // Mise à jour du titre de la modale
    modalTitle.textContent = `Fichiers pour ${yearTitle}`;

    // Efface le contenu précédent
    fileList.innerHTML = "";

    // Ajout des fichiers au format stylisé
    if (files.length > 0) {
        files.forEach(file => {
            const fileItem = document.createElement("a");
            fileItem.href = file.Url_fichier;
            fileItem.target = "_blank";
            fileItem.className = "file-item";
            fileItem.innerHTML = `
                <img src="image/iconFile.png" alt="Fichier">
                <span>${file.Url_fichier.split("/").pop()}</span>
            `;
            fileList.appendChild(fileItem);
        });
    } else {
        fileList.innerHTML = "<p>Aucun fichier disponible.</p>";
    }

    // Affiche la modale
    fileModal.classList.remove("hidden");
}
