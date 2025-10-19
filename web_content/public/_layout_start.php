<?php
/*  _layout_start.php – lavender UI, numeric role‑IDs (1 hr | 2 emp | 3 int) */
if (session_status() === PHP_SESSION_NONE) session_start();

/* ── translate numeric role ID → word we use in links ── */
$roleId = $_SESSION['role'] ?? 0;
$roleWord = match ($roleId) {
    1       => 'hr',
    2       => 'employee',
    3       => 'intern',
    default => 'employee'  // ✅ fallback for unknown role ids
};

/* where does “Dashboard / Overview” link point? */
$dashPage = match ($roleWord) {
    'intern'   => 'intern_dashboard.php',
    'employee' => 'employee_dashboard.php',
    'hr'       => 'hr_dashboard.php',
};

/* highlight logic */
$pageTitle        = $pageTitle ?? '';
$isDashActive     = in_array($pageTitle,
                     ['Employee Dashboard','Intern Dashboard','HR Dashboard']);
$isGateActive     = ($pageTitle === 'New Gate‑Pass');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?: 'Gate‑Pass System' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
    :root {
        --lavender-bg: #e7e9f8;
        --lavender-card: #f8f9fd;
        --indigo: #3c5bff;
        --text-dark: #2b2b49;
        --link-hover: #eef0fc;
        --card-radius: 1.25rem;
    }

    body {
        font-family: "Inter", sans-serif;
        height: 100vh;
        overflow: hidden;
        background: var(--lavender-bg);
        color: var(--text-dark);
    }

    /* ── Sidebar ── */
    .sidebar {
        width: 260px;
        background: #fff;
        box-shadow: 4px 0 20px rgba(0, 0, 0, .06);
        border-right: 1px solid #dfe3f4;
        display: flex;
        flex-direction: column;
        padding-inline: 1rem;
        position: relative;
    }

    .sidebar a {
        display: block;
        padding: .75rem 1rem;
        margin: .35rem 0;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        border-radius: .8rem;
        transition: background .18s, transform .18s;
    }

    .sidebar a:hover {
        background: var(--link-hover);
        transform: translateX(6px);
    }

    .sidebar a.active {
        background: var(--indigo);
        color: #fff !important;
    }

    .logo-box img {
        width: 230px;
        height: 72px;
        padding-right: 30px;
        border-radius: .65rem;
    }

    .logout-link {
        position: absolute;
        left: 1rem;
        right: 1rem;
        bottom: 1.2rem;
        text-align: center;
        padding: .6rem 0;
        border: none;
        border-radius: 1rem;
        font-weight: 600;
        background: var(--indigo);
        color: #fff;
        transition: background .18s;
    }

    .logout-link:hover {
        background: #dc3545 !important;
        color: #fff !important;
    }

    /* table row colours */
    .status-approved {
        background: #d2f4d2 !important;
    }

    .status-pending {
        background: #fff6d5 !important;
    }

    .status-rejected {
        background: #ffd8d8 !important;
    }

    main {
        overflow: auto;
        width: 100%;
        padding: 2rem;
    }

    .card {
        border-radius: var(--card-radius);
        background: var(--lavender-card);
    }

    .card-stat {
        min-width: 200px;
        border: 0;
    }
    </style>
</head>

<body class="d-flex">

    <!-- ───────────── Sidebar ───────────── -->
    <nav class="sidebar">

        <!-- Logo -->
        <div class="logo-box p-4 pb-3">
            <img src="/gatepass_system/public/assets/namelogo-removebg-preview.png" alt="logo">
        </div>

        <!-- Dashboard / Overview -->
        <a href="<?= $dashPage ?>" class="<?= $isDashActive ? 'active' : '' ?>">
            <?= $roleWord==='hr' ? 'Overview' : 'Dashboard' ?>
        </a>

        <!-- Show Gate‑Pass option to all non‑HR users -->
        <?php if ($roleId != 1): ?>
        <a href="gatepass_form.php" class="<?= $isGateActive ? 'active' : '' ?>">Request Gate‑Pass</a>
        <?php endif; ?>

        <!-- HR-specific links removed:
        <a href="hr_dashboard.php?view=pending">Pending</a>
        <a href="hr_dashboard.php?view=approved">Approved</a>
        <a href="hr_dashboard.php?view=rejected">Rejected</a>
        -->

        <!-- HR user management -->
        <?php if ($roleWord==='hr'): ?>
        <a href="user_create.php" class="<?= $pageTitle==='Add User'?'active':'' ?>">Add User</a>
        <a href="department_create.php" class="<?= $pageTitle==='Add Department'?'active':'' ?>">Add Department</a>
        <a href="user_list.php" class="<?= $pageTitle==='All Users'?'active':'' ?>">All Users</a>

        <?php endif; ?>

        <!-- Logout -->
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>

    <!-- ───────────── Main content ───────────── -->
    <main>