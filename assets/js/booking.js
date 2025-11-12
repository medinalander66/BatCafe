const typeEl = document.getElementById('type');
const timeEl = document.getElementById('start_time');
const hoursEl = document.getElementById('hours');
const personsEl = document.getElementById('persons');
const remainingEl = document.getElementById('remaining-seats');
const submitBtn = document.getElementById('submit-btn');

// Date dropdowns
const yearEl = document.getElementById('year');
const monthEl = document.getElementById('month');
const dayEl = document.getElementById('day');

// Time slot adjustment buttons
const increaseBtn = document.getElementById('increase-time');
const decreaseBtn = document.getElementById('decrease-time');

// Operating hours
const MIN_HOUR = 13; // 1 PM
const MAX_HOUR = 25; // 1 AM next day

// ----------------------------
// Helper: Get selected date in YYYY-MM-DD
// ----------------------------
function getSelectedDate() {
    const year = yearEl.value;
    const month = String(monthEl.value).padStart(2,'0');
    const day = String(dayEl.value).padStart(2,'0');
    return `${year}-${month}-${day}`;
}

// ----------------------------
// Check remaining seats (AJAX)
// ----------------------------
function checkRemainingSeats() {
    if (typeEl.value !== 'Study') {
        remainingEl.textContent = '';
        submitBtn.disabled = false;
        return;
    }

    const date = getSelectedDate();
    const time = timeEl.value;
    const hours = parseFloat(hoursEl.value);

    if (!date || !time || !hours) return;

    fetch(`booking.php?check_seats=1&date=${date}&time=${time}&hours=${hours}`)
        .then(res => res.json())
        .then(data => {
            console.log('AJAX Response:', data);
            const remaining = 20 - parseInt(data.current_total, 10);
            remainingEl.textContent = remaining>0 ? `Remaining Study seats: ${remaining}` : '❌ Study room full at this time';
            remainingEl.style.color = remaining>0 ? 'green':'red';
            submitBtn.disabled = remaining<=0;
        })
        .catch(err => console.error('AJAX error:', err));
}

// Trigger when relevant inputs change
[typeEl, timeEl, hoursEl, monthEl, dayEl].forEach(el => el.addEventListener('change', checkRemainingSeats));

// ----------------------------
// Time slot dynamic adjustment
// ----------------------------
function adjustTime(minutes) {
    if (!timeEl.value) return;

    let [hours, mins] = timeEl.value.split(':').map(Number);
    let totalMinutes = hours * 60 + mins + minutes;

    // Limit between 13:00 and 25:00 (1 AM next day)
    if (totalMinutes < MIN_HOUR * 60) totalMinutes = MIN_HOUR * 60;
    if (totalMinutes > MAX_HOUR * 60) totalMinutes = MAX_HOUR * 60;

    let newHours = Math.floor(totalMinutes / 60);
    let newMins = totalMinutes % 60;

    // Wrap around after 24 hours
    if (newHours >= 24) newHours -= 24;

    timeEl.value = `${newHours.toString().padStart(2,'0')}:${newMins.toString().padStart(2,'0')}`;
}

increaseBtn.addEventListener('click', () => adjustTime(30));
decreaseBtn.addEventListener('click', () => adjustTime(-30));

// ----------------------------
// Populate date dropdowns (year/month/day) default to today
// ----------------------------
const today = new Date();
const currentYear = today.getFullYear();
const currentMonth = today.getMonth() + 1;
const currentDay = today.getDate();

// Year readonly
yearEl.value = currentYear;

// Populate month
for (let m=1; m<=12; m++){
    const option = document.createElement('option');
    option.value = m;
    option.textContent = new Date(currentYear,m-1).toLocaleString('default',{month:'long'});
    monthEl.appendChild(option);
}
monthEl.value = currentMonth;

// Populate days
function populateDays(year, month){
    const daysInMonth = new Date(year, month, 0).getDate();
    dayEl.innerHTML = '';
    for (let d=1; d<=daysInMonth; d++){
        const option = document.createElement('option');
        option.value = d;
        option.textContent = d;
        dayEl.appendChild(option);
    }
}
populateDays(currentYear, currentMonth);
dayEl.value = currentDay;

// Update days when month changes
monthEl.addEventListener('change', ()=>{
    populateDays(currentYear, parseInt(monthEl.value));
    if(dayEl.options.length < currentDay) dayEl.value = dayEl.options[0].value;
});


// Equipment toggle buttons
const projectorBtn = document.getElementById('projector-btn');
const speakerBtn = document.getElementById('speaker-btn');

function toggleButton(btn) {
    const isActive = btn.getAttribute('data-active') === '1';
    btn.setAttribute('data-active', isActive ? '0' : '1');
    btn.classList.toggle('active', !isActive);
}

// Click events
projectorBtn.addEventListener('click', () => toggleButton(projectorBtn));
speakerBtn.addEventListener('click', () => toggleButton(speakerBtn));



// Toggle Buttons for Equipment Selection
document.addEventListener("DOMContentLoaded", function () {
  const equipmentButtons = document.querySelectorAll(".equipment-btn");
  const selectedInput = document.getElementById("selected_equipment");

  equipmentButtons.forEach(button => {
    button.addEventListener("click", () => {
      // Toggle active class
      button.classList.toggle("active");

      // Collect all selected equipment
      const selected = Array.from(equipmentButtons)
        .filter(btn => btn.classList.contains("active"))
        .map(btn => btn.dataset.equipment);

      // Store selected list as comma-separated string in hidden input
      selectedInput.value = selected.join(", ");
    });
  });
});
