<?php
session_start();
require_once __DIR__ . '/db.php';

date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

$currentDate = date("Y-m-d");

/* CHECK TODAY ATTENDANCE */

$stmt = $con->prepare("
SELECT status,attendance_date 
FROM attendance 
WHERE user_id=? AND DATE(attendance_date)=?
");

$stmt->bind_param("is", $user_id, $currentDate);
$stmt->execute();
$res = $stmt->get_result();

$attendanceExists = false;
$status = "";
$attendanceTime = "";

if ($row = $res->fetch_assoc()) {
    $attendanceExists = true;
    $status = $row['status'];
    $attendanceTime = $row['attendance_date'];
}

/* HISTORY */

$stmt = $con->prepare("
SELECT attendance_date,status
FROM attendance
WHERE user_id=?
ORDER BY attendance_date DESC
LIMIT 30
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();

/* STATS */

$total = $con->query("SELECT COUNT(*) c FROM attendance WHERE user_id=$user_id")->fetch_assoc()['c'];

$present = $con->query("SELECT COUNT(*) c FROM attendance WHERE user_id=$user_id AND status='present'")->fetch_assoc()['c'];

$absent = $con->query("SELECT COUNT(*) c FROM attendance WHERE user_id=$user_id AND status='absent'")->fetch_assoc()['c'];

$percentage = $total ? round(($present / $total) * 100) : 0;

?>

<!DOCTYPE html>
<html>

<head>

    <title>My Attendance</title>

    <style>
        body {
            background: radial-gradient(circle at top left, #0d0d0d, #111);
            font-family: Poppins;
            color: white;
            padding: 40px;
            max-width: 750px;
            margin: auto;
            animation: fadeBody .8s ease;
        }

        @keyframes fadeBody {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            text-align: center;
            color: #ffb700;
            margin-bottom: 10px;
            animation: fadeDown .8s ease;
        }

        .clock {
            text-align: center;
            margin-bottom: 35px;
            font-size: 22px;
            font-weight: 600;
        }

        .card {
            background: #1c1c1c;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 0 18px rgba(255, 165, 0, .1);
            transition: .3s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(255, 165, 0, .2);
        }

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            row-gap: 22px;
            column-gap: 40px;
            font-size: 15px;
        }

        .label {
            color: #ffb700;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
        }

        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .present {
            color: #00ff6a;
            border: 1px solid #00ff6a;
        }

        .absent {
            color: #ff4d4d;
            border: 1px solid #ff4d4d;
        }

        .card-title {
            text-align: center;
            color: #ffb700;
            font-size: 18px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        th {
            background: #2c2c2c;
            color: #ffb700;
            padding: 10px;
        }

        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #333;
        }

        tr:hover td {
            background: #242424;
        }

        .status-present {
            color: #00ff6a;
            font-weight: 600;
        }

        .status-absent {
            color: #ff4d4d;
            font-weight: 600;
        }

        button {
            width: 100%;
            background: linear-gradient(135deg, #ffb700, #ffa500);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: .3s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 165, 0, .4);
        }
    </style>

</head>

<body>

    <h1>My Attendance</h1>

    <div class="clock" id="clock"></div>

    <!-- INFO CARD -->

    <div class="card">

        <div class="info">

            <div>
                <span class="label">Name</span>
                <?= htmlspecialchars($full_name) ?>
            </div>

            <div>
                <span class="label">Date</span>
                <?= $currentDate ?>
            </div>

            <div>
                <span class="label">Status</span>

                <?php if ($attendanceExists): ?>

                    <span class="badge <?= $status ?>">
                        <?= ucfirst($status) ?>
                    </span>

                <?php else: ?>

                    <span class="badge absent">
                        Not Marked
                    </span>

                <?php endif; ?>

            </div>

            <div>
                <span class="label">Time</span>

                <?php
                if ($attendanceExists) {
                    echo date("h:i:s A", strtotime($attendanceTime));
                } else {
                    echo "--";
                }
                ?>

            </div>

        </div>

    </div>

    <!-- HISTORY -->

    <div class="card">

        <div class="card-title">Attendance History</div>

        <table>

            <thead>

                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>

            </thead>

            <tbody>

                <?php while ($row = $history->fetch_assoc()): ?>

                    <tr>

                        <td><?= date("Y-m-d", strtotime($row['attendance_date'])) ?></td>

                        <td><?= date("h:i:s A", strtotime($row['attendance_date'])) ?></td>

                        <td class="status-<?= $row['status'] ?>">

                            <?= ucfirst($row['status']) ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

    <!-- STATS -->

    <div class="card">

        <div class="card-title">Attendance Statistics</div>

        <table>

            <tr>
                <th>Total Days</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Attendance %</th>
            </tr>

            <tr>

                <td><?= $total ?></td>

                <td style="color:#00ff6a;font-weight:600">
                    <?= $present ?>
                </td>

                <td style="color:#ff4d4d;font-weight:600">
                    <?= $absent ?>
                </td>

                <td><?= $percentage ?>%</td>

            </tr>

        </table>

    </div>

    <button onclick="window.location.href='index.php'">
        Back
    </button>

    <script>
        /* LIVE CLOCK */

        function updateClock() {

            let now = new Date();

            let time = now.toLocaleTimeString();

            document.getElementById("clock").innerText = time;

        }

        setInterval(updateClock, 1000);

        updateClock();

        /* TABLE ROW ANIMATION */

        const rows = document.querySelectorAll("tbody tr");

        rows.forEach((row, i) => {

            row.style.opacity = "0";
            row.style.transform = "translateY(10px)";

            setTimeout(() => {

                row.style.transition = "all .4s ease";
                row.style.opacity = "1";
                row.style.transform = "translateY(0)";

            }, i * 120);

        });
    </script>

</body>

</html>