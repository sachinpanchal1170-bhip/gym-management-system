<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Add Workout Type
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_name'])) {
    $type = trim($_POST['type_name']);
    if ($type !== "") {
        $stmt = $con->prepare("INSERT INTO workout_types (type_name) VALUES (?)");
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Workout type added!";
        header("Location: admin_workout_type.php");
        exit;
    }
}

// Add Exercise
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exercise_name'])) {
    $type_id = intval($_POST['type_id']);
    $exercise = trim($_POST['exercise_name']);
    if ($type_id && $exercise !== "") {
        $stmt = $con->prepare("INSERT INTO exercises (type_id, exercise_name) VALUES (?, ?)");
        $stmt->bind_param("is", $type_id, $exercise);
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Exercise added!";
        header("Location: admin_workout_type.php");
        exit;
    }
}

// Delete Type
if (isset($_GET['delete_type'])) {
    $id = intval($_GET['delete_type']);
    $con->query("DELETE FROM workout_types WHERE id=$id");
    $_SESSION['msg'] = "Workout type deleted!";
    header("Location: admin_workout_type.php");
    exit;
}

// Delete Exercise
if (isset($_GET['delete_exercise'])) {
    $id = intval($_GET['delete_exercise']);
    $con->query("DELETE FROM exercises WHERE id=$id");
    $_SESSION['msg'] = "Exercise deleted!";
    header("Location: admin_workout_type.php");
    exit;
}

$types = $con->query("SELECT * FROM workout_types ORDER BY type_name ASC");
$exercises = $con->query("SELECT e.*, wt.type_name 
                          FROM exercises e 
                          JOIN workout_types wt ON e.type_id = wt.id 
                          ORDER BY wt.type_name, e.exercise_name ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Workout Types & Exercises</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: 'Poppins', Arial, sans-serif;
            padding: 40px;
            margin: 0;
            animation: fadeInBody 0.8s ease-in-out;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .box {
            max-width: 720px;
            margin: 40px auto;
            background: #1c1c1c;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(255, 165, 0, 0.1);
            transition: all 0.4s ease;
            animation: slideUp 0.8s ease-in-out;
        }

        .box:hover {
            box-shadow: 0 0 35px rgba(255, 165, 0, 0.25);
            transform: scale(1.01);
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

        h2 {
            color: #ffa500;
            text-align: center;
            margin-bottom: 15px;
            animation: fadeDown 1s ease-in-out;
        }

        @keyframes fadeDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        input,
        select,
        button {
            padding: 10px;
            margin: 5px 0;
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        input,
        select {
            background: #333;
            color: #fff;
            width: 80%;
        }

        input:focus,
        select:focus {
            outline: none;
            box-shadow: 0 0 10px #ffa500;
        }

        button {
            background: #ffa500;
            color: #000;
            font-weight: bold;
            cursor: pointer;
            width: 200px;
            margin: 10px;
            border-radius: 25px;
            transition: 0.3s ease;
        }

        button:hover {
            background: #e69500;
            box-shadow: 0 0 15px rgba(255, 165, 0, 0.6);
            transform: scale(1.07);
        }

        form {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            animation: fadeInTable 0.9s ease-in-out;
        }

        @keyframes fadeInTable {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        th,
        td {
            border: 1px solid #444;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #222;
            color: #ffa500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:nth-child(even) {
            background: #252525;
        }

        tr:hover {
            background: #333;
            transform: scale(1.01);
            transition: 0.25s ease;
        }

        a {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s ease;
        }

        a:hover {
            color: #ff6666;
            text-shadow: 0 0 10px #ff4d4d;
        }

        .msg {
            color: #00ff80;
            text-align: center;
            margin-bottom: 10px;
            animation: fadeFlash 1s ease-in-out;
        }

        @keyframes fadeFlash {
            0% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.8;
            }
        }
    </style>
</head>

<body>
    <?php if (!empty($_SESSION['msg'])): ?>
        <p class="msg"><?= $_SESSION['msg']; ?></p>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <div class="box">
        <h2>🏋️ Manage Workout Types</h2>
        <form method="POST">
            <input type="text" name="type_name" placeholder="Enter new workout type" required>
            <br>
            <button type="submit">Add Workout Type</button>
            <button type="button" onclick="window.location.href='admin.php'">Back</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Action</th>
            </tr>
            <?php while ($t = $types->fetch_assoc()): ?>
                <tr>
                    <td><?= $t['id']; ?></td>
                    <td><?= htmlspecialchars($t['type_name']); ?></td>
                    <td><a href="?delete_type=<?= $t['id']; ?>" onclick="return confirm('Delete this type?')">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="box">
        <h2>💪 Manage Exercises</h2>
        <form method="POST">
            <select name="type_id" required>
                <option value="">-- Select Workout Type --</option>
                <?php
                $typesRes = $con->query("SELECT * FROM workout_types ORDER BY type_name ASC");
                while ($t = $typesRes->fetch_assoc()): ?>
                    <option value="<?= $t['id']; ?>"><?= htmlspecialchars($t['type_name']); ?></option>
                <?php endwhile; ?>
            </select>
            <br>
            <input type="text" name="exercise_name" placeholder="Enter exercise name" required>
            <br>
            <button type="submit">Add Exercise</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Workout Type</th>
                <th>Exercise</th>
                <th>Action</th>
            </tr>
            <?php while ($e = $exercises->fetch_assoc()): ?>
                <tr>
                    <td><?= $e['id']; ?></td>
                    <td><?= htmlspecialchars($e['type_name']); ?></td>
                    <td><?= htmlspecialchars($e['exercise_name']); ?></td>
                    <td><a href="?delete_exercise=<?= $e['id']; ?>" onclick="return confirm('Delete this exercise?')">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>

</html>