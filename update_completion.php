<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $attendance_date = $_POST['attendance_date'];

    $stmt = $con->prepare("UPDATE attendance SET completed_time = NOW() WHERE user_id = ? AND attendance_date = ?");
    $stmt->bind_param("is", $user_id, $attendance_date);

    $stmt->execute();
    $stmt->close();

    header("Location: trainer_schedule.php");
    exit;
}
?>
