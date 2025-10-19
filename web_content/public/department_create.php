<?php
/* department_create.php – HR can add a new user‑type */
$pageTitle = 'Add Department';
require_once __DIR__.'/../config/db.php';
require_once '_layout_start.php';

/* ── HR‑only guard ── */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: login.php'); exit;
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['type_name'] ?? '');

    if (!$name) {
        $error = 'Department name cannot be empty.';
    } else {
        try {
            $pdo->prepare(
              "INSERT INTO user_type_id (type_name, delflag) VALUES (?,0)"
            )->execute([$name]);
            $success = 'Department added successfully!';
        } catch (PDOException $ex) {
            // 23000 = duplicate entry if you add UNIQUE later
            $error = ($ex->getCode()==23000) ? 'That department already exists.' : $ex->getMessage();
        }
    }
}
?>
<style>
.dep-card {
    max-width: 500px;
    background: #fff;
    padding: 2rem 1.7rem;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, .1);
}

.dep-card .btn-primary {
    background: #1f79ff;
    border: none;
    font-weight: 600;
    border-radius: 10px;
}
</style>

<div class="dep-card container bg-light">
    <h3 class="mb-4 text-center">Add Department</h3>

    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if($error):   ?><div class="alert alert-danger"><?= $error   ?></div><?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Department / User‑Type Name</label>
            <input name="type_name" class="form-control" required>
        </div>

        <div class="d-flex flex-wrap justify-content-between">
            <button class="btn btn-primary">Create</button>
            <a href="hr_dashboard.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<?php require '_layout_end.php'; ?>