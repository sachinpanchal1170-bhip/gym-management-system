<?php
session_name("trainer_session");
session_start();
require_once "db.php";

if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$trainer_id = $_SESSION['trainer_id'];

/* Trainer Info */
$stmt = $con->prepare("SELECT full_name, email, phone, speciality, experience FROM trainers WHERE id=?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$trainer = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* Latest Notice */
$notices = $con->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 1");
$latestNotice = ($notices && $notices->num_rows > 0) ? $notices->fetch_assoc() : null;

/* Attendance */
$attendance = $con->query("
    SELECT attendance_date, status 
    FROM trainer_attendance 
    WHERE trainer_id=$trainer_id 
    ORDER BY attendance_date DESC LIMIT 5
");

/* Schedule */
$schedules = $con->query("
    SELECT date, time, activity 
    FROM trainer_schedule 
    WHERE trainer_id=$trainer_id 
    ORDER BY date DESC LIMIT 5
");

/* Diet Charts */
$diets = $con->query("
    SELECT d.assigned_at, u.full_name 
    FROM diet_charts d 
    JOIN users u ON d.user_id=u.user_id 
    WHERE d.trainer_id=$trainer_id 
    ORDER BY d.assigned_at DESC LIMIT 5
");

/* Salary */
$salary_data = $con->query("
    SELECT month_year,total_salary,paid_status 
    FROM trainer_salary 
    WHERE trainer_id=$trainer_id 
    ORDER BY id DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Trainer Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #0f0f12;
            color: #fff;
        }

        /* NAVBAR */
        .navbar {
            background: #141418;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #222;
        }

        .navbar a {
            color: #FFA500;
            margin-left: 25px;
            text-decoration: none;
            font-weight: 600;
        }

        .clock {
            color: #FFA500;
            font-weight: 600;
        }

        /* CONTAINER */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        /* SECTION BLOCK */
        .section-block {
            background: #1a1a1f;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 35px;
            border: 1px solid #222;
            animation: fadeUp .7s ease;
        }

        .section-block h3 {
            margin-top: 0;
            color: #FFA500;
        }

        .section-block button,
        .section-block a {
            margin-top: 15px;
            display: inline-block;
            background: #FFA500;
            color: #000;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        /* LIST */
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            padding: 10px 0;
            border-bottom: 1px solid #2a2a2f;
        }

        /* STATUS */
        .paid {
            color: #00ff88;
            font-weight: bold;
        }

        .pending {
            color: #ff4d4d;
            font-weight: bold;
        }

        /* QR */
        .qr-block {
            background: linear-gradient(135deg, #FFA500, #ffb733);
            color: #000;
        }

        .qr-block h3 {
            color: #000;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #1a1a1f;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
        }

        .close-btn {
            float: right;
            cursor: pointer;
            color: #FFA500;
        }

        /* ANIMATION */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="navbar">
        <div><strong style="color:#FFA500;">Trainer Dashboard</strong></div>
        <div class="clock" id="liveClock"></div>
        <div>
            <a href="trainer_schedule.php">Schedule</a>
            <a href="diet_charts.php">Diet Charts</a>
            <a href="trainer_attendance.php">Attendance</a>
            <a href="trainer_logout.php">Logout</a>
        </div>
    </div>

    <div class="container">

        <!-- PROFILE -->
        <div class="section-block">
            <h3>Welcome, <?= htmlspecialchars($trainer['full_name']); ?> 👋</h3>
            <p>Email: <?= htmlspecialchars($trainer['email']); ?></p>
            <p>Phone: <?= htmlspecialchars($trainer['phone']); ?></p>
            <p>Speciality: <?= htmlspecialchars($trainer['speciality']); ?> | Experience: <?= htmlspecialchars($trainer['experience']); ?> years</p>
        </div>

        <!-- QR -->
        <div class="section-block qr-block">
            <h3>Scan Member QR</h3>
            <p>Mark attendance instantly using QR scanner.</p>
            <a href="scan_qr.php">Open Scanner</a>
        </div>

        <!-- NOTICE -->
        <?php if ($latestNotice): ?>
            <div class="section-block">
                <h3>Latest Notice</h3>
                <p><?= htmlspecialchars($latestNotice['notice_text']); ?></p>
                <button onclick="openModal()">View Notice</button>
            </div>
        <?php endif; ?>

        <!-- ATTENDANCE -->
        <div class="section-block">
            <h3>Recent Attendance</h3>
            <ul>
                <?php while ($row = $attendance->fetch_assoc()): ?>
                    <li><?= $row['attendance_date']; ?> — <?= ucfirst($row['status']); ?></li>
                <?php endwhile; ?>
            </ul>
            <a href="trainer_attendance.php">View Full Attendance</a>
        </div>

        <!-- SCHEDULE -->
        <div class="section-block">
            <h3>Upcoming Schedule</h3>
            <ul>
                <?php while ($row = $schedules->fetch_assoc()): ?>
                    <li><?= $row['date']; ?> <?= $row['time']; ?> — <?= htmlspecialchars($row['activity']); ?></li>
                <?php endwhile; ?>
            </ul>
            <a href="trainer_schedule.php">Manage Schedule</a>
        </div>

        <!-- DIET -->
        <div class="section-block">
            <h3>Recent Diet Charts</h3>
            <ul>
                <?php while ($d = $diets->fetch_assoc()): ?>
                    <li><?= htmlspecialchars($d['assigned_at']); ?> — <?= htmlspecialchars($d['full_name']); ?></li>
                <?php endwhile; ?>
            </ul>
            <a href="diet_charts.php">View Diet Charts</a>
        </div>

        <!-- SALARY -->
        <div class="section-block">
            <h3>Salary Records</h3>
            <ul>
                <?php while ($sal = $salary_data->fetch_assoc()): ?>
                    <li>
                        <?= $sal['month_year']; ?> —
                        ₹<?= number_format($sal['total_salary'], 2); ?>
                        <?php if ($sal['paid_status'] == 1): ?>
                            <span class="paid">Paid</span>
                        <?php else: ?>
                            <span class="pending">Pending</span>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

    </div>

    <?php if ($latestNotice): ?>
        <div class="modal" id="noticeModal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">✖</span>
                <h3 style="color:#FFA500;">Notice</h3>
                <p><?= htmlspecialchars($latestNotice['notice_text']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <script>
        /* LIVE CLOCK */
        function updateClock() {
            document.getElementById("liveClock").innerHTML =
                new Date().toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        /* MODAL */
        function openModal() {
            document.getElementById("noticeModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("noticeModal").style.display = "none";
        }
    </script>

</body>

</html>