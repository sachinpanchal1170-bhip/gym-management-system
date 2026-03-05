<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$stmt = $con->prepare("
    SELECT plan, amount, start_date, end_date
    FROM memberships
    WHERE email = ?
    ORDER BY start_date DESC
");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>GymEdge | Renewal History</title>
    <meta charset="UTF-8">

    <style>
       body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: radial-gradient(circle at top, #111 0%, #000 70%);
    color: #eee;
    display: flex;
    justify-content: center;   /* keep horizontal center */
    align-items: flex-start;   /* move to top */
    min-height: 100vh;
    padding-top: 80px;         /* spacing from top */
}
        .container {
            width: 90%;
            max-width: 1000px;
            background: rgba(20, 20, 20, 0.95);
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(255, 215, 0, 0.15);
            animation: fadeUp 0.8s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            text-align: center;
            color: gold;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 12px;
        }

        th {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #000;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #333;
        }

        tr {
            transition: 0.3s ease;
        }

        tr:hover {
            background: rgba(255, 215, 0, 0.08);
            transform: scale(1.01);
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .active {
            background: #1f8f2f;
            color: #fff;
        }

        .expired {
            background: #c0392b;
            color: #fff;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 16px;
            border: 1px solid gold;
            border-radius: 20px;
            color: gold;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .back-btn:hover {
            background: gold;
            color: #000;
        }

        .empty-msg {
            text-align: center;
            padding: 30px;
            color: #aaa;
        }
    </style>
</head>

<body>

<div class="container">

    <a href="index.php" class="back-btn">← Back</a>

    <h2>🏋️ Your Membership History</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Plan</th>
                <th>Amount</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
            </tr>

            <?php while($row = $result->fetch_assoc()): 
                $status = (strtotime($row['end_date']) >= time()) ? "Active" : "Expired";
            ?>
            <tr>
                <td><?= htmlspecialchars($row['plan']); ?></td>
                <td>₹<?= number_format($row['amount'], 2); ?></td>
                <td><?= date('d M Y', strtotime($row['start_date'])); ?></td>
                <td><?= date('d M Y', strtotime($row['end_date'])); ?></td>
                <td>
                    <span class="status <?= strtolower($status); ?>">
                        <?= $status; ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="empty-msg">
            No membership history found.
        </div>
    <?php endif; ?>

</div>

</body>
</html>