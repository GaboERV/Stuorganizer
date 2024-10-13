document.addEventListener('DOMContentLoaded', () => {
    const calendarContainer = document.getElementById('calendar');
    const currentMonthElement = document.getElementById('currentMonth');
    const prevMonthButton = document.getElementById('prevMonth');
    const nextMonthButton = document.getElementById('nextMonth');
    const eventList = document.getElementById('eventList');
    const addEventButton = document.getElementById('addEventButton');
    const orderByDateButton = document.getElementById('orderByDate');
    const orderByAlphabeticalButton = document.getElementById('orderByAlphabetical');
    const toggleColumButton = document.getElementById('toggleColum');
    const colum = document.querySelector('.colum');
    
    let currentDate = new Date();
    let events = [];

    function fetchEvents() {
        fetch('/Proyecto/calendario/api.php')
            .then(response => response.json())
            .then(data => {
                events = data;
                renderEvents();
                renderCalendar(currentDate);
            })
            .catch(error => console.error('Error fetching events:', error));
    }

    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        currentMonthElement.textContent = date.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
        
        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);
        const firstDayOfWeek = firstDayOfMonth.getDay();
        const lastDateOfMonth = lastDayOfMonth.getDate();
        
        let calendarHTML = '<div class="calendar-grid">';
        for (let i = 0; i < firstDayOfWeek; i++) {
            calendarHTML += '<div class="calendar-day empty"></div>';
        }
        for (let i = 1; i <= lastDateOfMonth; i++) {
            calendarHTML += `<div class="calendar-day">${i}</div>`;
        }
        calendarHTML += '</div>';
        
        calendarContainer.innerHTML = calendarHTML;

        // Render events on the calendar
        events.forEach(event => {
            const eventStartDate = new Date(event.startDate);
            if (eventStartDate.getMonth() === month && eventStartDate.getFullYear() === year) {
                const dayElement = calendarContainer.querySelector(`.calendar-day:nth-child(${eventStartDate.getDate() + firstDayOfWeek})`);
                if (dayElement) {
                    const eventElement = document.createElement('div');
                    eventElement.classList.add('calendar-event');
                    eventElement.textContent = event.title;
                    eventElement.style.backgroundColor = event.color;
                    dayElement.appendChild(eventElement);
                }
            }
        });
    }

    function addEvent(title, startDate, endDate, color) {
        fetch('/Proyecto/calendario/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, startDate, endDate, color })
        })
        .then(response => response.json())
        .then(data => {
            if (data.id) {
                events.push({ id: data.id, title, startDate, endDate, color });
                renderEvents();
                renderCalendar(currentDate);
            } else {
                console.error('Error adding event:', data.error);
            }
        })
        .catch(error => console.error('Error adding event:', error));
    }

    function updateEvent(id, title, startDate, endDate, color) {
        fetch('/Proyecto/calendario/api.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, title, startDate, endDate, color })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                const eventIndex = events.findIndex(event => event.id === id);
                events[eventIndex] = { id, title, startDate, endDate, color };
                renderEvents();
                renderCalendar(currentDate);
            } else {
                console.error('Error updating event:', data.error);
            }
        })
        .catch(error => console.error('Error updating event:', error));
    }

    function deleteEvent(id) {
        fetch('/Proyecto/calendario/api.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                events = events.filter(event => event.id !== id);
                renderEvents();
                renderCalendar(currentDate);
            } else {
                console.error('Error deleting event:', data.error);
            }
        })
        .catch(error => console.error('Error deleting event:', error));
    }

    function renderEvents() {
        eventList.innerHTML = '';
        events.forEach(event => {
            const li = document.createElement('li');
            li.textContent = `${event.title} (${event.startDate} - ${event.endDate})`;
            li.style.color = event.color;
            eventList.appendChild(li);
        });
    }

    function sortEventsByDate() {
        events.sort((a, b) => new Date(a.startDate) - new Date(b.startDate));
        renderEvents();
    }

    function sortEventsAlphabetically() {
        events.sort((a, b) => a.title.localeCompare(b.title));
        renderEvents();
    }

    prevMonthButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });

    nextMonthButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });

    addEventButton.addEventListener('click', () => {
        const title = document.getElementById('title').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const eventColor = document.getElementById('eventColor').value;
        if (title && startDate && endDate) {
            addEvent(title, startDate, endDate, eventColor);
        }
    });

    orderByDateButton.addEventListener('click', sortEventsByDate);
    orderByAlphabeticalButton.addEventListener('click', sortEventsAlphabetically);
    toggleColumButton.addEventListener('click', () => {
        colum.style.display = colum.style.display === 'none' ? 'block' : 'none';
    });

    fetchEvents();
});
