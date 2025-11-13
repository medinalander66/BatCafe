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


const reservationDateInput = document.getElementById('reservation_date');

function updateReservationDate() {
    const year = yearEl.value;
    const month = monthEl.value.padStart(2,'0');
    const day = dayEl.value.padStart(2,'0');
    document.getElementById('reservation_date').value = `${year}-${month}-${day}`;
}

// Call initially
updateReservationDate();

// Call on change
[yearEl, monthEl, dayEl].forEach(el => el.addEventListener('change', updateReservationDate));





// ----------------------------
// Check remaining seats (AJAX)
// ----------------------------
function checkRemainingSeats() {
    const remainingWrapper = document.querySelector('.remaining-seats-wrapper');

    const date = getSelectedDate();
    const time = timeEl.value;
    const hours = parseFloat(hoursEl.value);

    // Hide wrapper if inputs are incomplete
    if (!date || !time || !hours) {
        remainingWrapper.style.display = 'none';
        submitBtn.disabled = false;
        return;
    }

    // Study type
    if (typeEl.value === 'Study') {
        fetch(`booking.php?check_seats=1&date=${date}&time=${time}&hours=${hours}`)
            .then(res => res.json())
            .then(data => {
                const remaining = 20 - parseInt(data.current_total, 10);
                remainingWrapper.style.display = 'flex';
                remainingEl.textContent =
                    remaining > 0
                        ? `Remaining Study seats: ${remaining}`
                        : '❌ Study room full at this time';
                remainingEl.style.color = remaining > 0 ? 'green' : 'red';
                submitBtn.disabled = remaining <= 0;
            })
            .catch(err => console.error('AJAX error:', err));
    }
    // Gathering type
    else if (typeEl.value === 'Gathering') {
        fetch(`booking.php?check_gathering=1&date=${date}&time=${time}&hours=${hours}`)
            .then(res => res.json())
            .then(data => {
                remainingWrapper.style.display = 'flex';
                if (data.is_available) {
                    remainingEl.textContent = `✅ The room is free to reserve for a Gathering at this date or time.`;
                    remainingEl.style.color = 'green';
                    submitBtn.disabled = false;
                } else {
                    remainingEl.textContent = ` Room already reserved for a Gathering at this date/time.`;
                    remainingEl.style.color = 'red';
                    submitBtn.disabled = true;
                }
            })
            .catch(err => console.error('AJAX error:', err));
    }
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
    
    // ✅ Dynamically check remaining seats whenever time changes
    checkRemainingSeats();
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


// ----------------------------
// Cost Calculation
// ----------------------------
const calculateBtn = document.getElementById('calculate-btn');
const costWrapper = document.getElementById('cost-breakdown-wrapper');

const roomFeeEl = document.getElementById('room-fee-val');
const equipmentFeeEl = document.getElementById('equipment-fee-val');
const totalFeeEl = document.getElementById('total-fee-val');


const BASE_RATE = 50; // Room per hour
const EQUIPMENT_RATE = 150; // Each equipment per hour

let projectorSelected = false;
let speakerSelected = false;

// Toggle equipment selection
projectorBtn.addEventListener('click', () => {
    projectorSelected = !projectorSelected;
    projectorBtn.classList.toggle('selected', projectorSelected);
});

speakerBtn.addEventListener('click', () => {
    speakerSelected = !speakerSelected;
    speakerBtn.classList.toggle('selected', speakerSelected);
});

// Update cost dynamically
function updateCostBreakdown() {
    const hours = parseFloat(hoursEl.value) || 0;
    const type = typeEl.value;

    // Room fee only applies to Study or Gathering rooms
    const roomFee = (type === 'Study' || type === 'Gathering') ? BASE_RATE * hours : 0;
    const equipmentFee = ((projectorSelected ? EQUIPMENT_RATE : 0) + (speakerSelected ? EQUIPMENT_RATE : 0)) * hours;
    const totalFee = roomFee + equipmentFee;

    roomFeeEl.textContent = `₱${roomFee.toFixed(2)}`;
    equipmentFeeEl.textContent = `₱${equipmentFee.toFixed(2)}`;
    totalFeeEl.textContent = `₱${totalFee.toFixed(2)}`;

    costWrapper.style.display = hours > 0 ? 'flex' : 'none';
}

// Trigger dynamic calculation on relevant inputs
[hoursEl, typeEl].forEach(el => el.addEventListener('change', updateCostBreakdown));


calculateBtn.addEventListener('click', updateCostBreakdown);

let costVisible = false; // Track visibility

calculateBtn.addEventListener('click', () => {
    costVisible = !costVisible; // Toggle state

    if (costVisible) {
        updateCostBreakdown();          // Update values
        costWrapper.style.display = 'flex'; // Show
        calculateBtn.textContent = 'Hide';
    } else {
        costWrapper.style.display = 'none'; // Hide
        calculateBtn.textContent = 'Calculate Total';
    }
});

// Ensure dynamic updates still happen on input changes
[hoursEl, typeEl].forEach(el => el.addEventListener('change', () => {
    if (costVisible) updateCostBreakdown(); // Only update if visible
}));
[projectorBtn, speakerBtn].forEach(el => el.addEventListener('click', () => {
    if (costVisible) updateCostBreakdown(); // Update if visible
}));
