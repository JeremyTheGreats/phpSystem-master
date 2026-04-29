<?php
session_start();
include "../db.php";

// 1. ADMIN ACCESS CHECK
// Note: Role check is case-sensitive 'Admin' based on your system logic
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// 2. FETCH USERS
// Updated to use first_name and last_name as per your database schema
$query = "SELECT user_id, first_name, last_name, email, role, status, points FROM user ORDER BY role ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Directory | CrimsonGate Admin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Outfit:wght@700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --crimson: #ff2e2e;
            --bg: #050505;
            --sidebar-bg: #0d0d0d;
            --panel: #0f0f0f;
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f0f0f0;
            --text-dim: #777777;
            --success: #00ffa3;
            --warning: #ffc107;
            --gold: #ffd700;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--glass-border);
            padding: 2.5rem 1.5rem;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            color: var(--crimson);
            font-weight: 900;
            font-size: 1.5rem;
            margin-bottom: 3.5rem;
            text-align: center;
            letter-spacing: 1px;
        }

        .logo span {
            color: white;
        }

        .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dim);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.3s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }

        .logout-link {
            margin-top: auto;
            color: var(--crimson);
            text-decoration: none;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 280px;
            padding: 4rem;
            width: calc(100% - 280px);
        }

        .header-section {
            margin-bottom: 3rem;
        }

        .header-section h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
        }

        /* --- TABLE --- */
        .table-container {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: rgba(255, 255, 255, 0.02);
            padding: 20px 25px;
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 18px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            vertical-align: middle;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #1a1a1a, #0a0a0a);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit';
            font-weight: 700;
            color: var(--crimson);
        }

        .points-badge {
            background: rgba(255, 215, 0, 0.05);
            border: 1px solid rgba(255, 215, 0, 0.2);
            padding: 6px 12px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gold);
            font-weight: 700;
        }

        .role-badge {
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .role-admin {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }

        .role-user {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        .btn-status {
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            border: 1px solid transparent;
        }

        .btn-approve {
            color: var(--success);
            border-color: rgba(0, 255, 163, 0.2);
        }

        .btn-approve:hover {
            background: var(--success);
            color: #000;
        }

        .btn-suspend {
            color: var(--text-dim);
            border-color: var(--glass-border);
        }

        .btn-suspend:hover {
            border-color: var(--crimson);
            color: var(--crimson);
        }

        .status-dot {
            height: 6px;
            width: 6px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .text-active {
            color: var(--success);
        }

        .text-pending {
            color: var(--warning);
        }

        .text-inactive {
            color: var(--text-dim);
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="logo">CRIMSON<span>ADMIN</span></div>
        <ul class="nav-links">
            <li><a href="admindash.php"><i class="fas fa-chart-pie"></i> Overview</a></li>
            <li><a href="event.php"><i class="fas fa-calendar-check"></i> Events</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="user.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_vouchers.php"><i class="fas fa-tags"></i> Vouchers</a></li>
        </ul>
        <a href="../index.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
    </aside>

    <main class="main-content">
        <header class="header-section">
            <h1>Staff Directory</h1>
            <p style="color: var(--text-dim);">Audit team permissions, reward points, and manage account connectivity.
            </p>
        </header>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Email Identity</th>
                        <th>Access Level</th>
                        <th>Account Points</th>
                        <th>Account Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $status = strtolower($row['status'] ?? 'pending');
                        $points = $row['points'] ?? 0;
                        $dotColor = ($status == 'active') ? 'var(--success)' : (($status == 'pending') ? 'var(--warning)' : 'var(--text-dim)');
                        ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar">
                                        <?php echo strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)); ?>
                                    </div>
                                    <div class="user-details">
                                        <strong><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></strong>
                                        <span style="font-size: 0.7rem; color: var(--text-dim);">ID:
                                            #USR-<?php echo str_pad($row['user_id'], 4, '0', STR_PAD_LEFT); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="color: var(--text-dim); font-size: 0.9rem;">
                                <?php echo htmlspecialchars($row['email']); ?>
                            </td>
                            <td>
                                <span
                                    class="role-badge <?php echo (strtolower($row['role']) == 'admin') ? 'role-admin' : 'role-user'; ?>">
                                    <?php echo $row['role']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="points-badge">
                                    <i class="fas fa-coins"></i> <?php echo number_format($points); ?>
                                </div>
                            </td>
                            <td>
                                <span class="text-<?php echo $status; ?>"
                                    style="font-size: 0.8rem; font-weight: 600; text-transform: capitalize;">
                                    <span class="status-dot" style="background-color: <?php echo $dotColor; ?>"></span>
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($status !== 'active'): ?>
                                    <a href="update_user_status.php?id=<?php echo $row['user_id']; ?>&status=active"
                                        class="btn-status btn-approve">
                                        <i class="fas fa-user-check"></i> Activate
                                    </a>
                                <?php else: ?>
                                    <a href="update_user_status.php?id=<?php echo $row['user_id']; ?>&status=inactive"
                                        class="btn-status btn-suspend"
                                        onclick="return confirm('Suspend access for this user?')">
                                        <i class="fas fa-user-slash"></i> Suspend
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>