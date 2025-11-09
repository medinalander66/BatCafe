<?php
require_once 'db/connect.php';

class BookingSystem {
    private $pdo;
    private $baseRate = 50.00;
    private $equipmentRate = 150.00;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Handle AJAX check for remaining Study seats
    public function ajaxCheckSeats($date, $time, $hours) {
        if (!$date || !$time || $hours <= 0) {
            echo json_encode(['current_total' => 0]);
            exit;
        }

        $startEnd = $this->computeEndTime($date, $time, $hours);
        $start_str = $startEnd['start'];
        $end_str = $startEnd['end'];

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
        $stmt->execute([$date, $start_str, $start_str, $end_str, $end_str, $start_str, $end_str]);
        $current_total = intval($stmt->fetchColumn());

        echo json_encode(['current_total' => $current_total]);
        exit;
    }

    // Compute end time from start time and hours
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

    // Process a new booking
    public function processBooking($data) {
        $errors = [];

        // Sanitize inputs
        $student_id = trim($data['student_id'] ?? '');
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $reservation_date = trim($data['reservation_date'] ?? '');
        $start_time = trim($data['start_time'] ?? '');
        $hours = floatval($data['hours'] ?? 0);
        $persons = intval($data['persons'] ?? 0);
        $type = isset($data['type']) && $data['type'] === 'Gathering' ? 'Gathering' : 'Study';
        $projector = isset($data['projector']) ? 1 : 0;
        $speaker = isset($data['speaker']) ? 1 : 0;

        // Basic validation
        $errors = $this->validateBooking($student_id, $name, $email, $phone, $reservation_date, $start_time, $hours, $persons);

        if (!empty($errors)) return ['errors' => $errors];

        // Compute start and end time
        $startEnd = $this->computeEndTime($reservation_date, $start_time, $hours);
        $start_str = $startEnd['start'];
        $end_str = $startEnd['end'];

        // Reservation logic based on type
        switch ($type) {
            case 'Gathering':
                if ($this->checkOverlap($reservation_date, $start_str, $end_str)) {
                    $errors[] = "❌ Cannot reserve Gathering. The room is already booked (Study or Gathering) at this time.";
                }
                break;
            case 'Study':
                if ($this->checkOverlapGathering($reservation_date, $start_str, $end_str)) {
                    $errors[] = "❌ Cannot reserve Study. There is a Gathering reservation overlapping this time.";
                } elseif ($this->checkStudyCapacity($reservation_date, $start_str, $end_str, $persons) > 20) {
                    $errors[] = "❌ Study room slots full. Cannot add {$persons} more persons.";
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
            $student_id, $name, $email, $phone,
            $reservation_date, $start_str, $end_str, $hours, $persons,
            $projector, $speaker, $booking_fee, $equipment_fee, $total_fee, $type
        ]);

        return ['success' => "✅ Reservation confirmed for <strong>{$type}</strong>! Total: ₱" . number_format($total_fee, 2) .
            "<br>From {$start_str} to {$end_str} for {$persons} person(s)."];
    }

    // Validation helper
    private function validateBooking($student_id, $name, $email, $phone, $reservation_date, $start_time, $hours, $persons) {
        $errors = [];
        if ($student_id === '') $errors[] = "Please enter Student ID.";
        if ($name === '') $errors[] = "Please enter Full Name.";
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid Email.";
        if ($phone === '') $errors[] = "Please enter Phone.";
        if ($reservation_date === '' || !strtotime($reservation_date)) $errors[] = "Please enter a valid reservation date.";
        if ($start_time === '' || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start_time)) $errors[] = "Please enter a valid start time.";
        if ($hours <= 0) $errors[] = "Number of hours must be greater than zero.";
        if ($persons < 1) $errors[] = "Number of persons must be at least 1.";
        return $errors;
    }

    // Check any overlapping booking (for Gathering)
    private function checkOverlap($date, $start, $end) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM bookings
            WHERE reservation_date = ?
              AND is_finished = 0
              AND (
                    (start_time <= ? AND end_time > ?)
                 OR (start_time < ? AND end_time >= ?)
                 OR (start_time >= ? AND end_time <= ?)
              )
        ");
        $stmt->execute([$date, $start, $start, $end, $end, $start, $end]);
        return intval($stmt->fetchColumn()) > 0;
    }

    // Check for overlapping Gathering (for Study)
    private function checkOverlapGathering($date, $start, $end) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM bookings
            WHERE reservation_date = ?
              AND is_finished = 0
              AND type = 'Gathering'
              AND (
                    (start_time <= ? AND end_time > ?)
                 OR (start_time < ? AND end_time >= ?)
                 OR (start_time >= ? AND end_time <= ?)
              )
        ");
        $stmt->execute([$date, $start, $start, $end, $end, $start, $end]);
        return intval($stmt->fetchColumn()) > 0;
    }

    // Check current total Study persons
    private function checkStudyCapacity($date, $start, $end, $newPersons) {
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
        return intval($stmt->fetchColumn()) + $newPersons;
    }
}

// ----------------------------
// Handle AJAX request for remaining seats
// ----------------------------
$bookingSystem = new BookingSystem($pdo);
if (isset($_GET['check_seats']) && $_GET['check_seats'] == 1) {
    $bookingSystem->ajaxCheckSeats($_GET['date'] ?? '', $_GET['time'] ?? '', floatval($_GET['hours'] ?? 0));
}

// ----------------------------
// Handle booking form submission
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $bookingSystem->processBooking($_POST);
    $message = $result['success'] ?? implode('<br>', $result['errors'] ?? []);
}


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Bat Cave Cafe — Booking</title>
<style>
body { background:#111; color:#eee; font-family:Arial, sans-serif; padding:20px; }
form { background:#1b1b1b; padding:18px; width:420px; border-radius:8px; }
label { display:block; margin-top:10px; color:#ccc; }
input, select { width:100%; padding:6px; margin-top:4px; border-radius:6px; background:#222; color:#fff; border:1px solid #333; }
.msg { background:#222; padding:10px; margin-bottom:10px; border-radius:6px; }
button { margin-top:12px; padding:10px 14px; border-radius:6px; border:0; background:#ffcc00; color:#000; font-weight:bold; cursor:pointer; }
</style>
</head>
<body>
<h2>Bat Cave Café — Room Reservation</h2>

<?php if (!empty($message)): ?>
  <div class="msg"><?php echo $message; ?></div>
<?php endif; ?>

<form method="POST" action="booking.php" id="booking-form">
    <label for="student_id">Student ID:</label>
    <input type="text" name="student_id" id="student_id" required>

    <label for="name">Full Name:</label>
    <input type="text" name="name" id="name" required>

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required>

    <label for="phone">Phone:</label>
    <input type="text" name="phone" id="phone" required>

    <label for="reservation_date">Date:</label>
    <input type="date" name="reservation_date" id="reservation_date" required>

    <label for="start_time">Start Time:</label>
    <input type="time" name="start_time" id="start_time" required>

    <label for="hours">Hours:</label>
    <input type="number" name="hours" id="hours" min="1" step="0.5" required>

    <label for="persons">Number of Persons:</label>
    <input type="number" name="persons" id="persons" min="1" max="20" required>

    <label for="type">Room Type:</label>
    <select name="type" id="type" required>
        <option value="Study" selected>Study</option>
        <option value="Gathering">Gathering</option>
    </select>

    <label>
        <input type="checkbox" name="projector"> Projector
    </label>
    <label>
        <input type="checkbox" name="speaker"> Speaker & Microphone
    </label>

    <p id="remaining-seats" style="font-weight:bold; color:green;"></p>

    <button type="submit" id="submit-btn">Reserve</button>
</form>


<!-- 
Javascript: "booking.js"
-->
<script src="js/booking.js"></script>
</body>
</html>

