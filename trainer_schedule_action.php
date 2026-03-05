<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$trainer_id = $_SESSION['trainer_id'];

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'complete') {
        $status = 'Completed';
        $msg = "✅ Session marked as completed!";
    } elseif ($action === 'cancel') {
        $status = 'Canceled';
        $msg = "⚠️ Session has been canceled!";
    } else {
        // Invalid action
        header("Location: trainer_schedule.php");
        exit;
    }

    // Update the schedule status
    $stmt = $con->prepare("UPDATE trainer_schedule SET status=? WHERE id=? AND trainer_id=?");
    $stmt->bind_param("sii", $status, $id, $trainer_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['msg'] = $msg;
}

header("Location: trainer_schedule.php");
exit;
?>
