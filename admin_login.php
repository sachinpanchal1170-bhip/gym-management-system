<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

$con = new mysqli('localhost', 'root', '', 'gymedge');
if ($con->connect_error) {
    die("Admin DB Connection failed: " . $con->connect_error);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $con->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if ($password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            header("Location: admin.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login | GymEdge</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            background: url("assets/img/ad.jpg") no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Poppins", sans-serif;
            color: #fff;
        }

        .login-card {
            width: 380px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 16px;
            padding: 45px 40px;
            box-shadow: 0 8px 35px rgba(0, 0, 0, 0.7);
            text-align: center;
            backdrop-filter: blur(10px);
            animation: fadeIn 1s ease-in-out;
        }

        .login-card h1 {
            color: #f5b638;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .login-card h3 {
            font-size: 14px;
            color: #ccc;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input::placeholder {
            color: #bbb;
        }

        input:focus {
            border-color: #f5b638;
            background: rgba(255, 255, 255, 0.15);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #f5b638, #ff6a00);
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: scale(1.03);
            box-shadow: 0 0 10px rgba(245, 182, 56, 0.4);
        }

        .error {
            color: #ff5555;
            background: rgba(255, 0, 0, 0.15);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: 500;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .login-card {
                width: 85%;
                padding: 35px 25px;
            }
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h1>Admin Access</h1>
        <h3>GymEdge Management Panel</h3>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Enter Username" required autofocus>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>