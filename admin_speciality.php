<?php
session_start();
require_once "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$msg = "";

// Add speciality
if (isset($_POST['add_speciality'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $con->prepare("INSERT INTO specialities (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $_SESSION['msg'] = "Speciality added successfully!";
        header("Location: admin_speciality.php");
        exit;
    }
}

// Delete speciality
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $con->query("DELETE FROM specialities WHERE id = $id");
    $_SESSION['msg'] = "Speciality deleted successfully!";
    header("Location: admin_speciality.php");
    exit;
}

// Update speciality
if (isset($_POST['update_speciality'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $stmt = $con->prepare("UPDATE specialities SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    $_SESSION['msg'] = "Speciality updated successfully!";
    header("Location: admin_speciality.php");
    exit;
}

// Show message after redirect
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

$result = $con->query("SELECT * FROM specialities ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Trainer Specialities</title>
    <style>
        body {
            background: #111;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            width: 650px;
            margin: 50px auto;
            background: #1c1c1c;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.15);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            color: #FFA500;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .msg {
            color: #a6d608;
            text-align: center;
            margin-bottom: 10px;
            animation: fadeOutMsg 3s ease-in-out forwards;
        }

        @keyframes fadeOutMsg {
            0% {
                opacity: 1;
            }

            70% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        form input[name="name"] {
            width: 450px;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #333;
            font-size: 16px;
            background: #222;
            color: #fff;
            transition: all 0.3s ease;
        }

        form input[name="name"]:focus {
            border-color: #FFA500;
            box-shadow: 0 0 8px #FFA500;
            outline: none;
        }

        .btn {
            background: #FFA500;
            color: #111;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #ffb732;
            box-shadow: 0 0 10px #FFA500;
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #333;
            text-align: center;
            transition: background 0.3s ease;
        }

        th {
            background: #222;
            color: #FFA500;
        }

        tr:hover td {
            background: #1f1f1f;
        }

        tr {
            animation: fadeInRow 0.5s ease forwards;
        }

        @keyframes fadeInRow {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        input.edit-input {
            width: 520px;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #333;
            font-size: 16px;
            background: #222;
            color: #fff;
            display: none;
            transition: all 0.3s ease;
        }

        input.edit-input:focus {
            border-color: #FFA500;
            box-shadow: 0 0 8px #FFA500;
        }

        .footer-btn {
            margin-top: 20px;
        }
    </style>

    <script>
        function enableEdit(id) {
            const span = document.getElementById('text-' + id);
            const input = document.getElementById('input-' + id);
            const form = input.closest('form');
            span.style.display = 'none';
            input.style.display = 'inline';
            input.style.animation = 'fadeIn 0.3s ease-in';
            input.focus();

            input.addEventListener('blur', () => form.submit());
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        }
    </script>
</head>

<body>
    <div class="container">
        <h2>Trainer Speciality Management</h2>
        <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

        <!-- Add Speciality Form -->
        <form method="POST" style="margin-bottom:20px;">
            <input type="text" name="name" placeholder="Enter speciality name..." required>
            <button type="submit" name="add_speciality" class="btn">Add Speciality</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Speciality Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <span id="text-<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></span>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="text" name="name" id="input-<?= $row['id'] ?>" class="edit-input"
                                value="<?= htmlspecialchars($row['name']) ?>">
                            <input type="hidden" name="update_speciality" value="1">
                        </form>
                    </td>
                    <td>
                        <button type="button" class="btn" onclick="enableEdit(<?= $row['id'] ?>)">✏️ Update</button>
                        <button class="btn" onclick="if(confirm('Delete this speciality?')) window.location.href='?delete=<?= $row['id'] ?>'">🗑 Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <div class="footer-btn">
            <button class="btn" onclick="window.location.href='admin.php'">⬅ Back to Dashboard</button>
        </div>
    </div>
</body>

</html>