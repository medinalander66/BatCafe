<?php
require_once 'db/connect.php';

if (isset($_GET['finish_id'])) {
  $id = $_GET['finish_id'];
  $stmt = $pdo->prepare("UPDATE bookings SET is_finished = 1 WHERE id = ?");
  $stmt->execute([$id]);
  echo "<p>✅ Booking #$id marked as finished.</p>";
}

// Fetch active bookings
$stmt = $pdo->query("SELECT * FROM bookings WHERE is_finished = 0 ORDER BY reservation_date, start_time");
$bookings = $stmt->fetchAll();
?>

<h2>Active Room Bookings</h2>
<table border="1" cellpadding="6">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Date</th>
    <th>Start</th>
    <th>End</th>
    <th>Action</th>
  </tr>
  <?php foreach ($bookings as $b): ?>
    <tr>
      <td><?= $b['id'] ?></td>
      <td><?= htmlspecialchars($b['name']) ?></td>
      <td><?= $b['reservation_date'] ?></td>
      <td><?= $b['start_time'] ?></td>
      <td><?= $b['end_time'] ?></td>
      <td><a href="?finish_id=<?= $b['id'] ?>">Mark as Finished</a></td>
    </tr>
  <?php endforeach; ?>
</table>
