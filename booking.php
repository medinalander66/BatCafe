<?php
require_once 'db/connect.php';

class BookingSystem {
    private $pdo;
    private $baseRate = 50.00;
    private $equipmentRate = 150.00;
    private $maxStudySeats = 20;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // AJAX: Check remaining Study seats
    public function ajaxCheckSeats($date, $time, $hours) {
        header('Content-Type: application/json');

        if (!$date || !$time || $hours <= 0) {
            echo json_encode(['current_total' => 0]);
            exit;
        }

        $times = $this->computeEndTime($date, $time, $hours);
        $start = $times['start'];
        $end = $times['end'];

        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(persons),0) AS total_persons
            FROM bookings
            WHERE reservation_date = ?
              AND is_finished = 0
              AND type = 'Study'
              AND (
                    (start_time <= ? AND end_time > ?)
                 OR (start_time < ? AND end_time >= ?)
                 OR (start_time >= ? AND end_time <= ?)
              )
        ");
        $stmt->execute([$date, $start, $start, $end, $end, $start, $end]);
        $current_total = intval($stmt->fetchColumn());

        echo json_encode(['current_total' => $current_total]);
        exit;
    }

    // Compute end time
    private function computeEndTime($date, $time, $hours) {
        $start_datetime = new DateTime("$date $time");
        $end_datetime = clone $start_datetime;
        $whole = floor($hours);
        $fraction = $hours - $whole;
        $end_datetime->modify("+{$whole} hours");
        if ($fraction > 0) {
            $minutes = intval(round($fraction * 60));
            if ($minutes > 0) $end_datetime->modify("+{$minutes} minutes");
        }
        return [
            'start' => $start_datetime->format('H:i:s'),
            'end' => $end_datetime->format('H:i:s')
        ];
    }

    // Process booking submission
   public function processBooking($data) {
    $errors = [];

    $student_id = trim($data['student_id'] ?? '');
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $reservation_date_raw = trim($data['reservation_date'] ?? '');
    $start_time = trim($data['start_time'] ?? '');
    $hours = floatval($data['hours'] ?? 0);
    $persons = intval($data['persons'] ?? 0);
    $type = isset($data['type']) && $data['type'] === 'Gathering' ? 'Gathering' : 'Study';
    $projector = isset($data['projector']) ? 1 : 0;
    $speaker = isset($data['speaker']) ? 1 : 0;
    $year  = intval($data['year'] ?? 0);
    $month = intval($data['month'] ?? 0);
    $day   = intval($data['day'] ?? 0);


    $reservation_date = $reservation_date_raw; // Already in YYYY-MM-DD from the dropdowns
    if (checkdate($month, $day, $year)) {
        $reservation_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
    } else {
        $reservation_date = '';
    }

    // Validation
    $errors = $this->validateBooking(
        $student_id, $name, $email, $phone, 
        $reservation_date, $start_time, $hours, $persons
    );
    if (!empty($errors)) return ['errors' => $errors];

    $times = $this->computeEndTime($reservation_date, $start_time, $hours);
    $start = $times['start'];
    $end = $times['end'];

    // Check rules
    switch ($type) {
        case 'Gathering':
            if ($this->checkOverlap($reservation_date, $start, $end)) {
                $errors[] = "❌ Cannot reserve Gathering. Room already booked at this time.";
            }
            break;
        case 'Study':
            if ($this->checkOverlapGathering($reservation_date, $start, $end)) {
                $errors[] = "❌ Cannot reserve Study. Gathering reservation overlaps this time.";
            } elseif ($this->checkStudyCapacity($reservation_date, $start, $end, $persons) > $this->maxStudySeats) {
                $errors[] = "❌ Study room full. Cannot add {$persons} more persons.";
            }
            break;
    }

    if (!empty($errors)) return ['errors' => $errors];

    // Calculate fees
    $booking_fee = $this->baseRate * $hours;
    $equipment_fee = (($projector ? $this->equipmentRate : 0) + ($speaker ? $this->equipmentRate : 0)) * $hours;
    $total_fee = $booking_fee + $equipment_fee;

    // Insert booking
    $insert = $this->pdo->prepare("
        INSERT INTO bookings
        (student_id, name, email, phone, reservation_date, start_time, end_time, hours, persons,
         projector, speaker_mike, booking_fee, equipment_fee, total_fee, type, is_finished)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
    ");
    $insert->execute([
        $student_id, $name, $email, $phone, $reservation_date, $start, $end, $hours, $persons,
        $projector, $speaker, $booking_fee, $equipment_fee, $total_fee, $type
    ]);

    return [
        'success' => "✅ Reservation confirmed for <strong>{$type}</strong>! Total: ₱" . number_format($total_fee, 2) .
        "<br>From {$start} to {$end} for {$persons} person(s)."
    ];
}


    private function validateBooking($student_id, $name, $email, $phone, $reservation_date, $start_time, $hours, $persons) {
        $errors = [];
        if (!$student_id) $errors[]="Student ID required";
        if (!$name) $errors[]="Full Name required";
        if (!$email || !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]="Valid Email required";
        if (!$phone) $errors[]="Phone required";
        if (!$reservation_date || !strtotime($reservation_date)) $errors[]="Valid date required";
        if (!$start_time || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/',$start_time)) $errors[]="Valid start time required";
        if ($hours<=0) $errors[]="Hours must be >0";
        if ($persons<1) $errors[]="Persons must be >=1";
        return $errors;
    }

    private function checkOverlap($date,$start,$end){
        $stmt=$this->pdo->prepare("
            SELECT COUNT(*) FROM bookings
            WHERE reservation_date=? AND is_finished=0
              AND ((start_time<=? AND end_time>? )
                   OR (start_time<? AND end_time>=? )
                   OR (start_time>=? AND end_time<=? ))
        ");
        $stmt->execute([$date,$start,$start,$end,$end,$start,$end]);
        return intval($stmt->fetchColumn())>0;
    }

    private function checkOverlapGathering($date,$start,$end){
        $stmt=$this->pdo->prepare("
            SELECT COUNT(*) FROM bookings
            WHERE reservation_date=? AND is_finished=0 AND type='Gathering'
              AND ((start_time<=? AND end_time>? )
                   OR (start_time<? AND end_time>=? )
                   OR (start_time>=? AND end_time<=? ))
        ");
        $stmt->execute([$date,$start,$start,$end,$end,$start,$end]);
        return intval($stmt->fetchColumn())>0;
    }

    private function checkStudyCapacity($date,$start,$end,$newPersons){
        $stmt=$this->pdo->prepare("
            SELECT COALESCE(SUM(persons),0) AS total_persons
            FROM bookings
            WHERE reservation_date=? AND is_finished=0 AND type='Study'
              AND ((start_time<=? AND end_time>? )
                   OR (start_time<? AND end_time>=? )
                   OR (start_time>=? AND end_time<=? ))
        ");
        $stmt->execute([$date,$start,$start,$end,$end,$start,$end]);
        return intval($stmt->fetchColumn()) + $newPersons;
    }

    public function ajaxCheckGathering($date, $time, $hours) {
      $times = $this->computeEndTime($date, $time, $hours);
      $start = $times['start'];
      $end   = $times['end'];

    return !$this->checkOverlap($date, $start, $end);
  }
}

// Initialize
$bookingSystem = new BookingSystem($pdo);

// Handle AJAX
if(isset($_GET['check_seats']) && $_GET['check_seats']==1){
    $bookingSystem->ajaxCheckSeats($_GET['date'] ?? '', $_GET['time'] ?? '', floatval($_GET['hours'] ?? 0));
}
if(isset($_GET['check_gathering']) && $_GET['check_gathering']==1){
    header('Content-Type: application/json');

    $date = $_GET['date'] ?? '';
    $time = $_GET['time'] ?? '';
    $hours = floatval($_GET['hours'] ?? 0);

    $is_available = false;
    if ($date && $time && $hours > 0) {
        $is_available = $bookingSystem->ajaxCheckGathering($date, $time, $hours);
    }

    echo json_encode(['is_available' => $is_available]);
    exit;
}

// Handle form submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    $result = $bookingSystem->processBooking($_POST);
    $message = $result['success'] ?? implode('<br>',$result['errors'] ?? []);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Bat Cave Café — Room Reservation</title>
  <script src="assets/js/script.js" ></script>
  <link rel="stylesheet" href="assets/css/globalStyles.css">
  <link rel="stylesheet" href="assets/css/booking.css">

</head>
<body>

  <!-- 🔹 Navigation Bar -->
  <script src="assets/js/navbar.js" ></script>
  <!-- 🔹 Page Header -->
  <header>
    <h2 class="page-title">Book a Room</h2>
    <p class="subtitle">Reserve your private study space.</p>
    <p class="hours">Operating Hours: 1:00 PM – 1:00 AM</p>
  </header>

<main>
  <div class="booking-container card">
    <?php if (!empty($message)): ?>
      <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="booking.php" id="booking-form">

    <div class="two-column-form">
      <label for="type">Room Type *</label>
      <select name="type" id="type" required>
        <option value="Study" selected>Study</option>
        <option value="Gathering">Gathering</option>
      </select>

      <!-- ===== Column 1 ===== -->
      <div class="column">
        <label for="persons">Number of Persons *</label>
        <input type="number" name="persons" id="persons" min="1" max="20" required>

        <label for="start_time">Time Slot *</label>
        <div class="time-slot-wrapper">
        <button type="button" class="adjust-btn" id="decrease-time">-</button>
        
        <input type="time" name="start_time" id="start_time" required value="13:00" min="13:00" max="01:00">
        
        <button type="button" class="adjust-btn" id="increase-time">+</button>
        </div>

        <label for="student_id">Student ID *</label>
        <input type="text" name="student_id" id="student_id" required>

        <label for="email">Email *</label>
        <input type="email" name="email" id="email" required>
      </div>

      <!-- ===== Column 2 ===== -->
      <div class="column">
        <label for="reservation_date">Reservation Date *</label>
        <input type="hidden" name="reservation_date" id="reservation_date">
        <div class="reservation-date-wrapper">
        <!-- Year (readonly, current year) -->
        <input type="number" name="year" id="year" readonly>

        <!-- Month -->
        <select name="month" id="month" required></select>

        <!-- Day -->
        <select name="day" id="day" required></select>
        </div>

        <label for="hours">Duration (Hours) *</label>
        <select name="hours" id="hours" required>
          <option value="1">1</option>
          <option value="1.5">1.5</option>
          <option value="2">2</option>
          <option value="2.5">2.5</option>
          <option value="3">3</option>
          <option value="3.5">3.5</option>
          <option value="4">4</option>
        <option value="4.5">4.5</option>
        <option value="5">5</option>
        <option value="5.5">5.5</option>
        <option value="6">6</option>
        <option value="6.5">6.5</option>
        <option value="7">7</option>
        <option value="7.5">7.5</option>
        <option value="8">8</option>
        <option value="8.5">8.5</option>
        <option value="9">9</option>
        <option value="9.5">9.5</option>
        <option value="10">10</option>
        <option value="10.5">10.5</option>
        <option value="11">11</option>
        <option value="11.5">11.5</option>
        <option value="12">12</option>
        </select>

        <label for="name">Full Name *</label>
        <input type="text" name="name" id="name" required>

        <label for="phone">Phone *</label>
        <input type="text" name="phone" id="phone" required>

      </div>
</div>
      <div class="remaining-seats-wrapper">
          <img src="assets/images/icons/users.png" alt="Users" class="icon" id="users-icon">
          <p id="remaining-seats"></p>
      </div>
      
      <div class="equipment-wrapper">
        <label>Rental Equipment (Optional):</label>
        <div class="column">
        <button type="button" class="equipment-btn" id="projector-btn">
          <span class="icon">📽️</span> Projector <span class="price">₱150/hr</span>
        </button>
        </div>
        <div class="column">
        <button type="button" class="equipment-btn" id="speaker-btn">
          <span class="icon">🎤</span> Speaker & Mic <span class="price">₱150/hr</span>
        </button>
        </div>
      </div>

      <div class="reserve-calculate-wrapper">
        <div class="column">
          <button type="button" id="calculate-btn" class="rc-btn">Calculate Total</button>
        </div>
        <div class="column">
          <button button type="submit" id="submit-btn" class="rc-btn">Reserve</button>
        </div>

        <div id="cost-breakdown-wrapper" class="cost-breakdown-wrapper" style="display:none;">
        <div class="cost-breakdown-column">
          <h3>Cost Breakdown</h3>
          <p>Room Fee (per hour)</p>
          <p>Equipment Fee</p>
          <hr>
          <p id="total-estimated-cost-p">Total Estimated Cost</p>
        </div>
        <div class="cost-breakdown-column numbers-column">
          <p id="room-fee-val">₱0.00</p>
          <p id="equipment-fee-val">₱0.00</p>
          <hr>
          <p id="total-fee-val">₱0.00</p>
        </div>
      </div>

      </div>
    </form>
  </div>
</main>

<script src="assets/js/footer.js" defer></script>

  <script src="assets/js/theme-toggle.js"></script>
  <script src="assets/js/booking.js"></script>
  <script  src="assets/js/script.js"></script>
</body>
</html>



