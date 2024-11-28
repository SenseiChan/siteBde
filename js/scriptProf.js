function changeMonth(direction) {
    const urlParams = new URLSearchParams(window.location.search);
    let year = parseInt(urlParams.get('year') || new Date().getFullYear());
    let month = parseInt(urlParams.get('month') || new Date().getMonth() + 1);

    month += direction;

    if (month < 1) {
        month = 12;
        year--;
    } else if (month > 12) {
        month = 1;
        year++;
    }

    window.location.search = `?month=${month}&year=${year}`;
}
document.addEventListener('DOMContentLoaded', () => {
    const calendar = document.querySelector('.calendar-grid');

    function updateCalendar(month, year) {
        fetch(`profil.php?month=${month}&year=${year}`)
            .then(response => response.text())
            .then(data => {
                calendar.innerHTML = data; // Remplace le contenu du calendrier
            })
            .catch(error => console.error('Erreur lors de la mise à jour du calendrier:', error));
    }

    document.querySelector('.prev-month').addEventListener('click', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const month = parseInt(urlParams.get('month') || new Date().getMonth() + 1) - 1;
        const year = parseInt(urlParams.get('year') || new Date().getFullYear());
        updateCalendar(month < 1 ? 12 : month, month < 1 ? year - 1 : year);
    });

    document.querySelector('.next-month').addEventListener('click', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const month = parseInt(urlParams.get('month') || new Date().getMonth() + 1) + 1;
        const year = parseInt(urlParams.get('year') || new Date().getFullYear());
        updateCalendar(month > 12 ? 1 : month, month > 12 ? year + 1 : year);
    });
});




document.addEventListener('DOMContentLoaded', () => {
    const editButton = document.querySelector('.edit-info-btn');
    const modal = document.querySelector('.modal');
    const closeModal = document.querySelector('.close-modal');
    const saveButton = document.querySelector('.save-info-btn');
    const blurElements = document.querySelectorAll('.blur-target'); // Select elements to blur

    // Add blur effect and show modal
    editButton.addEventListener('click', () => {
        blurElements.forEach(element => element.classList.add('blur')); // Add blur to specific elements
        modal.classList.remove('hidden'); // Show the modal
    });

    // Close modal and remove blur
    closeModal.addEventListener('click', () => {
        blurElements.forEach(element => element.classList.remove('blur')); // Remove blur from specific elements
        modal.classList.add('hidden'); // Hide the modal
    });

    // Save information and close modal
    saveButton.addEventListener('click', () => {
        const tel = document.getElementById('tel').value;
        const email = document.getElementById('email').value;
        const numNomRue = document.getElementById('numNomRue').value;
        const ville = document.getElementById('ville').value;
        const codePostal = document.getElementById('codePostal').value;

        // Send data to the server via AJAX
        fetch('update_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tel: tel,
                email: email,
                numNomRue: numNomRue,
                ville: ville,
                codePostal: codePostal,
            }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Informations mises à jour avec succès');
                } else {
                    alert('Erreur lors de la mise à jour : ' + data.message);
                }
                blurElements.forEach(element => element.classList.remove('blur'));
                modal.classList.add('hidden');
            })
            .catch(error => {
                console.error('Erreur :', error);
                alert('Erreur lors de la mise à jour.');
            });
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const historyButton = document.querySelector('.view-history');
    const historyModal = document.querySelector('.history-modal');
    const closeHistoryModal = document.querySelector('.close-history-modal');
    const historyContent = document.querySelector('.history-content');
    const transactionSearch = document.querySelector('#transaction-search');
    let currentPage = 0; // Track the current page of transactions
    const blurElements = document.querySelectorAll('.blur-target'); // Select elements to blur

    const fetchTransactions = (page, searchQuery = '') => {
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id') || ''; // Ensure it defaults to logged-in user only if absent
    
        fetch(`fetch_transactions.php?page=${page}&query=${searchQuery}&user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const transactions = data.transactions;
    
                    if (page === 0) historyContent.innerHTML = ''; // Clear previous content for new searches
    
                    transactions.forEach(transaction => {
                        const transactionElement = document.createElement('div');
                        transactionElement.classList.add('transaction-item');
                        transactionElement.innerHTML = `
                            <span>${transaction.description}</span>
                            <span>${new Date(transaction.date).toLocaleDateString()}</span>
                            <span>${transaction.amount}€</span>
                        `;
                        historyContent.appendChild(transactionElement);
                    });
    
                    if (transactions.length === 0 && page === 0) {
                        historyContent.innerHTML = '<p>Aucun résultat trouvé.</p>';
                    }
                } else {
                    alert(data.message || 'Erreur lors de la récupération des transactions.');
                }
            })
            .catch(error => console.error('Erreur :', error));
    };    
    

    historyButton.addEventListener('click', () => {
        blurElements.forEach(element => element.classList.add('blur')); // Add blur to specific elements
        historyModal.classList.remove('hidden'); // Show the modal

        // Fetch the first page of transactions
        currentPage = 0;
        fetchTransactions(currentPage);
    });

    closeHistoryModal.addEventListener('click', () => {
        historyModal.classList.add('hidden'); // Show the modal
        blurElements.forEach(element => element.classList.remove('blur')); // Add blur to specific elements
    });

    // Handle transaction search
    transactionSearch.addEventListener('input', (e) => {
        const searchQuery = e.target.value.trim();
        currentPage = 0; // Reset to the first page
        fetchTransactions(currentPage, searchQuery); // Fetch transactions based on search query
    });

    // Swipe functionality to load more transactions
    historyModal.addEventListener('wheel', (e) => {
        if (e.deltaY > 0) { // Scroll down
            currentPage++;
            fetchTransactions(currentPage, transactionSearch.value.trim());
        }
    });
});



document.addEventListener('DOMContentLoaded', () => {
    const badgeButton = document.querySelector('.view-badges'); // Add this class to the "Badges" button
    const badgeModal = document.querySelector('.badge-modal');
    const closeBadgeModal = document.querySelector('.close-badge-modal');
    const blurElements = document.querySelectorAll('.blur-target'); // Elements to blur

    badgeButton.addEventListener('click', () => {
        blurElements.forEach(element => element.classList.add('blur'));
        badgeModal.classList.remove('hidden');
    });

    closeBadgeModal.addEventListener('click', () => {
        blurElements.forEach(element => element.classList.remove('blur'));
        badgeModal.classList.add('hidden');
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const toggleRoleButton = document.querySelector('#toggle-role-btn');

    if (toggleRoleButton) {
        toggleRoleButton.addEventListener('click', () => {
            const userId = toggleRoleButton.dataset.userId;
            console.log("ok")

            // Send an AJAX request to toggle the user's role
            fetch('toggle_role.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ userId }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the button text
                        toggleRoleButton.textContent = data.newRole === 2 
                            ? 'Rétrograder en Membre' 
                            : 'Promouvoir en Admin';
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || 'Une erreur est survenue.');
                    }
                })
                .catch(error => console.error('Erreur:', error));
        });
    }
});


document.addEventListener('DOMContentLoaded', () => {
    const addEventButton = document.querySelector('.add-event-btn');
    const modal = document.querySelector('.modal-add-event');
    const closeModal = document.querySelector('.close-modal-event');
    const form = document.getElementById('add-event-form');

    // Ouvrir la modalité
    addEventButton.addEventListener('click', () => {
        modal.classList.remove('hidden');
    });

    // Fermer la modalité
    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Soumettre le formulaire
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        fetch('add_calandar.php', {
            method: 'POST',
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert('Événement ajouté avec succès');
                    modal.classList.add('hidden');
                    location.reload(); // Recharge la page pour afficher l'événement dans le calendrier
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch((error) => console.error('Erreur:', error));
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const profilePicInput = document.getElementById('profile-pic-input');
    const profilePicForm = document.getElementById('profile-pic-form');
    const profilePicPreview = document.getElementById('profile-pic-preview');

    profilePicInput.addEventListener('change', () => {
        if (profilePicInput.files && profilePicInput.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                profilePicPreview.src = e.target.result; // Prévisualiser la nouvelle image
            };
            reader.readAsDataURL(profilePicInput.files[0]);

            // Envoyer automatiquement le formulaire
            profilePicForm.submit();
        }
    });
});
