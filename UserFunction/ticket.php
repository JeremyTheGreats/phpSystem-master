<?php
session_start();
// FIX: Go up one folder to find db.php
include "../db.php";

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user') {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets | CrimsonGate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700;900&family=Inter:wght@400;600&display=swap"
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

        /* Hover Background Layer */
        html::before {
            content: "";
            position: fixed;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 0.6s ease;
            z-index: -1;
            pointer-events: none;
        }

        html.bg-on::before {
            background-image:
                linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.5)),
                var(--page-bg);
            opacity: 1;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            padding: 2.5rem 1.2rem;
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--crimson);
            margin-bottom: 3.5rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-decoration: none;
        }

        .logo span {
            color: white;
        }

        nav ul {
            list-style: none;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--text-dim);
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 1rem 1.2rem;
            border-radius: 12px;
            transition: 0.3s;
            font-weight: 500;
        }

        nav ul li.active a,
        nav ul li a:hover {
            background: rgba(255, 46, 46, 0.08);
            color: var(--crimson);
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .logout-btn {
            color: var(--crimson);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.9rem 1.2rem;
            font-weight: 600;
            transition: 0.3s;
        }

        .logout-btn:hover {
            opacity: 0.8;
        }

        /* --- CONTENT --- */
        .content {
            margin-left: 260px;
            flex: 1;
            padding: 3rem 4rem;
        }

        header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 2rem;
        }

        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(480px, 1fr));
            gap: 30px;
        }

        .ticket-card {
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            border: 1px solid var(--border);
            transition: 0.4s;
        }

        .ticket-card:hover {
            border-color: var(--crimson);
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.05);
        }

        .event-info {
            padding: 2rem;
            flex: 2.5;
            border-right: 2px dashed rgba(255, 255, 255, 0.1);
        }

        .booking-action {
            flex: 1.2;
            background: rgba(255, 46, 46, 0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .book-btn {
            background: var(--crimson);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            width: 100%;
            transition: 0.3s;
        }

        .book-btn:hover {
            background: var(--crimson-dim);
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <a href="dash.php" class="logo">CRIMSON<span>GATE</span></a>
        <nav>
            <ul>
                <li><a href="dash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li class="active"><a href="ticket.php"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="my_tickets.php"><i class="fas fa-receipt"></i> My Tickets</a></li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="content">
        <header>
            <h1>Find Your Next Experience</h1>
        </header>

        <div class="ticket-grid">
            <?php while ($row = $events->fetch_assoc()): ?>
                <div class="ticket-card" style="--card-bg: url('../<?php echo htmlspecialchars($row['poster']); ?>');">
                    <div class="event-info">
                        <span
                            style="color: var(--crimson); font-weight: 800; font-size: 0.7rem; text-transform: uppercase;">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </span>
                        <h2 style="font-size: 1.8rem; margin: 8px 0;"><?php echo htmlspecialchars($row['artist']); ?></h2>
                        <span style="color: var(--text-dim); font-size: 0.9rem;">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['venue']); ?>
                        </span>
                    </div>

                    <div class="booking-action">
                        <div style="margin-bottom: 15px; text-align: center;">
                            <span style="font-size: 0.75rem; color: var(--text-dim); display: block;">Tickets from</span>
                            <span
                                style="font-size: 1.4rem; font-weight: 900;">₱<?php echo number_format($row['price']); ?></span>
                        </div>
                        <a href="Process/seatno.php?event_id=<?php echo $row['id']; ?>" style="width:100%;">
                            <button class="book-btn">Book Seat</button>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script>
        const htmlEl = document.documentElement;

        document.querySelectorAll(".ticket-card").forEach(card => {
            card.addEventListener("mouseenter", () => {
                const bg = getComputedStyle(card).getPropertyValue("--card-bg").trim();
                htmlEl.style.setProperty("--page-bg", bg);
                htmlEl.classList.add("bg-on");
            });

            card.addEventListener("mouseleave", () => {
                htmlEl.classList.remove("bg-on");
            });
        });
    </script>
</body>

</html>