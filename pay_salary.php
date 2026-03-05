<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$salary_id = intval($_GET['id']);

/* Fetch salary */
$stmt = $con->prepare("SELECT ts.*, t.full_name FROM trainer_salary ts JOIN trainers t ON ts.trainer_id = t.id WHERE ts.id = ?");
$stmt->bind_param("i", $salary_id);
$stmt->execute();
$res = $stmt->get_result();
$salary = $res->fetch_assoc();
$stmt->close();

if (!$salary) die("Salary record not found");

/* Handle cash immediate payment */
if (isset($_GET['mode']) && $_GET['mode'] === 'cash') {
    $u = $con->prepare("UPDATE trainer_salary SET paid_status = 1, payment_mode = 'Cash', payment_datetime = NOW() WHERE id = ?");
    $u->bind_param("i", $salary_id);
    $u->execute();
    $u->close();
    header("Location: trainer_salary.php?msg=paid&method=cash");
    exit;
}

/* If mode=online, redirect to online_payment simulation */
if (isset($_GET['mode']) && $_GET['mode'] === 'online') {
    header("Location: online_payment.php?salary_id={$salary_id}");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Pay Salary - <?= htmlspecialchars($salary['full_name']) ?></title>
    <style>
        body {
            background: #0d0d0d;
            color: #eee;
            font-family: Poppins, sans-serif
        }

        .box {
            width: 420px;
            margin: 60px auto;
            background: #141414;
            padding: 20px;
            border-radius: 10px;
            text-align: center
        }

        h2 {
            color: #ffa500
        }

        .amt {
            font-size: 20px;
            color: #00ff88;
            margin-bottom: 14px
        }

        button {
            padding: 10px 14px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            margin: 8px
        }

        .cash {
            background: #28ff94;
            color: #000
        }

        .online {
            background: #ffa500;
            color: #000
        }

        a.back {
            display: inline-block;
            margin-top: 12px;
            color: #cfcfcf;
            text-decoration: none
        }
    </style>
</head>

<body>
    <div class="box">
        <h2>Pay Salary — <?= htmlspecialchars($salary['full_name']) ?></h2>
        <p class="amt">Amount: <strong>₹ <?= number_format($salary['total_salary'], 2) ?></strong></p>

        <p>Select payment method:</p>

        <button class="online" onclick="location.href='pay_salary.php?id=<?= $salary_id ?>&mode=online'">Online Payment</button>

        <div style="margin-top:16px">
            <a href="trainer_salary.php" class="back">← Back</a>
        </div>
    </div>
</body>

</html>