<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user gender
$stmt = $con->prepare("SELECT gender FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$userGender = strtolower(trim($user['gender'] ?? ''));

// Fetch user's scheduled sessions
$scheduleStmt = $con->prepare("
  SELECT ts.*, t.full_name AS trainer_name, t.speciality AS training_name
  FROM trainer_schedule ts
  JOIN trainers t ON ts.trainer_id = t.id
  WHERE ts.user_id = ?
  ORDER BY ts.date DESC, ts.time ASC
");
$scheduleStmt->bind_param("i", $user_id);
$scheduleStmt->execute();
$schedules = $scheduleStmt->get_result();

// Fetch trainers based on user's gender
if ($userGender === 'male' || $userGender === 'female') {
    $trainerStmt = $con->prepare("SELECT full_name, speciality, experience FROM trainers WHERE gender = ? ORDER BY full_name ASC");
    $trainerStmt->bind_param("s", $userGender);
    $trainerStmt->execute();
    $trainerQuery = $trainerStmt->get_result();
} else {
    // fallback if gender not set
    $trainerQuery = $con->query("SELECT full_name, speciality, experience FROM trainers ORDER BY full_name ASC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Schedule - GymEdge Fitness Center</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #000;
            color: #fff;
            font-family: 'Oswald', 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.95);
            padding: 20px 0;
            border-bottom: 2px solid #e0a300;
            z-index: 999;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            animation: fadeIn 1.5s ease;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.5px;
            transition: color 0.3s ease;
        }

        nav a:hover,
        nav a.active {
            color: #e0a300;
        }

        .container {
            margin-top: 140px;
            text-align: center;
            padding: 20px;
            animation: fadeIn 1.2s ease;
        }

        h1 {
            font-size: 2.8rem;
            color: #e0a300;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 2px;
            animation: fadeIn 1.5s ease;
        }

        .session-grid,
        .trainer-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            animation: fadeIn 1.5s ease;
        }

        .session-card,
        .trainer-card {
            background: #111;
            border: 1px solid #1f1f1f;
            border-radius: 16px;
            width: 330px;
            padding: 25px 20px;
            text-align: left;
            transition: transform 0.3s ease, background 0.3s ease;
            opacity: 0;
            animation: fadeUp 0.8s ease forwards;
        }

        .session-card:nth-child(n),
        .trainer-card:nth-child(n) {
            animation-delay: calc(0.1s * var(--i));
        }

        .session-card:hover,
        .trainer-card:hover {
            transform: translateY(-8px);
            background: #181818;
        }

        .trainer-name {
            font-size: 1.2em;
            font-weight: 600;
            color: #e0a300;
            margin-bottom: 10px;
        }

        .session-info {
            color: #ccc;
            margin: 5px 0;
            font-size: 0.95em;
            font-family: 'Poppins', sans-serif;
        }

        .training-task {
            margin-top: 10px;
            color: #ffd700;
            font-weight: 600;
            font-size: 1em;
        }

        .status {
            margin-top: 12px;
            padding: 8px 14px;
            border-radius: 12px;
            font-size: 0.9em;
            text-transform: uppercase;
            font-weight: 600;
            display: inline-block;
        }

        .upcoming {
            background: #006eff;
        }

        .completed {
            background: #1abc9c;
        }

        .canceled {
            background: #e74c3c;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        footer {
            margin-top: 80px;
            text-align: center;
            font-size: 14px;
            color: #999;
            border-top: 1px solid #111;
            padding: 30px 0;
            font-family: 'Poppins', sans-serif;
        }

        @media (max-width: 768px) {
            nav {
                flex-wrap: wrap;
                gap: 15px;
                padding: 15px 0;
            }

            .session-card,
            .trainer-card {
                width: 90%;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav>
        <a href="index.php">HOME</a>
        <a href="aboutus.php">ABOUT</a>
        <a href="workout.php">TRACKING</a>
        <a href="attendance.php">ATTENDANCE</a>
        <a href="notice.php">NOTICE</a>
        <a href="membership.php">MEMBERSHIPS</a>
        <a href="user_schedule.php" class="active">SCHEDULE</a>
        <a href="contactus.php">CONTACT US</a>
    </nav>

    <!-- Page Content -->
    <div class="container">
        <h1>My Training Schedule</h1>

        <div class="session-grid">
            <?php if ($schedules->num_rows > 0): ?>
                <?php $i = 0;
                while ($row = $schedules->fetch_assoc()): $i++; ?>
                    <?php
                    $statusClass = strtolower($row['status']);
                    $statusText = ucfirst($row['status']);
                    ?>
                    <div class="session-card" style="--i:<?= $i; ?>">
                        <div class="trainer-name"><?= htmlspecialchars($row['trainer_name']); ?></div>
                        <div class="session-info"><strong>Date:</strong> <?= htmlspecialchars($row['date']); ?></div>
                        <div class="session-info"><strong>Start Time:</strong> <?= htmlspecialchars($row['time']); ?></div>
                        <div class="session-info"><strong>End Time:</strong> <?= htmlspecialchars($row['end_time']); ?></div>
                        <div class="training-task"><strong>Training:</strong> <?= htmlspecialchars($row['training_name']); ?></div>
                        <span class="status <?= $statusClass; ?>"><?= $statusText; ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No training sessions assigned yet.</p>
            <?php endif; ?>
        </div>

        <!-- Trainer Section -->
        <h1 style="margin-top:70px;">Our Trainers</h1>
        <div class="trainer-grid">
            <?php if ($trainerQuery->num_rows > 0): ?>
                <?php $i = 0;
                while ($trainer = $trainerQuery->fetch_assoc()): $i++; ?>
                    <div class="trainer-card" style="--i:<?= $i; ?>">
                        <div class="trainer-name"><?= htmlspecialchars($trainer['full_name']); ?></div>
                        <div class="session-info"><strong>Speciality:</strong> <?= htmlspecialchars($trainer['speciality']); ?></div>
                        <div class="session-info"><strong>Experience:</strong> <?= htmlspecialchars($trainer['experience']); ?> years</div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No trainers found for your gender.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        &copy; <?= date("Y"); ?> GymEdge Fitness Center. All rights reserved.
    </footer>

</body>

</html>