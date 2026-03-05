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
SELECT *
FROM users
WHERE user_id = $id
")->fetch_assoc();

if (!$user) {
    echo "Member not found";
    exit;
}

$email = $user['email'];

/* MEMBERSHIP */

$membership = $con->query("
SELECT *
FROM memberships
WHERE email = '$email'
ORDER BY end_date DESC
LIMIT 1
")->fetch_assoc();

/* ATTENDANCE */

$attendance = $con->query("
SELECT status, attendance_date
FROM attendance
WHERE user_id = $id
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
            animation: fadeBody .6s ease;
        }

        @keyframes fadeBody {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        .card {
            background: #1c1c1c;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(255, 165, 0, .2);
            transition: .3s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(255, 165, 0, .3);
        }

        h2 {
            color: #ffa500;
            margin-bottom: 15px;
        }

        p {
            margin: 8px 0;
            font-size: 15px;
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

        tr:hover {
            background: #262626;
        }

        .present {
            color: #00ff6a;
            font-weight: 600;
        }

        .absent {
            color: #ff4c4c;
            font-weight: 600;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
            color: #ffa500;
            text-decoration: none;
            font-weight: 600;
            transition: .3s;
        }

        .back:hover {
            color: #fff;
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="card">

            <h2>Member Details</h2>

            <p><b>Name :</b> <?= htmlspecialchars($user['full_name']) ?></p>
            <p><b>Email :</b> <?= htmlspecialchars($user['email']) ?></p>
            <p><b>Phone :</b> <?= htmlspecialchars($user['phone']) ?></p>

        </div>


        <div class="card">

            <h2>Membership</h2>

            <?php if ($membership): ?>

                <p><b>Plan :</b> <?= htmlspecialchars($membership['plan']) ?></p>
                <p><b>Start :</b> <?= htmlspecialchars($membership['start_date']) ?></p>
                <p><b>End :</b> <?= htmlspecialchars($membership['end_date']) ?></p>
                <p><b>Amount :</b> ₹<?= htmlspecialchars($membership['amount']) ?></p>

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

                        <td><?= htmlspecialchars($row['attendance_date']) ?></td>

                        <td class="<?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            </table>

        </div>

        <a href="manage_members.php" class="back">⬅ Back</a>

    </div>

</body>

</html>