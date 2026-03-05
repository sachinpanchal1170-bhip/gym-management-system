<?php
// MUST MATCH trainer.php
session_name("trainer_session");
session_start();
require_once "db.php";

// Redirect if trainer not logged in
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$trainer_id = $_SESSION['trainer_id'];
$trainer_name = $_SESSION['trainer_name'] ?? "Trainer";

/* --- Fetch Trainer Details --- */
$stmt = $con->prepare("SELECT full_name, gender FROM trainers WHERE id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$stmt->bind_result($trainer_name, $trainer_gender);
$stmt->fetch();
$stmt->close();

/* --- Fetch Users of Same Gender --- */
$stmt = $con->prepare("SELECT user_id, full_name FROM users WHERE gender = ? ORDER BY full_name ASC");
$stmt->bind_param("s", $trainer_gender);
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();

/* --- Handle Diet Assignment --- */
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_diet'])) {
    $user_id = $_POST['user_id'];
    $meals = [
        "Breakfast",
        "Mid-Morning Snack",
        "Lunch",
        "Evening Snack",
        "Dinner"
    ];

    $diet_text = "";

    foreach ($meals as $meal) {
        $key = strtolower(str_replace(" ", "_", $meal));
        $value = trim($_POST[$key]);

        if (!empty($value)) {
            $diet_text .= strtoupper($meal) . ":\n" . $value . "\n\n";
        }
    }

    if (!empty($diet_text)) {
        $stmt = $con->prepare("INSERT INTO diet_charts (user_id, trainer_id, diet_chart, assigned_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $trainer_id, $diet_text);
        $stmt->execute();
        $stmt->close();

        $msg = "✅ Diet chart assigned successfully!";
    } else {
        $msg = "⚠️ Please fill at least one meal.";
    }
}

/* --- Fetch Diet Charts Assigned by this Trainer --- */
$diets = $con->query("
    SELECT d.*, u.full_name 
    FROM diet_charts d
    JOIN users u ON d.user_id = u.user_id
    WHERE d.trainer_id = {$trainer_id}
    ORDER BY d.assigned_at DESC
");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Assign Diet Chart</title>
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: Poppins, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Fade-in animation for container */
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #1a1a1a;
            padding: 30px;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.2);
            border-radius: 10px;
            animation: fadeIn 0.7s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2,
        h3 {
            color: #ffa500;
            text-align: center;
        }

        form {
            margin-top: 20px;
            padding: 20px;
            background: #111;
            border-radius: 10px;
            animation: fadeIn 0.8s ease-out;
        }

        label {
            color: #ffa500;
            font-weight: bold;
        }

        select,
        textarea,
        button {
            width: 100%;
            margin-top: 8px;
            padding: 10px;
            border-radius: 6px;
            border: none;
            background: #222;
            color: #fff;
            transition: 0.25s ease;
        }

        select:hover,
        textarea:hover {
            background: #2a2a2a;
        }

        textarea {
            height: 70px;
        }

        /* Button animation */
        button {
            background: #ffa500;
            color: #000;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            transition: 0.25s ease-in-out;
        }

        button:hover {
            transform: scale(1.08);
            background: #ffc34d;
        }

        /* Diet box animation */
        .diet-box {
            background: #222;
            padding: 15px;
            margin-top: 15px;
            border-left: 3px solid #ffa500;
            border-radius: 6px;
            opacity: 0;
            animation: slideIn 0.6s ease forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .back-btn {
            display: block;
            text-align: center;
            color: #ffa500;
            margin-top: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s ease;
        }

        .back-btn:hover {
            color: #fff;
            transform: scale(1.05);
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>Assign Diet Chart</h2>
        <p style="text-align:center;">Trainer: <strong><?= htmlspecialchars($trainer_name) ?></strong></p>

        <?php if (!empty($msg)): ?>
            <p style="color:#00ff88; text-align:center;"><?= $msg ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Select User (<?= $trainer_gender ?>)</label>
            <select name="user_id" required>
                <option value="">Select User</option>
                <?php while ($u = $users->fetch_assoc()): ?>
                    <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                <?php endwhile; ?>
            </select>

            <br><br>
            <label>Breakfast</label>
            <textarea name="breakfast"></textarea>

            <label>Mid-Morning Snack</label>
            <textarea name="mid-morning_snack"></textarea>

            <label>Lunch</label>
            <textarea name="lunch"></textarea>

            <label>Evening Snack</label>
            <textarea name="evening_snack"></textarea>

            <label>Dinner</label>
            <textarea name="dinner"></textarea>

            <button type="submit" name="assign_diet">Assign Diet</button>
        </form>

        <h3>Assigned Diet Charts</h3>

        <?php if ($diets->num_rows > 0): ?>
            <?php while ($d = $diets->fetch_assoc()): ?>
                <div class="diet-box">
                    <strong><?= htmlspecialchars($d['full_name']) ?></strong><br>
                    <pre style="white-space:pre-wrap;"><?= htmlspecialchars($d['diet_chart']) ?></pre>
                    <small>Assigned at: <?= $d['assigned_at'] ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">No diet charts assigned yet.</p>
        <?php endif; ?>

        <a href="trainer.php" class="back-btn">⬅ Back to Dashboard</a>

    </div>

</body>

</html>