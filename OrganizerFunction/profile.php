<?php
session_start();
include '../db.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Organizer') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// 2. FETCH CURRENT USER DATA
$stmt = $conn->prepare("SELECT email, status FROM User WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 3. HANDLE FORM SUBMISSION
if (isset($_POST['update_profile'])) {
    $new_email = trim($_POST['email']);
    $new_pass = $_POST['new_password'];

    if (!empty($new_pass)) {
        // Update both Email and Password
        $hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT);
        $update = $conn->prepare("UPDATE User SET email = ?, password = ? WHERE user_id = ?");
        $update->bind_param("ssi", $new_email, $hashed_pass, $user_id);
    } else {
        // Update Email only
        $update = $conn->prepare("UPDATE User SET email = ? WHERE user_id = ?");
        $update->bind_param("si", $new_email, $user_id);
    }

    if ($update->execute()) {
        $_SESSION['email'] = $new_email; // Update session email
        $success_msg = "Profile updated successfully!";
        // Refresh local user data
        $user['email'] = $new_email;
    } else {
        $error_msg = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | CrimsonGate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --crimson: #ff2e2e;
            --bg: #050505;
            --sidebar-bg: #0d0d0d;
            --panel: #0f0f0f;
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f0f0f0;
            --text-dim: #777777;
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
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--glass-border);
            padding: 2.5rem 1.5rem;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            color: var(--crimson);
            font-weight: 900;
            font-size: 1.5rem;
            margin-bottom: 3.5rem;
            text-align: center;
        }

        .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dim);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.3s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }

        .logout-link {
            margin-top: auto;
            color: var(--crimson);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            gap: 10px;
            padding: 10px;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-container {
            width: 100%;
            max-width: 600px;
        }

        .header-sec {
            margin-bottom: 2rem;
        }

        .header-sec h1 {
            font-family: 'Outfit';
            font-size: 2.5rem;
        }

        .settings-card {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--text-dim);
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .form-group input {
            width: 100%;
            background: #000;
            border: 1px solid var(--glass-border);
            padding: 15px;
            border-radius: 12px;
            color: white;
            font-family: 'Inter';
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: var(--crimson);
            outline: none;
        }

        .btn-save {
            background: var(--crimson);
            color: white;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            font-size: 1rem;
        }

        .btn-save:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .alert-success {
            background: rgba(0, 255, 163, 0.1);
            color: #00ffa3;
            border: 1px solid rgba(0, 255, 163, 0.2);
        }

        .alert-error {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
            border: 1px solid rgba(255, 46, 46, 0.2);
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.05);
            font-size: 0.7rem;
            color: var(--crimson);
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="logo">CRIMSON<span>ORG</span></div>
        <ul class="nav-links">
            <li><a href="organizerdash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="manage_events.php"><i class="fas fa-calendar-check"></i> My Events</a></li>
            <li><a href="sales_report.php"><i class="fas fa-chart-line"></i> Sales Report</a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-user-cog"></i> Profile</a></li>
        </ul>
        <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </aside>

    <main class="main-content">
        <div class="profile-container">
            <div class="header-sec">
                <p style="color: var(--crimson); font-weight: 700; text-transform: uppercase;">Account Management</p>
                <h1>Profile Settings</h1>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-error">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="settings-card">
                <form method="POST">
                    <div class="form-group">
                        <label>Account Status</label>
                        <span class="badge">
                            <?php echo strtoupper($user['status']); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Change Password (Leave blank to keep current)</label>
                        <input type="password" name="new_password" placeholder="••••••••">
                    </div>

                    <button type="submit" name="update_profile" class="btn-save">SAVE CHANGES</button>
                </form>
            </div>
        </div>
    </main>

</body>

</html>