<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);
$data = $con->query("SELECT * FROM trainer_salary WHERE id = $id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Salary</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: Poppins;
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: #ffa500;
        }

        form {
            width: 450px;
            margin: 20px auto;
            background: #1c1c1c;
            padding: 20px;
            border-radius: 10px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #ffa500;
            color: #111;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #ffbf33;
        }

        a.back {
            color: #ffa500;
            display: block;
            margin: 20px auto;
            text-align: center;
        }
    </style>
</head>

<body>

    <h1>Edit Salary</h1>

    <form method="POST">

        <label>Month</label>
        <input type="text"
            name="month_year"
            value="<?= htmlspecialchars($data['month_year'], ENT_QUOTES) ?>"
            required>

        <label>Present Days</label>
        <input type="number"
            name="present_days"
            value="<?= htmlspecialchars($data['present_days'], ENT_QUOTES) ?>"
            required>

        <label>Per Day Salary</label>
        <input type="number"
            name="per_day_salary"
            value="<?= htmlspecialchars($data['per_day_salary'], ENT_QUOTES) ?>"
            required>

        <label>Bonus</label>
        <input type="number"
            name="bonus"
            value="<?= htmlspecialchars($data['bonus'], ENT_QUOTES) ?>">

        <label>Deductions</label>
        <input type="number"
            name="deductions"
            value="<?= htmlspecialchars($data['deductions'], ENT_QUOTES) ?>">

        <button name="update">Update Salary</button>

    </form>

    <a class="back" href="trainer_salary.php">⬅ Back</a>

</body>

</html>

<?php
if (isset($_POST['update'])) {

    $month = $_POST['month_year'];
    $present = $_POST['present_days'];
    $perday = $_POST['per_day_salary'];
    $bonus = $_POST['bonus'];
    $ded = $_POST['deductions'];

    // Calculate total salary
    $total = ($present * $perday) + $bonus - $ded;

    $update = $con->prepare("
        UPDATE trainer_salary 
        SET month_year=?, present_days=?, per_day_salary=?, bonus=?, deductions=?, total_salary=? 
        WHERE id=?
    ");
    $update->bind_param("siiiddi", $month, $present, $perday, $bonus, $ded, $total, $id);
    $update->execute();

    header("Location: trainer_salary.php");
    exit;
}
?>