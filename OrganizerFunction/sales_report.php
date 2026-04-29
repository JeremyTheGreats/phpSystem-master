<?php
session_start();
include '../db.php';

// 1. SECURITY CHECK - Matches your login.php variables
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Organizer') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. FETCH DETAILED SALES
// Note: We filter by organizer_id in the event table using your session's user_id
$query = "SELECT 
            e.title, 
            e.event_date, 
            e.price,
            COUNT(b.booking_id) as tickets_sold,
            (COUNT(b.booking_id) * e.price) as total_revenue
          FROM event e
          LEFT JOIN booking b ON e.event_id = b.event_id
          WHERE e.organizer_id = ?
          GROUP BY e.event_id
          ORDER BY total_revenue DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sales_data = $stmt->get_result();

$total_revenue = 0;
$total_tickets = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report | CrimsonGate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;900&display=swap" rel="stylesheet">
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

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px; background: var(--sidebar-bg); border-right: 1px solid var(--glass-border);
            padding: 2.5rem 1.5rem; position: fixed; height: 100vh; display: flex; flex-direction: column;
        }
        .logo { font-family: 'Outfit', sans-serif; color: var(--crimson); font-weight: 900; font-size: 1.5rem; margin-bottom: 3.5rem; text-align: center; }
        .nav-links { list-style: none; flex-grow: 1; }
        .nav-links a {
            text-decoration: none; color: var(--text-dim); padding: 14px 18px; display: flex;
            align-items: center; gap: 15px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; transition: 0.3s;
        }
        .nav-links a:hover, .nav-links a.active { background: rgba(255, 46, 46, 0.1); color: var(--crimson); }
        .logout-link { margin-top: auto; color: var(--crimson); text-decoration: none; font-weight: 600; display: flex; gap: 10px; padding: 10px;}

        .main-content { flex: 1; margin-left: 280px; padding: 4rem; }
        .header-sec { margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end; }
        .header-sec h1 { font-family: 'Outfit'; font-size: 2.5rem; }

        .report-card { background: var(--panel); border: 1px solid var(--glass-border); border-radius: 24px; padding: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid var(--glass-border); }
        td { padding: 20px 15px; border-bottom: 1px solid var(--glass-border); font-size: 0.95rem; }
        
        .revenue-text { color: #00ffa3; font-weight: 700; }
        .print-btn {
            background: transparent; border: 1px solid var(--crimson); color: var(--crimson);
            padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s;
        }
        .print-btn:hover { background: var(--crimson); color: white; }

        @media print { .sidebar, .print-btn { display: none; } .main-content { margin-left: 0; padding: 2rem; } }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo">CRIMSON<span>ORG</span></div>
        <ul class="nav-links">
            <li><a href="organizerdash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="manage_events.php"><i class="fas fa-calendar-check"></i> My Events</a></li>
            <li><a href="sales_report.php" class="active"><i class="fas fa-chart-line"></i> Sales Report</a></li>
            <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
        </ul>
        <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </aside>

    <main class="main-content">
        <div class="header-sec">
            <div>
                <p style="color: var(--crimson); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Revenue Report</p>
                <h1>Sales Performance</h1>
            </div>
            <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Export PDF</button>
        </div>

        <div class="report-card">
            <table>
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Tickets Sold</th>
                        <th>Gross Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sales_data->num_rows > 0): ?>
                        <?php while($row = $sales_data->fetch_assoc()): 
                            $total_revenue += $row['total_revenue'];
                            $total_tickets += $row['tickets_sold'];
                        ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                            <td>₱<?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo number_format($row['tickets_sold']); ?></td>
                            <td class="revenue-text">₱<?php echo number_format($row['total_revenue'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr style="background: rgba(255,255,255,0.02);">
                            <td colspan="3" style="text-align: right; font-weight: 700; padding: 25px;">TOTALS:</td>
                            <td style="font-weight: 700;"><?php echo number_format($total_tickets); ?></td>
                            <td class="revenue-text" style="font-size: 1.2rem;">₱<?php echo number_format($total_revenue, 2); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 50px; color: var(--text-dim);">No sales recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>