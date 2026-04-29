<?php
session_start();
include '../db.php';

// 1. ADMIN ACCESS CHECK
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// 2. FETCH EVENTS (JOIN with venue table to get venue_name)
// Based on image_b5699c.png, we use 'event' table
$query = "SELECT e.*, v.venue_name 
          FROM event e 
          LEFT JOIN venue v ON e.venue_id = v.venue_id 
          ORDER BY e.event_date ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Console | CrimsonGate Admin</title>
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
            margin-left: 280px;
            padding: 4rem;
            width: calc(100% - 280px);
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

        .create-btn {
            background: var(--crimson);
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 800;
            font-family: 'Outfit';
            font-size: 0.8rem;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .create-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 46, 46, 0.4);
        }

        /* --- GRID & CARDS --- */
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .event-card {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            overflow: hidden;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
        }

        .event-card:hover {
            border-color: rgba(255, 46, 46, 0.5);
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .poster-area {
            height: 400px;
            /* Taller height for movie/concert posters */
            background-size: cover;
            background-position: center;
            position: relative;
            background-color: #1a1a1a;
            /* Fallback color */
        }

        .poster-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(0, 0, 0, 0.8));
        }

        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 900;
            text-transform: uppercase;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 2;
        }

        .details-area {
            padding: 25px;
            flex-grow: 1;
            position: relative;
            z-index: 2;
        }

        .details-area h3 {
            font-family: 'Outfit';
            font-size: 1.4rem;
            margin-bottom: 8px;
            color: #fff;
        }

        .info-tag {
            color: var(--text-dim);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .info-tag i {
            color: var(--crimson);
            font-size: 0.8rem;
            width: 15px;
            text-align: center;
        }

        .footer-row {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-label {
            font-family: 'Outfit';
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--success);
        }

        .action-links {
            display: flex;
            gap: 10px;
        }

        .icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            color: var(--text-dim);
            display: flex;
            align-items: center;
            justify-content: center;
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
            <li><a href="admindash.php"><i class="fas fa-chart-pie"></i> Overview</a></li>
            <li><a href="event.php" class="active"><i class="fas fa-calendar-check"></i> Events</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="user.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_vouchers.php"><i class="fas fa-tags"></i> Vouchers</a></li>
        </ul>
        <a href="../logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
    </aside>

    <main class="main-content">
        <header class="header-section">
            <div>
                <h1>Event Console</h1>
                <p style="color: var(--text-dim);">Live stage management and booking availability.</p>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="venue.php" class="create-btn"
                    style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);">
                    <i class="fas fa-map-location-dot"></i> ADD VENUE
                </a>
                <a href="createevent.php" class="create-btn">
                    <i class="fas fa-plus"></i> NEW EVENT
                </a>
            </div>
        </header>

        <section class="event-grid">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $isActive = (strtolower($row['status']) == 'active');
                    $badgeStyle = $isActive ? 'background: rgba(0, 255, 163, 0.15); color: #00ffa3;' : 'background: rgba(255, 46, 46, 0.15); color: var(--crimson);';
                    $current_id = $row['event_id']; // Matches image_b5699c.png
                    $poster_path = htmlspecialchars($row['poster']);
                    ?>
                    <div class="event-card">
                        <div class="poster-area" style="background-image: url('../<?php echo $poster_path; ?>');">
                            <div class="poster-overlay"></div>
                            <div class="status-badge" style="<?php echo $badgeStyle; ?>">
                                <i class="fas fa-circle"
                                    style="font-size: 0.4rem; margin-right: 5px; vertical-align: middle;"></i>
                                <?php echo htmlspecialchars($row['status']); ?>
                            </div>
                        </div>

                        <div class="details-area">
                            <h3><?php echo htmlspecialchars($row['artist']); ?></h3>
                            <div class="info-tag"><i class="fas fa-music"></i> <?php echo htmlspecialchars($row['title']); ?>
                            </div>

                            <!-- Fixed Info Layout -->
                            <div class="info-group">
                                <div class="info-tag"><i class="fas fa-location-dot"></i>
                                    <?php echo htmlspecialchars($row['venue_name'] ?? 'No Venue Assigned'); ?></div>
                                <div class="info-tag"><i class="fas fa-calendar-day"></i>
                                    <?php echo date('M d, Y', strtotime($row['event_date'])); ?></div>
                                <div class="info-tag"><i class="fas fa-clock"></i>
                                    <?php echo date('h:i A', strtotime($row['event_time'])); ?></div>
                            </div>

                            <div class="footer-row">
                                <div class="price-label">₱<?php echo number_format($row['price'], 2); ?></div>
                                <div class="action-links">
                                    <a href="editevent.php?id=<?php echo $current_id; ?>" class="icon-btn" title="Edit Event">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <a href="delete_event.php?id=<?php echo $current_id; ?>" class="icon-btn"
                                        onclick="return confirm('Delete this event?')" title="Delete Event">
                                        <i class="fas fa-trash-can"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 100px; color: var(--text-dim);">
                    <i class="fas fa-calendar-xmark" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.2;"></i>
                    <p>No events found in the registry.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>