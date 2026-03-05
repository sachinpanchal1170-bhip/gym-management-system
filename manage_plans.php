<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle Add Plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plan'])) {
    $name = trim($_POST['plan_name']);
    $price = floatval($_POST['plan_price']);
    $description = trim($_POST['plan_description']);

    if ($name !== '' && $price >= 0) {
        $stmt = $con->prepare("INSERT INTO membership_plans (name, price, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $name, $price, $description);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "✅ Plan added successfully!";
        } else {
            $_SESSION['msg'] = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['msg'] = "⚠ Please enter a valid name and price.";
    }
    header("Location: manage_plans.php");
    exit;
}

// Handle Update Plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_plan'])) {
    $id = intval($_POST['plan_id']);
    $name = trim($_POST['plan_name']);
    $price = floatval($_POST['plan_price']);
    $description = trim($_POST['plan_description']);

    if ($id && $name !== '' && $price >= 0) {
        $stmt = $con->prepare("UPDATE membership_plans SET name=?, price=?, description=? WHERE plan_id=?");
        $stmt->bind_param("sdsi", $name, $price, $description, $id);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "✅ Plan updated successfully!";
        } else {
            $_SESSION['msg'] = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: manage_plans.php");
    exit;
}

// Handle Delete Plan
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $con->prepare("DELETE FROM membership_plans WHERE plan_id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['msg'] = "🗑️ Plan deleted successfully!";
    } else {
        $_SESSION['msg'] = "❌ Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: manage_plans.php");
    exit;
}

// Fetch all plans
$plans = $con->query("SELECT * FROM membership_plans ORDER BY plan_id ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Membership Plans</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #0f0f0f;
            color: #fff;
            margin: 0;
            padding: 0;
            animation: fadeInBody 0.8s ease-in;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1,
        h2 {
            text-align: center;
            color: #ffa500;
            letter-spacing: 1px;
            animation: fadeInDown 0.8s ease-in-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background: #1a1a1a;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(255, 165, 0, 0.1);
            animation: zoomIn 0.8s ease;
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            animation: fadeInUp 0.9s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        th,
        td {
            padding: 15px;
            border: 1px solid #333;
            text-align: left;
            font-size: 16px;
        }

        th {
            background: #222;
            color: #ffd700;
        }

        tr {
            transition: background 0.3s, transform 0.2s;
        }

        tr:hover {
            background: #2a2a2a;
            transform: scale(1.01);
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin: 6px 0;
            border-radius: 6px;
            border: 1px solid #555;
            background: #222;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        input:focus,
        textarea:focus {
            border-color: #ffa500;
            box-shadow: 0 0 8px #ffa500;
            outline: none;
        }

        button {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 165, 0, 0.4);
        }

        .add-btn {
            background: #28a745;
            color: #fff;
        }

        .update-btn {
            background: #ffc107;
            color: #111;
        }

        .delete-btn {
            background: #dc3545;
            color: #fff;
        }

        .back-btn {
            background: #6c63ff;
            color: #fff;
        }

        .update-form {
            display: none;
            animation: fadeInUp 0.8s ease;
            margin-top: 25px;
            padding: 20px;
            background: #191919;
            border-radius: 10px;
        }

        .msg {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            color: #90ee90;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .center-btns {
            text-align: center;
            margin-top: 20px;
        }

        .center-btns button {
            margin: 8px;
        }
    </style>

    <script>
        function showUpdateForm(btn) {
            document.getElementById('update_plan_id').value = btn.getAttribute('data-id');
            document.getElementById('update_plan_name').value = btn.getAttribute('data-name');
            document.getElementById('update_plan_price').value = btn.getAttribute('data-price');
            document.getElementById('update_plan_description').value = btn.getAttribute('data-description');
            document.getElementById('updateForm').style.display = 'block';
            document.getElementById('updateForm').scrollIntoView({
                behavior: 'smooth'
            });
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>🏋️‍♂️ Manage Membership Plans</h1>

        <!-- Status Message -->
        <?php if (isset($_SESSION['msg'])): ?>
            <p class="msg"><?= $_SESSION['msg']; ?></p>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <!-- Add Plan -->
        <h2>Add New Plan</h2>
        <form method="POST">
            <input type="text" name="plan_name" placeholder="Plan Name" required>
            <input type="number" name="plan_price" placeholder="Price" step="0.01" required>
            <textarea name="plan_description" placeholder="Description"></textarea>
            <div class="center-btns">
                <button type="submit" name="add_plan" class="add-btn">➕ Add Plan</button>
                <button type="button" class="back-btn" onclick="window.location.href='admin.php'">⬅ Back</button>
            </div>
        </form>

        <!-- Current Plans -->
        <h2>Current Plans</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php while ($plan = $plans->fetch_assoc()): ?>
                <tr>
                    <td><?= $plan['plan_id'] ?></td>
                    <td><?= htmlspecialchars($plan['name']) ?></td>
                    <td>₹<?= number_format($plan['price'], 2) ?></td>
                    <td><?= htmlspecialchars($plan['description']) ?></td>
                    <td>
                        <button type="button" class="update-btn"
                            data-id="<?= $plan['plan_id'] ?>"
                            data-name="<?= htmlspecialchars($plan['name'], ENT_QUOTES) ?>"
                            data-price="<?= $plan['price'] ?>"
                            data-description="<?= htmlspecialchars($plan['description'], ENT_QUOTES) ?>"
                            onclick="showUpdateForm(this)">
                            ✏️ Update
                        </button>
                        <a href="?delete_id=<?= $plan['plan_id'] ?>" onclick="return confirm('Delete this plan?');">
                            <button type="button" class="delete-btn">🗑️ Delete</button>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Update Form -->
        <div class="update-form" id="updateForm">
            <h2>Update Plan</h2>
            <form method="POST">
                <input type="hidden" name="plan_id" id="update_plan_id">
                <input type="text" name="plan_name" id="update_plan_name" placeholder="Plan Name" required>
                <input type="number" name="plan_price" id="update_plan_price" placeholder="Price" step="0.01" required>
                <textarea name="plan_description" id="update_plan_description" placeholder="Description"></textarea>
                <button type="submit" name="update_plan" class="update-btn">💾 Save Changes</button>
            </form>
        </div>
    </div>
</body>

</html>