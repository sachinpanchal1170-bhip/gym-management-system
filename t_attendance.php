<?php
session_start();
require_once "db.php";
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}
$trainer_id = $_SESSION['trainer_id'];
$attendance = $con->query("SELECT * FROM attendance WHERE trainer_id = $trainer_id ORDER BY date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer Attendance</title>
    <style>
        body { background: #111; color: #fff; font-family: Arial, sans-serif; }
        .container { max-width: 700px; margin: 40px auto; background: #222; border-radius: 10px; padding: 30px; }
        h2 { color: #FFA500; }
        table { width:100%; border-collapse:collapse; margin-top:20px;}
        th, td { padding:10px; border:1px solid #444; text-align:center;}
        th { background:#333; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Attendance</h2>
        <table>
            <tr><th>Date</th><th>Status</th></tr>
            <?php while($row = $attendance->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <a href="trainer.php" style="color:#FFA500;">Back to Dashboard</a>
    </div>
</body>
</html>