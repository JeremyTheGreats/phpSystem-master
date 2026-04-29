<?php
session_start();
include '../db.php';

// 1. ADMIN ACCESS CHECK
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";
$success = "";

// 2. HANDLE VENUE CREATION
if (isset($_POST['add_venue'])) {
    $v_name = mysqli_real_escape_string($conn, $_POST['venue_name']);
    $v_location = mysqli_real_escape_string($conn, $_POST['location']);
    $v_capacity = (int) $_POST['capacity'];

    $sql = "INSERT INTO venue (venue_name, location, capacity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $v_name, $v_location, $v_capacity);

    if ($stmt->execute()) {
        $success = "Venue added successfully!";
    } else {
        $error = "Error adding venue: " . $conn->error;
    }
    $stmt->close();
}

// 3. FETCH EXISTING VENUES
$query = "SELECT * FROM venue ORDER BY venue_id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Venues | Crimson Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --crimson: #ff2e2e;
            --bg: #050505;
            --panel: #0d0d0d;
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-dim: #7d7d7d;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling inherited from your dashboard */
        .sidebar {
            width: 260px;
            background: var(--panel);
            border-right: 1px solid var(--glass-border);
            height: 100vh;
            position: fixed;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
        }

        .logo-text {
            font-family: 'Outfit';
            font-weight: 900;
            font-size: 1.4rem;
            color: #fff;
            margin-bottom: 60px;
        }

        .logo-text span {
            color: var(--crimson);
        }

        .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: var(--text-dim);
            text-decoration: none;
            border-radius: 12px;
            transition: 0.3s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 60px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            align-items: start;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            padding: 30px;
            border-radius: 24px;
        }

        h2 {
            font-family: 'Outfit';
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        label {
            display: block;
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--text-dim);
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        input {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: #fff;
            margin-bottom: 20px;
        }

        .btn-add {
            width: 100%;
            padding: 15px;
            background: var(--crimson);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: 'Outfit';
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 46, 46, 0.3);
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            text-align: left;
            color: var(--text-dim);
            font-size: 0.7rem;
            text-transform: uppercase;
            padding: 15px;
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02);
            font-size: 0.9rem;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: rgba(0, 255, 163, 0.1);
            color: #00ffa3;
        }

        .alert-error {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="logo-text"><span>CRIMSON</span>ADMIN</div>
        <ul class="nav-links">
            <li><a href="admindash.php"><i class="fas fa-chart-pie"></i> Overview</a></li>
            <li><a href="event.php" class="active"><i class="fas fa-calendar-alt"></i> Events</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="user.php"><i class="fas fa-users"></i> Users</a></li>
        </ul>
        <a href="../index.php" style="color:var(--crimson); text-decoration:none; margin-top:auto; font-weight:bold;"><i
                class="fas fa-power-off"></i> Logout</a>
    </aside>

    <main class="main-content">
        <h1 style="font-family: 'Outfit'; margin-bottom: 40px;">Venue Management</h1>

        <?php if ($success)
            echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if ($error)
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <div class="grid-container">
            <div class="card">
                <h2>Add New Venue</h2>
                <form method="POST">
                    <label>Venue Name</label>
                    <input type="text" name="venue_name" placeholder="e.g. Grand Arena" required>

                    <label>Location</label>
                    <input type="text" name="location" placeholder="e.g. Cebu City" required>

                    <label>Max Capacity</label>
                    <input type="number" name="capacity" placeholder="e.g. 5000" required>

                    <button type="submit" name="add_venue" class="btn-add">REGISTER VENUE</button>
                </form>
            </div>

            <div class="card">
                <h2>Existing Venues</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Venue Name</th>
                            <th>Location</th>
                            <th>Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td style="font-weight: 700;">
                                    <?php echo htmlspecialchars($row['venue_name']); ?>
                                </td>
                                <td style="color: var(--text-dim);">
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </td>
                                <td><span style="color: var(--crimson); font-weight: 800;">
                                        <?php echo number_format($row['capacity']); ?>
                                    </span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>

</html>