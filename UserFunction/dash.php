<?php
session_start();
// FIX 1: Ensure db connection is correct
include '../db.php';

$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page == '../index.php' && isset($_SESSION['email'])) {
    session_unset();
    session_destroy();
}

if ($current_page != 'index.php' && !isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];

    $u_query = "SELECT id, points FROM user WHERE email = '$user_email' LIMIT 1";
    $u_res = mysqli_query($conn, $u_query);

    if (mysqli_num_rows($u_res) > 0) {
        $user_data = mysqli_fetch_assoc($u_res);
        $user_id = $user_data['id'];
        $reward_points = $user_data['points'];
    } else {
        $user_id = 0;
        $reward_points = 0;
    }

    $display_name = ucwords(str_replace(['.', '_'], ' ', explode('@', $user_email)[0]));

    $t_query = "SELECT COUNT(*) as total FROM bookings WHERE user_id = '$user_id'";
    $t_res = mysqli_fetch_assoc(mysqli_query($conn, $t_query));
    $ticket_count = sprintf("%02d", $t_res['total']);
}

// 3. Fetch Events for Slider
$event_query = "SELECT * FROM events WHERE status = 'active' ORDER BY event_date ASC LIMIT 5";
$event_result = mysqli_query($conn, $event_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrimsonGate | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700;900&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bg: #050505;
            --sidebar-bg: #0a0a0a;
            --card-bg: #101010;
            --accent-red: #ff2e2e;
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #ffffff;
            --text-dim: #999999;
            --font-main: 'Inter', sans-serif;
            --font-headings: 'Outfit', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: var(--font-main);
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            padding: 2.5rem 1.2rem;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .logo {
            font-family: var(--font-headings);
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--accent-red);
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

        nav ul {
            list-style: none;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--text-dim);
            display: flex;
            align-items: center;
            padding: 0.9rem 1.2rem;
            border-radius: 12px;
            transition: 0.3s;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        nav ul li.active a,
        nav ul li a:hover {
            background: rgba(255, 46, 46, 0.08);
            color: var(--accent-red);
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 3rem 4rem;
        }

        header {
            margin-bottom: 3rem;
        }

        .welcome-text {
            color: var(--accent-red);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        header h1 {
            font-family: var(--font-headings);
            font-weight: 900;
            font-size: 2.5rem;
        }

        /* --- SLIDER --- */
        .slider-section {
            margin-bottom: 3.5rem;
        }

        .slider-wrapper {
            display: flex;
            gap: 25px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding: 10px 5px;
            scrollbar-width: none;
            scroll-behavior: smooth;
        }

        .slider-wrapper::-webkit-scrollbar {
            display: none;
        }

        .event-card {
            min-width: 100%;
            height: 380px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            /* Ensure no repeating */
            border-radius: 24px;
            position: relative;
            scroll-snap-align: start;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 3rem;
            border: 1px solid var(--border);
            overflow: hidden;
            text-decoration: none;
            color: white;
            transition: 0.3s ease;
        }

        .event-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, transparent 70%);
            z-index: 1;
        }

        .card-content {
            position: relative;
            z-index: 2;
        }

        .card-content h3 {
            font-family: var(--font-headings);
            font-size: 2.2rem;
            font-weight: 800;
        }

        /* --- STATS GRID --- */
        .stats-grid {
            display: flex;
            gap: 25px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            flex: 1;
        }

        .stat-label {
            color: var(--text-dim);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-value {
            font-family: var(--font-headings);
            font-size: 3rem;
            font-weight: 900;
        }

        .clickable-card {
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .clickable-card:hover {
            border-color: var(--accent-red);
            transform: translateY(-5px);
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <a href="dash.php" class="logo">CRIMSON<span>GATE</span></a>
        <nav>
            <ul>
                <li class="active"><a href="dash.php"><i class="fas fa-th-large"></i>&nbsp;&nbsp;Dashboard</a></li>
                <li><a href="ticket.php"><i class="fas fa-ticket-alt"></i>&nbsp;&nbsp;Tickets</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i>&nbsp;&nbsp;Profile</a></li>
                <li><a href="my_tickets.php"><i class="fas fa-receipt"></i>&nbsp;&nbsp;My Tickets</a></li>
            </ul>
        </nav>
        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border);">
            <a href="../index.php"
                style="color:var(--accent-red); text-decoration:none; display:flex; align-items:center; gap:10px; font-weight:600;">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header>
            <p class="welcome-text">SECURE ACCESS GRANTED</p>
            <h1>Welcome, <?php echo $display_name; ?></h1>
        </header>

        <section class="slider-section">
            <div class="slider-wrapper" id="autoSlider">
                <?php while ($event = mysqli_fetch_assoc($event_result)): ?>
                    <a href="Process/seatno.php?event_id=<?php echo $event['id']; ?>" class="event-card"
                        style="background-image: url('../<?php echo htmlspecialchars($event['poster']); ?>');">
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p style="color: rgba(255,255,255,0.8);">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                            </p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </section>

        <section class="stats-grid">
            <div class="card">
                <div class="stat-label">MY TICKETS</div>
                <div class="stat-value"><?php echo $ticket_count; ?></div>
            </div>

            <a href="voucher.php" class="card clickable-card">
                <div class="stat-label">REWARD POINTS</div>
                <div class="stat-value" style="color: var(--accent-red);">
                    <?php echo number_format((float) $reward_points); ?><span>pts</span>
                </div>
                <div style="font-size: 0.65rem; color: var(--accent-red); font-weight: 800; margin-top: 10px;">
                    REDEEM NOW <i class="fas fa-arrow-right"></i>
                </div>
            </a>
        </section>
    </main>

    <script>
        const slider = document.getElementById('autoSlider');
        function autoPlay() {
            if (!slider) return;
            const card = slider.querySelector('.event-card');
            if (!card) return;
            const cardWidth = slider.offsetWidth; // Use slider width for full slide
            if (slider.scrollLeft >= (slider.scrollWidth - slider.clientWidth) - 10) {
                slider.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                slider.scrollBy({ left: cardWidth, behavior: 'smooth' });
            }
        }
        setInterval(autoPlay, 5000);
    </script>
</body>

</html>