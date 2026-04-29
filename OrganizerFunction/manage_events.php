<?php
session_start();
include '../db.php';

// Top of manage_events.php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Organizer') {
    header("Location: ../login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
// Use $user_id in your queries instead of $organizer_id

// 2. FETCH ALL EVENTS FOR THIS ORGANIZER
$query = "SELECT e.*, v.venue_name 
          FROM event e 
          LEFT JOIN venue v ON e.venue_id = v.venue_id 
          WHERE e.organizer_id = ? 
          ORDER BY e.event_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage My Events | CrimsonGate</title>
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
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 4rem;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .header-flex h1 {
            font-family: 'Outfit';
            font-size: 2.2rem;
        }

        .btn-add {
            background: var(--crimson);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        /* --- EVENT CARDS GRID --- */
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .event-card {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: 0.3s;
        }

        .event-card:hover {
            transform: translateY(-5px);
            border-color: var(--crimson);
        }

        .poster-box {
            height: 180px;
            width: 100%;
            position: relative;
        }

        .poster-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-active {
            background: #00ffa3;
            color: #000;
        }

        .status-inactive {
            background: var(--crimson);
            color: #fff;
        }

        .card-body {
            padding: 20px;
        }

        .card-body h3 {
            font-family: 'Outfit';
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-dim);
            font-size: 0.85rem;
            margin-bottom: 8px;
        }

        .info-row i {
            color: var(--crimson);
            width: 15px;
        }

        .card-footer {
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.02);
            border-top: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-btns {
            display: flex;
            gap: 10px;
        }

        .action-btns a {
            color: var(--text-dim);
            transition: 0.3s;
            font-size: 1.1rem;
        }

        .action-btns a:hover {
            color: var(--crimson);
        }

        .price-tag {
            font-weight: 800;
            color: var(--text-main);
            font-size: 1.1rem;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="logo">CRIMSON<span>ORG</span></div>
        <ul class="nav-links">
            <li><a href="organizerdash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="manage_events.php" class="active"><i class="fas fa-calendar-check"></i> My Events</a></li>
            <li><a href="sales_report.php"><i class="fas fa-chart-line"></i> Sales Report</a></li>
            <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
        </ul>
        <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </aside>

    <main class="main-content">
        <div class="header-flex">
            <div>
                <h1>My Events</h1>
                <p style="color: var(--text-dim);">Manage your active performances and bookings.</p>
            </div>
            <a href="create_event.php" class="btn-add"><i class="fas fa-plus"></i> CREATE EVENT</a>
        </div>

        <div class="event-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="event-card">
                        <div class="poster-box">
                            <img src="../<?php echo htmlspecialchars($row['poster']); ?>" alt="Poster">
                            <span
                                class="status-tag <?php echo (strtolower($row['status']) == 'active') ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h3>
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                            <div class="info-row"><i class="fas fa-user-friends"></i>
                                <?php echo htmlspecialchars($row['artist']); ?>
                            </div>
                            <div class="info-row"><i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($row['venue_name'] ?? 'No Venue'); ?>
                            </div>
                            <div class="info-row"><i class="fas fa-calendar-alt"></i>
                                <?php echo date('M d, Y', strtotime($row['event_date'])); ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="price-tag">₱
                                <?php echo number_format($row['price'], 2); ?>
                            </span>
                            <div class="action-btns">
                                <a href="edit_event.php?id=<?php echo $row['event_id']; ?>" title="Edit Event"><i
                                        class="fas fa-edit"></i></a>
                                <a href="delete_event.php?id=<?php echo $row['event_id']; ?>" title="Delete Event"
                                    onclick="return confirm('Are you sure you want to delete this event?')"><i
                                        class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 100px; color: var(--text-dim);">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.1;"></i>
                    <p>You haven't created any events yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>