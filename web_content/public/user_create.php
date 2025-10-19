<?php
$pageTitle = 'Add User';
require_once __DIR__.'/../config/db.php';
require_once '_layout_start.php';

/* ── HR‑only guard ── */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: login.php'); exit;
}

/* ── fetch all active roles for dropdown ── */
$roles = $pdo->query('SELECT user_type_id, type_name
                      FROM user_type_id
                      WHERE delflag = 0
                      ORDER BY user_type_id')
             ->fetchAll(PDO::FETCH_KEY_PAIR);  // [id => name]

$error = '';

/* ── handle form submit ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first  = trim($_POST['first_name']  ?? '');
    $last   = trim($_POST['last_name']   ?? '');
    $email  = trim($_POST['email']       ?? '');
    $pwd    = trim($_POST['password']    ?? '');
    $roleId = intval($_POST['user_type_id'] ?? 0);

    if (!$first || !$last || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || !$pwd  || !array_key_exists($roleId, $roles)) {
        $error = 'Please fill every field correctly.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO user_master
                (first_name, last_name, email, password, user_type_id, delflag)
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$first, $last, $email, $pwd, $roleId]);

            // ✅ Redirect to user_list.php so new user shows immediately
            header("Location: user_list.php");
            exit;

        } catch (PDOException $ex) {
            if ($ex->getCode() == 23000) {
                $error = 'That email already exists.';
            } else {
                throw $ex;
            }
        }
    }
}
?>

<!-- ───────── styles ───────── -->
<style>
.user-form {
    max-width: 720px;
    background: #fff;
    padding: 2.5rem 2rem;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.user-form .form-label {
    font-weight: 500;
    color: #2b2b2b;
    font-size: .95rem;
}

.user-form .form-control,
.user-form .form-select {
    height: 45px;
    border-radius: 12px;
    font-size: .95rem;
    padding: 0 1rem;
}

.user-form .btn-primary {
    background: #1f79ff;
    border: none;
    padding: 10px 24px;
    font-weight: 600;
    border-radius: 10px;
}

.user-form .btn-secondary {
    border-radius: 10px;
}

@media(max-width:768px) {
    .user-form {
        padding: 1.5rem 1rem;
    }

    .user-form .btn {
        width: 100%;
        margin-top: 10px;
    }
}
</style>

<!-- ───────── form content ───────── -->
<div class="user-form container bg-light">
    <h3 class="mb-4 text-center">Add New User</h3>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" class="row g-4">

        <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input class="form-control" name="first_name" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input class="form-control" name="last_name" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Password</label>
            <input type="text" class="form-control" name="password" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Role</label>
            <select class="form-select" name="user_type_id" required>
                <option value="">Choose Role...</option>
                <?php foreach ($roles as $id => $name): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-12 d-flex flex-wrap justify-content-between">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="hr_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

    </form>
</div>

<?php require '_layout_end.php'; ?>