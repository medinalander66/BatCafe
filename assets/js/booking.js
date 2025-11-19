document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("booking-form");
  const calculateBtn = document.getElementById("calculate-btn");
  const submitBtn = document.getElementById("submit-btn");
  const equipmentButtons = document.querySelectorAll(".equipment-btn");
  const costWrapper = document.getElementById("cost-breakdown-wrapper");
  const roomFeeVal = document.getElementById("room-fee-val");
  const personFeeVal = document.getElementById("person-fee-val");
  const equipmentFeeVal = document.getElementById("equipment-fee-val");
  const totalFeeVal = document.getElementById("total-fee-val");
  const minimumFeeVal = document.getElementById("minimum-fee-val"); // new element for minimum fee


  const startTimeInput = document.getElementById("start_time");
  const increaseBtn = document.getElementById("increase-time");
  const decreaseBtn = document.getElementById("decrease-time");

  const yearInput = document.getElementById("year");
  const monthSelect = document.getElementById("month");
  const daySelect = document.getElementById("day");

  // Set current year
  const now = new Date();
  yearInput.value = now.getFullYear();

  // Populate months
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
    const opt = document.createElement("option");
    opt.value = index + 1;
    opt.textContent = month;
    monthSelect.appendChild(opt);
  });
  monthSelect.value = now.getMonth() + 1;

  // Populate days
  function populateDays() {
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
    if (y === now.getFullYear() && m === now.getMonth() + 1) {
      daySelect.value = now.getDate();
    } else {
      daySelect.value = 1;
    }
  }
  populateDays();
  monthSelect.addEventListener("change", populateDays);

  // Flatpickr setup
  flatpickr(startTimeInput, {
    enableTime: true,
    noCalendar: true,
    time_24hr: true,
    minuteIncrement: 30,
    defaultDate: "13:00",
    disable: [
      function (date) {
        const h = date.getHours(),
          m = date.getMinutes();
        if ((h >= 13 && h <= 23) || (h >= 0 && h <= 1)) {
          if (h === 23 && m > 30) return true;
          if (h === 1 && m > 0) return true;
          return false;
        }
        return true;
      },
    ],
    altInput: true,
    altFormat: "h:i K",
    dateFormat: "H:i",
  });

  function adjustFlatpickrTime(input, minutes) {
    let date = input._flatpickr.selectedDates[0] || new Date();
    date.setMinutes(date.getMinutes() + minutes);
    input._flatpickr.setDate(date, true);
  }

  increaseBtn.addEventListener("click", () =>
    adjustFlatpickrTime(startTimeInput, 30)
  );
  decreaseBtn.addEventListener("click", () =>
    adjustFlatpickrTime(startTimeInput, -30)
  );

  // Show cost wrapper and populate fees
  function showCostWrapper(data) {
    costWrapper.style.display = "flex";
    roomFeeVal.textContent = "₱" + (data.hourly_fee ?? 0).toFixed(2);
    personFeeVal.textContent = "₱" + (data.person_fee ?? 0).toFixed(2);
    equipmentFeeVal.textContent = "₱" + (data.equipment_fee ?? 0).toFixed(2);
    if (minimumFeeVal)
      minimumFeeVal.textContent = "₱" + (data.minimum_fee ?? 0).toFixed(2);
    totalFeeVal.textContent = "₱" + (data.total_fee ?? 0).toFixed(2);
  }

  // Equipment selection
  // Equipment selection
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
      calculateEstimate(false);
    });
  });

  // Gather form data
  // gather form data helper
  function gatherFormData() {
    const roomTypeEl = document.querySelector("#room_type");
    const room_type_id = parseInt(roomTypeEl.value, 10);

    return {
      hours: parseFloat(document.querySelector("#hours").value) || 0,
      persons: parseInt(document.querySelector("#persons").value, 10) || 1,
      room_type_id: isNaN(room_type_id) ? 0 : room_type_id,
      equipment: [
        ...document.querySelectorAll('input[name="equipment[]"]:checked'),
      ].map((e) => e.value),
      start_time: document.querySelector("#start_time").value,
      year: parseInt(document.querySelector("#year").value, 10),
      month: parseInt(document.querySelector("#month").value, 10),
      day: parseInt(document.querySelector("#day").value, 10),
      name: document.querySelector("#name").value,
      student_id: document.querySelector("#student_id").value,
      email: document.querySelector("#email").value,
      phone: document.querySelector("#phone").value,
    };
  }

  // calculate estimate dynamically
  function calculateEstimate(showWrapper = false) {
    const fd = gatherFormData();
    const body = new FormData();
    body.append("action", "calculate");
    body.append("hours", fd.hours);
    body.append("persons", fd.persons);
    body.append("room_type_id", fd.room_type_id);
    fd.equipment.forEach((code) => body.append("equipment_codes[]", code));

    fetch("booking.php", { method: "POST", body })
      .then((res) => res.json())
      .then((json) => {
        if (json.status === "ok") {
          showCostWrapper(json.data);
        } else {
          alert("Error calculating estimate: " + JSON.stringify(json));
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Network error while calculating estimate.");
      });
  }

    // Calculate button click
  calculateBtn.addEventListener("click", () => {
    calculateEstimate();
  });

  // Dynamic calculation on input changes
  const formElements = document.querySelectorAll("input, select");
  formElements.forEach((el) => {
    el.addEventListener("input", () => calculateEstimate(false));
    el.addEventListener("change", () => calculateEstimate(false));
  });

  // submit reservation
  submitBtn.addEventListener("click", () => {
    const fd = gatherFormData();
    const body = new FormData();
    body.append("action", "submit");
    body.append("room_type_id", fd.room_type_id);
    body.append("hours", fd.hours);
    body.append("persons", fd.persons);
    body.append("start_time", fd.start_time);
    body.append("year", fd.year);
    body.append("month", fd.month);
    body.append("day", fd.day);
    body.append("name", fd.name);
    body.append("student_id", fd.student_id);
    body.append("email", fd.email);
    body.append("phone", fd.phone);
    fd.equipment.forEach((code) => body.append("equipment[]", code));

    fetch("booking.php", { method: "POST", body })
      .then((res) => res.json())
      .then((json) => {
        if (json.status === "ok") {
          alert(
            `Reservation created! ID: ${json.data.reservation_id}\nEstimated total: ₱${json.data.estimated_total}`
          );
          window.location.reload();
        } else {
          alert("Error submitting reservation: " + JSON.stringify(json));
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Network error while submitting reservation.");
      });
  });
});
