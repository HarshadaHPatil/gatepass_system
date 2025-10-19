<?php

$pageTitle = 'Forgot Password';
require_once __DIR__.'/../config/db.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT 1 FROM user_master WHERE email = ? AND delflag = 0');
        $stmt->execute([$email]);

        if ($stmt->fetchColumn()) {
            header('Location: reset_password.php?email=' . urlencode($email));
            exit;
        } else {
            $error = 'No account found with that email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
    :root {
        --mx: 50%;
        --my: 50%;
    }

    html,
    body {
        height: 100%;
        margin: 0;
        font-family: "Poppins", sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background:
            radial-gradient(circle at var(--mx) var(--my), #1f79ff 0%, rgba(31, 121, 255, 0) 60%),
            radial-gradient(circle at calc(100% - var(--mx)) calc(100% - var(--my)), #ff8b38 0%, rgba(255, 139, 56, 0) 60%),
            radial-gradient(circle at var(--my) calc(100% - var(--mx)), #9da4b0 0%, rgba(157, 164, 176, 0) 60%),
            #5d7fad;
        background-attachment: fixed;
    }

    /* ===== login‑style card ===== */
    .card-box {
        width: 500px;
        max-width: 92%;
        padding: 3.4rem 3rem 2.4rem;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 12px 32px rgba(0, 0, 0, .12);
        text-align: center;
        position: relative;
    }

    .top-logo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #1f79ff;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: -55px;
        left: 50%;
        transform: translateX(-50%);
    }

    .top-logo img {
        max-width: 70%;
        max-height: 70%;
    }

    .card-box h3 {
        font-weight: 600;
        font-size: 1.45rem;
        color: #12305c;
        margin-top: 70px;
        margin-bottom: 1.5rem;
    }

    .card-box .form-control {
        height: 46px;
        border-radius: .85rem;
        font-size: .95rem;
    }

    .btn-blue {
        width: 100%;
        border-radius: .9rem;
        font-weight: 600;
        background: #1f79ff;
        border: none;
        color: #fff;
    }

    .btn-blue:hover {
        background: #1763d6;
    }

    .alert {
        font-size: .92rem;
        padding: .65rem 1rem;
    }

    @media(max-width:440px) {
        .card-box {
            padding: 2.4rem 1.4rem 1.8rem;
        }
    }
    </style>
</head>

<body>
    <div class="card-box">
        <!-- floating logo -->
        <div class="top-logo"><img src="assets/seaquid logo.png" alt="Seaquid logo"></div>

        <h3>Forgot Password</h3>

        <?php if($success): ?>
        <div class="alert alert-success mb-3"><?= $success ?></div>
        <a href="login.php" class="btn btn-blue">Back to Login</a>
        <?php else: ?>
        <?php if($error): ?><div class="alert alert-danger mb-3"><?= $error ?></div><?php endif; ?>

        <form method="post" class="text-start">
            <label class="form-label mb-1">Email Address</label>
            <input type="email" name="email" class="form-control mb-4" required>

            <button class="btn btn-blue py-2">Submit</button>
        </form>

        <a href="login.php" class="d-block mt-3" style="font-size:.9rem;">Back to Login</a>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('mousemove', e => {
        document.documentElement.style.setProperty('--mx', (e.clientX / innerWidth * 100) + '%');
        document.documentElement.style.setProperty('--my', (e.clientY / innerHeight * 100) + '%');
    });
    </script>
</body>

</html>