<?php
session_start();
include '../db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
// Folder name updated to match your requirement
$upload_folder = 'profilepics/';

// --- Reusable function to handle file upload ---
function handleFileUpload($fileInputName, $userId, $currentPic, $folder)
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return $currentPic; // Return current pic if no new file uploaded
    }

    // Ensure directory exists
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    $file = $_FILES[$fileInputName];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);

    // Validate type and size (max 5MB)
    if (!in_array($extension, $allowed) || $file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Invalid file type or size too large (Max 5MB).");
    }

    // Create unique filename
    $newFilename = $userId . "_" . time() . "." . $extension;
    $uploadPath = $folder . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Delete old non-default file
        if ($currentPic !== 'default.png' && file_exists($folder . $currentPic)) {
            unlink($folder . $currentPic);
        }
        return $newFilename;
    } else {
        throw new Exception("Failed to move uploaded file.");
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = $_POST['name'];
        $lname = $_POST['lname'];
        $email = $_POST['email'];

        // Use the function to get the new filename (or old one if no change)
        $new_pic = handleFileUpload('profile_pic', $user_id, $user['profile_pic'], $upload_folder);

        $update_stmt = $conn->prepare("UPDATE user SET name = ?, lname = ?, email = ?, profile_pic = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $name, $lname, $email, $new_pic, $user_id);

        if ($update_stmt->execute()) {
            $message = "<div style='color: #00ffaa; padding: 15px; background: rgba(0,255,170,0.05); border: 1px solid rgba(0,255,170,0.2); border-radius: 12px; margin-bottom: 25px; font-weight: 500;'>Profile updated successfully!</div>";
            // Refresh local user data
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        }
    } catch (Exception $e) {
        $message = "<div style='color: #ff5555; padding: 15px; background: rgba(255,85,85,0.05); border: 1px solid rgba(255,85,85,0.2); border-radius: 12px; margin-bottom: 25px; font-weight: 500;'>" . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | CrimsonGate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700;900&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg-dark: #050505;
            --sidebar-bg: #0a0a0a;
            --card-bg: #0c0c0c;
            --crimson: #ff2e2e;
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #ffffff;
            --text-dim: #888888;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            padding: 2.5rem 1.2rem;
            border-right: 1px solid var(--border);
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--crimson);
            text-align: center;
            margin-bottom: 3.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .logo span {
            color: white;
        }

        nav a {
            display: block;
            color: var(--text-dim);
            text-decoration: none;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        nav a:hover,
        nav a.active {
            background: rgba(255, 46, 46, 0.08);
            color: var(--crimson);
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 260px;
            padding: 50px;
            width: calc(100% - 260px);
        }

        .header-section {
            margin-bottom: 40px;
        }

        .header-section h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 800;
        }

        .profile-form {
            background: var(--card-bg);
            border: 1px solid var(--border);
            padding: 40px;
            border-radius: 20px;
            max-width: 700px;
        }

        /* Form Inputs */
        label {
            display: block;
            color: var(--text-dim);
            margin-bottom: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 15px;
            background: #121212;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: white;
            margin-bottom: 30px;
            font-size: 1rem;
        }

        input:focus {
            border-color: var(--crimson);
            outline: none;
            background: #161616;
        }

        /* Profile Pic Styling */
        .pic-upload-container {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 35px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 16px;
            border: 1px solid var(--border);
        }

        .current-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--bg-dark);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        .upload-controls {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .file-input {
            display: none;
        }

        .custom-file-upload {
            border: 1px solid var(--crimson);
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            color: white;
            background: transparent;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
            transition: 0.2s;
        }

        .custom-file-upload:hover {
            background: var(--crimson);
            color: white;
        }

        #file-name {
            font-size: 0.85rem;
            color: var(--text-dim);
        }

        .btn-submit {
            background: var(--crimson);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: #e02828;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="logo">CRIMSON<span>GATE</span></div>
        <nav>
            <a href="dash.php"><i class="fas fa-th-large" style="width:20px;"></i> Dashboard</a>
            <a href="profile.php" class="active"><i class="fas fa-user" style="width:20px;"></i> Profile</a>
            <a href="../index.php"><i class="fas fa-sign-out-alt" style="width:20px;"></i> Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-section">
            <h1>Edit Account Settings</h1>
        </div>

        <?php echo $message; ?>

        <form method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="pic-upload-container">
                <img src="../<?php echo $upload_folder . htmlspecialchars($user['profile_pic'] ?: 'default.png'); ?>"
                    class="current-pic" alt="Profile Picture">
                <div class="upload-controls">
                    <label for="profile_pic" class="custom-file-upload">
                        <i class="fas fa-camera"></i> Change Photo
                    </label>
                    <input type="file" name="profile_pic" id="profile_pic" class="file-input" accept="image/*"
                        onchange="updateFileName(this)">
                    <span id="file-name">No file chosen</span>
                </div>
            </div>

            <label>First Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label>Last Name</label>
            <input type="text" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <button type="submit" class="btn-submit">Save Account Changes</button>
        </form>
    </main>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0].name;
            document.getElementById('file-name').textContent = fileName;
        }
    </script>
</body>

</html>