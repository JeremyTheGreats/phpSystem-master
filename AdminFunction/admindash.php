<?php
session_start();
include '../db.php';

// SECURITY: Admin only
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// 1. OVERALL SALES
$sales_query = mysqli_query($conn, "SELECT SUM(total_price) as total_sales FROM booking WHERE status = 'confirmed'");
$sales_data = mysqli_fetch_assoc($sales_query);
$overall_sales = $sales_data['total_sales'] ?? 0;

// 2. TICKETS SOLD
$tickets_query = mysqli_query($conn, "SELECT COUNT(*) as total_tickets FROM booking");
$tickets_data = mysqli_fetch_assoc($tickets_query);
$tickets_sold = $tickets_data['total_tickets'] ?? 0;

// 3. WEBSITE USERS
$user_query = mysqli_query($conn, "SELECT COUNT(*) as total_users FROM user");
$user_data = mysqli_fetch_assoc($user_query);
$total_users = $user_data['total_users'] ?? 0;

// 4. UPDATED QUERY: Join event table with venue table to get the name
// Based on image_b5699c.png, we use 'event' (singular)
$events_sql = "SELECT e.*, v.venue_name 
               FROM event e 
               LEFT JOIN venue v ON e.venue_id = v.venue_id 
               ORDER BY e.event_date ASC";
$events_result = mysqli_query($conn, $events_sql);
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

        .nav-links a.active {
            border-left: 3px solid var(--crimson);
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 4rem;
        }

        .header-section {
            margin-bottom: 3rem;
        }

        .header-section h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            padding: 30px;
            border-radius: 20px;
            transition: 0.3s;
        }

        .stat-card:hover {
            border-color: var(--crimson);
            transform: translateY(-5px);
        }

        .stat-card h4 {
            color: var(--text-dim);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 15px;
        }

        .stat-card .value {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
        }

        .stat-card i {
            color: var(--crimson);
            font-size: 1.2rem;
            margin-bottom: 10px;
            display: block;
        }

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

        .status-inactive {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
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
        <a href="../logout.php"
            style="color:var(--crimson); text-decoration:none; padding: 20px; font-weight: bold; margin-top: auto;"><i
                class="fas fa-power-off"></i> Logout</a>
    </aside>

    <main class="main-content">
        <section class="header-section">
            <h1>System Analytics</h1>
            <p style="color: var(--text-dim);">Real-time performance tracking for CrimsonGate.</p>
        </section>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-coins"></i>
                <h4>Overall Sales</h4>
                <div class="value">₱<?php echo number_format($overall_sales, 2); ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-ticket-alt"></i>
                <h4>Tickets Sold</h4>
                <div class="value"><?php echo number_format($tickets_sold); ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-friends"></i>
                <h4>Website Users</h4>
                <div class="value"><?php echo number_format($total_users); ?></div>
            </div>
        </div>

        <div class="table-panel">
            <div class="table-header">
                <h3 style="font-family: 'Outfit'; font-size: 1.1rem;">Live Event Status</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Event Detail</th>
                        <th>Venue</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Seating Capacity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($events_result && mysqli_num_rows($events_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($events_result)): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700;"><?php echo htmlspecialchars($row['artist']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-dim);">
                                        <?php echo htmlspecialchars($row['title']); ?></div>
                                </td>
                                <!-- Displaying venue_name from the JOIN -->
                                <td><?php echo htmlspecialchars($row['venue_name'] ?? 'Unassigned'); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($row['event_date'])); ?><br>
                                    <span
                                        style="font-size: 0.75rem; color: var(--text-dim);"><?php echo date('h:i A', strtotime($row['event_time'])); ?></span>
                                </td>
                                <td>
                                    <span
                                        class="status-pill <?php echo (strtolower($row['status']) == 'active') ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo strtoupper($row['status']); ?>
                                    </span>
                                </td>
                                <!-- Displaying capacity based on your table structure -->
                                <td style="font-weight: 700; color: var(--text-dim);">
                                    <?php echo ($row['total_rows'] * $row['cols_per_row']); ?> Max Seats
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 40px 0;">
                                No events found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>