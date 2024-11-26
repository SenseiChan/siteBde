const editButton = document.querySelector('#editModeButton');
const body = document.body;

// Ajoutez une classe spécifique pour les éléments que vous souhaitez exclure du flou
const excludedSections = document.querySelectorAll('.no-blur');

editButton.addEventListener('click', function (event) {
    event.preventDefault();

    // Vérifiez si le flou est déjà activé
    if (body.classList.contains('blur')) {
        body.classList.remove('blur');

        // Supprime également le flou sur les sections exclues
        excludedSections.forEach(section => {
            section.style.filter = 'none';
        });
    } else {
        body.classList.add('blur');

        // Assurez-vous que les sections exclues ne sont pas floutées
        excludedSections.forEach(section => {
            section.style.filter = 'none';
        });
    }
});
