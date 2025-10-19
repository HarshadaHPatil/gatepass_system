<?php
/* gatepass_form.php – All non-HR users can request Gate‑Pass */
$pageTitle = 'New Gate‑Pass';
require_once __DIR__.'/../config/db.php';
require_once '_layout_start.php';

/* ── Allow all roles except HR (role_id = 1) ── */
if (!isset($_SESSION['user']) || $_SESSION['role'] == 1) {
    header('Location: login.php'); exit;
}

$user    = $_SESSION['user'];            // entire user row from user_master
$roleId  = $_SESSION['role'];            // role_id: 2, 3, 4, ...
$userId  = $user['user_id'];             // primary key from user_master

/* ── Redirect all non-HR users to employee_dashboard.php ── */
$redirectPage = 'employee_dashboard.php';

/* ── Handle form submit ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason    = trim($_POST['reason']);
    $date_for  = $_POST['date_for'];
    $from_time = $_POST['from_time'];
    $to_time   = $_POST['to_time'];

    $stmt = $pdo->prepare("
        INSERT INTO gatepasses
            (user_id, reason, date_for, from_time, to_time, status, delflag)
        VALUES
            (?, ?, ?, ?, ?, 'pending', 0)
    ");
    $stmt->execute([$userId, $reason, $date_for, $from_time, $to_time]);

    header("Location: {$redirectPage}");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- ===== header row ===== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Request a Gate‑Pass</h2>
        <div id="datetime-bar" class="fw-medium" style="color:#444;">
            <span id="date-span"><?= date('l, j F Y') ?></span> •
            <span id="time-span"><?= date('h:i:s A') ?></span>
        </div>
    </div>

    <form method="post" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Date For</label>
            <input class="form-control" type="date" name="date_for" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">From Time</label>
            <input class="form-control" type="time" name="from_time" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">To Time</label>
            <input class="form-control" type="time" name="to_time" required>
        </div>
        <div class="col-12">
            <label class="form-label">Reason</label>
            <textarea class="form-control" name="reason" rows="3" style="min-height:90px;" required></textarea>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Submit</button>
            <a class="btn btn-secondary ms-2" href="<?= $redirectPage ?>">Cancel</a>
        </div>
    </form>

    <!-- live clock -->
    <script>
    (() => {
        const d = document.getElementById('date-span');
        const t = document.getElementById('time-span');

        function tick() {
            const n = new Date();
            d.textContent = n.toLocaleDateString(undefined, {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            t.textContent = n.toLocaleTimeString(undefined, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        tick();
        setInterval(tick, 1000);
    })();
    </script>

    <?php require '_layout_end.php'; ?>
</body>

</html>