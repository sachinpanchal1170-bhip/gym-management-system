<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$full_name  = $_SESSION['full_name'] ?? 'Unknown';
$authorRole = $_SESSION['role'] ?? 'user';

$successMessage = '';
$errorMessage   = '';

date_default_timezone_set('Asia/Kolkata');

// Handle posting notices (admin/trainer only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['notice']) && ($authorRole === 'admin' || $authorRole === 'trainer')) {

    $notice     = trim($_POST['notice']);
    $created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');

    if ($notice === '') {
        $errorMessage = "Notice cannot be empty.";
    } else {
        $stmt = $con->prepare("INSERT INTO notices (notice_text, full_name, author_role, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $notice, $full_name, $authorRole, $created_at);

        if ($stmt->execute()) {
            $_SESSION['successMessage'] = "Notice posted successfully!";
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $errorMessage = "Failed to post notice.";
        }
    }
}

// Success message handling
if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}

// Retrieve notices
if ($authorRole === 'user') {
    $result = $con->query("SELECT notice_text, full_name, author_role, created_at FROM notices WHERE author_role IN ('admin', 'trainer') ORDER BY created_at DESC");
} else {
    $result = $con->query("SELECT notice_text, full_name, author_role, created_at FROM notices ORDER BY created_at DESC");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices</title>

    <style>
        body {
            margin: 0;
            background: #000;
            color: #eee;
            font-family: 'Poppins', sans-serif;
            padding: 40px 20px;
            overflow-x: hidden;
        }

        h1,
        h2 {
            text-align: center;
            color: #FFD700;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .container {
            max-width: 700px;
            margin: 20px auto;
            background: #151515;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.08);
        }

        textarea {
            width: 100%;
            height: 90px;
            padding: 10px;
            border-radius: 6px;
            background: #111;
            color: #eee;
            border: 1px solid #333;
            resize: none;
        }

        textarea:focus {
            border-color: #FFD700;
            outline: none;
        }

        button {
            margin-top: 10px;
            background: #FFD700;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #ffca2c;
        }

        .readonly-text {
            background: #222;
            color: #bbb;
            padding: 8px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .message {
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
        }

        .success {
            background: rgba(0, 255, 100, 0.15);
            color: #6dff8a;
        }

        .error {
            background: rgba(255, 0, 0, 0.15);
            color: #ff6b6b;
        }

        /* Compact Notice Card */
        .notice {
            background: #1b1b1b;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            border-left: 3px solid #FFD700;
            animation: fadeIn 0.4s ease;
        }

        .notice-text {
            font-size: 15px;
            color: #f0f0f0;
            margin-bottom: 6px;
        }

        .author {
            font-size: 12px;
            color: #ffd86b;
            margin-bottom: 4px;
        }

        .datetime {
            font-size: 11px;
            color: #999;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <h1>Notice Board</h1>

    <div class="container">

        <?php if ($successMessage): ?>
            <div class="message success"><?= $successMessage ?></div>
        <?php elseif ($errorMessage): ?>
            <div class="message error"><?= $errorMessage ?></div>
        <?php endif; ?>

        <div class="readonly-text"><?= htmlspecialchars($full_name) ?> (<?= htmlspecialchars($authorRole) ?>)</div>

        <?php if ($authorRole === 'admin' || $authorRole === 'trainer'): ?>
            <form method="POST">
                <label>Current Time:</label>
                <div class="readonly-text" id="currentDateTime"></div>
                <input type="hidden" name="created_at" id="created_at">

                <label>Write Notice</label>
                <textarea name="notice" placeholder="Write your notice here..."></textarea>

                <button type="submit">Post Notice</button>
                <button type="button" onclick="window.location.href='index.php'">Back</button>
            </form>
        <?php else: ?>
            <button onclick="window.location.href='index.php'">Back</button>
        <?php endif; ?>
    </div>

    <h2>Recent Notices</h2>

    <div class="container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="notice">
                <div class="notice-text"><?= nl2br(htmlspecialchars($row['notice_text'])) ?></div>
                <div class="author">Posted by <?= htmlspecialchars($row['author_role']) ?> — <?= htmlspecialchars($row['full_name']) ?></div>
                <div class="datetime"><?= htmlspecialchars($row['created_at']) ?></div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            const formatted = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0') + ' ' +
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0');

            document.getElementById('currentDateTime').textContent = formatted;
            document.getElementById('created_at').value = formatted;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>

</body>

</html>