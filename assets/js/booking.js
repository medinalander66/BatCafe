// js/booking.js

const typeEl = document.getElementById('type');
const dateEl = document.getElementById('reservation_date');
const timeEl = document.getElementById('start_time');
const hoursEl = document.getElementById('hours');
const remainingEl = document.getElementById('remaining-seats');

function checkRemainingSeats() {
    if (typeEl.value !== 'Study') {
        remainingEl.textContent = '';
        return; // Only check for Study type
    }

    const date = dateEl.value;
    const time = timeEl.value;
    const hours = hoursEl.value;

    if (!date || !time || !hours) return;

    fetch(`check_seats.php?date=${date}&time=${time}&hours=${hours}`)
        .then(response => response.json())
        .then(data => {
            const remaining = 20 - data.current_total;
            if (remaining > 0) {
                remainingEl.style.color = 'green';
                remainingEl.textContent = `Remaining Study seats: ${remaining}`;
            } else {
                remainingEl.style.color = 'red';
                remainingEl.textContent = `❌ Study room full at this time`;
            }
        })
        .catch(err => {
            console.error(err);
        });
}

// Trigger when user changes date, time, hours, or type
[typeEl, dateEl, timeEl, hoursEl].forEach(el => {
    el.addEventListener('change', checkRemainingSeats);
});
