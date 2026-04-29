<?php
session_start();
include '../db.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Organizer') {
    header("Location: ../login.php");
    exit;
}

$organizer_email = $_SESSION['email'];

// 2. FETCH STATISTICS (Using Prepared Statements for Security)
// Total Events
$stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM event WHERE organizer_id = ?");
$stmt1->bind_param("s", $_SESSION['user_id']);
$stmt1->execute();
$event_count = $stmt1->get_result()->fetch_assoc()['total'];

// Total Bookings for their events
$stmt2 = $conn->prepare("SELECT COUNT(b.booking_id) as total FROM booking b 
                         JOIN event e ON b.event_id = e.event_id 
                         WHERE e.organizer_id = ?");
$stmt2->bind_param("s", $_SESSION['user_id']);
$stmt2->execute();
$booking_count = $stmt2->get_result()->fetch_assoc()['total'];

// Total Revenue
$stmt3 = $conn->prepare("SELECT SUM(e.price) as total FROM booking b 
                         JOIN event e ON b.event_id = e.event_id 
                         WHERE e.organizer_id = ?");
$stmt3->bind_param("s", $_SESSION['user_id']);
$stmt3->execute();
$revenue = $stmt3->get_result()->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Console | CrimsonGate</title>
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
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            color: var(--crimson);
            font-weight: 900;
            font-size: 1.5rem;
            margin-bottom: 3.5rem;
            text-align: center;
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
            font-weight: 600;
            display: flex;
            gap: 10px;
            padding: 10px;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 4rem;
        }

        .welcome-sec {
            margin-bottom: 3rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .welcome-sec h1 {
            font-family: 'Outfit';
            font-size: 2.5rem;
            margin-top: 5px;
        }

        .create-event-btn {
            background: var(--crimson);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .create-event-btn:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        /* --- STAT CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--panel);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--crimson);
        }

        .stat-card i {
            color: var(--crimson);
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .stat-val {
            font-size: 2rem;
            font-weight: 800;
            font-family: 'Outfit';
            display: block;
        }

        .stat-label {
            color: var(--text-dim);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- TABLE --- */
        .section-card {
            background: var(--panel);
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            padding: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            color: var(--text-dim);
            font-size: 0.75rem;
            text-transform: uppercase;
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 18px 15px;
            border-bottom: 1px solid var(--glass-border);
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-active {
            background: rgba(0, 255, 163, 0.1);
            color: #00ffa3;
        }

        .status-soldout {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="logo">CRIMSON<span>ORG</span></div>
        <ul class="nav-links">
            <li><a href="organizerdash.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="manage_events.php"><i class="fas fa-calendar-check"></i> My Events</a></li>
            <li><a href="sales_report.php"><i class="fas fa-chart-line"></i> Sales Report</a></li>
            <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
        </ul>
        <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </aside>

    <main class="main-content">
        <div class="welcome-sec">
            <div>
                <p style="color: var(--crimson); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                    Organizer Overview</p>
                <h1>Welcome Back, <?php echo htmlspecialchars(explode('@', $organizer_email)[0]); ?>!</h1>
            </div>
            <a href="create_event.php" class="create-event-btn"><i class="fas fa-plus"></i> NEW EVENT</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-microphone-lines"></i>
                <span class="stat-val"><?php echo $event_count; ?></span>
                <span class="stat-label">Total Events</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <span class="stat-val"><?php echo number_format($booking_count); ?></span>
                <span class="stat-label">Total Attendees</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-wallet"></i>
                <span class="stat-val">₱<?php echo number_format($revenue, 2); ?></span>
                <span class="stat-label">Total Revenue</span>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h2 style="font-family: 'Outfit';">Recent Performance</h2>
                <a href="manage_events.php"
                    style="color: var(--crimson); text-decoration: none; font-size: 0.8rem; font-weight: 700;">View All
                    Events →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Venue</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch recent events with Venue Names
                    $event_query = $conn->prepare("SELECT e.*, v.venue_name 
                                                 FROM event e 
                                                 LEFT JOIN venue v ON e.venue_id = v.venue_id 
                                                 WHERE e.organizer_id = ? 
                                                 ORDER BY e.event_date DESC LIMIT 5");
                    $event_query->bind_param("s", $_SESSION['user_id']);
                    $event_query->execute();
                    $events = $event_query->get_result();

                    if ($events->num_rows > 0):
                        while ($row = $events->fetch_assoc()):
                            ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td style="color: var(--text-dim);"><?php echo htmlspecialchars($row['venue_name'] ?? 'N/A'); ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                                <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <span
                                        class="status-badge <?php echo (strtolower($row['status']) == 'active') ? 'status-active' : 'status-soldout'; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 40px;">No events
                                created yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>