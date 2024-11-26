document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById("myModal");
    const openBtn = document.getElementById("openModal");
    const closeBtn = document.getElementById("closeModal");

    // Afficher la fenêtre modale
    openBtn.onclick = function() {
        modal.style.display = "flex";
    };

    // Fermer la fenêtre modale
    closeBtn.onclick = function() {
        modal.style.display = "none";
    };

    // Fermer la fenêtre en cliquant à l'extérieur
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
});