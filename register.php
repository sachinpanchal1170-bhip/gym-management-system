<?php
session_start();
require_once "db.php";

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname  = trim($_POST['fullname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['ph'] ?? '');
    $password  = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';
    $gender    = $_POST['gender'] ?? '';
    $age       = (int)($_POST['age'] ?? 0);

    $stmt = $con->prepare("SELECT user_id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errorMessage = "Email already exists.";
    } elseif ($password !== $cpassword) {
        $errorMessage = "Passwords do not match.";
    } else {

        /* PROFILE PHOTO */
        $photo = "";
        if (!empty($_FILES['photo']['name'])) {
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }
            $photo = time() . "_" . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo);
        }

        /* INSERT USER (PLAIN PASSWORD AS REQUESTED) */
        $stmt = $con->prepare("
            INSERT INTO users 
            (full_name, email, phone, password, gender, age, profile_photo)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssis",
            $fullname,
            $email,
            $phone,
            $password,   // plain password
            $gender,
            $age,
            $photo
        );

        if (!$stmt->execute()) {
            die("Registration error: " . $stmt->error);
        }

        /* SESSION */
        $_SESSION['user_id']   = $stmt->insert_id;
        $_SESSION['full_name'] = $fullname;
        $_SESSION['email']     = $email;

        header("Location: membership.php");
        exit();
    }
}
?>
<!-- HTML + CSS EXACTLY AS YOUR ORIGINAL (unchanged) -->


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>GymEdge | Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: radial-gradient(circle at top, #111, #000);
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }

        /* ===== CONTAINER ===== */
        .container {
            width: 100%;
            max-width: 600px;
            background: linear-gradient(160deg, #1f1f1f, #141414);
            border-radius: 16px;
            padding: 45px;
            box-shadow: 0 18px 55px rgba(0, 0, 0, .75);
            animation: slideFadeIn .9s ease-out;
        }

        @keyframes slideFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        h2 {
            color: #f5b400;
            text-align: center;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        /* ===== LABELS & INPUTS ===== */
        label {
            display: block;
            margin-top: 16px;
            font-size: 14px;
            color: #ccc;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"],
        input[type="password"] {
            width: 100%;
            height: 42px;
            padding: 8px 18px;
            margin-top: 6px;
            border-radius: 12px;
            border: none;
            font-size: 15px;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        input:focus {
            outline: none;
            transform: scale(1.02);
            box-shadow: 0 0 0 2px rgba(245, 180, 0, .6);
        }

        /* ===== GENDER ===== */
        .gender-wrapper {
            margin-top: 22px;
            text-align: center;
        }

        .gender-box {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 10px;
        }

        .gender-box label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: transform .2s ease, color .2s ease;
        }

        .gender-box label:hover {
            color: #f5b400;
            transform: scale(1.1);
        }

        .gender-box input:checked+span {
            color: #f5b400;
            font-weight: bold;
        }

        /* ===== PROFILE PHOTO ===== */
        .photo-wrapper {
            margin-top: 26px;
            text-align: center;
        }

        .photo-box {
            margin-top: 10px;
            padding: 20px;
            border-radius: 14px;
            background: linear-gradient(145deg, #222, #111);
            border: 1px dashed rgba(245, 180, 0, .7);
            box-shadow: inset 0 0 15px rgba(0, 0, 0, .7);
            transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease;
        }

        .photo-box:hover {
            transform: scale(1.03);
            border-color: #f5b400;
            box-shadow: inset 0 0 18px rgba(245, 180, 0, .35);
        }

        .photo-box input {
            color: #ccc;
            cursor: pointer;
        }

        /* ===== BUTTON ===== */
        button {
            width: 100%;
            padding: 14px;
            margin-top: 35px;
            border-radius: 30px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
            cursor: pointer;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        button:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 12px 30px rgba(245, 180, 0, .45);
        }

        button:active {
            transform: scale(.97);
        }

        /* ===== ERROR ===== */
        .error {
            color: #ff4d4d;
            margin-top: 18px;
            text-align: center;
        }

        /* ===== MOBILE ===== */
        @media (max-width: 600px) {
            .container {
                padding: 35px 25px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>WELCOME TO GYM EDGE FITNESS</h2>

        <form method="POST" enctype="multipart/form-data">

            <label>Full Name</label>
            <input type="text" name="fullname" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Phone</label>
            <input type="tel" name="ph" maxlength="10" pattern="[0-9]{10}" required>

            <label>Age</label>
            <input type="number" name="age" min="10" max="100" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="cpassword" required>

            <div class="gender-wrapper">
                <label>Gender</label>
                <div class="gender-box">
                    <label><input type="radio" name="gender" value="Male" required> Male</label>
                    <label><input type="radio" name="gender" value="Female" required> Female</label>
                </div>
            </div>

            <div class="photo-wrapper">
                <label>Profile Photo</label>
                <div class="photo-box">
                    <input type="file" name="photo" accept="image/*" required>
                </div>
            </div>

            <button type="submit">Register & Pay</button>

            <?php if ($errorMessage): ?>
                <div class="error"><?= htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

        </form>
    </div>

</body>

</html>