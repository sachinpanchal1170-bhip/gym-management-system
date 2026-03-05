<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch trainers list
$trainers = $con->query("SELECT id, full_name FROM trainers ORDER BY full_name ASC");

if (isset($_POST['save_salary'])) {

    $trainer_id     = intval($_POST['trainer_id']);
    $month_year     = $_POST['month_year'];
    $present_days   = intval($_POST['present_days']);
    $per_day_salary = floatval($_POST['per_day_salary']);
    $bonus          = floatval($_POST['bonus']);
    $deductions     = floatval($_POST['deductions']);

    // Safe numeric calculation
    $total_salary = ($present_days * $per_day_salary) + $bonus - $deductions;

    $stmt = $con->prepare("
        INSERT INTO trainer_salary 
        (trainer_id, month_year, present_days, per_day_salary, bonus, deductions, total_salary)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "isiiddi",
        $trainer_id,
        $month_year,
        $present_days,
        $per_day_salary,
        $bonus,
        $deductions,
        $total_salary
    );
    $stmt->execute();

    header("Location: trainer_salary.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Salary</title>

    <style>
        /* Fade page */
        body {
            background: #111;
            color: #eee;
            font-family: Poppins, sans-serif;
            padding: 40px;
            opacity: 0;
            animation: fadeIn 0.8s ease forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        h1 {
            text-align: center;
            color: #ffa500;
            margin-bottom: 25px;
            animation: slideUp 0.8s ease-out;
        }

        form {
            max-width: 450px;
            margin: auto;
            background: #1c1c1c;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.2);
            animation: slideUp 0.9s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 600;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            background: #222;
            border: 1px solid #444;
            color: #fff;
            border-radius: 6px;
            transition: 0.25s ease;
        }

        input:hover,
        select:hover,
        input:focus,
        select:focus {
            transform: scale(1.03);
            background: #2a2a2a;
            border-color: #ffa500;
            box-shadow: 0 0 10px rgba(255, 165, 0, 0.25);
        }

        button {
            width: 100%;
            margin-top: 15px;
            padding: 12px;
            background: #ffa500;
            color: #111;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.25s ease;
        }

        button:hover {
            background: #ffb833;
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(255, 165, 0, 0.5);
        }

        .cancel-btn {
            background: #444;
            color: #eee;
        }

        .cancel-btn:hover {
            background: #555;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>

    <h1>Add Trainer Salary</h1>

    <form method="POST">

        <label>Select Trainer</label>
        <select name="trainer_id" required>
            <option disabled selected>— Select —</option>
            <?php while ($t = $trainers->fetch_assoc()): ?>
                <option value="<?= $t['id'] ?>"><?= $t['full_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Month-Year (Example: January 2025)</label>
        <input type="text" name="month_year" required>

        <label>Present Days</label>
        <input type="number" name="present_days" required>

        <label>Per Day Salary</label>
        <input type="number" name="per_day_salary" required>

        <label>Bonus</label>
        <input type="number" name="bonus" value="0">

        <label>Deductions</label>
        <input type="number" name="deductions" value="0">

        <button type="submit" name="save_salary">Save Salary</button>

        <button type="button" class="cancel-btn" onclick="window.location.href='trainer_salary.php'">
            Cancel
        </button>

    </form>

</body>

</html>