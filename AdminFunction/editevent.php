<?php
include '../db.php';
session_start();

// 1. SECURITY CHECK
// Updated 'admin' to 'Admin' to match your session logic
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// 2. FETCH EVENT DATA
if (!isset($_GET['id'])) {
    header("Location: event.php");
    exit;
}

$id = (int) $_GET['id'];
// Updated table name to 'event' (singular)
$result = mysqli_query($conn, "SELECT * FROM events WHERE id = $id");
$event = mysqli_fetch_assoc($result);

if (!$event) {
    die("Event not found.");
}

$success = "";
$error = "";

// 3. UPDATE LOGIC
if (isset($_POST['update_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $artist = mysqli_real_escape_string($conn, $_POST['artist']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $price = $_POST['price'];
    $status = $_POST['status'];

    // Handle Image Upload
    $poster_path = $event['poster']; // Keep old path by default

    if (!empty($_FILES['poster']['name'])) {
        $filename = time() . "_" . $_FILES['poster']['name'];
        // Path adjusted: go up one level to find the images folder
        if (move_uploaded_file($_FILES['poster']['tmp_name'], "../images/" . $filename)) {

            // Delete the old physical file if a new one is uploaded
            if (!empty($event['poster']) && file_exists("../" . $event['poster'])) {
                unlink("../" . $event['poster']);
            }

            $poster_path = "images/" . $filename;
        }
    }

    // Updated table name to 'event'
    $sql = "UPDATE event SET title=?, artist=?, venue=?, price=?, status=?, poster=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdssi", $title, $artist, $venue, $price, $status, $poster_path, $id);

    if ($stmt->execute()) {
        $success = "Event updated successfully!";
        // Refresh local data to show new values in form
        $result = mysqli_query($conn, "SELECT * FROM event WHERE id = $id");
        $event = mysqli_fetch_assoc($result);
    } else {
        $error = "Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event | CrimsonGate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Outfit:wght@900&display=swap"
        rel="stylesheet">
    <style>
        body {
            background: #050505;
            color: white;
            font-family: 'Inter', sans-serif;
            padding: 50px;
            display: flex;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: #0d0d0d;
            padding: 40px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        h2 {
            font-family: 'Outfit';
            color: #ff2e2e;
            font-size: 2rem;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        label {
            display: block;
            margin-top: 15px;
            font-size: 0.85rem;
            color: #777;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input,
        select {
            width: 100%;
            padding: 14px;
            margin: 8px 0;
            background: #151515;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            font-family: inherit;
            outline: none;
        }

        input:focus {
            border-color: #ff2e2e;
        }

        .btn {
            background: #ff2e2e;
            color: white;
            border: none;
            padding: 16px;
            width: 100%;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 800;
            font-family: 'Outfit';
            margin-top: 30px;
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 46, 46, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .alert-success {
            background: rgba(0, 255, 163, 0.1);
            color: #00ffa3;
            border: 1px solid #00ffa3;
        }

        .alert-error {
            background: rgba(255, 46, 46, 0.1);
            color: #ff2e2e;
            border: 1px solid #ff2e2e;
        }

        .back-link {
            display: block;
            margin-top: 25px;
            color: #555;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: 0.2s;
        }

        .back-link:hover {
            color: #fff;
        }

        .current-poster {
            width: 100px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #333;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit Event</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Event Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>

            <label>Artist</label>
            <input type="text" name="artist" value="<?php echo htmlspecialchars($event['artist']); ?>" required>

            <label>Venue</label>
            <input type="text" name="venue" value="<?php echo htmlspecialchars($event['venue']); ?>" required>

            <label>Base Price (₱)</label>
            <input type="number" name="price" value="<?php echo $event['price']; ?>" step="0.01" required>

            <label>Status</label>
            <select name="status">
                <option value="active" <?php echo ($event['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="soldout" <?php echo ($event['status'] == 'soldout') ? 'selected' : ''; ?>>Sold Out</option>
            </select>

            <label>Current Poster</label>
            <?php if (!empty($event['poster'])): ?>
                <img src="../<?php echo $event['poster']; ?>" class="current-poster" alt="Poster">
            <?php endif; ?>

            <label>Change Poster (Leave blank to keep current)</label>
            <input type="file" name="poster" accept="image/*">

            <button type="submit" name="update_event" class="btn">SAVE CHANGES</button>
        </form>

        <a href="event.php" class="back-link">← Return to Events</a>
    </div>
</body>

</html>