<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch Male Sessions
$maleSessions = $con->query("
    SELECT ts.*, 
           t.full_name AS trainer_name, 
           t.gender AS trainer_gender,
           u.full_name AS user_name, 
           u.gender AS user_gender
    FROM trainer_schedule ts
    LEFT JOIN trainers t ON ts.trainer_id = t.id
    LEFT JOIN users u ON ts.user_id = u.user_id
    WHERE u.gender = 'male'
    ORDER BY ts.date DESC, ts.time ASC
");

// Fetch Female Sessions
$femaleSessions = $con->query("
    SELECT ts.*, 
           t.full_name AS trainer_name, 
           t.gender AS trainer_gender,
           u.full_name AS user_name, 
           u.gender AS user_gender
    FROM trainer_schedule ts
    LEFT JOIN trainers t ON ts.trainer_id = t.id
    LEFT JOIN users u ON ts.user_id = u.user_id
    WHERE u.gender = 'female'
    ORDER BY ts.date DESC, ts.time ASC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - View Sessions</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #0e0e0e;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            animation: fadeInBody 0.8s ease-in;
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
            background: #1a1a1a;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            padding: 30px;
            margin-top: 40px;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(40px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h3 {
            text-align: center;
            color: #FFD700;
            font-weight: 600;
            margin-bottom: 40px;
        }

        h4 {
            color: #FFA500;
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: 600;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }

        .table {
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
        }

        .table th {
            background: #222;
            color: #FFD700;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.9rem;
            padding: 6px 10px;
            border-radius: 8px;
        }

        .back-btn {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .btn-warning {
            font-weight: bold;
            color: #000;
            transition: transform 0.2s ease;
        }

        .btn-warning:hover {
            transform: scale(1.05);
        }

        .no-data {
            color: #999;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3>Admin - All Training Sessions</h3>

        <!-- Male Sessions -->
        <h4>👨 Male Sessions</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Trainer</th>
                    <th>Trainer Gender</th>
                    <th>User</th>
                    <th>User Gender</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Activity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($maleSessions->num_rows > 0): ?>
                    <?php while ($row = $maleSessions->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['trainer_name']); ?></td>
                            <td><?= ucfirst($row['trainer_gender']); ?></td>
                            <td><?= htmlspecialchars($row['user_name']); ?></td>
                            <td><?= ucfirst($row['user_gender']); ?></td>
                            <td><?= htmlspecialchars($row['date']); ?></td>
                            <td><?= date("h:i A", strtotime($row['time'])) . " - " . date("h:i A", strtotime($row['end_time'])); ?></td>
                            <td><?= htmlspecialchars($row['activity']); ?></td>
                            <td>
                                <?php
                                if ($row['status'] === 'Completed') echo '<span class="badge bg-success">Completed</span>';
                                elseif ($row['status'] === 'Canceled') echo '<span class="badge bg-danger">Canceled</span>';
                                else echo '<span class="badge bg-primary">Scheduled</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">No male user sessions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Female Sessions -->
        <h4>👩 Female Sessions</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Trainer</th>
                    <th>Trainer Gender</th>
                    <th>User</th>
                    <th>User Gender</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Activity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($femaleSessions->num_rows > 0): ?>
                    <?php while ($row = $femaleSessions->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['trainer_name']); ?></td>
                            <td><?= ucfirst($row['trainer_gender']); ?></td>
                            <td><?= htmlspecialchars($row['user_name']); ?></td>
                            <td><?= ucfirst($row['user_gender']); ?></td>
                            <td><?= htmlspecialchars($row['date']); ?></td>
                            <td><?= date("h:i A", strtotime($row['time'])) . " - " . date("h:i A", strtotime($row['end_time'])); ?></td>
                            <td><?= htmlspecialchars($row['activity']); ?></td>
                            <td>
                                <?php
                                if ($row['status'] === 'Completed') echo '<span class="badge bg-success">Completed</span>';
                                elseif ($row['status'] === 'Canceled') echo '<span class="badge bg-danger">Canceled</span>';
                                else echo '<span class="badge bg-primary">Scheduled</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">No female user sessions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="back-btn">
        <a href="admin.php" class="btn btn-warning px-4 py-2">Back to Dashboard</a>
    </div>
</body>

</html>