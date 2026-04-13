<?php
include '../db.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = (int) $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM events WHERE id = $id");
$event = mysqli_fetch_assoc($result);

if (!$event) {
    die("Event not found.");
}

$success = "";
$error = "";

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
        if (move_uploaded_file($_FILES['poster']['tmp_name'], "../images/" . $filename)) {
            $poster_path = "images/" . $filename;
        }
    }

    $sql = "UPDATE events SET title=?, artist=?, venue=?, price=?, status=?, poster=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdssi", $title, $artist, $venue, $price, $status, $poster_path, $id);

    if ($stmt->execute()) {
        $success = "Event updated successfully!";
        // Refresh local data
        $result = mysqli_query($conn, "SELECT * FROM events WHERE id = $id");
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
    <title>Edit Event | CrimsonGate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Outfit:wght@900&display=swap"
        rel="stylesheet">
    <style>
        body {
            background: #050505;
            color: white;
            font-family: 'Inter';
            padding: 50px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #0d0d0d;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h2 {
            font-family: 'Outfit';
            color: #ff2e2e;
            margin-bottom: 20px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: #1a1a1a;
            border: 1px solid #333;
            color: white;
            border-radius: 8px;
        }

        .btn {
            background: #ff2e2e;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #777;
            text-decoration: none;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit Event</h2>

        <?php if ($success)
            echo "<p style='color:#00ffa3'>$success</p>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Event Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>

            <label>Artist</label>
            <input type="text" name="artist" value="<?php echo htmlspecialchars($event['artist']); ?>" required>

            <label>Venue</label>
            <input type="text" name="venue" value="<?php echo htmlspecialchars($event['venue']); ?>" required>

            <label>Base Price (₱)</label>
            <input type="number" name="price" value="<?php echo $event['price']; ?>" required>

            <label>Status</label>
            <select name="status">
                <option value="active" <?php if ($event['status'] == 'active')
                    echo 'selected'; ?>>Active</option>
                <option value="soldout" <?php if ($event['status'] == 'soldout')
                    echo 'selected'; ?>>Sold Out</option>
            </select>

            <label>Change Poster (Leave blank to keep current)</label>
            <input type="file" name="poster">

            <button type="submit" name="update_event" class="btn">Save Changes</button>
        </form>
        <a href="admindash.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>

</html>