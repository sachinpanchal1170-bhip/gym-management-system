<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$sql = "SELECT w.workout_date, w.workout_time, w.workout_type, w.duration_minutes, w.calories_burned, w.notes, u.full_name 
        FROM workouts w
        JOIN users u ON w.user_id = u.user_id
        ORDER BY w.workout_date DESC, w.workout_time DESC";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Workout Tracker</title>
    <style>
        body {
            background-color: #111;
            color: #eee;
            font-family: 'Poppins', Arial, sans-serif;
            padding: 30px;
            margin: 0;
            animation: fadeInBody 0.8s ease-in-out;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #1c1c1c;
            border-radius: 12px;
            padding: 25px 30px;
            box-shadow: 0 8px 25px rgba(255, 165, 0, 0.15);
            animation: scaleIn 0.8s ease-in-out;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.97);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        h1 {
            color: #ffa500;
            text-align: center;
            margin-bottom: 20px;
            animation: slideDown 0.8s ease-in-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-links {
            text-align: center;
            margin: 20px 0 0;
            animation: fadeUp 1s ease-in-out;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-links a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 18px;
            background: #ffa500;
            color: #000;
            font-weight: bold;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 0 8px rgba(255, 165, 0, 0.4);
        }

        .nav-links a:hover {
            background: #e69500;
            transform: scale(1.08);
            box-shadow: 0 0 15px rgba(255, 165, 0, 0.6);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 14px;
            border-radius: 10px;
            overflow: hidden;
            animation: fadeInTable 0.9s ease-in-out;
        }

        @keyframes fadeInTable {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        th,
        td {
            padding: 12px 15px;
            border: 1px solid #444;
            text-align: left;
            transition: background 0.3s ease, color 0.3s ease;
        }

        th {
            background-color: #222;
            color: #e0a300;
            font-size: 15px;
        }

        tbody tr:nth-child(odd) {
            background-color: #252525;
        }

        tbody tr:hover {
            background-color: #333;
            transform: scale(1.01);
            transition: all 0.25s ease;
        }

        /* Animate rows appearing one by one */
        tbody tr {
            opacity: 0;
            animation: fadeInRow 0.5s ease forwards;
        }

        @keyframes fadeInRow {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        tbody tr:nth-child(1) {
            animation-delay: 0.1s;
        }

        tbody tr:nth-child(2) {
            animation-delay: 0.2s;
        }

        tbody tr:nth-child(3) {
            animation-delay: 0.3s;
        }

        tbody tr:nth-child(4) {
            animation-delay: 0.4s;
        }

        tbody tr:nth-child(5) {
            animation-delay: 0.5s;
        }

        tbody tr:nth-child(6) {
            animation-delay: 0.6s;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Admin Workout Tracker - User Activity Log</h1>

        <?php if ($result->num_rows === 0): ?>
            <p style="text-align:center; animation: fadeUp 1s ease;">No workout activity found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Workout Type</th>
                        <th>Duration</th>
                        <th>Calories Burned</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']); ?></td>
                            <td><?= htmlspecialchars($row['workout_date']); ?></td>
                            <td>
                                <?php
                                $time = DateTime::createFromFormat('H:i:s', $row['workout_time']);
                                echo $time ? htmlspecialchars($time->format('h:i A')) : htmlspecialchars($row['workout_time']);
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['workout_type']); ?></td>
                            <td>
                                <?php
                                $minutes = $row['duration_minutes'] ?? 0;
                                $hours = floor($minutes / 60);
                                $mins  = $minutes % 60;
                                $durationTime = new DateTime();
                                $durationTime->setTime($hours % 24, $mins);
                                echo htmlspecialchars($durationTime->format('H:i'));
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['calories_burned']); ?></td>
                            <td><?= htmlspecialchars($row['notes']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="nav-links">
                <a href="admin.php">⬅ Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>