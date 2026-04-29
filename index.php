<?php
session_start();
include "db.php";

/** * NORMALIZED QUERY:
 * 1. JOINS Event with Venue to get the specific location name.
 * 2. JOINS Event with Tickets to find the lowest available price (Starting at ₱X).
 */
$event_query = "
    SELECT 
        e.event_id, 
        e.title, 
        e.poster, 
        e.event_date, 
        v.venue_name, 
        MIN(t.price) as min_price 
    FROM Event e
    INNER JOIN Venue v ON e.venue_id = v.venue_id
    INNER JOIN Tickets t ON e.event_id = t.event_id
    WHERE e.status = 'active' 
    GROUP BY e.event_id
    ORDER BY e.event_date ASC 
    LIMIT 6";

$event_result = $conn->query($event_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrimsonGate | Premier Event Access</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Outfit:wght@700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --crimson: #ff2e2e;
            --crimson-glow: rgba(255, 46, 46, 0.4);
            --bg: #050505;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-dim: #999999;
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
            overflow-x: hidden;
        }

        /* --- BACKGROUND LAYER --- */
        .page-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(5, 5, 5, 0.85), rgba(5, 5, 5, 0.95)), url("style/back.jpg");
            background-size: cover;
            background-position: center;
            z-index: -2;
        }

        /* --- NAVIGATION & LOGO --- */
        header {
            position: fixed;
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1100px;
            background: rgba(15, 15, 15, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 12px 35px;
            border-radius: 100px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .logo {
            transition: 0.3s;
            text-decoration: none;
            font-family: 'Outfit';
            font-weight: 900;
            font-size: 1.3rem;
        }

        .logo:hover {
            transform: scale(1.05);
            filter: drop-shadow(0 0 8px var(--crimson));
        }

        nav a {
            color: var(--text-dim);
            text-decoration: none;
            margin-left: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.3s;
            position: relative;
        }

        nav a:not(.btn-nav):hover {
            color: #fff;
        }

        nav a:not(.btn-nav)::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: var(--crimson);
            transition: 0.3s;
        }

        nav a:not(.btn-nav):hover::after {
            width: 100%;
        }

        .btn-nav {
            background: var(--crimson);
            color: white !important;
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 700;
            box-shadow: 0 4px 15px var(--crimson-glow);
            transition: 0.4s;
            display: inline-block;
            text-decoration: none;
        }

        .btn-nav:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px var(--crimson-glow);
        }

        /* --- HERO SECTION --- */
        .hero {
            height: 85vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* --- EVENT CARDS --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            padding: 40px 5% 100px;
            max-width: 1300px;
            margin: 0 auto;
        }

        .card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            transition: 0.4s;
        }

        .card:hover {
            transform: translateY(-10px);
            border-color: var(--crimson);
        }

        .card-img-container {
            overflow: hidden;
            height: 220px;
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.6s;
        }

        .card:hover .card-img {
            transform: scale(1.1);
        }

        .btn-ticket {
            border: 1px solid var(--crimson);
            color: var(--crimson);
            padding: 8px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
            transition: 0.3s;
        }

        .btn-ticket:hover {
            background: var(--crimson);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="page-background"></div>

    <header>
        <a href="index.php" class="logo" style="color:var(--crimson);">CRIMSON<span style="color:#fff">GATE</span></a>
        <nav>
            <a href="#events">Live Events</a>
            <a href="login.php">Log In</a>
            <a href="register.php" class="btn-nav">Register</a>
        </nav>
    </header>

    <section class="hero">
        <span
            style="color:var(--crimson); letter-spacing:5px; font-weight:800; font-size:0.7rem; text-transform:uppercase;">The
            Philippines' Elite Venue</span>
        <h1 style="font-family:'Outfit'; font-size: clamp(3rem, 8vw, 6rem); margin: 20px 0;">Life is <i>Live.</i></h1>
        <p style="max-width:500px; color:var(--text-dim); margin-bottom:40px;">Your portal to exclusive concerts,
            theater, and premium sporting events.</p>
        <a href="#events" class="btn-nav" style="padding: 16px 45px;">Explore Lineup</a>
    </section>

    <section id="events">
        <h2 style="text-align:center; font-family:'Outfit'; font-size:2.5rem; margin-bottom:30px;">Featured <span
                style="color:var(--crimson);">Performances</span></h2>
        <div class="grid">
            <?php if ($event_result && $event_result->num_rows > 0): ?>
                <?php while ($event = $event_result->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-img-container">
                            <img src="<?php echo htmlspecialchars($event['poster']); ?>" class="card-img" alt="Poster">
                        </div>
                        <div style="padding: 25px;">
                            <span style="color:var(--crimson); font-weight:800; font-size:0.7rem;">
                                <?php echo date("M d, Y", strtotime($event['event_date'])); ?>
                            </span>
                            <h3 style="font-family:'Outfit'; margin: 10px 0;"><?php echo htmlspecialchars($event['title']); ?>
                            </h3>
                            <p style="color:var(--text-dim); font-size:0.8rem;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['venue_name']); ?>
                            </p>
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:15px; border-top:1px solid var(--glass-border);">
                                <div>
                                    <span
                                        style="font-size: 0.65rem; color: var(--text-dim); display: block; text-transform: uppercase;">Starting
                                        at</span>
                                    <strong>₱<?php echo number_format($event['min_price'], 0); ?></strong>
                                </div>
                                <a href="login.php" class="btn-ticket">Secure Entry</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1/-1; color: var(--text-dim); padding: 50px;">No live events
                    currently available.</p>
            <?php endif; ?>
        </div>
    </section>

    <footer style="background: rgba(5,5,5,0.9); padding: 80px 5% 40px; border-top: 1px solid var(--glass-border);">
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px; max-width: 1300px; margin: 0 auto 50px;">

            <div class="f-col">
                <div class="logo" style="color:var(--crimson); margin-bottom:20px;">CRIMSONGATE</div>
                <p style="color:var(--text-dim); font-size:0.85rem;">Redefining event access. Built for the ultimate fan
                    experience.</p>
            </div>

            <div class="f-col">
                <h4 style="color:#fff; font-size:0.8rem; margin-bottom:20px; text-transform:uppercase;">Platform</h4>
                <ul style="list-style:none;">
                    <li style="margin-bottom:10px;"><a href="login.php"
                            style="color: var(--text-dim); text-decoration: none;">User Login</a></li>
                    <li style="margin-bottom:10px;"><a href="register.php"
                            style="color: var(--text-dim); text-decoration: none;">Register</a></li>
                    <li style="margin-bottom:10px;"><a href="#events"
                            style="color: var(--text-dim); text-decoration: none;">Live Events</a></li>
                </ul>
            </div>

            <div class="f-col">
                <h4 style="color:#fff; font-size:0.8rem; margin-bottom:20px; text-transform:uppercase;">Questions?</h4>
                <ul style="list-style:none; color: var(--text-dim); font-size: 0.85rem;">
                    <li style="margin-bottom:12px;"><i class="fas fa-envelope"
                            style="color:var(--crimson); margin-right:10px;"></i> support@crimsongate.ph</li>
                    <li style="margin-bottom:12px;"><i class="fas fa-phone"
                            style="color:var(--crimson); margin-right:10px;"></i> +63 (02) 8888-GATE</li>
                </ul>
            </div>

            <div class="f-col">
                <h4 style="color:#fff; font-size:0.8rem; margin-bottom:20px; text-transform:uppercase;">Follow Us</h4>
                <div style="display:flex; gap:15px;">
                    <a href="#" style="color: var(--text-dim); font-size: 1.3rem;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: var(--text-dim); font-size: 1.3rem;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: var(--text-dim); font-size: 1.3rem;"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>

        <div
            style="max-width: 1300px; margin: 0 auto; border-top: 1px solid var(--glass-border); padding-top: 25px; display:flex; justify-content:space-between; font-size:0.7rem; color:#555;">
            <p>&copy; 2026 CrimsonGate PH. All Rights Reserved.</p>
            <p><i class="fas fa-circle" style="color:#00ff78; font-size:6px; margin-right:5px;"></i> Systems Operational
            </p>
        </div>
    </footer>
</body>

</html>