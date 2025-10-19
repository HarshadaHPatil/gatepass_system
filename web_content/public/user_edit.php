<?php
$pageTitle = 'Edit User';
require_once __DIR__ . '/../config/db.php';
require_once '_layout_start.php';

// ✅ Only HR (role_id = 1) can access
if (!isset($_SESSION['user']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit;
}

// ✅ Get the user ID from the URL
$user_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ✅ Fetch the user details
$stmt = $pdo->prepare("SELECT * FROM user_master WHERE user_id = ? AND delflag = 0");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<div class='alert alert-danger'>User not found!</div>";
    require '_layout_end.php';
    exit;
}

// ✅ Fetch all available roles from user_type_id table
$roles = $pdo->query("SELECT user_type_id, type_name FROM user_type_id WHERE delflag = 0")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $user_type_id = (int) $_POST['user_type_id'];

    $stmt = $pdo->prepare("UPDATE user_master SET first_name = ?, last_name = ?, email = ?, user_type_id = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->execute([$first_name, $last_name, $email, $user_type_id, $user_id]);

    echo "<script>alert('User updated successfully!'); window.location.href='user_list.php';</script>";
    exit;
}
?>

<h3 class="fw-semibold mb-4">Edit User</h3>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>"
                    class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>"
                    class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="user_type_id" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['user_type_id'] ?>"
                        <?= $user['user_type_id'] == $role['user_type_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role['type_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="user_list.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require '_layout_end.php'; ?>