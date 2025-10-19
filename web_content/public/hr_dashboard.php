<?php
$pageTitle = 'HR Dashboard';
require_once __DIR__.'/../config/db.php';
require_once '_layout_start.php';

/* ─────────── Guard: HR only ─────────── */
if (!isset($_SESSION['user']) || $_SESSION['role'] != 1) {
    header('Location: login.php'); exit;
}

$hr   = $_SESSION['user'];
$hrId = $hr['user_id'];

/* ─────────── Optional status filter ─────────── */
$view         = $_GET['view'] ?? '';
$statusFilter = '';
if     ($view === 'pending')  $statusFilter = "AND g.status = 'pending'";
elseif ($view === 'approved') $statusFilter = "AND g.status = 'approved'";
elseif ($view === 'rejected') $statusFilter = "AND g.status = 'rejected'";

/* ─────────── Pagination vars ─────────── */
$perPage = 10;
$page    = max(1, intval($_GET['p'] ?? 1));
$offset  = ($page - 1) * $perPage;

/* ─────────── Counts for pager & stat cards ─────────── */
$totalRows = $pdo->query("
    SELECT COUNT(*) FROM gatepasses g
    WHERE g.delflag = 0 $statusFilter
")->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$stats = $pdo->query("
  SELECT  SUM(status='pending')  AS pending,
          SUM(status='approved') AS approved,
          SUM(status='rejected') AS rejected
  FROM gatepasses WHERE delflag = 0
")->fetch(PDO::FETCH_ASSOC);

/* ─────────── Fetch paginated list with user details ─────────── */
$list = $pdo->prepare("
  SELECT g.*, u.first_name, u.user_type_id
  FROM gatepasses g
  JOIN user_master u ON u.user_id = g.user_id
  WHERE g.delflag = 0 $statusFilter
  ORDER BY g.created_at DESC
  LIMIT :per OFFSET :off
");
$list->bindValue(':per', $perPage, PDO::PARAM_INT);
$list->bindValue(':off', $offset, PDO::PARAM_INT);
$list->execute();
$rows = $list->fetchAll(PDO::FETCH_ASSOC);

/* ─────────── Dynamic role name lookup ─────────── */
$roleName = $pdo->query('SELECT user_type_id, type_name FROM user_type_id')
                ->fetchAll(PDO::FETCH_KEY_PAIR);

/* ─────────── Handle approve / reject ─────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['gatepass_id'], $_POST['action'])
    && in_array($_POST['action'], ['approved','rejected']))
{
    $pdo->prepare("
        UPDATE gatepasses
        SET status = ?, approver_id = ?, updated_at = NOW()
        WHERE gatepass_id = ?
    ")->execute([$_POST['action'], $hrId, $_POST['gatepass_id']]);

    header("Location: hr_dashboard.php?view=$view&p=$page"); exit;
}
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-semibold m-0">Welcome <?= htmlspecialchars($hr['first_name']) ?>!</h3>
    <div id="datetime-bar" class="fw-medium" style="color:#444;">
        <span id="date-span"><?= date('l, j F Y') ?></span> •
        <span id="time-span"><?= date('h:i:s A') ?></span>
    </div>
</div>

<!-- Stat Cards -->
<div class="d-flex flex-wrap gap-3 mb-4">
    <a href="?view=pending" class="text-decoration-none">
        <div
            class="card card-stat shadow-sm bg-warning-subtle text-warning <?= $view==='pending' ? 'border border-3 border-warning' : '' ?>">
            <div class="card-body"><span class="fs-4 fw-bold"><?= $stats['pending'] ?></span>
                <div>Pending</div>
            </div>
        </div>
    </a>
    <a href="?view=approved" class="text-decoration-none">
        <div
            class="card card-stat shadow-sm bg-success-subtle text-success <?= $view==='approved' ? 'border border-3 border-success' : '' ?>">
            <div class="card-body"><span class="fs-4 fw-bold"><?= $stats['approved'] ?></span>
                <div>Approved</div>
            </div>
        </div>
    </a>
    <a href="?view=rejected" class="text-decoration-none">
        <div
            class="card card-stat shadow-sm bg-danger-subtle text-danger <?= $view==='rejected' ? 'border border-3 border-danger' : '' ?>">
            <div class="card-body"><span class="fs-4 fw-bold"><?= $stats['rejected'] ?></span>
                <div>Rejected</div>
            </div>
        </div>
    </a>
    <a href="hr_dashboard.php" class="text-decoration-none">
        <div
            class="card card-stat shadow-sm bg-light text-dark <?= $view=='' ? 'border border-3 border-secondary' : '' ?>">
            <div class="card-body"><span
                    class="fs-4 fw-bold"><?= $stats['pending'] + $stats['approved'] + $stats['rejected'] ?></span>
                <div>All</div>
            </div>
        </div>
    </a>
</div>

<h4 class="text-center mb-3">
    <?= $view ? "Showing <span class='text-capitalize'>$view</span> requests"
              : "All Gate‑Pass Requests" ?>
</h4>

<!-- Table -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sr No</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows): foreach ($rows as $i => $g): ?>
                <tr class="status-<?= $g['status'] ?>">
                    <td><?= $offset + $i + 1 ?></td>
                    <td><?= htmlspecialchars($g['first_name']) ?></td>
                    <td><?= htmlspecialchars($roleName[$g['user_type_id']] ?? '—') ?></td>
                    <td><?= $g['date_for'] ?></td>
                    <td><?= date('g:i a', strtotime($g['from_time'])) ?></td>
                    <td><?= date('g:i a', strtotime($g['to_time'])) ?></td>
                    <td><?= htmlspecialchars($g['reason']) ?></td>
                    <td>
                        <?php if ($g['status'] === 'approved'): ?>
                        <span class="badge bg-success">Approved</span>
                        <?php elseif ($g['status'] === 'rejected'): ?>
                        <span class="badge bg-danger">Rejected</span>
                        <?php else: ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($g['status'] === 'pending'): ?>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#actModal"
                            data-id="<?= $g['gatepass_id'] ?>" data-act="approved">Approve</button>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#actModal"
                            data-id="<?= $g['gatepass_id'] ?>" data-act="rejected">Reject</button>
                        <?php elseif ($g['status'] === 'approved'): ?>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#actModal"
                            data-id="<?= $g['gatepass_id'] ?>" data-act="rejected">Reject</button>
                        <?php elseif ($g['status'] === 'rejected'): ?>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#actModal"
                            data-id="<?= $g['gatepass_id'] ?>" data-act="approved">Approve</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="9" class="p-4 text-center text-secondary">No records found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1):
   $base = 'hr_dashboard.php' . ($view ? "?view=$view&" : '?'); ?>
<nav class="mt-3" aria-label="Pages">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $base ?>p=<?= $page-1 ?>">« Prev</a>
        </li>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= $base ?>p=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $base ?>p=<?= $page+1 ?>">Next »</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Confirm Modal -->
<div class="modal fade" id="actModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
            </div>
            <form method="post">
                <div class="modal-body">
                    Are you sure you want to <span id="txtAct">approve</span> this request?
                    <input type="hidden" name="gatepass_id" id="modId">
                    <input type="hidden" name="action" id="modAct">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Yes, proceed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/* Modal filler */
document.getElementById('actModal').addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    modId.value = b.dataset.id;
    modAct.value = b.dataset.act;
    txtAct.textContent = b.dataset.act;
});

/* Live clock */
(() => {
    const d = document.getElementById('date-span');
    const t = document.getElementById('time-span');
    const tick = () => {
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
    };
    tick();
    setInterval(tick, 1000);
})();
</script>

<?php require '_layout_end.php'; ?>