<?php
session_name("trainer_session");   // ✅ FIXED — must match trainer.php
session_start();
require_once "db.php";

if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$trainer_id = intval($_SESSION['trainer_id']);
$trainer_name = $_SESSION['trainer_name'] ?? 'Trainer';

/* Filters */
$filter_month = isset($_GET['month']) && $_GET['month'] !== '' ? $_GET['month'] : null;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

$where = ['trainer_id = ?'];
$params = [$trainer_id];
$types = 'i';

if ($filter_month) {
    $where[] = 'month_year LIKE ?';
    $types .= 's';
    $params[] = $filter_month . '%';
}

if ($filter_status === 'paid') {
    $where[] = 'paid_status = 1';
} elseif ($filter_status === 'pending') {
    $where[] = 'paid_status = 0';
}

$where_sql = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT id, month_year, present_days, per_day_salary, bonus, deductions, total_salary, paid_status, payment_mode, payment_datetime
        FROM trainer_salary
        $where_sql
        ORDER BY id DESC";

$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Your Salary Details</title>
    <style>
        body {
            background: #0d0d0d;
            color: #eee;
            font-family: Poppins, sans-serif;
            padding: 24px;
            opacity: 0;
            animation: fadePage 0.8s ease forwards;
        }

        @keyframes fadePage {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .wrap {
            max-width: 980px;
            margin: 0 auto;
            background: #141414;
            padding: 18px;
            border-radius: 10px;
            animation: fadeUp 0.8s ease-out;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: #ffa500;
            text-align: center;
        }

        h3 {
            animation: fadeUp 0.8s ease-out;
        }

        .filters {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        select,
        input[type="month"] {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #111;
            color: #eee;
            transition: 0.25s ease;
        }

        select:hover,
        input[type="month"]:hover {
            transform: scale(1.05);
            background: #1b1b1b;
        }

        button {
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            background: #ffa500;
            color: #111;
            font-weight: 700;
            transition: 0.25s ease;
        }

        button:hover {
            transform: scale(1.07);
            box-shadow: 0 0 12px rgba(255, 165, 0, 0.5);
        }

        .filters a {
            padding: 8px 12px;
            border-radius: 6px;
            background: #444;
            color: #fff;
            text-decoration: none;
            margin-left: 8px;
        }

        .filters a:hover {
            transform: scale(1.07);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #222;
            text-align: center;
        }

        th {
            background: #1e1e1e;
            color: #ffd07a;
        }

        tbody tr:hover {
            background: #1f1f1f;
            transform: scale(1.01);
        }

        .paid {
            color: #00ff6a;
            font-weight: 700;
            animation: glowGreen 2s infinite alternate;
        }

        @keyframes glowGreen {
            from {
                text-shadow: 0 0 4px #00ff6a;
            }

            to {
                text-shadow: 0 0 12px #00ff6a;
            }
        }

        .unpaid {
            color: #ff6b6b;
            font-weight: 700;
            animation: glowRed 2s infinite alternate;
        }

        @keyframes glowRed {
            from {
                text-shadow: 0 0 4px #ff6b6b;
            }

            to {
                text-shadow: 0 0 12px #ff6b6b;
            }
        }

        .back {
            display: block;
            margin: 12px auto;
            text-align: center;
            color: #cfcfcf;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .back:hover {
            transform: scale(1.08);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <h1>Your Salary Details</h1>
        <h3 style="text-align:center;color:#ccc">Welcome, <?= htmlspecialchars($trainer_name) ?></h3>

        <form method="GET" class="filters">
            <label>Month
                <input type="month" name="month" value="<?= htmlspecialchars($filter_month ?? '') ?>">
            </label>

            <label>Status
                <select name="status">
                    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="paid" <?= $filter_status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Not Paid</option>
                </select>
            </label>

            <button type="submit">Filter</button>
            <a href="trainer_view_salary.php">Reset</a>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Month</th>
                    <th>Present Days</th>
                    <th>Per Day</th>
                    <th>Bonus</th>
                    <th>Deductions</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                </tr>

                <?php while ($r = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['month_year']) ?></td>
                        <td><?= (int)$r['present_days'] ?></td>
                        <td>₹ <?= number_format($r['per_day_salary'], 2) ?></td>
                        <td>₹ <?= number_format($r['bonus'], 2) ?></td>
                        <td>₹ <?= number_format($r['deductions'], 2) ?></td>

                        <td style="font-weight:700;color:#00ff88">
                            ₹ <?= number_format($r['total_salary'], 2) ?>
                        </td>

                        <td>
                            <?= $r['paid_status']
                                ? '<span class="paid">✓ Paid</span>'
                                : '<span class="unpaid">✗ Not Paid</span>' ?>
                        </td>

                        <td style="font-size:13px;color:#cfcfcf">
                            <?= $r['payment_mode'] ? htmlspecialchars($r['payment_mode']) . '<br/>' : '' ?>
                            <?= $r['payment_datetime'] ? htmlspecialchars($r['payment_datetime']) : '' ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

        <?php else: ?>
            <p style="text-align:center;color:#ccc;">No salary records found.</p>
        <?php endif; ?>

        <a class="back" href="trainer.php">⬅ Back to Dashboard</a>
    </div>
</body>

</html>

<?php $stmt->close(); ?>