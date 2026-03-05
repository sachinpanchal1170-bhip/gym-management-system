<?php
session_start();
require_once __DIR__ . '/db.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($email) || empty($oldPassword) || empty($newPassword)) {
        $errorMessage = "All fields are required.";
    } elseif ($oldPassword === $newPassword) {
        $errorMessage = "New password cannot be the same as old password.";
    } else {
        
        $stmt = $con->prepare("SELECT user_id FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $oldPassword);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            
            $updateStmt = $con->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $updateStmt->bind_param("si", $newPassword, $user['user_id']);

            if ($updateStmt->execute()) {
                $successMessage = "Password updated successfully.";
            } else {
                $errorMessage = "Failed to update password.";
            }
            $updateStmt->close();
        } else {
            $errorMessage = "Email or old password is incorrect.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Change Password</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: auto;
            padding: 40px;
        }

        input[type="email"],
        input[type="password"],
        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
        }

        button {
            background: #e0a300;
            color: #222;
            font-weight: bold;
            cursor: pointer;
            margin-left: 15px;
        }

        button:hover {
            background: #b28700;
        }

        .message {
            margin-top: 20px;
            margin-bottom: 15px;

            font-weight: 700;
        }

        .success {
            color: #a6d608;
        }

        .error {
            color: #f04141;
        }

        label {
            font-weight: 700;
        }

        form label,
        form input,
        form button {
            display: block;
            margin-bottom: 20px;
        }   
    </style>
</head>

<body>

    <h1>Change Password</h1>

    <?php if ($successMessage): ?>
        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
    <?php elseif ($errorMessage): ?>
        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="email">Registered Email Address</label>
        <input type="email" name="email" id="email" placeholder="Enter your registered email" required />

        <label for="old_password">Old Password</label>
        <input type="password" name="old_password" id="old_password" placeholder="Enter your old password" required />

        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password" placeholder="Enter your new password" required />

        <button type="submit">Change Password</button>
        <button type="button" onclick="window.location.href='index.php'">Back</button>
    </form>

</body>

</html>