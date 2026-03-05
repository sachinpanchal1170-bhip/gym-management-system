<?php
session_name("trainer_session");   // ✅ MUST MATCH trainer.php
session_start();
require_once('connection.php');

// Redirect if already logged in
if (isset($_SESSION['trainer_id'])) {
    header("Location: trainer.php");
    exit();
}

$error_message = '';

if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        $stmt = $con->prepare("SELECT * FROM trainers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $trainer = $result->fetch_assoc();

            if ($password === $trainer['password']) {

                // ✅ Correct trainer session variables
                $_SESSION['trainer_id'] = $trainer['id'];
                $_SESSION['email'] = $trainer['email'];
                $_SESSION['trainer_name'] = $trainer['full_name'];

                header("Location: trainer.php");
                exit();
            } else {
                $error_message = "Incorrect password. Please try again.";
            }
        } else {
            $error_message = "Email not found.";
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
    <title>Trainer Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0a0a0a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            animation: fadeInBody 1.2s ease-in-out;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: scale(0.97);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .login-container {
            background: #1a1a1a;
            padding: 40px 35px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.25);
            width: 360px;
            text-align: center;
            animation: floatBox 4s ease-in-out infinite alternate, fadeInBox 1.2s ease-in-out;
        }

        @keyframes floatBox {
            0% {
                transform: translateY(0px);
            }

            100% {
                transform: translateY(-10px);
            }
        }

        @keyframes fadeInBox {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0px);
            }
        }

        h2 {
            color: #FFD700;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        p {
            color: #bbb;
            font-size: 14px;
            margin-bottom: 25px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: #111;
            color: #fff;
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
            transform: scale(1.02);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: #FFD700;
            color: #000;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            margin-top: 12px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .login-btn:hover {
            background: #e6c200;
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(255, 215, 0, 0.4);
        }

        .error_message {
            color: #ff4444;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            animation: shake 0.3s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-4px);
            }

            50% {
                transform: translateX(4px);
            }

            75% {
                transform: translateX(-4px);
            }
        }

        .extra-links {
            margin-top: 15px;
            color: #aaa;
            font-size: 14px;
        }

        .extra-links a {
            color: #FFD700;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .extra-links a:hover {
            text-decoration: underline;
            color: #e6c200;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Trainer Login</h2>
        <p>Access your dashboard</p>

        <?php if (!empty($error_message)): ?>
            <p class="error_message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="login-btn" name="login">Login</button>
        </form>

        <div class="extra-links">
            <a href="t_forgot_password.php">Forgot Password?</a><br>
            <span>Don’t have an account?</span> <a href="trainer_register.php">Register Here</a>
        </div>
    </div>
</body>

</html>