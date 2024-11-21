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

