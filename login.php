<?php
session_start();
require_once('connection.php');

$error_message = '';

if (isset($_POST['login'])) {
    $email = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($con, $_POST['password']) : '';

    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if ($password === $user['password']) {
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error_message = "Incorrect password. Please try again.";
            }
        } else {
            $error_message = "Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | GymEdge</title>
    <style>
        /* === GLOBAL === */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: radial-gradient(circle at top, #111, #000);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 1.2s ease-in-out;
            overflow: hidden;
        }

        /* === LOGIN CONTAINER === */
        .login-container {
            background: rgba(20, 20, 20, 0.95);
            width: 400px;
            padding: 50px 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.15);
            text-align: center;
            transform: translateY(50px);
            opacity: 0;
            animation: slideUp 1s ease forwards;
        }

        h2 {
            color: #FFD700;
            margin-bottom: 10px;
            letter-spacing: 1px;
            animation: glowText 2s ease-in-out infinite alternate;
        }

        p {
            color: #bbb;
            margin-bottom: 20px;
        }

        /* === INPUTS === */
        input {
            width: 100%;
            max-width: 320px;
            padding: 14px;
            margin: 12px auto;
            display: block;
            border: 1px solid #333;
            border-radius: 5px;
            font-size: 16px;
            background: #222;
            color: #fff;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border: 2px solid #FFD700;
            box-shadow: 0 0 10px #FFD700;
            transform: scale(1.02);
        }

        /* === BUTTON === */
        .login-btn {
            background: linear-gradient(to right, #FFD700, orange);
            border: none;
            color: #000;
            padding: 12px;
            width: 200px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
            margin: 20px auto;
            display: block;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 0 15px #FFD700;
        }

        /* === LINKS === */
        a {
            color: #FFD700;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            text-decoration: underline;
            color: orange;
        }

        .error_message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: bold;
            animation: fadeIn 0.6s ease-in;
        }

        /* === ANIMATIONS === */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(80px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes glowText {
            from {
                text-shadow: 0 0 10px #FFD700, 0 0 20px #FFA500;
            }

            to {
                text-shadow: 0 0 20px #FFD700, 0 0 40px #FFA500;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Welcome Back!</h2>
        <p>Enter your details to access your account</p>

        <?php if (!empty($error_message)): ?>
            <p class="error_message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="login-btn" name="login">Login</button>
        </form>

        <div class="extra-links">
            <a href="forgot_password.php">Forgot Password?</a><br>
            <span style="color:#bbb;">Don’t have an account?</span>
            <a href="register.php">Register Here</a>
        </div>
    </div>
</body>

</html>