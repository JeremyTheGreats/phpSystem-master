<?php
// 1. SECURITY & CACHE HEADERS
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
include '../db.php';

// 2. ADMIN ACCESS CHECK
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 3. FETCH DATA
$sql = "SELECT 
            b.id AS ref_id, 
            CONCAT(u.name, ' ', u.lname) AS customer_name, 
            e.title AS event_name, 
            b.seat_number, 
            b.price, 
            b.payment_method, 
            b.status,
            b.booking_date 
        FROM bookings b
        LEFT JOIN user u ON b.user_id = u.id
        LEFT JOIN events e ON b.event_id = e.id
        ORDER BY b.id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Registry | CrimsonAdmin</title>
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

        /* --- SIDEBAR [UNIFIED] --- */
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

        /* --- MAIN AREA --- */
        .main-content {
            margin-left: 280px;
            padding: 4rem;
            width: calc(100% - 280px);
        }

        .header-section {
            margin-bottom: 3rem;
        }

        .header-section h1 {
            font-family: 'Outfit';
            font-size: 2.2rem;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .header-section p {
            color: var(--text-dim);
            font-size: 1rem;
        }

        /* --- TABLE --- */
        .table-container {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
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
            letter-spacing: 1.2px;
            color: var(--text-dim);
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            font-size: 0.9rem;
            vertical-align: middle;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.01);
        }

        .date-text {
            font-weight: 600;
            color: #efefef;
        }

        .time-text {
            display: block;
            font-size: 0.75rem;
            color: var(--text-dim);
        }

        .seat-badge {
            display: inline-block;
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid rgba(255, 46, 46, 0.2);
        }

        .status-confirmed {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.65rem;
            font-weight: 800;
            background: rgba(0, 255, 163, 0.1);
            color: var(--success);
            border: 1px solid rgba(0, 255, 163, 0.2);
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="logo">CRIMSON<span>ADMIN</span></div>
        <ul class="nav-links">
            <li><a href="admindash.php"><i class="fas fa-chart-pie"></i> Overview</a></li>
            <li><a href="event.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
            <li><a href="bookings.php" class="active"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="user.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_vouchers.php"><i class="fas fa-tags"></i> Vouchers</a></li>
        </ul>
        <a href="../index.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
    </aside>

    <main class="main-content">
        <header class="header-section">
            <h1>Booking Registry</h1>
            <p>Monitor transactions and finalized seat allocations in real-time.</p>
        </header>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Registered On</th>
                        <th>Customer Details</th>
                        <th>Event</th>
                        <th>Seat</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td style="color:var(--text-dim); font-family:monospace;">
                                    #CG-<?php echo str_pad($row['ref_id'], 5, '0', STR_PAD_LEFT); ?>
                                </td>

                                <td>
                                    <span
                                        class="date-text"><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></span>
                                    <span class="time-text"><?php echo date('h:i A', strtotime($row['booking_date'])); ?></span>
                                </td>

                                <td>
                                    <div style="font-weight: 700;"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-dim);">Verified Buyer</div>
                                </td>

                                <td><?php echo htmlspecialchars($row['event_name']); ?></td>

                                <td><span class="seat-badge"><?php echo htmlspecialchars($row['seat_number']); ?></span></td>

                                <td style="font-weight: 700;">₱<?php echo number_format($row['price'], 2); ?></td>

                                <td>
                                    <span class="status-confirmed">
                                        <i class="fas fa-circle-check"></i>
                                        <?php echo strtoupper($row['status'] ?: 'CONFIRMED'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7"
                                style="text-align:center; padding:60px; color:var(--text-dim); font-style: italic;">
                                No active bookings found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>