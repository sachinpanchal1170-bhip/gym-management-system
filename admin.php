<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$adminUsername = $_SESSION['admin_username'] ?? 'Admin';

// Fetch counts for dashboard
$totalMembers  = $con->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;
$totalTrainers = $con->query("SELECT COUNT(*) AS c FROM trainers")->fetch_assoc()['c'] ?? 0;
$totalWorkouts = $con->query("SELECT COUNT(*) AS c FROM workouts")->fetch_assoc()['c'] ?? 0;
$totalTypes    = $con->query("SELECT COUNT(*) AS c FROM workout_types")->fetch_assoc()['c'] ?? 0;
$totalPlans    = $con->query("SELECT COUNT(*) AS c FROM membership_plans")->fetch_assoc()['c'] ?? 0;
$totalTrainerAttendance = $con->query("SELECT COUNT(*) AS c FROM trainer_attendance")->fetch_assoc()['c'] ?? 0;
$totalMemberAttendance = $con->query("SELECT COUNT(*) AS c FROM attendance")->fetch_assoc()['c'] ?? 0;
$totalSpeciality = $con->query("SELECT COUNT(*) AS c FROM specialities")->fetch_assoc()['c'] ?? 0;
$totalSessions = $con->query("SELECT COUNT(*) AS c FROM trainer_schedule")->fetch_assoc()['c'] ?? 0;
$totalVideoLibrary = $con->query("SELECT COUNT(*) AS c FROM videos")->fetch_assoc()['c'] ?? 0;
$totalSalaries = $con->query("SELECT COUNT(*) AS c FROM trainer_salary")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            background-image: url("assets/img/admin1.jpg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            color: #eee;
            font-family: Arial, sans-serif;
            margin: 0;
            opacity: 0;
            animation: fadeInBody 1s ease forwards;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.65);
            z-index: -1;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 40px;
            padding-right: 500px;
            background: rgba(0, 0, 0, 0.9);
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeIn 1s ease 0.2s forwards;
        }

        .navbar .logo img {
            height: 60px;
            width: auto;
        }

        .navbar .menu {
            display: flex;
            gap: 100px;
            margin-right: 300px;
        }

        .navbar .menu a {
            color: #ffa500;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.8px;
            transition: color 0.3s;
        }

        .navbar .menu a:hover {
            color: #fff;
        }

        h1 {
            text-align: center;
            color: #ffa500;
            margin-top: 20px;
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeIn 1s ease 0.4s forwards;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
            opacity: 0;
            animation: fadeIn 1s ease 0.6s forwards;
        }

        .info-box {
            background: rgba(28, 28, 28, 0.9);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.6);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeIn 0.8s ease forwards;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-box:nth-child(1) {
            animation-delay: 0.7s;
        }

        .info-box:nth-child(2) {
            animation-delay: 0.9s;
        }

        .info-box:nth-child(3) {
            animation-delay: 1.1s;
        }

        .info-box:nth-child(4) {
            animation-delay: 1.3s;
        }

        .info-box:nth-child(5) {
            animation-delay: 1.5s;
        }

        .info-box:nth-child(6) {
            animation-delay: 1.7s;
        }

        .info-box:nth-child(7) {
            animation-delay: 1.9s;
        }

        .info-box:nth-child(8) {
            animation-delay: 2.1s;
        }

        .info-box:nth-child(9) {
            animation-delay: 2.3s;
        }

        .info-box:nth-child(10) {
            animation-delay: 2.5s;
        }

        .info-box:nth-child(11){
            animation-delay: 2.7s;
        }

        .info-box:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(255, 165, 0, 0.7);
        }

        .info-box h3 {
            margin: 0;
            color: #ffa500;
            font-size: 18px;
        }

        .info-box p {
            margin: 8px 0 0;
            font-size: 15px;
            color: #ddd;
        }

        .info-box a {
            display: inline-block;
            margin-top: 10px;
            font-size: 13px;
            color: #ffa500;
            text-decoration: none;
            font-weight: bold;
        }

        .info-box a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <img src="assets/img/gym_logo1.jpg" alt="GymEdge Logo">
        </div>
        <div class="menu">
            <a href="admin.php">Dashboard</a>
            <a href="admin_notices.php">Notices</a>
            <a href="admin_logout.php">Logout</a>
        </div>
    </div>

    <h1>Welcome, <?= htmlspecialchars($adminUsername) ?></h1>

    <div class="info-boxes">
        <div class="info-box">
            <h3>Members</h3>
            <p>Total: <?= $totalMembers ?></p>
            <a href="manage_members.php">View Members</a>
        </div>

        <div class="info-box">
            <h3>Trainers</h3>
            <p>Total: <?= $totalTrainers ?></p>
            <a href="manage_trainers.php">View Trainers</a>
        </div>

        <div class="info-box">
            <h3>Workouts</h3>
            <p>Logged: <?= $totalWorkouts ?></p>
            <a href="admin_workout.php">View Logs</a>
        </div>

        <div class="info-box">
            <h3>Workout Types</h3>
            <p>Total: <?= $totalTypes ?></p>
            <a href="admin_workout_type.php">Manage Types</a>
        </div>

        <div class="info-box">
            <h3>Membership Plans</h3>
            <p>Total: <?= $totalPlans ?></p>
            <a href="manage_plans.php">View Plans</a>
        </div>

        <div class="info-box">
            <h3>Trainer Attendance</h3>
            <p>Total Records: <?= $totalTrainerAttendance ?></p>
            <a href="admin_trainer_attendance.php">View Attendance</a>
        </div>

        <div class="info-box">
            <h3>Member Attendance</h3>
            <p>Total Records: <?= $totalMemberAttendance ?></p>
            <a href="admin_user_attendance.php">View Attendance</a>
        </div>

        <div class="info-box">
            <h3>Specialities</h3>
            <p>Total: <?= $totalSpeciality ?></p>
            <a href="admin_speciality.php">View Specialities</a>
        </div>

        <div class="info-box">
            <h3>User Sessions</h3>
            <p>Total Records: <?= $totalSessions ?></p>
            <a href="admin_session.php">View Sessions</a>
        </div>

        <div class="info-box">
            <h3>Manage Video Library</h3>
            <p>Total Records: <?= $totalVideoLibrary ?></p>
            <a href="manage_videos.php">View Library</a>
        </div>
        
        <div class="info-box">
            <h3>Trainer Salary</h3>
            <p>Total Salary: <?= $totalSalaries ?> </p>
            <a href="trainer_salary.php">View Salaries</a>
    </div>
</body>

</html>