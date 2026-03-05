<?php
session_start();
require_once "db.php";

/* ADMIN LOGIN CHECK */
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

/* DELETE MEMBER */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $con->query("DELETE FROM diet_charts WHERE user_id = $id");
    $con->query("DELETE FROM user_diet_custom WHERE user_id = $id");
    $con->query("DELETE FROM users WHERE user_id = $id");

    header("Location: manage_members.php");
    exit;
}

/* SEARCH SYSTEM */

$search = "";

if (isset($_GET['search'])) {
    $search = $con->real_escape_string($_GET['search']);
}

$query = "
SELECT user_id, full_name, email, phone, created_at
FROM users
WHERE full_name LIKE '%$search%'
OR email LIKE '%$search%'
OR phone LIKE '%$search%'
ORDER BY created_at ASC
";

$result = $con->query($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <title>Manage Members</title>

    <style>
        body {
            background: #111;
            color: #eee;
            font-family: 'Poppins', Arial;
            padding: 30px;
            animation: fadeBody .6s ease;
        }

        @keyframes fadeBody {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            text-align: center;
            color: #ffa500;
            margin-bottom: 20px;
        }

        /* SEARCH BOX */

        .search-box {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 10px;
            width: 250px;
            border-radius: 6px;
            border: none;
            outline: none;
        }

        .search-box button {
            padding: 10px 15px;
            background: #ffa500;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        /* TABLE */

        table {
            width: 100%;
            border-collapse: collapse;
            background: #1c1c1c;
            box-shadow: 0 0 20px rgba(255, 165, 0, .15);
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border: 1px solid #444;
        }

        th {
            background: #222;
            color: #ffa500;
        }

        tr:nth-child(odd) {
            background: #252525;
        }

        tr:hover {
            background: #333;
            transform: scale(1.01);
            transition: .2s;
        }

        /* BUTTONS */

        a.btn {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
            margin: 2px;
            display: inline-block;
        }

        .assign {
            background: #17a2b8;
            color: #fff;
        }

        .profile {
            background: #28a745;
            color: #fff;
        }

        .delete {
            background: #dc3545;
            color: #fff;
        }

        a.btn:hover {
            transform: scale(1.08);
            box-shadow: 0 0 10px rgba(255, 165, 0, .4);
        }

        /* BACK BUTTON */

        .back {
            display: inline-block;
            margin-top: 25px;
            color: #ffa500;
            text-decoration: none;
            padding: 10px 18px;
            border: 2px solid #ffa500;
            border-radius: 8px;
        }

        .back:hover {
            background: #ffa500;
            color: #111;
        }
    </style>

</head>

<body>

    <h1>Manage Members</h1>

    <!-- SEARCH BAR -->

    <div class="search-box">

        <form method="GET">

            <input type="text" name="search" placeholder="Search member by name, email, phone" value="<?= htmlspecialchars($search) ?>">

            <button type="submit">Search</button>

        </form>

    </div>

    <?php if ($result->num_rows > 0): ?>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Join Date</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>

                        <td><?= $row['user_id'] ?></td>

                        <td><?= htmlspecialchars($row['full_name']) ?></td>

                        <td><?= htmlspecialchars($row['email']) ?></td>

                        <td><?= htmlspecialchars($row['phone']) ?></td>

                        <td><?= htmlspecialchars($row['created_at']) ?></td>

                        <td>

                            <a class="btn profile" href="member_profile.php?id=<?= $row['user_id'] ?>">
                                Profile
                            </a>

                            <a class="btn delete"
                                href="manage_members.php?delete=<?= $row['user_id'] ?>"
                                onclick="return confirm('Delete this member?');">

                                Delete

                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p style="text-align:center;margin-top:30px;">
            No members found.
        </p>

    <?php endif; ?>

    <div style="text-align:center;">
        <a href="admin.php" class="back">Back to Dashboard</a>
    </div>

</body>

</html>