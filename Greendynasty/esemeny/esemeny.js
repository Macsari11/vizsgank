document.addEventListener("DOMContentLoaded", function () {
    const calendar = document.getElementById("calendar").querySelector("tbody");
    const currentMonthYear = document.getElementById("currentMonthYear");
    const prevMonthBtn = document.getElementById("prevMonth");
    const nextMonthBtn = document.getElementById("nextMonth");

    let today = new Date();
    let currentYear = today.getFullYear();
    let currentMonth = today.getMonth();

    
    const games = [
        { date: new Date(2025, 2, 10), opponent: "Miami Heat", time: "19:30" }, 
        { date: new Date(2025, 2, 15), opponent: "Los Angeles Lakers", time: "20:00" }, 
        { date: new Date(2025, 2, 20), opponent: "Golden State Warriors", time: "18:30" }, 
    ];

    function updateCalendar(year, month) {
        calendar.innerHTML = "";
        let firstDay = new Date(year, month, 1).getDay();
        let lastDate = new Date(year, month + 1, 0).getDate();

        let monthNames = [
            "Január", "Február", "Március", "Április", "Május", "Június",
            "Július", "Augusztus", "Szeptember", "Október", "November", "December"
        ];

        currentMonthYear.textContent = `${monthNames[month]} ${year}`;

        let row = document.createElement("tr");
        let dayOffset = firstDay === 0 ? 6 : firstDay - 1;

        for (let i = 0; i < dayOffset; i++) {
            row.appendChild(document.createElement("td"));
        }

        for (let day = 1; day <= lastDate; day++) {
            let cell = document.createElement("td");
            cell.textContent = day;

            if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                cell.classList.add("today");
            }

            
            const gameDate = new Date(year, month, day);
            const game = games.find(g => g.date.toDateString() === gameDate.toDateString());
            if (game) {
                cell.classList.add("game-day");
                cell.dataset.game = JSON.stringify(game);
                cell.addEventListener("click", showGamePopup);
            }

            row.appendChild(cell);

            if ((day + dayOffset) % 7 === 0) {
                calendar.appendChild(row);
                row = document.createElement("tr");
            }
        }

        if (row.childNodes.length > 0) {
            calendar.appendChild(row);
        }
    }

    function showGamePopup(event) {
        const game = JSON.parse(event.target.dataset.game);
        const popup = document.createElement("div");
        popup.className = "popup";
        popup.innerHTML = `
            <div class="popup-content">
                <h3>Mérkőzés: Boston Celtics vs. ${game.opponent}</h3>
                <p>Dátum: ${game.date.toLocaleDateString("hu-HU")}</p>
                <p>Idő: ${game.time}</p>
                <button onclick="this.parentElement.parentElement.remove()">Bezár</button>
            </div>
        `;
        document.body.appendChild(popup);
    }

    prevMonthBtn.addEventListener("click", function () {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar(currentYear, currentMonth);
    });

    nextMonthBtn.addEventListener("click", function () {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar(currentYear, currentMonth);
    });

    updateCalendar(currentYear, currentMonth);

   
    document.getElementById('year').textContent = new Date().getFullYear();
});