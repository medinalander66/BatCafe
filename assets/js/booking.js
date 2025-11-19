// assets/js/Booking.js
document.addEventListener("DOMContentLoaded", function () {
  // DOM refs
  const form = document.getElementById("booking-form");
  const calculateBtn = document.getElementById("calculate-btn");
  const submitBtn = document.getElementById("submit-btn");
  const equipmentButtons = document.querySelectorAll(".equipment-btn");
  const costWrapper = document.getElementById("cost-breakdown-wrapper");
  const roomFeeVal = document.getElementById("room-fee-val");
  const equipmentFeeVal = document.getElementById("equipment-fee-val");
  const totalFeeVal = document.getElementById("total-fee-val");
  // Elements
  const startTimeInput = document.getElementById("start_time");
  const increaseBtn = document.getElementById("increase-time");
  const decreaseBtn = document.getElementById("decrease-time");

  // date selects
  const yearInput = document.getElementById("year");
  const monthSelect = document.getElementById("month");
  const daySelect = document.getElementById("day");

  // set year to current
  const now = new Date();
  yearInput.value = now.getFullYear();

  const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];

  months.forEach((month, index) => {
    const option = document.createElement("option");
    option.value = index + 1; // <-- integer submitted
    option.textContent = month; // <-- text shown
    monthSelect.appendChild(option);
  });

  // auto-select current month
  monthSelect.value = new Date().getMonth() + 1;

  // populate days helper
  function populateDays() {
    const now = new Date();
    const y = parseInt(yearInput.value, 10);
    const m = parseInt(monthSelect.value, 10);

    const daysInMonth = new Date(y, m, 0).getDate();

    daySelect.innerHTML = "";

    for (let d = 1; d <= daysInMonth; d++) {
      const opt = document.createElement("option");
      opt.value = d;
      opt.textContent = d;
      daySelect.appendChild(opt);
    }

    // ---- auto-select current day if month/year match today ----
    if (y === now.getFullYear() && m === now.getMonth() + 1) {
      daySelect.value = now.getDate();
    } else {
      // fallback (optional)
      daySelect.value = 1;
    }
  }
  populateDays();
  monthSelect.addEventListener("change", populateDays);

  // Helper to add/subtract minutes with boundaries (13:00 → 01:00 next day)
 flatpickr(startTimeInput, { 
    enableTime: true,
    noCalendar: true,
    time_24hr: true,           // store time in 24-hour format
    minuteIncrement: 30,
    defaultDate: "13:00",      // 1:00 PM in 24-hour
    disable: [
        function(date) {
            const h = date.getHours();
            const m = date.getMinutes();

            // Allowed: 13:00 - 23:30 AND 00:00 - 01:00
            if ((h >= 13 && h <= 23) || (h >= 0 && h <= 1)) {
                if (h === 23 && m > 30) return true;  // disable after 23:30
                if (h === 1 && m > 0) return true;    // disable after 01:00
                return false; // allowed
            }
            return true; // disable everything else
        }
    ],
    
    altInput: true,             // show alternate display input
    altFormat: "h:i K",         // 12-hour display (e.g., 1:00 PM)
    dateFormat: "H:i"           // actual value sent to PHP in 24-hour format
});

// Optional: buttons to increase/decrease by 30 minutes
increaseBtn.addEventListener("click", () =>
    adjustFlatpickrTime(startTimeInput, 30)
);
decreaseBtn.addEventListener("click", () =>
    adjustFlatpickrTime(startTimeInput, -30)
);

function adjustFlatpickrTime(input, minutes) {
    let date = input._flatpickr.selectedDates[0] || new Date();
    date.setMinutes(date.getMinutes() + minutes);
    input._flatpickr.setDate(date, true);
}


  // equipment selection set
  const selectedEquipment = new Set();
  equipmentButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const code = btn.dataset.code;
      if (!code) return;
      if (selectedEquipment.has(code)) {
        selectedEquipment.delete(code);
        btn.classList.remove("selected");
      } else {
        selectedEquipment.add(code);
        btn.classList.add("selected");
      }
      // Recalculate dynamically on click
      calculateEstimate(false);
    });
  });

  // helper to gather form data
  function gatherFormData() {
    const hours = document.getElementById("hours").value;
    const equipment = Array.from(selectedEquipment);
    return {
      type: document.getElementById("type").value,
      persons: document.getElementById("persons").value,
      start_time: document.getElementById("start_time").value,
      year: document.getElementById("year").value,
      month: document.getElementById("month").value,
      day: document.getElementById("day").value,
      hours: hours,
      name: document.getElementById("name").value,
      student_id: document.getElementById("student_id").value,
      email: document.getElementById("email").value,
      phone: document.getElementById("phone").value,
      equipment: equipment,
    };
  }

  // show estimate result
  function showEstimate(data) {
    costWrapper.style.display = "flex";
    roomFeeVal.textContent = formatPHP(data.room_fee);
    equipmentFeeVal.textContent = formatPHP(data.equipment_fee);
    totalFeeVal.textContent = formatPHP(data.total);
  }

  function formatPHP(amount) {
    return "₱" + Number(amount).toFixed(2);
  }

  // Calculate button - AJAX call to booking.php?action=calculate
  calculateBtn.addEventListener("click", () => {
    const fd = gatherFormData();
    const body = new FormData();
    body.append("action", "calculate");
    body.append("hours", fd.hours);
    for (const code of fd.equipment) body.append("equipment[]", code);

    fetch("booking.php", { method: "POST", body })
      .then(async (res) => {
        if (!res.ok) {
          const err = await res.json().catch(() => null);
          alert("Failed to calculate. " + (err?.message || res.statusText));
          return;
        }
        const json = await res.json();
        if (json.status === "ok") {
          // Update the cost numbers
          roomFeeVal.textContent = formatPHP(json.data.room_fee);
          equipmentFeeVal.textContent = formatPHP(json.data.equipment_fee);
          totalFeeVal.textContent = formatPHP(json.data.total);

          // Toggle wrapper display
          if (costWrapper.style.display === "flex") {
            costWrapper.style.display = "none";
            calculateBtn.textContent = "Show Estimate";
          } else {
            costWrapper.style.display = "flex";
            calculateBtn.textContent = "Hide Estimate";
          }
        } else {
          alert("Error: " + JSON.stringify(json));
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Network error while calculating.");
      });
  });

  // Reusable function to perform AJAX calculation
  // === Reusable AJAX calculation function ===
  // showWrapper = true if the calculate button was clicked
  function calculateEstimate(showWrapper = false) {
    const fd = gatherFormData();
    const body = new FormData();
    body.append("action", "calculate");
    body.append("hours", fd.hours);
    for (const code of fd.equipment) body.append("equipment[]", code);

    fetch("booking.php", { method: "POST", body })
      .then(async (res) => {
        if (!res.ok) {
          const err = await res.json().catch(() => null);
          alert("Failed to calculate. " + (err?.message || res.statusText));
          return;
        }
        const json = await res.json();
        if (json.status === "ok") {
          // Only show the wrapper if explicitly requested
          if (showWrapper) costWrapper.style.display = "flex";
          // Always update the numbers
          roomFeeVal.textContent = formatPHP(json.data.room_fee);
          equipmentFeeVal.textContent = formatPHP(json.data.equipment_fee);
          totalFeeVal.textContent = formatPHP(json.data.total);
        } else {
          alert("Error: " + JSON.stringify(json));
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Network error while calculating.");
      });
  }

  // === Button click triggers calculation and shows wrapper ===

  // === Dynamic calculation on form changes, without showing wrapper ===
  const formElements = document.querySelectorAll("input, select");
  formElements.forEach((el) => {
    el.addEventListener("input", () => calculateEstimate(false));
    el.addEventListener("change", () => calculateEstimate(false));
  });

  // Submit reservation
  submitBtn.addEventListener("click", function () {
    const fd = gatherFormData();
    const body = new FormData();
    body.append("action", "submit");
    body.append("type", fd.type);
    body.append("persons", fd.persons);
    body.append("start_time", fd.start_time);
    body.append("year", fd.year);
    body.append("month", fd.month);
    body.append("day", fd.day);
    body.append("hours", fd.hours);
    body.append("name", fd.name);
    body.append("student_id", fd.student_id);
    body.append("email", fd.email);
    body.append("phone", fd.phone);
    for (const code of fd.equipment) body.append("equipment[]", code);

    fetch("booking.php", {
      method: "POST",
      body: body,
    })
      .then(async (res) => {
        const json = await res.json().catch(() => null);
        if (!res.ok) {
          if (json && json.errors) {
            alert("Validation errors: " + JSON.stringify(json.errors));
          } else {
            alert("Submit failed: " + res.statusText);
          }
          return;
        }
        if (json.status === "ok") {
          alert(
            "Reservation created! ID: " +
              json.data.reservation_id +
              "\nEstimated total: ₱" +
              json.data.estimated_total
          );
          // optionally redirect or clear form
          window.location.reload();
        } else {
          alert("Error: " + JSON.stringify(json));
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Network error while submitting reservation.");
      });
  });
});
