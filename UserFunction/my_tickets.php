<?php
session_start();
include '../db.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$current_page = basename($_SERVER['PHP_SELF']);

// 1. Fetch user ID
$u_stmt = $conn->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
$u_stmt->bind_param("s", $email);
$u_stmt->execute();
$user_data = $u_stmt->get_result()->fetch_assoc();
$user_id = $user_data['id'] ?? 0;

// 2. FIXED QUERY: Removed the non-existent 'points_earned' column to fix the Fatal Error
$query = "SELECT b.id as booking_id, b.seat_number, b.price, b.qr_code_data,
          e.title, e.event_date, e.venue, e.artist 
          FROM bookings b 
          JOIN events e ON b.event_id = e.id 
          WHERE b.user_id = ? AND b.status = 'paid'
          ORDER BY b.id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Tickets | CrimsonGate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg-dark: #080808;
            --sidebar-bg: #0a0a0a;
            --crimson: #ff2e2e;
            --crimson-dim: #b30000;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text-dim: #a0a0a0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at 50% -20%, #4b0000 0%, #080808 80%);
            color: white;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            padding: 2.5rem 1.2rem;
            height: 100vh;
            position: fixed;
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 3rem 4rem;
        }

        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
            gap: 25px;
        }

        .ticket-card {
            background: var(--card);
            border-radius: 20px;
            display: flex;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .ticket-main {
            padding: 25px;
            flex: 1;
            border-right: 2px dashed #222;
        }

        .ticket-stub {
            padding: 20px;
            width: 140px;
            text-align: center;
            background: rgba(255, 255, 255, 0.02);
        }

        .event-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            color: #fff;
            margin: 0;
        }

        .event-artist {
            color: var(--crimson);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin-bottom: 15px;
        }

        .qr-wrapper {
            background: white;
            padding: 5px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .btn-save {
            background: transparent;
            border: 1px solid var(--border);
            color: #fff;
            padding: 8px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 0.7rem;
        }

        /* --- PRINT STYLES: Fixes the white/blank screen issue --- */
        @media print {

            /* Hide UI elements */
            .sidebar,
            header,
            .btn-save,
            .no-tickets {
                display: none !important;
            }

            /* Hide all tickets by default */
            .ticket-card {
                display: none !important;
            }

            /* ONLY show the ticket being printed */
            .printable-active {
                display: flex !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                background: #111 !important;
                color: white !important;
                border: none !important;
            }

            body {
                background: white !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .qr-wrapper {
                background: white !important;
                -webkit-print-color-adjust: exact;
            }
        }

        /* Sidebar Container */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            /* #0a0a0a */
            border-right: 1px solid var(--border);
            padding: 2.5rem 1.2rem;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            /* Allows the logout to stay at the bottom */
            z-index: 100;
        }

        /* Logo Styling */
        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--crimson);
            text-transform: uppercase;
            letter-spacing: 2px;
            text-decoration: none;
            margin-bottom: 3.5rem;
            display: block;
            text-align: center;
        }

        .logo span {
            color: white;
        }

        /* Navigation Links */
        nav ul {
            list-style: none;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--text-dim);
            /* #888 */
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.9rem 1.2rem;
            border-radius: 12px;
            transition: 0.3s;
            margin-bottom: 8px;
            font-weight: 500;
        }

        /* Active and Hover States */
        nav ul li.active a,
        nav ul li a:hover {
            background: rgba(255, 46, 46, 0.08);
            color: var(--crimson);
        }

        /* Logout Section at Bottom */
        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .logout-btn {
            color: var(--crimson) !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            padding: 0.9rem 1.2rem;
            transition: 0.3s;
        }

        .logout-btn:hover {
            opacity: 0.8;
        }

        /* Update the base ticket-card to handle transitions */
        .ticket-card {
            background: #111;
            /* Ensuring visibility against the body gradient */
            border-radius: 20px;
            display: flex;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            /* Smooth pop effect */
            cursor: default;
            position: relative;
        }

        /* Hover Effect: Lift, Glow, and Border highlight */
        .ticket-card:hover {
            transform: translateY(-8px);
            /* Lifts the card up */
            border-color: rgba(255, 46, 46, 0.4);
            /* Brightens the border */
            box-shadow: 0 15px 30px rgba(255, 46, 46, 0.1), 0 0 15px rgba(0, 0, 0, 0.5);
            /* Crimson under-glow */
        }

        /* Subtle glow behind the QR code area on hover */
        .ticket-card:hover .ticket-stub {
            background: rgba(255, 46, 46, 0.03);
        }

        /* Make the SAVE button stand out more when the card is hovered */
        .ticket-card:hover .btn-save {
            background: var(--crimson);
            border-color: var(--crimson);
            color: white;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <a href="dash.php" class="logo">CRIMSON<span>GATE</span></a>

        <nav>
            <ul>
                <li class="<?= ($current_page == 'dash.php') ? 'active' : ''; ?>">
                    <a href="dash.php"><i class="fas fa-th-large"></i> Dashboard</a>
                </li>
                <li class="<?= ($current_page == 'ticket.php') ? 'active' : ''; ?>">
                    <a href="ticket.php"><i class="fas fa-ticket-alt"></i> Tickets</a>
                </li>
                <li class="<?= ($current_page == 'profile.php') ? 'active' : ''; ?>">
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                </li>
                <li class="<?= ($current_page == 'my_tickets.php') ? 'active' : ''; ?>">
                    <a href="my_tickets.php"><i class="fas fa-receipt"></i> My Tickets</a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header>
            <h1>My Tickets</h1>
        </header>

        <div class="ticket-grid">
            <?php while ($row = $result->fetch_assoc()):
                $ticketID = "tkt-" . $row['booking_id'];
                $qrData = !empty($row['qr_code_data']) ? $row['qr_code_data'] : "REF-" . $row['booking_id'];
                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
                ?>
                <div class="ticket-card" id="<?= $ticketID ?>">
                    <div class="ticket-main">
                        <h3 class="event-title"><?= htmlspecialchars($row['title']) ?></h3>
                        <div class="event-artist"><?= htmlspecialchars($row['artist']) ?></div>
                        <p><i class="fa fa-calendar"></i> <?= date('M d, Y', strtotime($row['event_date'])) ?></p>
                        <p><i class="fa fa-map-marker"></i> <?= htmlspecialchars($row['venue']) ?></p>
                    </div>
                    <div class="ticket-stub">
                        <div class="qr-wrapper">
                            <img src="<?= $qrUrl ?>" width="80">
                        </div>
                        <div style="font-size:0.7rem; color:#888;">SEAT</div>
                        <div style="font-weight:900; font-size:1.2rem;"><?= $row['seat_number'] ?></div>
                        <button class="btn-save" onclick="printTicket('<?= $ticketID ?>')">SAVE</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script>
        function printTicket(id) {
            const ticket = document.getElementById(id);
            if (!ticket) return;

            // 1. Add class to the specific ticket
            ticket.classList.add('printable-active');

            // 2. Small delay to ensure CSS applies before print dialog
            setTimeout(() => {
                window.print();
                // 3. Remove class after printing
                ticket.classList.remove('printable-active');
            }, 100);
        }
    </script>
</body>

</html>