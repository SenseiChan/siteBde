document.addEventListener("DOMContentLoaded", function () {
    const addReleveBtn = document.getElementById("add-releve");
    const addFileModal = document.getElementById("add-file-modal");
    const closeAddModal = document.querySelector(".close-add-modal");
    const addFileForm = document.getElementById("add-file-form");
    const fileModal = document.getElementById("file-modal");
    const closeFileModal = document.querySelector(".close-modal");
    const fileList = document.getElementById("file-list");

    // Affichage de la modale d'ajout
    addReleveBtn.addEventListener("click", () => {
        addFileModal.classList.remove("hidden");
    });

    // Fermeture de la modale d'ajout
    closeAddModal.addEventListener("click", () => {
        addFileModal.classList.add("hidden");
    });

    // Fermeture de la modale des fichiers
    closeFileModal.addEventListener("click", () => {
        fileModal.classList.add("hidden");
    });

    // Gestion du formulaire d'ajout
    addFileForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(addFileForm);

        try {
            const response = await fetch("banque.php", {
                method: "POST",
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                alert("Fichier ajouté avec succès !");
                location.reload(); // Recharge la page pour voir les nouveaux fichiers
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert("Erreur lors de l'ajout du fichier.");
        }
    });

    // Gestion de l'affichage des fichiers par année
    document.querySelectorAll(".year-folder").forEach((folder) => {
        folder.addEventListener("click", async (e) => {
            e.preventDefault();

            const year = folder.getAttribute("data-year");
            const type = folder.getAttribute("data-type");

            try {
                const response = await fetch(`banque.php?action=get_files&year=${year}&type=${type}`);
                const result = await response.json();

                if (Array.isArray(result) && result.length > 0) {
                    fileList.innerHTML = ""; // Vide la liste actuelle

                    result.forEach((file) => {
                        const listItem = document.createElement("li");
                        const link = document.createElement("a");
                        link.href = file.Url_fichier;
                        link.textContent = file.Url_fichier.split("/").pop(); // Affiche uniquement le nom du fichier
                        link.target = "_blank"; // Ouvre le fichier dans un nouvel onglet
                        listItem.appendChild(link);
                        fileList.appendChild(listItem);
                    });

                    const fileModalTitle = document.getElementById("file-modal-title");
                    fileModalTitle.textContent = `Fichiers pour ${year}`;
                    fileModal.classList.remove("hidden");
                } else {
                    alert("Aucun fichier trouvé pour cette année.");
                }
            } catch (error) {
                console.error("Erreur:", error);
                alert("Erreur lors de la récupération des fichiers.");
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const addReleveBtn = document.getElementById("add-releve");
    const addFileModal = document.getElementById("add-file-modal");
    const closeAddModal = document.querySelector(".close-add-modal");
    const addFileForm = document.getElementById("add-file-form");

    // Afficher la modale d'ajout
    addReleveBtn.addEventListener("click", () => {
        addFileModal.classList.remove("hidden");
    });

    // Fermer la modale d'ajout
    closeAddModal.addEventListener("click", () => {
        addFileModal.classList.add("hidden");
    });

    // Gestion du formulaire d'ajout
    addFileForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(addFileForm);

        try {
            const response = await fetch("banque.php", {
                method: "POST",
                body: formData,
            });

            // Pas besoin de traiter de JSON, car PHP redirige directement
            if (response.ok) {
                alert("Traitement en cours...");
            }
        } catch (error) {
            alert("Erreur lors de l'ajout du fichier.");
        }
    });
});