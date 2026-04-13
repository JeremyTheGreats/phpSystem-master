<?php
session_start();
include '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// FETCH LIVE STATS
$rev_query = mysqli_query($conn, "SELECT SUM(price) as total FROM bookings WHERE status = 'confirmed'");
$rev_data = mysqli_fetch_assoc($rev_query);
$total_revenue = $rev_data['total'] ?? 0;

$ticket_query = mysqli_query($conn, "SELECT COUNT(*) as ticket_count FROM bookings");
$ticket_data = mysqli_fetch_assoc($ticket_query);
$tickets_sold = $ticket_data['ticket_count'] ?? 0;

$active_query = mysqli_query($conn, "SELECT COUNT(*) as active_count FROM events WHERE status = 'active'");
$active_data = mysqli_fetch_assoc($active_query);
$active_events = $active_data['active_count'] ?? 0;

$user_count_query = mysqli_query($conn, "SELECT COUNT(*) as u_count FROM user WHERE role = 'user'");
$user_data = mysqli_fetch_assoc($user_count_query);
$total_users = $user_data['u_count'] ?? 0;

$events_result = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Console | CrimsonGate</title>
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

        /* --- UNIFIED SIDEBAR (From Bookings) --- */
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
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }

        .nav-links a.active {
            border-left: 3px solid var(--crimson);
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
            flex: 1;
            margin-left: 280px;
            padding: 4rem;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 3rem;
        }

        .header-section h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
        }

        .btn-primary {
            background: var(--crimson);
            color: #fff;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
            font-family: 'Outfit';
            font-size: 0.8rem;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 46, 46, 0.4);
        }

        /* --- STATS CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            padding: 25px;
            border-radius: 20px;
        }

        .stat-card h4 {
            color: var(--text-dim);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
        }

        /* --- TABLE PANEL --- */
        .table-panel {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
        }

        .table-header {
            padding: 25px 30px;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--glass-border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 15px 30px;
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 20px 30px;
            border-bottom: 1px solid var(--glass-border);
            font-size: 0.95rem;
        }

        .status-pill {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-active {
            background: rgba(0, 255, 163, 0.1);
            color: var(--success);
        }

        .status-soldout {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            color: var(--text-dim);
            text-decoration: none;
            transition: 0.2s;
        }

        .icon-btn:hover {
            background: var(--crimson);
            color: white;
            border-color: var(--crimson);
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="logo">CRIMSON<span>ADMIN</span></div>

        <ul class="nav-links">
            <li><a href="admindash.php" class="active"><i class="fas fa-chart-pie"></i> Overview</a></li>
            <li><a href="event.php"><i class="fas fa-calendar-check"></i> Events</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="user.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_vouchers.php"><i class="fas fa-tags"></i> Vouchers</a></li>
        </ul>

        <a href="../index.php" class="logout-link">
            <i class="fas fa-power-off"></i> Logout
        </a>
    </aside>

    <main class="main-content">
        <section class="header-section">
            <div>
                <h1>System Overview</h1>
                <p style="color: var(--text-dim);">Live analytical data from your event gateways.</p>
            </div>

        </section>

        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Revenue</h4>
                <div class="value">₱<?php echo number_format($total_revenue, 0); ?></div>
            </div>
            <div class="stat-card">
                <h4>Tickets Issued</h4>
                <div class="value"><?php echo number_format($tickets_sold); ?></div>
            </div>
            <div class="stat-card">
                <h4>Active Gates</h4>
                <div class="value"><?php echo $active_events; ?></div>
            </div>
            <div class="stat-card">
                <h4>Platform Users</h4>
                <div class="value"><?php echo $total_users; ?></div>
            </div>
        </div>

        <div class="table-panel">
            <div class="table-header">
                <h3 style="font-family: 'Outfit'; font-size: 1.1rem;">Live Event Schedule</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Performance</th>
                        <th>Venue</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Tools</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($events_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($events_result)): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700;"><?php echo htmlspecialchars($row['artist']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-dim);">
                                        <?php echo htmlspecialchars($row['title']); ?></div>
                                </td>
                                <td><i class="fas fa-location-dot" style="color: var(--crimson); margin-right: 5px;"></i>
                                    <?php echo htmlspecialchars($row['venue']); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($row['event_date'])); ?><br>
                                    <span
                                        style="font-size: 0.75rem; color: var(--text-dim);"><?php echo date('h:i A', strtotime($row['event_time'])); ?></span>
                                </td>
                                <td>
                                    <span
                                        class="status-pill <?php echo ($row['status'] == 'active') ? 'status-active' : 'status-soldout'; ?>">
                                        <?php echo strtoupper($row['status']); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 700;">₱<?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <a href="editevent.php?id=<?php echo $row['id']; ?>" class="icon-btn"><i
                                            class="fas fa-edit"></i></a>
                                    <a href="delete_event.php?id=<?php echo $row['id']; ?>" class="icon-btn"
                                        onclick="return confirm('Archive this event?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-dim); padding: 40px;">No events active.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>