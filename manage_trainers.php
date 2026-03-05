<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// DELETE TRAINER + ALL LINKED DATA
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete trainer attendance
    $stmt1 = $con->prepare("DELETE FROM trainer_attendance WHERE trainer_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // Delete trainer schedule
    $stmt2 = $con->prepare("DELETE FROM trainer_schedule WHERE trainer_id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    // Delete trainer salary (if exists)
    $stmt3 = $con->prepare("DELETE FROM trainer_salary WHERE trainer_id = ?");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();

    // Delete trainer record
    $stmt4 = $con->prepare("DELETE FROM trainers WHERE id = ?");
    $stmt4->bind_param("i", $id);
    $stmt4->execute();

    header("Location: manage_trainers.php");
    exit;
}

$result = $con->query("SELECT id, full_name, email, phone, experience, speciality, hire_date FROM trainers ORDER BY hire_date ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Trainers</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: 'Poppins', Arial, sans-serif;
            padding: 30px;
            overflow-x: hidden;
            animation: fadeInBody 0.6s ease-in-out;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            text-align: center;
            color: #ffa500;
            letter-spacing: 1px;
            margin-bottom: 20px;
            animation: fadeDown 0.8s ease-in-out;
        }

        @keyframes fadeDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: #1c1c1c;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.15);
            animation: fadeInTable 0.8s ease-in-out;
        }

        @keyframes fadeInTable {
            from {
                opacity: 0;
                transform: scale(0.98);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border: 1px solid #444;
            transition: background 0.3s ease, color 0.3s ease;
        }

        th {
            background: #222;
            color: #ffa500;
            font-size: 15px;
        }

        tr:nth-child(odd) {
            background: #252525;
        }

        tr:hover {
            background: #333;
            transform: scale(1.01);
            transition: all 0.25s ease;
        }

        tbody tr {
            opacity: 0;
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

        tbody tr:nth-child(1) {
            animation-delay: 0.1s;
        }

        tbody tr:nth-child(2) {
            animation-delay: 0.2s;
        }

        tbody tr:nth-child(3) {
            animation-delay: 0.3s;
        }

        tbody tr:nth-child(4) {
            animation-delay: 0.4s;
        }

        tbody tr:nth-child(5) {
            animation-delay: 0.5s;
        }

        tbody tr:nth-child(6) {
            animation-delay: 0.6s;
        }

        a.btn {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 13px;
            text-decoration: none;
            margin: 0 3px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        a.edit {
            background: #007bff;
            color: #fff;
        }

        a.edit:hover {
            background: #339dff;
            transform: scale(1.1);
            box-shadow: 0 0 10px rgba(51, 157, 255, 0.4);
        }

        a.delete {
            background: #dc3545;
            color: #fff;
        }

        a.delete:hover {
            background: #ff4d4d;
            transform: scale(1.1);
            box-shadow: 0 0 10px rgba(255, 77, 77, 0.4);
        }

        a.back {
            display: inline-block;
            margin-top: 30px;
            text-align: center;
            color: #ffa500;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 18px;
            border: 2px solid #ffa500;
            border-radius: 8px;
            transition: all 0.3s ease;
            animation: fadeUp 0.9s ease-in-out;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        a.back:hover {
            background: #ffa500;
            color: #111;
            transform: scale(1.05);
        }
    </style>
</head>

<body>

    <h1>Total Trainers</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Experience</th>
                    <th>Specialty</th>
                    <th>Hire Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['experience']) ?></td>
                        <td><?= htmlspecialchars($row['speciality']) ?></td>
                        <td><?= htmlspecialchars($row['hire_date']) ?></td>

                        <td>
                            <a href="edit_trainer.php?id=<?= $row['id'] ?>" class="btn edit">Edit</a>

                            <a href="manage_trainers.php?delete=<?= $row['id'] ?>"
                                class="btn delete"
                                onclick="return confirm('Are you sure you want to delete this trainer? All attendance, schedule & salary records will also be deleted!')">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p style="text-align:center; margin-top:30px;">No trainers found.</p>
    <?php endif; ?>

    <div style="text-align:center;">
        <a href="admin.php" class="back">⬅ Back to Dashboard</a>
    </div>

</body>

</html> 