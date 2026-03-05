<?php
// ✅ FIXED: Use SAME trainer session everywhere
session_name("trainer_session");
session_start();

require_once "db.php";

// Check login
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$trainer_id = $_SESSION['trainer_id'];

// Fetch trainer info
$stmt = $con->prepare("SELECT full_name, speciality, gender FROM trainers WHERE id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$trainer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ------------------------------------
// ADD SCHEDULE
// ------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $end_time = $_POST['end_time'];
    $activity = $trainer['speciality'];
    $user_id = $_POST['user'];
    $status = '';

    $stmt = $con->prepare("INSERT INTO trainer_schedule (trainer_id, date, time, end_time, activity, user_id, status)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssis", $trainer_id, $date, $time, $end_time, $activity, $user_id, $status);
    $stmt->execute();
    $stmt->close();

    $_SESSION['msg'] = "✅ New session added successfully!";
    header("Location: trainer_schedule.php");
    exit;
}

// ------------------------------------
// ACTION HANDLING (complete, cancel, delete)
// ------------------------------------
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    $check = $con->prepare("SELECT trainer_id FROM trainer_schedule WHERE id=?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->bind_result($owner_id);
    $check->fetch();
    $check->close();

    if ($owner_id == $trainer_id) {
        if ($action === 'complete') {
            $stmt = $con->prepare("UPDATE trainer_schedule SET status='Completed' WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['msg'] = "✅ Session marked as completed!";
        } elseif ($action === 'cancel') {
            $stmt = $con->prepare("UPDATE trainer_schedule SET status='Canceled' WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['msg'] = "⚠️ Session canceled!";
        } elseif ($action === 'delete') {
            $con->query("DELETE FROM trainer_schedule WHERE id=$id");
            $_SESSION['msg'] = "🗑️ Schedule deleted successfully!";
        }
    }

    header("Location: trainer_schedule.php");
    exit;
}

// Fetch sessions
$mySchedules = $con->query("
    SELECT ts.*, u.full_name AS user_name
    FROM trainer_schedule ts
    LEFT JOIN users u ON ts.user_id = u.user_id
    WHERE ts.trainer_id = $trainer_id
    ORDER BY ts.date DESC, ts.time ASC
");

$trainerGender = strtolower($trainer['gender'] ?? '');
$otherSchedules = $con->query("
    SELECT ts.*, t.full_name AS trainer_name, u.full_name AS user_name
    FROM trainer_schedule ts
    LEFT JOIN trainers t ON ts.trainer_id = t.id
    LEFT JOIN users u ON ts.user_id = u.user_id
    WHERE ts.trainer_id != $trainer_id
      AND u.gender = '{$trainerGender}'
    ORDER BY ts.date DESC, ts.time ASC
");

// Users dropdown (filtered)
if ($trainerGender === 'male') {
    $users = $con->query("SELECT user_id, full_name FROM users WHERE gender='male' ORDER BY full_name ASC");
} elseif ($trainerGender === 'female') {
    $users = $con->query("SELECT user_id, full_name FROM users WHERE gender='female' ORDER BY full_name ASC");
} else {
    $users = $con->query("SELECT user_id, full_name FROM users ORDER BY full_name ASC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trainer Schedule</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #111;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            margin-top: 40px;
            padding: 30px;
            border-radius: 12px;
            background: #1c1c1c;
            animation: fadeSlide 0.6s ease;
        }

        /* Fade-in container */
        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h3,
        h4 {
            color: #FFA500;
        }

        .table th {
            background: #222;
            color: #FFD700;
        }

        /* Table row fade-in */
        tbody tr {
            opacity: 0;
            animation: rowFade 0.6s ease forwards;
        }

        tbody tr:hover {
            background: #222 !important;
            transition: 0.25s ease-in-out;
        }

        @keyframes rowFade {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn {
            border: none;
            background: #FFA500;
            font-weight: bold;
        }

        .btn:hover {
            background: #fff !important;
            color: #000 !important;
        }

        /* Button animation */
        .btn-anim {
            transition: 0.25s ease-in-out;
        }

        .btn-anim:hover {
            transform: scale(1.07);
        }

        /* Remove underline from navbar links & buttons */
        a,
        .btn {
            text-decoration: none !important;
        }

        /* Fix Add button vertical alignment */
        .add-btn-col {
            display: flex;
            align-items: end;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <div class="navbar" style="background:#1c1c1c; padding:15px 30px; display:flex; justify-content:space-between;">
        <h2 style="color:#FFA500;">Trainer Dashboard</h2>
        <div>
            <a href="trainer.php" style="color:#FFD700; margin-left:20px;">Home</a>
            <a href="trainer_schedule.php" style="color:#FFD700; margin-left:20px;">Schedule</a>
            <a href="diet_charts.php" style="color:#FFD700; margin-left:20px;">Diet Charts</a>
            <a href="trainer_attendance.php" style="color:#FFD700; margin-left:20px;">Attendance</a>
            <a href="t_notice.php" style="color:#FFD700; margin-left:20px;">Notices</a>
            <a href="trainer_logout.php" style="color:#FFD700; margin-left:20px;">Logout</a>
        </div>
    </div>

    <div class="container">

        <h3 class="text-center mb-3">Trainer Schedule - <?= htmlspecialchars($trainer['full_name']); ?></h3>
        <h5 class="text-center text-info mb-4">Speciality: <?= htmlspecialchars($trainer['speciality']); ?></h5>

        <?php if (!empty($_SESSION['msg'])): ?>
            <div class="alert alert-success text-center"><?= $_SESSION['msg']; ?></div>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <!-- Add schedule -->
        <h4>Add New Session</h4>
        <form method="POST" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2">
                    <label>Start Time</label>
                    <input type="time" name="time" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>End Time</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>User</label>
                    <select name="user" class="form-select">
                        <option value="">Select User</option>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 add-btn-col">
                    <button type="submit" name="add_schedule" class="btn btn-primary w-100 btn-anim">Add</button>
                </div>
            </div>
        </form>

        <!-- My sessions -->
        <h4 class="mt-4">My Sessions</h4>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Activity</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($mySchedules->num_rows > 0): ?>
                    <?php while ($row = $mySchedules->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['date'] ?></td>
                            <td><?= date("h:i A", strtotime($row['time'])) . " - " . date("h:i A", strtotime($row['end_time'])) ?></td>
                            <td><?= $row['activity'] ?></td>
                            <td><?= $row['user_name'] ?? 'Not Assigned' ?></td>
                            <td><?= $row['status'] ?: 'Scheduled' ?></td>
                            <td>
                                <?php if ($row['status'] == ''): ?>
                                    <a href="?id=<?= $row['id'] ?>&action=complete"
                                        class="btn btn-sm btn-anim"
                                        style="background:#28a745;color:#fff;">Done</a>

                                    <a href="?id=<?= $row['id'] ?>&action=cancel"
                                        class="btn btn-sm btn-anim"
                                        style="background:#ff5722;color:#fff;">Cancel</a>
                                <?php endif; ?>

                                <a href="?id=<?= $row['id'] ?>&action=delete"
                                    class="btn btn-sm btn-anim"
                                    style="background:#dc3545;color:#fff;">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No sessions yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Other trainers -->
        <h4 class="mt-4">Other Trainers’ Sessions</h4>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Trainer</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Activity</th>
                    <th>User</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($otherSchedules->num_rows > 0): ?>
                    <?php while ($row = $otherSchedules->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['trainer_name'] ?></td>
                            <td><?= $row['date'] ?></td>
                            <td><?= date("h:i A", strtotime($row['time'])) . " - " . date("h:i A", strtotime($row['end_time'])) ?></td>
                            <td><?= $row['activity'] ?></td>
                            <td><?= $row['user_name'] ?></td>
                            <td><?= $row['status'] ?: 'Scheduled' ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No sessions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="text-center mt-4">
            <a href="trainer.php" class="btn btn-warning px-4">Back to Dashboard</a>
        </div>
    </div>

</body>

</html>