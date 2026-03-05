<?php
session_start();

if (!isset($_SESSION['payment_success'])) {
    header("Location: membership.php");
    exit();
}

$plan   = $_SESSION['payment_success']['plan'];
$amount = $_SESSION['payment_success']['amount'];

/* remove after showing once */
unset($_SESSION['payment_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Success | GymEdge</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    margin: 0;
    height: 100vh;
    background: radial-gradient(circle at top, #111, #000);
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Poppins', sans-serif;
    color: #fff;
}

.card {
    background: linear-gradient(160deg, rgba(255,255,255,.06), rgba(255,255,255,.01));
    border-radius: 20px;
    padding: 40px 50px;
    text-align: center;
    border: 1px solid rgba(245,180,0,.35);
    box-shadow: 0 20px 60px rgba(0,0,0,.8);
    animation: popIn .8s ease;
}

@keyframes popIn {
    from { opacity: 0; transform: scale(.8); }
    to   { opacity: 1; transform: scale(1); }
}

h1 {
    color: #2aff2a;
    margin-bottom: 10px;
}

.plan {
    color: #f5b400;
    font-size: 22px;
    margin-top: 15px;
}

.amount {
    font-size: 26px;
    font-weight: bold;
    margin: 10px 0;
}

.msg {
    color: #ccc;
    margin-top: 10px;
}

.redirect {
    margin-top: 20px;
    font-size: 14px;
    color: #aaa;
}
</style>

<script>
setTimeout(() => {
    window.location.href = "membership.php";
}, 3000);
</script>

</head>
<body>

<div class="card">
    <h1>🎉 Registered Successfully!</h1>
    <p class="msg">Your membership payment was completed</p>

    <div class="plan"><?= htmlspecialchars($plan) ?> Plan</div>
    <div class="amount">₹<?= number_format($amount, 2) ?></div>

    <p class="redirect">Redirecting to your membership...</p>
</div>

</body>
</html>
