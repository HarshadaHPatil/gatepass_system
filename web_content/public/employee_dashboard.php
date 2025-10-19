<?php
/* employee_dashboard.php – Dashboard for ALL non-HR users */
$pageTitle = 'Employee Dashboard';
require_once __DIR__.'/../config/db.php';
require_once '_layout_start.php';

/* ✅ Allow ALL users except HR (role_id = 1) */
if (!isset($_SESSION['user']) || $_SESSION['role'] == 1) {
    header('Location: login.php'); exit;
}

$emp     = $_SESSION['user'];
$userId  = $emp['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_id'])) {
    $withdrawId = intval($_POST['withdraw_id']);
    $pdo->prepare("DELETE FROM gatepasses WHERE gatepass_id = ? AND user_id = ? AND status = 'pending'")
        ->execute([$withdrawId, $userId]);

    // ✅ Fix warnings and redirection
    $status = $_GET['status'] ?? '';
    $page   = $_GET['p'] ?? 1;
    header("Location: employee_dashboard.php?status=$status&p=$page");
    exit;
}


/* Filters */
$statusFilter = $_GET['status'] ?? ''; // approved, pending, rejected, or empty

/* Pagination */
$perPage = 10;
$page    = max(1, intval($_GET['p'] ?? 1));
$offset  = ($page - 1) * $perPage;

/* Total pages */
$countSql = "SELECT COUNT(*) FROM gatepasses WHERE user_id = :id AND delflag = 0";
if ($statusFilter) {
    $countSql .= " AND status = :status";
}
$totalRows = $pdo->prepare($countSql);
$totalRows->bindValue(':id', $userId, PDO::PARAM_INT);
if ($statusFilter) {
    $totalRows->bindValue(':status', $statusFilter);
}
$totalRows->execute();
$totalPages = max(1, ceil($totalRows->fetchColumn() / $perPage));

/* Stats */
$stat = $pdo->prepare("
  SELECT SUM(status='approved') AS approved,
         SUM(status='pending')  AS pending,
         SUM(status='rejected') AS rejected
  FROM gatepasses
  WHERE user_id = :id AND delflag = 0
");
$stat->execute([':id'=>$userId]);
$stats = $stat->fetch(PDO::FETCH_ASSOC);
$stats = $stats ?: ['approved'=>0,'pending'=>0,'rejected'=>0];

/* Paginated & filtered list */
$sql = "
  SELECT gatepass_id, reason, date_for, from_time, to_time, status
  FROM gatepasses
  WHERE user_id = :id AND delflag = 0
";
if ($statusFilter) {
    $sql .= " AND status = :status";
}
$sql .= " ORDER BY created_at DESC LIMIT :per OFFSET :off";

$list = $pdo->prepare($sql);
$list->bindValue(':id', $userId, PDO::PARAM_INT);
if ($statusFilter) {
    $list->bindValue(':status', $statusFilter);
}
$list->bindValue(':per', $perPage, PDO::PARAM_INT);
$list->bindValue(':off', $offset, PDO::PARAM_INT);
$list->execute();
$rows = $list->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ===== Header ===== -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-semibold m-0">Welcome <?= htmlspecialchars($emp['first_name']) ?>!</h3>
    <div id="datetime-bar" class="fw-medium" style="color:#444;">
        <span id="date-span"><?= date('l, j F Y') ?></span> •
        <span id="time-span"><?= date('h:i:s A') ?></span>
    </div>
</div>

<!-- ===== Stat Cards ===== -->
<div class="d-flex flex-wrap gap-3 mb-4">
    <a href="?status=approved" class="text-decoration-none">
        <div
            class="card card-stat shadow-sm bg-success-subtle text-success <?= $statusFilter==='approved' ? 'border border-3 border-success' : '' ?>">
            <div class="card-body"><span class="fs-4 fw-bold"><?= $stats['approved'] ?></span>
                <div>Approved</div>
            </div>
        </div>
    </a>
    <a href="?status=pending" class="text-decoration-none">
        <div
            class="card card-stat shadow-sm bg-warning-subtle text-warning <?= $statusFilter==='pending' ? 'border border-3 border-warning' : '' ?>">
            <div class="card-body"><span class="fs-4 fw-bold"><?= $stats['pending'] ?></span>
                <div>Pending</div>
            </div>
        </div>
    </a>
    <a href="?status=rejected" class="text-decoration-none">
        <div
            class="card card-stat shadow-sm bg-danger-subtle text-danger <?= $statusFilter==='rejected' ? 'border border-3 border-danger' : '' ?>">
            <div class="card-body"><span class="fs-4 fw-bold"><?= $stats['rejected'] ?></span>
                <div>Rejected</div>
            </div>
        </div>
    </a>
    <a href="employee_dashboard.php" class="text-decoration-none">
        <div
            class="card card-stat shadow-sm bg-light text-dark <?= $statusFilter=='' ? 'border border-3 border-secondary' : '' ?>">
            <div class="card-body"><span
                    class="fs-4 fw-bold"><?= $stats['approved'] + $stats['pending'] + $stats['rejected'] ?></span>
                <div>All</div>
            </div>
        </div>
    </a>
</div>

<h4 class="text-center mb-3">Your Gate‑Pass History</h4>

<!-- ===== Table ===== -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sr No</th>
                    <th>Date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows): foreach ($rows as $i=>$g): ?>
                <tr class="status-<?= $g['status'] ?>">
                    <td><?= $offset + $i + 1 ?></td>
                    <td><?= $g['date_for'] ?></td>
                    <td><?= date('g:i a', strtotime($g['from_time'])) ?></td>
                    <td><?= date('g:i a', strtotime($g['to_time'])) ?></td>
                    <td><?= htmlspecialchars($g['reason']) ?></td>
                    <td class="fw-semibold"><?= ucfirst($g['status']) ?></td>
                    <td>
                        <?php if ($g['status'] === 'pending'): ?>
                        <form method="post"
                            onsubmit="return confirm('Are you sure you want to withdraw this request?');">
                            <input type="hidden" name="withdraw_id" value="<?= $g['gatepass_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Withdraw</button>
                        </form>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="7" class="p-4 text-center text-secondary">No
                        requests<?= $statusFilter ? " with status '$statusFilter'" : "" ?>.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== Pagination ===== -->
<?php if ($totalPages > 1): ?>
<nav class="mt-3" aria-label="Pages">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $page==1?'disabled':'' ?>">
            <a class="page-link" href="?<?= http_build_query(['status'=>$statusFilter,'p'=>$page-1]) ?>">« Prev</a>
        </li>
        <?php for($p=1;$p<=$totalPages;$p++): ?>
        <li class="page-item <?= $p==$page?'active':'' ?>">
            <a class="page-link" href="?<?= http_build_query(['status'=>$statusFilter,'p'=>$p]) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page==$totalPages?'disabled':'' ?>">
            <a class="page-link" href="?<?= http_build_query(['status'=>$statusFilter,'p'=>$page+1]) ?>">Next »</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

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