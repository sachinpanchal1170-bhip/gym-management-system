<?php
session_name("trainer_session");
session_start();

require_once "db.php";

if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$trainer_id = $_SESSION['trainer_id'];

$stmt = $con->prepare("SELECT full_name FROM trainers WHERE id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer = $result->fetch_assoc();
$stmt->close();

$filterDate = $_GET['date'] ?? '';

$query = "SELECT * FROM trainer_attendance WHERE trainer_id = ?";
if (!empty($filterDate)) {
    $query .= " AND attendance_date = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $trainer_id, $filterDate);
} else {
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $trainer_id);
}

$stmt->execute();
$attendance = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trainer Attendance</title>

    <style>
        body {
            background: #000;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        /* ============================
           NAVBAR
        ============================ */
        .navbar {
            background: #1c1c1c;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(255, 165, 0, 0.2);
        }

        .navbar h2 {
            color: #FFA500;
            margin: 0;
        }

        .navbar a {
            margin-left: 20px;
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .navbar a:hover {
            color: #fff;
            transform: translateY(-2px);
        }

        /* ============================
           MAIN CONTAINER
        ============================ */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: #1c1c1c;
            padding: 30px;
            border-radius: 10px;
            animation: fadeIn 0.8s ease;
        }

        /* Fade animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================
           TABLE
        ============================ */
        table {
            width: 100%;
            background: #222;
            border-radius: 10px;
            border-collapse: collapse;
            overflow: hidden;
            animation: fadeIn 1s ease;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #444;
            text-align: center;
        }

        th {
            background: #FFA500;
            color: #000;
        }

        tr:hover {
            background: #333;
            transition: 0.3s ease;
        }

        /* Status Colors */
        .status-present {
            color: #4CAF50;
            font-weight: bold;
        }

        .status-absent {
            color: #F44336;
            font-weight: bold;
        }

        .status-leave {
            color: #FFEB3B;
            font-weight: bold;
        }

        /* ============================
           BUTTONS
        ============================ */
        .back-btn {
            display: block;
            width: 220px;
            margin: 30px auto 0;
            background: #FFA500;
            color: #000;
            text-align: center;
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .back-btn:hover {
            background: #fff;
            transform: scale(1.05);
        }

        /* Filter section */
        .filter-box {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter-box input[type="date"] {
            padding: 10px;
            border-radius: 5px;
            border: none;
            background: #333;
            color: #fff;
        }

        .filter-btn {
            padding: 10px 18px;
            margin-left: 10px;
            background: #FFA500;
            color: #000;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .filter-btn:hover {
            background: #fff;
            transform: scale(1.05);
        }

        .reset-btn {
            padding: 10px 18px;
            background: #F44336;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .reset-btn:hover {
            background: #fff;
            color: #000;
            transform: scale(1.05);
        }
    </style>

</head>

<body>

    <div class="navbar">
        <h2>Trainer Dashboard</h2>
        <div>
            <a href="trainer.php">Home</a>
            <a href="trainer_schedule.php">Schedule</a>
            <a href="diet_charts.php">Diet Charts</a>
            <a href="trainer_attendance.php">Attendance</a>
            <a href="t_notice.php">Notices</a>
            <a href="trainer_logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h3>Attendance Record - <?= htmlspecialchars($trainer['full_name']) ?></h3>

        <!-- FILTER SECTION -->
        <div class="filter-box">
            <form method="GET">
                <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">
                <button class="filter-btn" type="submit">Filter</button>
                <button class="reset-btn" type="button" onclick="window.location='trainer_attendance.php'">Reset</button>
            </form>
        </div>

        <!-- TABLE -->
        <table>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Check-In</th>
                <th>Check-Out</th>
            </tr>

            <?php if ($attendance->num_rows > 0): ?>
                <?php while ($row = $attendance->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['attendance_date'] ?></td>
                        <td class="status-<?= strtolower($row['status']) ?>">
                            <?= $row['status'] ?>
                        </td>
                        <td><?= $row['check_in_time'] ?? '-' ?></td>
                        <td><?= $row['check_out_time'] ?? '-' ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No attendance records found.</td>
                </tr>
            <?php endif; ?>
        </table>

        <a href="trainer.php" class="back-btn">← Back to Dashboard</a>
    </div>

</body>

</html>