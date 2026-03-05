<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

/* ------------------ HANDLE DELETIONS ------------------- */
if (isset($_GET['delete'])) {
    $did = intval($_GET['delete']);
    $stmt = $con->prepare("DELETE FROM trainer_salary WHERE id = ?");
    $stmt->bind_param("i", $did);
    $stmt->execute();
    $stmt->close();
    header("Location: trainer_salary.php");
    exit;
}
if (isset($_GET['quick_pay'])) {
    $pid = intval($_GET['quick_pay']);
    $stmt = $con->prepare("UPDATE trainer_salary SET paid_status = 1, payment_mode = 'Cash (admin)', payment_datetime = NOW() WHERE id = ?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $stmt->close();
    header("Location: trainer_salary.php?msg=paid");
    exit;
}

/* ------------------ FETCH FILTER INPUT ------------------- */
$filter_trainer = isset($_GET['trainer_id']) && $_GET['trainer_id'] !== '' ? intval($_GET['trainer_id']) : null;
$filter_month   = isset($_GET['month']) && $_GET['month'] !== '' ? $_GET['month'] : null; // format YYYY-MM
$filter_status  = isset($_GET['status']) ? $_GET['status'] : 'all'; // all / paid / pending

/* ------------------ Build Query with Filters ------------------- */
$where = [];
$params = [];
$types = '';

if ($filter_trainer) {
    $where[] = 'ts.trainer_id = ?';
    $types .= 'i';
    $params[] = $filter_trainer;
}
if ($filter_month) {
    // stored month_year column might be 'YYYY-MM' or other; we'll match using LIKE
    $where[] = 'ts.month_year LIKE ?';
    $types .= 's';
    $params[] = $filter_month . '%';
}
if ($filter_status === 'paid') {
    $where[] = 'ts.paid_status = 1';
} elseif ($filter_status === 'pending') {
    $where[] = 'ts.paid_status = 0';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT ts.id, ts.trainer_id, t.full_name, ts.month_year, ts.present_days, 
           ts.per_day_salary, ts.bonus, ts.deductions, ts.total_salary, ts.paid_status, ts.payment_mode, ts.payment_datetime
    FROM trainer_salary ts
    JOIN trainers t ON ts.trainer_id = t.id
    $where_sql
    ORDER BY ts.id DESC
";

$stmt = $con->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* ------------------ Fetch trainers for filter dropdown ------------------- */
$trainers = $con->query("SELECT id, full_name FROM trainers ORDER BY full_name ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Trainer Salary Management</title>
    <style>
        body {
            background: #0d0d0d;
            color: #eee;
            font-family: Poppins, sans-serif;
            padding: 24px;
            animation: fadePage 0.6s ease-out;
        }

        @keyframes fadePage {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        h1 {
            color: #ffa500;
            text-align: center;
            margin-bottom: 20px;
            animation: fadeUp 0.7s ease-out;
        }

        .box {
            width: 95%;
            max-width: 1400px;
            /* Increased width */
            margin: 0 auto;
            background: #141414;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
            animation: fadeUp 0.7s ease-out;
        }

        @keyframes fadeUp {
            0% {
                opacity: 0;
                transform: translateY(25px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ---------------- FILTER SECTION ----------------- */ 
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            /* Center filters */
            margin-bottom: 18px;
        }

        select,
        input[type="month"] {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #111;
            color: #eee;
            min-width: 150px;
            transition: 0.25s ease;
        }

        select:hover,
        input[type="month"]:hover {
            background: #1b1b1b;
            transform: scale(1.03);
        }

        .btn {
            padding: 10px 15px;
            border-radius: 8px;
            background: #ffa500;
            color: #111;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.25s ease;
            white-space: nowrap;
        }

        .btn:hover {
            transform: scale(1.06);
            box-shadow: 0 0 12px rgba(255, 165, 0, 0.6);
        }

        .btn.secondary {
            background: #444;
            color: #fff;
        }

        /* ---------------- TABLE IMPROVED SIZE ---------------- */
        table {
            width: 100%;
            min-width: 1100px;
            /* Ensures table doesn't shrink */
            border-collapse: collapse;
            margin-top: 20px;
            animation: borderGlow 4s infinite alternate;
        }

        @keyframes borderGlow {
            0% {
                box-shadow: 0 0 5px #ffa500;
            }

            100% {
                box-shadow: 0 0 14px #ffa500;
            }
        }

        th,
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #222;
            text-align: center;
            font-size: 14px;
        }

        th {
            background: #1e1e1e;
            color: #ffd07a;
            font-size: 15px;
        }

        /* Column widths for better alignment */
        th:nth-child(1) {
            width: 60px;
        }

        th:nth-child(2) {
            width: 160px;
        }

        th:nth-child(3) {
            width: 130px;
        }

        th:nth-child(4) {
            width: 100px;
        }

        th:nth-child(5) {
            width: 110px;
        }

        th:nth-child(6) {
            width: 110px;
        }

        th:nth-child(7) {
            width: 110px;
        }

        th:nth-child(8) {
            width: 130px;
        }

        th:nth-child(9) {
            width: 110px;
        }

        th:nth-child(10) {
            width: 180px;
        }

        th:nth-child(11) {
            width: 180px;
        }

        tr:nth-child(even) {
            background: #161616;
        }

        tbody tr {
            transition: background 0.3s ease, transform 0.2s ease;
        }

        tbody tr:hover {
            background: #1f1f1f;
            transform: scale(1.01);
        }

        .paid {
            color: #00ff6a;
            font-weight: 700;
        }

        .unpaid {
            color: #ff6b6b;
            font-weight: 700;
        }

        /* ---------------- ACTION BUTTON ALIGNMENT ---------------- */
        .actions a {
            margin: 3px 0;
            display: inline-block;
            padding: 7px 10px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            font-weight: 700;
            transition: 0.25s ease;
            font-size: 13px;
        }

        .actions a:hover {
            transform: scale(1.06);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
        }

        .edit {
            background: #007bff;
        }

        .pay {
            background: #28ff94;
            color: #000;
        }

        .info-small {
            font-size: 12px;
            color: #cfcfcf;
        }

        .center-row {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 18px;
        }
    </style>

</head>

<body>
    <div class="box">
        <h1>Trainer Salary Management</h1>

        <form method="GET" class="filters" style="align-items:center">
            <label>
                Trainer
                <select name="trainer_id">
                    <option value="">All Trainers</option>
                    <?php
                    // reset pointer and output options
                    $trainers->data_seek(0);
                    while ($t = $trainers->fetch_assoc()):
                        $sel = ($filter_trainer && $filter_trainer == $t['id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $t['id'] ?>" <?= $sel ?>><?= htmlspecialchars($t['full_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </label>

            <label>
                Month
                <input type="month" name="month" value="<?= htmlspecialchars($filter_month ?? '') ?>">
            </label>

            <label>
                Status
                <select name="status">
                    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="paid" <?= $filter_status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </label>

            <button type="submit" class="btn">Filter</button>

            <a href="trainer_salary.php" class="btn secondary right">Reset</a>
        </form>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px">
            <a href="add_salary.php" class="btn">+ Add Salary</a>
            <div class="info-small">Tip: Click <b>Pay</b> to choose Cash or Online & record payment date/time.</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trainer</th>
                    <th>Month</th>
                    <th>Present</th>
                    <th>Per Day</th>
                    <th>Bonus</th>
                    <th>Deductions</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['month_year']) ?></td>
                        <td><?= (int)$row['present_days'] ?></td>
                        <td>₹ <?= number_format($row['per_day_salary'], 2) ?></td>
                        <td>₹ <?= number_format($row['bonus'], 2) ?></td>
                        <td>₹ <?= number_format($row['deductions'], 2) ?></td>
                        <td style="font-weight:700;color:#00ff88">₹ <?= number_format($row['total_salary'], 2) ?></td>

                        <td>
                            <?php if ($row['paid_status']): ?>
                                <span class="paid">Paid</span>
                            <?php else: ?>
                                <span class="unpaid">Pending</span>
                            <?php endif; ?>
                        </td>

                        <td class="info-small">
                            <?= $row['payment_mode'] ? htmlspecialchars($row['payment_mode']) . '<br/>' : '-' ?>
                            <?= $row['payment_datetime'] ? htmlspecialchars($row['payment_datetime']) : '' ?>
                        </td>

                        <td class="actions">
                            <a class="edit" href="edit_salary.php?id=<?= $row['id'] ?>">Edit</a>

                            <?php if (!$row['paid_status']): ?>
                                <a class="pay" href="pay_salary.php?id=<?= $row['id'] ?>">Pay</a>
                                <!-- quick cash mark (optional) -->
                                <a class="btn" style="background:#ff8c42;color:#000" href="trainer_salary.php?quick_pay=<?= $row['id'] ?>" onclick="return confirm('Quick mark as paid (cash)?')">Quick Pay</a>
                            <?php else: ?>  
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="center-row">
            <a href="admin.php" class="btn secondary">⬅ Back to Dashboard</a>
        </div>
    </div>
</body>

</html>
<?php
$stmt->close();
?>