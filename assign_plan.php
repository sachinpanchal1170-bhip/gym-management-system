<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['user_id'])) {
    header("Location: manage_members.php");
    exit;
}

$user_id = intval($_GET['user_id']);

/* FETCH USER */

$stmt = $con->prepare("SELECT full_name FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* FETCH PLANS */

$plans = $con->query("SELECT * FROM membership_plans ORDER BY price ASC");


/* ASSIGN PLAN */

if (isset($_POST['plan_id'])) {

    $plan_id = intval($_POST['plan_id']);

    $plan = $con->query("SELECT duration_months FROM membership_plans WHERE plan_id=$plan_id")->fetch_assoc();

    $start = date("Y-m-d");
    $end = date("Y-m-d", strtotime("+" . $plan['duration_months'] . " months"));

    $stmt = $con->prepare("INSERT INTO memberships(user_id,plan_id,start_date,end_date) VALUES(?,?,?,?)");
    $stmt->bind_param("iiss", $user_id, $plan_id, $start, $end);
    $stmt->execute();

    header("Location: manage_members.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Assign Membership</title>

    <style>
        body {
            background: #111;
            color: #fff;
            font-family: Poppins;
            padding: 40px;
            text-align: center;
        }

        .card {
            background: #1c1c1c;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 20px rgba(255, 165, 0, .2);
        }

        select {
            padding: 10px;
            width: 100%;
            margin-top: 15px;
        }

        button {
            margin-top: 20px;
            background: #ffa500;
            border: none;
            padding: 12px 20px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
        }

        button:hover {
            background: #ffb733;
        }
    </style>

</head>

<body>

    <div class="card">

        <h2>Assign Plan</h2>

        <p>Member : <b><?= htmlspecialchars($user['full_name']) ?></b></p>

        <form method="POST">

            <select name="plan_id" required>

                <option value="">Select Membership Plan</option>

                <?php while ($p = $plans->fetch_assoc()): ?>

                    <option value="<?= $p['plan_id'] ?>">

                        <?= $p['name'] ?> -
                        <?= $p['duration_months'] ?> Months -
                        ₹<?= $p['price'] ?>

                    </option>

                <?php endwhile; ?>

            </select>

            <button type="submit">Assign Plan</button>

        </form>

    </div>

</body>

</html>