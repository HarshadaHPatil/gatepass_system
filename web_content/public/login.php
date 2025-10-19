<?php
/* login.php  – single‑table version */
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $pwd   = trim($_POST['password'] ?? '');

    /* one query against user_master */
    $stmt = $pdo->prepare(
        "SELECT * FROM user_master
         WHERE email = ? AND delflag = 0
         LIMIT 1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    /* check password (plain text for now) */
    if ($user && $user['password'] === $pwd) {

        /* store who & what‑type in session */
        $_SESSION['user'] = $user;               // the whole row
        $_SESSION['role'] = $user['user_type_id']; // 1 = HR, 2 = Emp, 3 = Int

        /* send to the correct dashboard */
        switch ($user['user_type_id']) {
            case 1:
                header('Location: hr_dashboard.php');
                break; // HR
            case 3:
                header('Location: intern_dashboard.php');
                break; // Intern
            default:
                header('Location: employee_dashboard.php');
                break; // Employee + any new role
        }
        exit;
    }

    /* login failed */
    $error = 'Invalid email or password';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Seaquid – Login</title>
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
            radial-gradient(circle at calc(var(--my)) calc(100% - var(--mx)), #9da4b0 0%, rgba(157, 164, 176, 0) 60%),
            #5d7fad;
        background-attachment: fixed;
    }

    .card-box {
        width: 500px;
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
        max-height: 70%
    }

    h2 {
        font-weight: 600;
        font-size: 1.45rem;
        color: #12305c;
        margin-top: 70px;
        margin-bottom: .35rem;
    }

    h5 {
        font-weight: 500;
        color: #12305c;
        margin-bottom: 2.1rem;
    }

    .form-control {
        border-radius: .75rem;
        font-size: .95rem
    }

    .password-wrap {
        position: relative;
    }

    .toggle-eye {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        width: 24px;
        height: 24px;
        cursor: pointer;
        color: #6c757d;
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
        background: #1763d6
    }

    .footer-note {
        margin-top: 2rem;
        font-size: .8rem;
        color: #4c4c4c
    }

    #error-msg {
        color: #e74c3c
    }
    </style>
</head>

<body>

    <div class="card-box">
        <div class="top-logo"><img src="assets/seaquid logo.png" alt="Seaquid logo"></div>

        <h2>Seaquid Technology India Pvt Ltd</h2>
        <h5>GatePass Application</h5>
        <?php if ($error): ?><p id="error-msg"><?= $error ?></p><?php endif; ?>

        <form method="post" autocomplete="off" class="text-start">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control mb-3" required>

            <label class="form-label">Password</label>
            <div class="password-wrap mb-4">
                <input id="pwd" type="password" name="password" class="form-control" required>
                <!-- closed eye icon by default -->
                <svg id="eye" class="toggle-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 1l22 22" />
                    <path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a20.29 20.29 0 014.06-5.26" />
                    <path d="M9.53 9.53a3 3 0 004.24 4.24" />
                    <path d="M12 12a3 3 0 013 3" />
                </svg>
            </div>

            <!-- right‑aligned forgot pwd link -->
            <div class="text-end mb-3">
                <a href="forgot_password.php" class="link-primary" style="font-size:0.9rem;">Forgot&nbsp;password?</a>
            </div>


            <button class="btn btn-blue py-2">Login</button>
        </form>

        <div class="footer-note">© <?= date('Y') ?> Seaquid Technology India Pvt Ltd | GatePass System</div>
    </div>

    <script>
    /* mouse‑wave background */
    document.addEventListener('mousemove', e => {
        document.documentElement.style.setProperty('--mx', (e.clientX / innerWidth * 100) + '%');
        document.documentElement.style.setProperty('--my', (e.clientY / innerHeight * 100) + '%');
    });

    /* show / hide password with eye toggle */
    const eye = document.getElementById('eye');
    const pwd = document.getElementById('pwd');
    let shown = false;

    const getIcon = isShown => isShown ?
        `<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
     <circle cx="12" cy="12" r="3"/>` :
        `<path d="M1 1l22 22"/>
     <path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a20.29 20.29 0 014.06-5.26"/>
     <path d="M9.53 9.53a3 3 0 004.24 4.24"/>
     <path d="M12 12a3 3 0 013 3"/>`;

    eye.addEventListener('click', () => {
        shown = !shown;
        pwd.type = shown ? 'text' : 'password';
        eye.innerHTML = getIcon(shown);
    });
    </script>
</body>

</html>