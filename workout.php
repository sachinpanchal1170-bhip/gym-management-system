<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $con->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();
$fullName = $userData['full_name'] ?? "User";

// Workout types
$typesResult = $con->query("SELECT id, type_name FROM workout_types ORDER BY type_name ASC");

// Exercises
$exercisesResult = $con->query("SELECT e.id, e.exercise_name, wt.type_name 
                                FROM exercises e 
                                JOIN workout_types wt ON e.type_id = wt.id 
                                ORDER BY wt.type_name, e.exercise_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workout_date = date('Y-m-d');
    $workout_time = $_POST['workout_time'] ?? date('H:i:s');
    $workout_type = trim($_POST['workout_type'] ?? '');
    $exercise_name = trim($_POST['exercise_name'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $sets = intval($_POST['sets'] ?? 0);
    $reps = intval($_POST['reps'] ?? 0);
    $weight = floatval($_POST['weight'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $calories = intval($_POST['calories'] ?? 0);

    if ($calories == 0 && $workout_type !== '') {
        switch (strtolower($workout_type)) {
            case "running":
            case "cardio":
                $calories = $duration * 10;
                break;
            case "weight training":
            case "strength":
            case "abs workout":
                $calories = ($sets * $reps * 0.5) + ($duration * 5);
                break;
            case "yoga":
                $calories = $duration * 4;
                break;
            case "cycling":
                $calories = $duration * 8;
                break;
            default:
                $calories = $duration * 6;
        }
    }

    if ($workout_type !== '' && $exercise_name !== '') {
        $stmt = $con->prepare("INSERT INTO workouts 
        (user_id, workout_date, workout_time, workout_type, exercise_name, duration_minutes, calories_burned, notes, sets, reps, weight) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "issssiisiid",
            $user_id,
            $workout_date,
            $workout_time,
            $workout_type,
            $exercise_name,
            $duration,
            $calories,
            $notes,
            $sets,
            $reps,
            $weight
        );
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Workout logged successfully!";
        header("Location: workout.php");
        exit;
    }
}

// Filters
$where = "WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($_GET['from_date'])) {
    $where .= " AND workout_date >= ?";
    $params[] = $_GET['from_date'];
    $types .= "s";
}
if (!empty($_GET['to_date'])) {
    $where .= " AND workout_date <= ?";
    $params[] = $_GET['to_date'];
    $types .= "s";
}
if (!empty($_GET['filter_type'])) {
    $where .= " AND workout_type = ?";
    $params[] = $_GET['filter_type'];
    $types .= "s";
}

// Fetch workouts
$sql = "SELECT workout_date, workout_time, workout_type, exercise_name, duration_minutes, calories_burned, notes, sets, reps, weight 
        FROM workouts $where ORDER BY workout_date DESC, workout_time DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Summary
$summary = $con->query("SELECT COUNT(*) as total_workouts, SUM(duration_minutes) as total_duration, SUM(calories_burned) as total_calories 
                        FROM workouts WHERE user_id = $user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Workout Tracker</title>
    <style>
        body {
            background: linear-gradient(135deg, #0d0d0d, #1a1a1a);
            color: #eee;
            font-family: 'Poppins', sans-serif;
            padding: 40px 20px;
            margin: 0;
            animation: fadeInBody 1s ease-in-out;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container {
            max-width: 950px;
            background: #1c1c1c;
            padding: 35px 45px;
            border-radius: 15px;
            margin: auto;
            box-shadow: 0 0 25px rgba(255, 165, 0, 0.1);
            animation: fadeUp 1s ease-in-out;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1,
        h2,
        h3 {
            color: #ffa500;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0 0 10px rgba(255, 165, 0, 0.3);
        }

        label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #ffa500;
            display: block;
        }

        input,
        select,
        textarea {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #444;
            background: #2c2c2c;
            color: #fff;
            padding: 10px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #ffa500;
            box-shadow: 0 0 10px #ffa500;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .row {
            display: grid;
            gap: 15px;
            margin-bottom: 12px;
        }

        button {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: black;
            border: none;
            padding: 12px;
            width: 220px;
            border-radius: 10px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
        }

        button:hover {
            background: linear-gradient(135deg, #ffb700, #ff9900);
            transform: scale(1.07);
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.4);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            animation: fadeInTable 1.2s ease-in-out;
        }

        @keyframes fadeInTable {
            from {
                opacity: 0;
                transform: translateY(15px);
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
            transition: all 0.2s ease;
        }

        th {
            background: #222;
            color: #ffa500;
            text-transform: uppercase;
        }

        tr:nth-child(odd) {
            background: #252525;
        }

        tr:hover {
            background: rgba(255, 165, 0, 0.1);
            transform: scale(1.01);
        }

        .info {
            text-align: center;
            margin-bottom: 15px;
            font-size: 16px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .success-message {
            background: rgba(76, 175, 80, 0.2);
            border-left: 4px solid #4CAF50;
            padding: 10px;
            text-align: center;
            color: #4CAF50;
            border-radius: 5px;
            animation: slideDown 1s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Welcome, <?= htmlspecialchars($fullName); ?> 💪</h1>
        <h2>Workout Tracker</h2>

        <?php if (!empty($_SESSION['message'])): ?>
            <div class="success-message"><?= $_SESSION['message'];
                                            unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <!-- Workout Form -->
        <form method="POST">
            <div class="row">
                <div><label>Date:</label><input type="date" value="<?= date('Y-m-d'); ?>" readonly></div>
                <div><label>Time:</label><input type="time" id="workoutTime" name="workout_time" step="1" readonly></div>
            </div>

            <div class="row">
                <div><label>Workout Type:</label>
                    <select name="workout_type" id="workoutType" required>
                        <option value="">-- Select Type --</option>
                        <?php while ($t = $typesResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($t['type_name']); ?>"><?= htmlspecialchars($t['type_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div><label>Exercise:</label>
                    <select name="exercise_name" id="exerciseSelect" required>
                        <option value="">-- Select Exercise --</option>
                        <?php while ($e = $exercisesResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($e['exercise_name']); ?>"
                                data-type="<?= htmlspecialchars(strtolower($e['type_name'])); ?>"
                                style="display:none;">
                                <?= htmlspecialchars($e['type_name'] . " - " . $e['exercise_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div><label>Duration (min):</label><input type="number" name="duration" min="0"></div>
            </div>

            <div class="row" id="setsRepsSection">
                <div><label>Sets:</label><input type="number" name="sets" min="0"></div>
                <div><label>Reps:</label><input type="number" name="reps" min="0"></div>
            </div>

            <div class="row">
                <div><label>Weight (kg):</label><input type="number" name="weight" min="0" step="0.5"></div>
                <div><label>Notes:</label><textarea name="notes"></textarea></div>
            </div>

            <input type="hidden" name="calories" id="calories">
            <button type="submit">Log Workout</button>
            <button type="button" onclick="window.location.href='index.php'">Back</button>
        </form>

        <!-- Workout Summary -->
        <div class="info">
            <strong>Total Workouts:</strong> <?= $summary['total_workouts'] ?? 0; ?> |
            <strong>Total Duration:</strong> <?= $summary['total_duration'] ?? 0; ?> min |
            <strong>Total Calories:</strong> <?= $summary['total_calories'] ?? 0; ?> kcal
        </div>

        <!-- Filter Workouts -->
        <h3>Filter Workouts</h3>
        <form method="GET">
            <div class="row">
                <div><label>From:</label><input type="date" name="from_date" value="<?= $_GET['from_date'] ?? ''; ?>"></div>
                <div><label>To:</label><input type="date" name="to_date" value="<?= $_GET['to_date'] ?? ''; ?>"></div>
                <div><label>Type:</label>
                    <select name="filter_type">
                        <option value="">-- All --</option>
                        <?php
                        $typesRes = $con->query("SELECT type_name FROM workout_types ORDER BY type_name ASC");
                        while ($t = $typesRes->fetch_assoc()):
                            $sel = ($_GET['filter_type'] ?? '') == $t['type_name'] ? "selected" : "";
                            echo "<option value='" . htmlspecialchars($t['type_name']) . "' $sel>" . htmlspecialchars($t['type_name']) . "</option>";
                        endwhile;
                        ?>
                    </select>
                </div>
            </div>
            <button type="submit">Apply Filter</button>
        </form>

        <!-- Past Workouts -->
        <h3>Your Past Workouts</h3>
        <?php if ($result->num_rows === 0): ?>
            <p>No workouts logged yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Exercise</th>
                        <th>Duration</th>
                        <th>Calories</th>
                        <th>Sets</th>
                        <th>Reps</th>
                        <th>Weight</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['workout_date']); ?></td>
                            <td><?= date("h:i:s A", strtotime($row['workout_time'])); ?></td>
                            <td><?= htmlspecialchars($row['workout_type']); ?></td>
                            <td><?= htmlspecialchars($row['exercise_name']); ?></td>
                            <td><?= htmlspecialchars($row['duration_minutes']); ?></td>
                            <td><?= htmlspecialchars($row['calories_burned']); ?></td>
                            <td><?= htmlspecialchars($row['sets']); ?></td>
                            <td><?= htmlspecialchars($row['reps']); ?></td>
                            <td><?= htmlspecialchars($row['weight']); ?></td>
                            <td><?= htmlspecialchars($row['notes']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const workoutTimeInput = document.getElementById('workoutTime');
            const typeSelect = document.querySelector("#workoutType");
            const exerciseSelect = document.querySelector("#exerciseSelect");
            const durationInput = document.querySelector("input[name='duration']");
            const setsInput = document.querySelector("input[name='sets']");
            const repsInput = document.querySelector("input[name='reps']");
            const setsRepsSection = document.getElementById("setsRepsSection");
            const caloriesInput = document.querySelector("#calories");

            function updateTime() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                workoutTimeInput.value = `${hours}:${minutes}:${seconds}`;
            }
            updateTime();
            setInterval(updateTime, 1000);

            function toggleSetsReps() {
                let type = typeSelect.value.toLowerCase();
                if (type.includes("strength") || type.includes("weight") || type.includes("abs")) {
                    setsRepsSection.style.display = "grid";
                } else {
                    setsRepsSection.style.display = "none";
                    setsInput.value = "";
                    repsInput.value = "";
                }
            }

            function calculateCalories() {
                let type = typeSelect.value.toLowerCase();
                let duration = parseInt(durationInput.value) || 0;
                let sets = parseInt(setsInput.value) || 0;
                let reps = parseInt(repsInput.value) || 0;
                let calories = 0;

                if (type.includes("running") || type.includes("cardio")) calories = duration * 10;
                else if (type.includes("weight") || type.includes("strength") || type.includes("abs")) calories = (sets * reps * 0.5) + (duration * 5);
                else if (type.includes("yoga")) calories = duration * 4;
                else if (type.includes("cycling")) calories = duration * 8;
                else calories = duration * 6;

                caloriesInput.value = Math.round(calories);
            }

            typeSelect.addEventListener("change", () => {
                const selectedType = typeSelect.value.toLowerCase();
                toggleSetsReps();
                calculateCalories();

                for (let i = 0; i < exerciseSelect.options.length; i++) {
                    const option = exerciseSelect.options[i];
                    const type = option.getAttribute("data-type");
                    if (!type || option.value === "") continue;
                    option.style.display = (type === selectedType) ? "block" : "none";
                }
                exerciseSelect.value = "";
            });

            exerciseSelect.addEventListener("change", () => {
                const selected = exerciseSelect.options[exerciseSelect.selectedIndex];
                const type = selected.getAttribute("data-type");
                if (type) {
                    for (let i = 0; i < typeSelect.options.length; i++) {
                        if (typeSelect.options[i].value.toLowerCase() === type) {
                            typeSelect.selectedIndex = i;
                            break;
                        }
                    }
                    toggleSetsReps();
                    calculateCalories();
                }
            });

            [durationInput, setsInput, repsInput].forEach(input => {
                input.addEventListener("input", calculateCalories);
            });
        });
    </script>
</body>

</html>