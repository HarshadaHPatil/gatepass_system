<?php
$pageTitle = 'All Users';
require_once __DIR__ . '/../config/db.php';
require_once '_layout_start.php';

/* ✅ Only HR can access */
if (!isset($_SESSION['user']) || $_SESSION['role'] != 1) {
    header('Location: login.php'); exit;
}

/* ✅ Handle deletion */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delId = intval($_POST['delete_user_id']);
    $pdo->prepare("UPDATE user_master SET delflag = 1 WHERE user_id = ?")->execute([$delId]);
    header("Location: user_list.php"); // Correct redirect
    exit;
}

/* ✅ Fetch all users with roles */
$stmt = $pdo->query("
    SELECT um.user_id, um.first_name, um.last_name, um.email, ut.type_name
    FROM user_master um
    LEFT JOIN user_type_id ut ON um.user_type_id = ut.user_type_id
    WHERE um.delflag = 0
    ORDER BY um.first_name
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- 🔗 Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<h3 class="fw-semibold mb-4">All Registered Users</h3>

<!-- 🔍 Live Search Input -->
<div class="mb-3" style="max-width: 400px;">
    <div class="input-group">
        <input type="text" id="liveSearch" class="form-control" placeholder="Search by name or email">
        <span class="input-group-text bg-white">
            <i class="bi bi-search text-secondary"></i>
        </span>
    </div>
</div>

<!-- ✅ Users Table -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0" id="usersTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody id="usersTbody">
                <?php if ($users): foreach ($users as $i => $user): ?>
                <tr>
                    <td class="index"><?= $i + 1 ?></td>
                    <td class="search-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td class="search-email"><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['type_name'] ?? 'Unknown') ?></td>
                    <td>
                        <a href="user_edit.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-outline-primary"
                            title="Edit">
                            Edit
                        </a>
                    </td>
                    <td>
                        <form method="POST" class="d-inline"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="delete_user_id" value="<?= $user['user_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="6" class="p-4 text-center text-muted">No users found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 🔁 Pagination Controls -->
<nav class="mt-3" aria-label="Page navigation">
    <ul class="pagination justify-content-center" id="pagination"></ul>
</nav>

<!-- ✅ Live Search + Pagination Script -->
<script>
let allRows = Array.from(document.querySelectorAll('#usersTbody tr'));
const perPage = 10;
let currentPage = 1;

function getFilteredRows() {
    const query = document.getElementById('liveSearch').value.toLowerCase();
    return allRows.filter(row => {
        const name = row.querySelector('.search-name').textContent.toLowerCase();
        const email = row.querySelector('.search-email').textContent.toLowerCase();
        return name.includes(query) || email.includes(query);
    });
}

function showPage(page, filteredRows) {
    const start = (page - 1) * perPage;
    const end = start + perPage;

    allRows.forEach(row => row.style.display = 'none');
    filteredRows.slice(start, end).forEach(row => row.style.display = '');

    renderPagination(filteredRows.length, page);
}

function renderPagination(totalItems, current) {
    const totalPages = Math.ceil(totalItems / perPage);
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if (totalPages <= 1) return;

    const createPageItem = (label, page, disabled = false, active = false) => {
        const li = document.createElement('li');
        li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
        li.innerHTML = `<a class="page-link" href="#">${label}</a>`;
        li.addEventListener('click', e => {
            e.preventDefault();
            if (!disabled) {
                currentPage = page;
                showPage(currentPage, getFilteredRows());
            }
        });
        return li;
    };

    pagination.appendChild(createPageItem('Previous', current - 1, current === 1));
    for (let i = 1; i <= totalPages; i++) {
        pagination.appendChild(createPageItem(i, i, false, i === current));
    }
    pagination.appendChild(createPageItem('Next', current + 1, current === totalPages));
}

document.getElementById('liveSearch').addEventListener('input', () => {
    currentPage = 1;
    showPage(currentPage, getFilteredRows());
});

showPage(currentPage, getFilteredRows());
</script>

<?php require '_layout_end.php'; ?>