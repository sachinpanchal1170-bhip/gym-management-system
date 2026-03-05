<?php
session_start();
require_once __DIR__ . '/db.php';
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

/* ================= DATE FILTER ================= */
$selectedDate = $_GET['date'] ?? '';

$where = "";
if (!empty($selectedDate)) {
    $where = "WHERE DATE(a.attendance_date) = '$selectedDate'";
}

/* ================= FETCH RECORDS ================= */
$sql = "
    SELECT u.full_name, u.role, a.status, a.attendance_date
    FROM attendance a
    JOIN users u ON a.user_id = u.user_id
    $where
    ORDER BY a.attendance_date DESC
";
$result = $con->query($sql);

/* ================= ATTENDANCE PERCENTAGE ================= */
$totalUsers = $con->query("SELECT COUNT(*) as total FROM users WHERE role IN ('user','trainer')")->fetch_assoc()['total'];

$totalAttendance = $con->query("
    SELECT COUNT(DISTINCT user_id) as total 
    FROM attendance 
    WHERE DATE(attendance_date) = CURDATE()
")->fetch_assoc()['total'];

$percentage = ($totalUsers > 0) ? round(($totalAttendance / $totalUsers) * 100) : 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Attendance Records</title>

    <style>
        body {
            background: linear-gradient(135deg, #000, #1a1a1a);
            color: #eee;
            font-family: Arial, sans-serif;
            max-width: 1100px;
            margin: auto;
            padding: 20px;
            min-height: 100vh;
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            text-align: center;
            color: #ffa500;
            margin-bottom: 10px;
            animation: slideDown 0.8s ease;
        }

        /* FILTER */
        .filter-box {
            text-align: center;
            margin: 20px 0;
            animation: fadeInUp 1s ease;
        }

        input[type="date"] {
            padding: 8px;
            border-radius: 6px;
            border: none;
        }

        .filter-btn {
            padding: 8px 15px;
            background: #ffa500;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #1c1c1c;
            border-radius: 10px;
            overflow: hidden;
            animation: fadeInUp 1.2s ease;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #333;
        }

        th {
            background: #222;
            color: #ffa500;
        }

        tr {
            transition: 0.3s ease;
        }

        tr:hover {
            background: rgba(255, 165, 0, 0.08);
        }

        .present {
            color: #4cd964;
            font-weight: bold;
        }

        .absent {
            color: #ff3b30;
            font-weight: bold;
        }

        /* PERCENTAGE CARD */
        .stats {
            margin-top: 20px;
            background: #1c1c1c;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            animation: fadeInUp 1.3s ease;
        }

        .progress-bar {
            width: 100%;
            background: #333;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
        }

        .progress-fill {
            height: 20px;
            background: linear-gradient(90deg, #4cd964, #28a745);
            transition: width 1.5s ease-in-out;
        }

        button {
            margin-top: 20px;
            background: linear-gradient(90deg, #ffb400, #ff9800);
            border: none;
            padding: 8px 10px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: scale(1.05);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
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

    <h1>Attendance Records</h1>

    <!-- DATE FILTER -->
    <div class="filter-box">
        <form method="GET">
            <input type="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
            <button type="submit" class="filter-btn">Filter</button>
            <button type="button" onclick="window.location='admin_user_attendance.php'">Reset</button>
        </form>
    </div>

    <!-- ATTENDANCE STATS -->
    <div class="stats">
        <h2>Today's Attendance</h2>
        <p><?= $percentage ?>% Members Present</p>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $percentage ?>%;"></div>
        </div>
    </div>

    <!-- RECORD TABLE -->
    <?php if ($result->num_rows === 0): ?>
        <div style="text-align:center; color:red; margin-top:20px;">No attendance records found.</div>
    <?php else: ?>
        <table>
            <tr>
                <th>Date & Time</th>
                <th>Name</th>
                <th>Role</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['attendance_date']) ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= ucfirst($row['role']) ?></td>
                    <td class="<?= strtolower($row['status']) ?>">
                        <?= ucfirst($row['status']) ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>

    <button onclick="window.location.href='admin.php'">Back</button>

</body>

</html>