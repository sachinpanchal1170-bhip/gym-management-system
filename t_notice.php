<?php
// FIXED: Use same session as trainer.php
session_name("trainer_session");
session_start();
require_once "db.php";

date_default_timezone_set('Asia/Kolkata');

// Ensure trainer is logged in
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$trainerName = $_SESSION['trainer_name'] ?? 'Trainer';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['notice'])) {
    $noticeText = trim($_POST['notice']);
    $createdAt = date('Y-m-d H:i:s');
    $authorRole = 'trainer';
    $authorName = $trainerName;

    if ($noticeText === '') {
        $errorMessage = 'Notice cannot be empty.';
    } else {
        $stmt = $con->prepare("INSERT INTO notices (notice_text, full_name, author_role, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $noticeText, $authorName, $authorRole, $createdAt);
        $stmt->execute();
        $successMessage = "Notice posted successfully!";
        $stmt->close();
        header("Refresh: 1");
    }
}

$noticesResult = $con->query("SELECT * FROM notices ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trainer Notice Board</title>
    <style>
        body {
            background: #0f0f0f;
            color: #f1f1f1;
            font-family: 'Poppins', sans-serif;
            padding: 30px;
            overflow-x: hidden;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #1b1b1b;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.15);
            animation: fadeInContainer 0.8s ease-in-out;
        }

        @keyframes fadeInContainer {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: #ffa500;
            text-align: center;
            margin-bottom: 20px;
            animation: popIn 0.8s ease;
        }

        @keyframes popIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        textarea {
            width: 100%;
            height: 90px;
            padding: 12px;
            border-radius: 10px;
            background: #252525;
            color: #fff;
            border: none;
            resize: none;
        }

        button {
            background: #ffa500;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
        }

        a.back {
            display: inline-block;
            text-decoration: none;
            margin-left: 10px;
            color: #ffa500;
        }

        .notice {
            background: #262626;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .author {
            color: #ffa500;
            font-size: 14px;
        }

        .datetime {
            color: #bbb;
            font-size: 12px;
            text-align: right;
        }

        .text {
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>📢 Trainer Notice Board</h1>

        <?php if ($successMessage): ?>
            <p style="color:#a6d608; font-weight:bold;"><?= $successMessage ?></p>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <p style="color:#ff5050; font-weight:bold;"><?= $errorMessage ?></p>
        <?php endif; ?>

        <form method="POST">
            <textarea name="notice" placeholder="Write your notice here..." required></textarea><br>
            <button type="submit">Post Notice</button>
            <a href="trainer.php" class="back">⬅ Back</a>
        </form>

        <h2 style="color:#ffa500; text-align:center;">Recent Notices</h2>

        <?php while ($row = $noticesResult->fetch_assoc()): ?>
            <div class="notice">
                <div class="author">
                    <?= ucfirst($row['author_role']) ?>: <?= htmlspecialchars($row['full_name']) ?>
                </div>
                <div class="datetime">
                    <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?>
                </div>
                <div class="text">
                    <?= nl2br(htmlspecialchars($row['notice_text'])) ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</body>

</html>