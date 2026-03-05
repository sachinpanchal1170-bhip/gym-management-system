<?php
require_once "db.php";
date_default_timezone_set('Asia/Kolkata');

if (!isset($_POST['qr_value'])) {
    exit("Invalid request.");
}

$qr = $_POST['qr_value'];
$parts = explode("_", $qr);

if (count($parts) !== 4) {
    exit("<span class='error'>Invalid QR Format</span>");
}

$user_id = $parts[2];
$qr_date = $parts[3];
$today = date('Y-m-d');

if ($qr_date !== $today) {
    exit("<span class='error'>QR Expired</span>");
}

$stmt = $con->prepare("SELECT id FROM attendance WHERE user_id=? AND DATE(attendance_date)=?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    exit("<span class='error'>Attendance Already Marked</span>");
}
$stmt->close();

$status = "present";
$stmt = $con->prepare("INSERT INTO attendance (user_id, status, attendance_date, recorded_at) VALUES (?, ?, NOW(), NOW())");
$stmt->bind_param("is", $user_id, $status);
$stmt->execute();
$stmt->close();

echo "<span class='success'>Attendance Marked Successfully ✅</span>";
?>