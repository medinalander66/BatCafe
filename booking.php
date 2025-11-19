<?php
// booking.php
declare(strict_types=1);

session_start();
date_default_timezone_set('Asia/Manila');

$config = require __DIR__ . '/config.php';

/*
 * Autoload classes from classes/ or src/
 */
spl_autoload_register(function ($class) {
  $paths = [
    __DIR__ . '/classes/' . $class . '.php',
    __DIR__ . '/src/' . $class . '.php',
  ];
  foreach ($paths as $file) {
    if (file_exists($file)) {
      require $file;
      return;
    }
  }
});

/* === Bootstrap DB connection === */
try {
  $dbConf = $config->db;
  $dsn = "mysql:host={$dbConf->host};dbname={$dbConf->dbname};charset={$dbConf->charset}";
  $pdo = new PDO($dsn, $dbConf->user, $dbConf->pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo "DB Connection error: " . htmlspecialchars($e->getMessage());
  exit;
}

/* === Instantiate repository/service objects === */
$EquipmentRepo = new EquipmentRepository($pdo);
$ReservationService = new ReservationService($pdo, $EquipmentRepo);

/* load room types and equipment for rendering */
$roomTypesStmt = $pdo->query("SELECT id, code, name, rate_per_hour FROM room_types ORDER BY id");
$roomTypes = $roomTypesStmt->fetchAll(PDO::FETCH_ASSOC);

$equipments = $EquipmentRepo->all();

/* === Route AJAX actions (POST) === */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
  header('Content-Type: application/json; charset=utf-8');

  $action = $_POST['action'];

  try {
    if ($action === 'calculate') {
      $equipment_codes = [];
      if (!empty($_POST['equipment_codes']) && is_array($_POST['equipment_codes'])) {
        $equipment_codes = $_POST['equipment_codes'];
      } elseif (!empty($_POST['equipment']) && is_array($_POST['equipment'])) {
        $equipment_codes = $_POST['equipment'];
      }

      $estimate = $ReservationService->calculateEstimate([
        'hours' => (float) ($_POST['hours'] ?? 0),
        'persons' => (int) ($_POST['persons'] ?? 1),
        'room_type_id' => (int) ($_POST['room_type_id'] ?? 0),
        'equipment_codes' => $equipment_codes,
      ]);

      echo json_encode([
        'status' => 'ok',
        'data' => [
          'hourly_fee' => $estimate['hourly_fee'],
          'person_fee' => $estimate['person_fee'],
          'equipment_fee' => $estimate['equipment_fee'],
          'minimum_fee' => $estimate['minimum_fee'],
          'total_fee' => $estimate['total_fee']
        ]
      ]);
      exit;
    }

    if ($action === 'submit') {
      // Build input for createReservation
      $input = [
        'room_type_id' => (int) ($_POST['room_type_id'] ?? 0),
        'persons' => (int) ($_POST['persons'] ?? 1),
        'start_time' => trim($_POST['start_time'] ?? ''),
        'year' => (int) ($_POST['year'] ?? 0),
        'month' => (int) ($_POST['month'] ?? 0),
        'day' => (int) ($_POST['day'] ?? 0),
        'hours' => (float) ($_POST['hours'] ?? 0),
        'name' => trim($_POST['name'] ?? ''),
        'student_id' => trim($_POST['student_id'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        // equipment[] optional
        'equipment' => is_array($_POST['equipment']) ? $_POST['equipment'] : (is_array($_POST['equipment_codes']) ? $_POST['equipment_codes'] : []),
      ];

      $createResult = $ReservationService->createReservation($input);

      echo json_encode(['status' => 'ok', 'data' => $createResult]);
      exit;
    }

    throw new RuntimeException('Unknown action');
  } catch (ValidationException $ve) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'errors' => $ve->getErrors()]);
    exit;
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
  }
}

/* === GET: render booking form (the HTML you supplied, slightly embedded) === */

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Bat Cave Café — Room Reservation</title>
  <script src="/assets/js/script.js"></script>
  <link rel="stylesheet" href="assets/css/globalStyles.css">
  <link rel="stylesheet" href="assets/css/booking.css">
  <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>

<body>

  <!-- 🔹 Navigation Bar -->
  <script src="assets/js/navbar.js"></script>
  <!-- 🔹 Page Header -->
  <header>
    <h2 class="page-title">Book a Room</h2>
    <p class="subtitle">Reserve your private study space.</p>
    <p class="hours">Operating Hours: 1:00 PM – 1:00 AM</p>
  </header>

  <main>
    <div class="booking-container card">
      <?php if (!empty($message)): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <form method="POST" action="booking.php" id="booking-form" onsubmit="return false;">
        <input type="hidden" name="action" value="">
        <div class="two-column-form">
          <label for="type">Room Type *</label>
          <select name="room_type_id" id="room_type" required>
            <?php foreach ($roomTypes as $rt): ?>
              <option value="<?= (int)$rt['id'] ?>"><?= htmlspecialchars($rt['name']) ?> (₱<?= number_format((float)$rt['rate_per_hour'], 2) ?>/hr)</option>
            <?php endforeach; ?>
          </select>
          <!-- ===== Column 1 ===== -->
          <div class="column">
            <label for="persons">Number of Persons *</label>
            <input type="number" placeholder="Enter Person Quantity" name="persons" id="persons" min="1" max="20" required>

            <label for="start_time">Time Slot *</label>
            <div class="time-slot-wrapper">
              <button type="button" class="adjust-btn" id="decrease-time">-</button>

              <input type="time" name="start_time" id="start_time" required value="13:00" min="13:00" max="01:00">

              <button type="button" class="adjust-btn" id="increase-time">+</button>
            </div>

            <label for="student_id">Student ID *</label>
            <input type="text" name="student_id" id="student_id" placeholder="Enter Student ID">

            <label for="email">Email *</label>
            <input type="email" name="email" id="email" placeholder="Enter Email" required>
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
              <?php
              $durations = [1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 6, 6.5, 7, 7.5, 8, 8.5, 9, 9.5, 10, 10.5, 11, 11.5, 12];
              foreach ($durations as $d) {
                echo '<option value="' . htmlspecialchars((string)$d) . '">' . $d . '</option>';
              }
              ?>
            </select>

            <label for="name">Full Name *</label>
            <input type="text" placeholder="Enter Fulll Name" name="name" id="name" required>

            <label for="phone">Phone *</label>
            <input type="text" name="phone" id="phone" placeholder="Enter Phone Number" required>

          </div>
        </div>
        <div class="remaining-seats-wrapper">
          <img src="assets/images/icons/users.png" alt="Users" class="icon" id="users-icon">
          <p id="remaining-seats"></p>
        </div>

        <div class="equipment-wrapper">
          <label>Rental Equipment (Optional):</label>
          <div class="column-equipment">
            <?php
            // Map equipment codes to icons
            $equipmentIcons = [
              'PROJECTOR'   => 'assets/images/icons/projector-white.png',
              'SPEAKER_MIKE' => 'assets/images/icons/microphone-white.png',
              // add more as needed
            ];
            ?>

            <?php foreach ($equipments as $eq): ?>
              <?php
              $iconSrc = $equipmentIcons[$eq['code']] ?? null;
              ?>
              <label class="equipment-label">
                <input type="checkbox" hidden name="equipment[]" value="<?= htmlspecialchars($eq['code']) ?>">
                <span class="equipment-btn" data-code="<?= htmlspecialchars($eq['code']) ?>">
                  <span class="icon"><?php if ($iconSrc): ?><img src="<?= $iconSrc ?>" alt="icon"><?php endif; ?></span>
                  <?= htmlspecialchars($eq['name']) ?>
                  <span class="price">₱<?= number_format((float)$eq['rate_per_hour'], 2) ?>/hr</span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="reserve-calculate-wrapper">
          <div class="column">
            <button type="button" id="calculate-btn" class="rc-btn">Calculate Total</button>
          </div>
          <div class="column">
            <button type="button" id="submit-btn" class="rc-btn">Reserve</button>
          </div>

          <div id="cost-breakdown-wrapper" class="cost-breakdown-wrapper" style="display:none;">
            <div class="cost-breakdown-column">
              <h3>Cost Breakdown</h3>
              <p>Room Fee (per hour)</p>
              <p>Person Fee</p>
              <p>Equipment Fee</p>
              <hr>
              <p id="total-estimated-cost-p">Total Estimated Cost</p>
            </div>
            <div class="cost-breakdown-column numbers-column">
              <p id="room-fee-val">₱0.00</p>
              <p id="person-fee-val">₱0.00</p>
              <p id="equipment-fee-val">₱0.00</p>
              <hr>
              <p id="total-fee-val">₱0.00</p>
            </div>
          </div>
        </div>
      </form>
    </div>
  </main>
  <script src="assets/js/booking.js" defer></script>
  <script src="assets/js/footer.js" defer></script>
  <script src="assets/js/theme-toggle.js" defer></script>
  <script src="assets/js/script.js" defer></script>

</body>

</html>