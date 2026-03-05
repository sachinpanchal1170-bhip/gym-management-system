<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['salary_id'])) die("Invalid request");
$salary_id = intval($_GET['salary_id']);

/* fetch salary record */
$stmt = $con->prepare("SELECT ts.*, t.full_name FROM trainer_salary ts JOIN trainers t ON ts.trainer_id = t.id WHERE ts.id = ?");
$stmt->bind_param("i", $salary_id);
$stmt->execute();
$res = $stmt->get_result();
$salary = $res->fetch_assoc();
$stmt->close();
if (!$salary) die("Salary record not found");

/* If posting confirmation, update DB */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'] ?? 'Online';
    $txn = 'TXN' . time() . rand(100, 999);
    $u = $con->prepare("UPDATE trainer_salary SET paid_status = 1, payment_mode = ?, payment_datetime = NOW() WHERE id = ?");
    $u->bind_param("si", $method, $salary_id);
    $u->execute();
    $u->close();

    // Optionally save transaction id in another column if you have it; here we simply redirect
    header("Location: trainer_salary.php?msg=paid&method=" . urlencode($method));
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Online Payment - <?= htmlspecialchars($salary['full_name']) ?></title>
    <style>
        body {
            background: #0d0d0d;
            color: #eee;
            font-family: Poppins, sans-serif
        }

        .box {
            width: 520px;
            margin: 60px auto;
            background: #141414;
            padding: 20px;
            border-radius: 10px
        }

        h2 {
            color: #ffa500;
            text-align: center
        }

        .row {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 12px
        }

        .method {
            padding: 12px 16px;
            border-radius: 8px;
            background: #222;
            border: 1px solid #2b2b2b;
            cursor: pointer
        }

        .method input {
            margin-right: 6px
        }

        .confirm {
            display: block;
            margin: 18px auto;
            padding: 10px 16px;
            background: #28ff94;
            border: none;
            border-radius: 8px;
            color: #000;
            font-weight: 700;
            cursor: pointer
        }

        .small {
            font-size: 13px;
            color: #cfcfcf;
            text-align: center
        }
    </style>
</head>

<body>
    <div class="box">
        <h2>Online Payment — <?= htmlspecialchars($salary['full_name']) ?></h2>
        <p style="text-align:center;color:#00ff88;font-weight:700">Amount: ₹ <?= number_format($salary['total_salary'], 2) ?></p>

        <form method="POST" style="text-align:center">
            <div class="row">
                <label class="method"><input type="radio" name="method" value="Card" checked> Card</label>
                <label class="method"><input type="radio" name="method" value="UPI"> UPI</label>
                <label class="method"><input type="radio" name="method" value="NetBanking"> NetBanking</label>
            </div>

            <button class="confirm" type="submit">Confirm Payment</button>
        </form>

        <p class="small">This is a simulated online payment. On confirm the salary will be marked paid and payment time recorded.</p>
        <p style="text-align:center"><a href="trainer_salary.php" style="color:#cfcfcf;text-decoration:none">← Cancel</a></p>
    </div>
</body>

</html>