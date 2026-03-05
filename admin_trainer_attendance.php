<?php
session_start();
require_once "db.php";

// Check if admin logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle manual "Auto Absent" button
if (isset($_POST['auto_absent'])) {
    $today = date("Y-m-d");

    // Mark absent only for trainers not already marked
    $con->query("
        INSERT INTO trainer_attendance (trainer_id, trainer_name, trainer_speciality, attendance_date, status)
        SELECT id, full_name, speciality, CURDATE(), 'Absent'
        FROM trainers
        WHERE id NOT IN (
            SELECT trainer_id FROM trainer_attendance WHERE DATE(attendance_date) = CURDATE()
        )
    ");

    echo "<script>alert('Unmarked trainers have been marked as Absent for today.'); window.location.href='admin_trainer_attendance.php';</script>";
    exit;
}

// Mark trainer as Present (Check-in)
if (isset($_GET['checkin'])) {
    $trainer_id = $_GET['checkin'];
    $today = date("Y-m-d");

    $check = $con->prepare("SELECT * FROM trainer_attendance WHERE trainer_id = ? AND DATE(attendance_date) = ?");
    $check->bind_param("is", $trainer_id, $today);
    $check->execute();
    $exists = $check->get_result();

    if ($exists->num_rows > 0) {
        echo "<script>alert('Trainer already marked for today.'); window.location.href='admin_trainer_attendance.php';</script>";
        exit;
    }

    $stmt = $con->prepare("
        INSERT INTO trainer_attendance (trainer_id, trainer_name, trainer_speciality, attendance_date, status, check_in_time)
        SELECT id, full_name, speciality, CURDATE(), 'Present', NOW() FROM trainers WHERE id = ?
    ");
    $stmt->bind_param("i", $trainer_id);
    $stmt->execute();

    echo "<script>alert('Trainer marked as Present.'); window.location.href='admin_trainer_attendance.php';</script>";
    exit;
}

// Handle trainer check-out
if (isset($_GET['checkout'])) {
    $id = $_GET['checkout'];
    $stmt = $con->prepare("UPDATE trainer_attendance SET check_out_time = NOW() WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<script>alert('Trainer check-out recorded successfully.'); window.location.href='admin_trainer_attendance.php';</script>";
    exit;
}

// ✅ Fetch all trainers and today’s attendance
$today = date("Y-m-d");
$query = "
    SELECT 
        t.id AS trainer_id,
        t.full_name AS trainer_name,
        t.speciality AS trainer_speciality,
        t.gender AS gender,
        ta.id AS attendance_id,
        ta.status,
        ta.check_in_time,
        ta.check_out_time,
        ta.attendance_date
    FROM trainers t
    LEFT JOIN trainer_attendance ta 
        ON t.id = ta.trainer_id AND DATE(ta.attendance_date) = '$today'
    ORDER BY t.full_name ASC
";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trainer Attendance - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #111;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            animation: fadeIn 1s ease-in;
        }

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

        h2 {
            color: #FFA500;
            text-align: center;
            margin-bottom: 20px;
            animation: slideInDown 0.8s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            background: #1c1c1c;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.5);
            margin-top: 40px;
            animation: zoomIn 0.8s ease;
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        table {
            background: #222;
            color: #fff;
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
            transition: all 0.3s ease;
        }

        table:hover {
            box-shadow: 0 0 15px rgba(255, 165, 0, 0.3);
        }

        th {
            background: #333;
            color: #FFD700;
        }

        tr {
            transition: background-color 0.3s ease;
        }

        tr:hover {
            background-color: #2a2a2a;
        }

        .btn {
            border-radius: 8px;
            font-weight: bold;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(255, 165, 0, 0.6);
        }

        .btn-warning {
            background: #FFA500;
            color: #000;
            border: none;
        }

        .btn-warning:hover {
            background: #FFD700;
        }

        .btn-success {
            background: #28a745;
            border: none;
        }

        .btn-danger {
            background: #dc3545;
            border: none;
        }

        .btn-secondary {
            background: #555;
            border: none;
        }

        .completed-row {
            background-color: #2f2f2f !important;
            opacity: 0.7;
        }

        .center-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .center-buttons button {
            margin: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Trainer Attendance Management (Admin)</h2>

        <form method="POST" onsubmit="return confirm('Mark all unmarked trainers as absent?');" class="text-center mb-3">
            <button type="submit" name="auto_absent" class="btn btn-warning px-4 py-2">
                Auto Mark Absent Trainers
            </button>
        </form>

        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trainer Name</th>
                    <th>Gender</th>
                    <th>Speciality</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $completed = ($row['status'] === 'Present' && !empty($row['check_out_time']));
                        $row_class = $completed ? "completed-row" : "";
                        ?>
                        <tr class="<?= $row_class ?>">
                            <td><?= $row['trainer_id']; ?></td>
                            <td><?= htmlspecialchars($row['trainer_name']); ?></td>
                            <td><?= htmlspecialchars($row['gender']); ?></td>
                            <td><?= htmlspecialchars($row['trainer_speciality']); ?></td>
                            <td><?= $row['attendance_date'] ?? $today; ?></td>
                            <td>
                                <?php if ($row['status'] === 'Present'): ?>
                                    <span class="badge bg-success">Present</span>
                                <?php elseif ($row['status'] === 'Absent'): ?>
                                    <span class="badge bg-danger">Absent</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Marked</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['check_in_time'] ? date("h:i A", strtotime($row['check_in_time'])) : '-'; ?></td>
                            <td><?= $row['check_out_time'] ? date("h:i A", strtotime($row['check_out_time'])) : '-'; ?></td>
                            <td>
                                <?php if ($row['status'] === 'Absent' || $row['status'] === NULL): ?>
                                    <a href="?checkin=<?= $row['trainer_id']; ?>" class="btn btn-success btn-sm">Mark Present</a>
                                <?php elseif ($row['status'] === 'Present' && empty($row['check_out_time'])): ?>
                                    <a href="?checkout=<?= $row['attendance_id']; ?>" class="btn btn-danger btn-sm">Check-Out</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Completed</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-muted">No trainers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="center-buttons">
            <button class="btn btn-warning px-4 py-2" onclick="window.location.href='admin.php'">
                ⬅ Back to Admin Dashboard
            </button>
        </div>
    </div>

</body>

</html>