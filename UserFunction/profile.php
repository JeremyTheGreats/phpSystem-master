<?php
session_start();
require_once '../db.php';

// Check if user is logged in and has correct role
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user') {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$user_email = $_SESSION['email'];
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch user data using email from session
$query = "SELECT * FROM user WHERE email = '$user_email' LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default.png';
} else {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | CrimsonGate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700;900&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bg-dark: #050505;
            --sidebar-bg: #0a0a0a;
            --card-bg: #101010;
            --crimson: #ff2e2e;
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
            font-family: var(--font-main);
        }

        body {
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at 50% -20%, #4b0000 0%, #080808 80%);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
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
            font-family: var(--font-headings);
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: 2px;
            color: var(--crimson);
            margin-bottom: 3.5rem;
            text-align: center;
            text-transform: uppercase;
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
            padding: 0.9rem 1.2rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
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

        .logout-link {
            color: var(--crimson);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            padding: 0.9rem 1.2rem;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 3rem 4rem;
        }

        .header-section {
            margin-bottom: 3rem;
        }

        .header-section h1 {
            font-family: var(--font-headings);
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .header-section p {
            color: var(--text-dim);
        }

        /* --- PROFILE CARD --- */
        .profile-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .profile-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--crimson);
        }

        .card-body {
            padding: 2rem;
        }

        .info-group {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .info-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-group label {
            color: var(--text-dim);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .info-group p {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
        }

        .badge {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .actions-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            height: fit-content;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .btn-edit {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid var(--border);
        }

        .btn-edit:hover {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <a href="dash.php" class="logo">CRIMSON<span>GATE</span></a>
        <nav>
            <ul>
                <li><a href="dash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="ticket.php"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                <li class="active"><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="my_tickets.php"><i class="fas fa-receipt"></i> My Tickets</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="../index.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="header-section">
            <p
                style="color: var(--crimson); font-weight: 700; font-size: 0.75rem; letter-spacing: 2px; text-transform: uppercase;">
                User Settings</p>
            <h1>Account Profile</h1>
        </div>

        <div class="profile-container">
            <div class="profile-card">
                <div class="card-header">
                    <img src="../profilepics/<?php echo htmlspecialchars($profilePic); ?>" class="profile-pic"
                        alt="Avatar">
                    <div>
                        <h2 style="font-family: var(--font-headings);">
                            <?php echo htmlspecialchars($user['name'] . ' ' . $user['lname']); ?></h2>
                        <p style="color:var(--text-dim); font-size: 0.9rem;">
                            <?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="info-group">
                        <label>First Name</label>
                        <p><?php echo htmlspecialchars($user['name']); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Last Name</label>
                        <p><?php echo htmlspecialchars($user['lname']); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Email Address</label>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Account Role</label>
                        <span class="badge"><?php echo htmlspecialchars($user['role']); ?></span>
                    </div>
                </div>
            </div>

            <div class="actions-card">
                <h3 style="margin-bottom: 1.5rem; font-family: var(--font-headings);">Quick Actions</h3>
                <a href="edit_profile.php" class="action-btn btn-edit">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>
    </main>
</body>

</html>