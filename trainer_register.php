<?php
session_start();
require_once "db.php";

$errorMessage = "";

// Fetch available specialities from DB
$specialities = $con->query("SELECT * FROM specialities ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $age = trim($_POST["age"]);
    $gender = trim($_POST["gender"]);
    $speciality = trim($_POST["speciality"]);
    $experience = trim($_POST["experience"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Password validation
    $passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($passwordPattern, $password)) {
        $errorMessage = "❌ Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
    } elseif ($password !== $confirm_password) {
        $errorMessage = "❌ Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $con->prepare("SELECT * FROM trainers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $check = $stmt->get_result();
        if ($check->num_rows > 0) {
            $errorMessage = "❌ Email already exists. Please use another.";
        } else {
            // Insert trainer with plain password
            $stmt = $con->prepare("INSERT INTO trainers (full_name, email, phone, age, gender, speciality, experience, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $full_name, $email, $phone, $age, $gender, $speciality, $experience, $password);

            if ($stmt->execute()) {
                echo "<script>
                        alert('Trainer registered successfully! Please login.');
                        window.location.href = 'trainer_login.php';
                      </script>";
                exit;
            } else {
                $errorMessage = "⚠️ Error occurred. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Registration</title>
    <style>
        body {
            background: #000;
            margin: 50px;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: rgba(255, 255, 255, 0.1);
        }

        .container {
            width: 100%;
            max-width: 480px;
            background: #1c1c1c;
            border-radius: 9px;
            padding: 50px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
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

        h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #FFA500;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
            color: #bbb;
            width: 100%;
            text-align: left;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 14px;
            background: #f9f9f9;
            transition: all 0.3s;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #2575fc;
            box-shadow: 0 0 5px rgba(37, 117, 252, 0.5);
            background: #fff;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: black;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        button:hover {
            transform: translateY(-3px);
        }

        .footer {
            margin-top: 15px;
            font-size: 16px;
            color: #bbb;
        }

        .footer a {
            color: #FFD700;
            text-decoration: none;
        }

        .footer a:hover {
            color: #fff;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>TRAINER REGISTRATION</h2>
        <?php
        if (!empty($errorMessage)) {
            echo "<div class='error'>$errorMessage</div>";
        }
        ?>
        <form method="POST" action="">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" placeholder="Enter Full Name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter Valid Email" required>

            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" maxlength="10" placeholder="Enter Phone Number" required>

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" min="18" max="80" placeholder="Enter Age" required>

            <label>Gender:</label>
            <div style="display:flex; justify-content:center;">
                <input type="radio" id="male" name="gender" value="Male" required>
                <label for="male" style="margin-right:20px;">Male</label>
                <input type="radio" id="female" name="gender" value="Female" required>
                <label for="female">Female</label>
            </div>

            <label for="speciality">Speciality:</label>
            <select id="speciality" name="speciality" required>
                <option value="">-- Select Speciality --</option>
                <?php while ($sp = $specialities->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($sp['name']) ?>"><?= htmlspecialchars($sp['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="experience">Experience (in years):</label>
            <input type="number" id="experience" name="experience" min="0" max="50" placeholder="Enter Experience" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter Password" required>

            <button type="submit">Register</button>
        </form>
        <div class="footer">
            <p>Already have an account? <a href="trainer_login.php">Login</a></p>
        </div>
    </div>
</body>

</html>