<?php
include '../db.php';
session_start();

// Security Check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

if (isset($_POST['create_event'])) {
    // Sanitize Inputs
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $artist = mysqli_real_escape_string($conn, $_POST['artist']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $total_rows = (int)$_POST['total_rows'];
    $cols_per_row = (int)$_POST['cols_per_row'];

    $price_reg = $_POST['price_reg'];
    $price_vip1 = isset($_POST['use_vip1']) ? $_POST['price_vip1'] : 0;
    $price_vip2 = isset($_POST['use_vip2']) ? $_POST['price_vip2'] : 0;
    $price_vip3 = isset($_POST['use_vip3']) ? $_POST['price_vip3'] : 0;

    // File Handling
    $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['poster']['name']);
    
    // 1. This is the PHYSICAL path used to move the file (keeps ../)
    $upload_path = "../images/" . $filename; 
    
    // 2. This is the DATABASE path (removes ../ for easier display on the front-end)
    $db_save_path = "images/" . $filename; 

    // Ensure directory exists
    if (!is_dir('../images/')) {
        mkdir('../images/', 0777, true);
    }

    // Use $upload_path for the move_uploaded_file function
    if (move_uploaded_file($_FILES['poster']['tmp_name'], $upload_path)) {
        
        $sql = "INSERT INTO events (title, artist, event_date, event_time, venue, price, price_vip1, price_vip2, price_vip3, total_rows, cols_per_row, poster, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt = $conn->prepare($sql);
        
        // 3. Bind $db_save_path to the database instead of the upload path
        $stmt->bind_param("sssssiiiiiis", 
            $title, 
            $artist, 
            $event_date, 
            $event_time, 
            $venue, 
            $price_reg, 
            $price_vip1, 
            $price_vip2, 
            $price_vip3, 
            $total_rows, 
            $cols_per_row, 
            $db_save_path
        );

        if ($stmt->execute()) {
            $success = "Event published successfully!";
        } else {
            $error = "Database Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Critical Error: Could not upload to folder. Check permissions.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event | Crimson Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --crimson: #ff2e2e;
            --bg: #050505;
            --panel: #0d0d0d;
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-dim: #7d7d7d;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

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
            letter-spacing: 1px;
            margin-bottom: 60px;
            padding-left: 10px;
        }

        .logo-text span:first-child { color: var(--crimson); }

        .nav-links { list-style: none; flex-grow: 1; }
        .nav-links li { margin-bottom: 10px; }
        .nav-links a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: var(--text-dim);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .nav-links a:hover { color: #fff; background: rgba(255, 255, 255, 0.03); }
        .nav-links a.active {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
            border-left: 3px solid var(--crimson);
        }

        .logout-link {
            padding: 12px 20px;
            color: var(--crimson);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 700;
            transition: 0.3s;
            margin-bottom: 20px;
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 60px;
            display: flex;
            justify-content: center;
        }

        .form-container { width: 100%; max-width: 850px; }
        .card {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 32px;
        }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full { grid-column: span 2; }

        label {
            display: block;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-dim);
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        input:not([type="checkbox"]) {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            color: #fff;
            outline: none;
        }

        input:focus { border-color: var(--crimson); }

        .tier-card {
            background: rgba(255, 255, 255, 0.015);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 20px;
        }

        .tier-card.disabled { opacity: 0.2; pointer-events: none; }
        .auto-price { color: #00ffa3 !important; font-weight: 800; }

        .upload-zone {
            border: 2px dashed var(--glass-border);
            border-radius: 24px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            background: rgba(0, 0, 0, 0.2);
        }

        #image-preview {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: none;
            z-index: 1;
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

        .btn-publish:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(255, 46, 46, 0.4);
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .alert-error { background: rgba(255, 46, 46, 0.1); color: var(--crimson); border: 1px solid rgba(255, 46, 46, 0.2); }
        .alert-success { background: rgba(0, 255, 163, 0.1); color: #00ffa3; border: 1px solid rgba(0, 255, 163, 0.2); }
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
        <a href="../index.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
    </aside>

    <main class="main-content">
        <div class="form-container">
            <h1 style="font-family: 'Outfit'; margin-bottom: 30px; font-size: 2.2rem;">Create Event</h1>

            <?php if($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="card">
                <div class="grid">
                    <div class="input-group full"><label>Concert Title</label><input type="text" name="title" required></div>
                    <div class="input-group"><label>Artist</label><input type="text" name="artist" required></div>
                    <div class="input-group"><label>Venue</label><input type="text" name="venue" required></div>
                    <div class="input-group"><label>Number of Rows</label><input type="number" name="total_rows" required></div>
                    <div class="input-group"><label>Seats per Row</label><input type="number" name="cols_per_row" required></div>
                    <div class="input-group full"><label style="color: #fff;">Regular Price (Base)</label><input type="number" name="price_reg" id="price_reg" oninput="updatePrices()" required></div>

                    <div class="tier-card" id="card_vip1">
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <label>VIP 1 (3.0x)</label>
                            <input type="checkbox" name="use_vip1" id="use_vip1" checked onchange="updatePrices()">
                        </div>
                        <input type="number" name="price_vip1" id="price_vip1" class="auto-price">
                    </div>

                    <div class="tier-card" id="card_vip2">
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <label>VIP 2 (2.0x)</label>
                            <input type="checkbox" name="use_vip2" id="use_vip2" checked onchange="updatePrices()">
                        </div>
                        <input type="number" name="price_vip2" id="price_vip2" class="auto-price">
                    </div>

                    <div class="tier-card full" id="card_vip3">
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <label>VIP 3 (1.5x)</label>
                            <input type="checkbox" name="use_vip3" id="use_vip3" checked onchange="updatePrices()">
                        </div>
                        <input type="number" name="price_vip3" id="price_vip3" class="auto-price">
                    </div>

                    <div class="input-group"><label>Date</label><input type="date" name="event_date" required></div>
                    <div class="input-group"><label>Time</label><input type="time" name="event_time" required></div>

                    <div class="input-group full">
                        <label>Poster Upload</label>
                        <div class="upload-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
                            <img id="image-preview">
                            <div id="upload-prompt" style="text-align:center; color: var(--text-dim);">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--crimson); margin-bottom:10px;"></i>
                                <p>Click or drag image here</p>
                            </div>
                            <input type="file" name="poster" id="file-input" style="display:none;" onchange="handleFile(this.files[0])" required>
                        </div>
                    </div>
                </div>
                <button type="submit" name="create_event" class="btn-publish">Publish Event</button>
            </form>
        </div>
    </main>

    <script>
        function updatePrices() {
            const base = document.getElementById('price_reg').value;
            const config = [
                { id: 'vip1', m: 3.0 },
                { id: 'vip2', m: 2.0 },
                { id: 'vip3', m: 1.5 }
            ];
            config.forEach(c => {
                const cb = document.getElementById('use_' + c.id);
                const inp = document.getElementById('price_' + c.id);
                const card = document.getElementById('card_' + c.id);
                if (cb.checked) {
                    card.classList.remove('disabled');
                    if (base > 0 && !inp.dataset.edited) inp.value = Math.round(base * c.m);
                } else {
                    card.classList.add('disabled');
                    inp.value = 0;
                }
            });
        }
        document.querySelectorAll('.auto-price').forEach(el => el.oninput = () => el.dataset.edited = "true");

        const dz = document.getElementById('drop-zone');
        dz.ondragover = (e) => { e.preventDefault(); dz.style.borderColor = "#ff2e2e"; };
        dz.ondragleave = () => dz.style.borderColor = "";
        dz.ondrop = (e) => { e.preventDefault(); handleFile(e.dataTransfer.files[0]); };

        function handleFile(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.getElementById('image-preview');
                    img.src = e.target.result;
                    img.style.display = 'block';
                    document.getElementById('upload-prompt').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>