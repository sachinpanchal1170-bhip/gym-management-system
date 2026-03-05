<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);

/* MEMBER INFO */

$user = $con->query("
SELECT * FROM users
WHERE user_id=$id
")->fetch_assoc();

/* MEMBERSHIP */

$membership = $con->query("
SELECT m.*,p.name
FROM memberships m
JOIN membership_plans p ON p.plan_id=m.plan_id
WHERE m.user_id=$id
ORDER BY m.end_date DESC
LIMIT 1
")->fetch_assoc();

/* ATTENDANCE */

$attendance = $con->query("
SELECT status,attendance_date
FROM attendance
WHERE user_id=$id
ORDER BY attendance_date DESC
LIMIT 10
");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Member Profile</title>

    <style>
        body {
            background: #111;
            color: #eee;
            font-family: Poppins;
            padding: 30px;
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        .card {
            background: #1c1c1c;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255, 165, 0, .2);
        }

        h2 {
            color: #ffa500;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        td,
        th {
            border: 1px solid #444;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #222;
            color: #ffa500;
        }

        .present {
            color: #00ff6a;
        }

        .absent {
            color: #ff4c4c;
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="card">

            <h2>Member Details</h2>

            <p><b>Name :</b> <?= $user['full_name'] ?></p>
            <p><b>Email :</b> <?= $user['email'] ?></p>
            <p><b>Phone :</b> <?= $user['phone'] ?></p>

        </div>


        <div class="card">

            <h2>Membership</h2>

            <?php if ($membership): ?>

                <p><b>Plan :</b> <?= $membership['name'] ?></p>
                <p><b>Start :</b> <?= $membership['start_date'] ?></p>
                <p><b>End :</b> <?= $membership['end_date'] ?></p>

            <?php else: ?>

                <p>No membership assigned</p>

            <?php endif; ?>

        </div>


        <div class="card">

            <h2>Recent Attendance</h2>

            <table>

                <tr>
                    <th>Date</th>
                    <th>Status</th>
                </tr>

                <?php while ($row = $attendance->fetch_assoc()): ?>

                    <tr>

                        <td><?= $row['attendance_date'] ?></td>

                        <td class="<?= $row['status'] ?>">

                            <?= ucfirst($row['status']) ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </table>

        </div>

        <a href="manage_members.php" style="color:#ffa500;">⬅ Back</a>

    </div>

</body>

</html>