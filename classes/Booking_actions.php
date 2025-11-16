<?php
require_once "db/connect.php"; 
require_once "classes/BookingManager.php";

$bookingManager = new BookingManager($conn,$db);

if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    switch ($action) {
        case "confirm":
            $bookingManager->updateStatus($id, "confirmed");
            break;

        case "cancel":
            $bookingManager->updateStatus($id, "cancelled");
            break;

        case "revert":
            $bookingManager->updateStatus($id, "pending");
            break;
    }
}

header("Location: ../admin_booking.php");
exit;
