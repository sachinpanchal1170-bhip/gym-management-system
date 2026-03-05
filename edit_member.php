<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: manage_members.php");
    exit;
}

$member_id = intval($_GET['id']);

// Fetch member details
$stmt = $con->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$member) {
    $_SESSION['msg'] = "❌ Member not found!";
    header("Location: manage_members.php");
    exit;
}

// Update member
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);

    // Check if email exists for another user
    $check = $con->prepare("SELECT user_id FROM users WHERE email=? AND user_id!=?");
    $check->bind_param("si", $email, $member_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['msg'] = "❌ Email already in use by another member!";
        $check->close();
    } else {
        $check->close();

        $stmt = $con->prepare("UPDATE users SET full_name=?, email=?, phone=?, gender=? WHERE user_id=?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $gender, $member_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['msg'] = "✅ Member details updated!";
        header("Location: manage_members.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Member</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: Arial, sans-serif;
            padding: 40px;
        }

        .box {
            max-width: 500px;
            margin: auto;
            background: #1c1c1c;
            padding: 20px;
            border-radius: 12px;
        }

        h2 {
            text-align: center;
            color: #ffa500;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: none;
            border-radius: 8px;
        }

        input,
        select {
            background: #333;
            color: #fff;
        }

        button {
            background: #ffa500;
            color: #000;
            font-weight: bold;
            cursor: pointer;
        }

        p.msg {
            text-align: center;
            color: lime;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="box">
        <h2>Edit Member</h2>

        <?php if (!empty($_SESSION['msg'])): ?>
            <p class="msg"><?= $_SESSION['msg'];
                            unset($_SESSION['msg']); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']); ?>" required>
            <input type="email" name="email" value="<?= htmlspecialchars($member['email']); ?>" required>
            <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']); ?>" required>
            <select name="gender" required>
                <option value="Male" <?= $member['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?= $member['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?= $member['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
            <button type="submit">Update Member</button>
            <button type="button" onclick="window.location.href='manage_members.php'">Back</button>
        </form>
    </div>
</body>

</html>