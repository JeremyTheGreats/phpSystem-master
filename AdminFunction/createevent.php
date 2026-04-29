<?php
include '../db.php';
session_start();

// 1. SECURITY CHECK
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";
$success = "";

// 2. FETCH DATA FOR DROPDOWNS
// Fetch Venues
$venues_result = mysqli_query($conn, "SELECT venue_id, venue_name FROM venue ORDER BY venue_name ASC");

// Fetch Organizers (users with role 'Organizer')
$organizers_result = mysqli_query($conn, "SELECT user_id, first_name FROM user WHERE role = 'Organizer' ORDER BY first_name ASC");

// 3. HANDLE EVENT CREATION
if (isset($_POST['create_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $artist = mysqli_real_escape_string($conn, $_POST['artist']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $venue_id = (int) $_POST['venue_id'];
    $organizer_id = (int) $_POST['organizer_id'];

    // NEW: Capture the missing fields
    $price = (float) $_POST['price_reg'];
    $total_rows = (int) $_POST['total_rows'];
    $cols_per_row = (int) $_POST['cols_per_row'];

    // File Handling
    $target_dir = "../images/";
    if (!is_dir($target_dir))
        mkdir($target_dir, 0777, true); // Ensure dir exists

    $filename = time() . "_" . basename($_FILES['poster']['name']);
    $upload_path = $target_dir . $filename;
    $db_save_path = "images/" . $filename;

    if (move_uploaded_file($_FILES['poster']['tmp_name'], $upload_path)) {

        // Updated SQL to include price, total_rows, and cols_per_row
        $sql = "INSERT INTO event (organizer_id, venue_id, title, artist, event_date, event_time, poster, price, total_rows, cols_per_row, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";

        $stmt = $conn->prepare($sql);


        $stmt->bind_param(
            "iisssssdii",
            $organizer_id,
            $venue_id,
            $title,
            $artist,
            $event_date,
            $event_time,
            $db_save_path,
            $price,
            $total_rows,
            $cols_per_row
        );

        if ($stmt->execute()) {
            $success = "Event created successfully!";
        } else {
            $error = "DB Error: " . $stmt->error;
        }
    } else {
        $error = "Upload failed. Check folder permissions.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Event | Crimson Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* [KEEP YOUR EXISTING CSS HERE - NO CHANGES NEEDED TO CSS] */
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
            font-weight: 600;
        }

        .nav-links a.active {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
            border-left: 3px solid var(--crimson);
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 60px;
            display: flex;
            justify-content: center;
        }

        .form-container {
            width: 100%;
            max-width: 850px;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 32px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full {
            grid-column: span 2;
        }

        label {
            display: block;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-dim);
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        input:not([type="checkbox"]),
        select {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            color: #fff;
            outline: none;
        }

        .btn-publish {
            width: 100%;
            padding: 22px;
            background: var(--crimson);
            color: white;
            border: none;
            border-radius: 18px;
            font-family: 'Outfit';
            font-weight: 900;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 30px;
            transition: 0.4s;
        }

        .upload-zone {
            border: 2px dashed var(--glass-border);
            border-radius: 24px;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
        }

        #image-preview {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: none;
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
        <a href="../logout.php" style="color:var(--crimson); text-decoration:none; padding: 20px; font-weight: bold;"><i
                class="fas fa-power-off"></i> Logout</a>
    </aside>

    <main class="main-content">
        <div class="form-container">
            <h1 style="font-family: 'Outfit'; margin-bottom: 30px;">Create New Event</h1>

            <?php if ($success): ?>
                <div style="color:#00ffa3; margin-bottom:20px;"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?>
                <div style="color:var(--crimson); margin-bottom:20px;"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="card">
                <div class="grid">
                    <div class="input-group full">
                        <label>Event Title</label>
                        <input type="text" name="title" placeholder="e.g. The Eras Tour" required>
                    </div>

                    <div class="input-group">
                        <label>Assign Organizer</label>
                        <select name="organizer_id" required>
                            <option value="">-- Choose Organizer --</option>
                            <?php while ($org = mysqli_fetch_assoc($organizers_result)): ?>
                                <option value="<?php echo $org['user_id']; ?>">
                                    <?php echo htmlspecialchars($org['first_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Select Venue</label>
                        <select name="venue_id" required>
                            <option value="">-- Choose Venue --</option>
                            <?php while ($v = mysqli_fetch_assoc($venues_result)): ?>
                                <option value="<?php echo $v['venue_id']; ?>">
                                    <?php echo htmlspecialchars($v['venue_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="input-group"><label>Artist / Performer</label><input type="text" name="artist" required>
                    </div>
                    <div class="input-group"><label>Base Price (₱)</label><input type="number" name="price_reg"
                            required></div>

                    <div class="input-group"><label>Date</label><input type="date" name="event_date" required></div>
                    <div class="input-group"><label>Time</label><input type="time" name="event_time" required></div>

                    <div class="input-group"><label>Total Rows</label><input type="number" name="total_rows" required>
                    </div>
                    <div class="input-group"><label>Seats per Row</label><input type="number" name="cols_per_row"
                            required></div>

                    <div class="input-group full">
                        <label>Event Poster</label>
                        <div class="upload-zone" onclick="document.getElementById('file-input').click()">
                            <img id="image-preview">
                            <div id="upload-prompt" style="text-align:center; color: var(--text-dim);">
                                <i class="fas fa-image" style="font-size: 2rem; color: var(--crimson);"></i>
                                <p>Click to upload poster</p>
                            </div>
                            <input type="file" name="poster" id="file-input" style="display:none;"
                                onchange="previewImage(this)">
                        </div>
                    </div>
                </div>
                <button type="submit" name="create_event" class="btn-publish">Publish Event</button>
            </form>
        </div>
    </main>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('image-preview').src = e.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                    document.getElementById('upload-prompt').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>