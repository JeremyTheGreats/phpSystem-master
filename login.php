<?php
session_start();
include "db.php";

/** * 1. IMPROVED LOGOUT / SESSION CLEARING
 * If someone visits login.php while already logged in, we reset the session.
 */
if (isset($_SESSION['email']) && !isset($_POST['login'])) {
    session_unset();
    session_destroy();
    header("Location: login.php?logged_out=1");
    exit;
}

$error = "";

/**
 * 2. SECURE LOGIN PROCESSING
 */
if (isset($_POST['login'])) {
    $email = trim($_POST['username']);
    $password = $_POST['password'];

    // Use Prepared Statements to prevent SQL Injection
    // Using user_id instead of id to match our new schema
    $stmt = $conn->prepare("SELECT user_id, email, password, role, status FROM User WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {

        // Check account standing
        if ($user['status'] === 'pending') {
            $error = "Account pending admin approval.";
        } elseif ($user['status'] === 'inactive') {
            $error = "Account deactivated. Contact support.";
        } else {
            // Clear old session data and regenerate ID for security (Prevents Session Fixation)
            session_unset();
            session_regenerate_id(true);

            // Map user data to session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            /**
             * 3. ROLE-BASED REDIRECTION
             * Organizers now have their own dedicated area.
             */
            switch ($user['role']) {
                case 'Admin':
                    header("Location: AdminFunction/admindash.php");
                    break;
                case 'Organizer':
                    header("Location: OrganizerFunction/organizerdash.php");
                    break;
                default:
                    header("Location: UserFunction/dash.php");
                    break;
            }
            exit;
        }
    } else {
        $error = "Invalid email or password!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | CrimsonGate</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Outfit:wght@700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script type="text/javascript">
        function preventBack() { window.history.forward(); }
        setTimeout("preventBack()", 0);
        window.onunload = function () { null };
    </script>

    <style>
        :root {
            --crimson: #ff2e2e;
            --crimson-glow: rgba(255, 46, 46, 0.3);
            --bg: #050505;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-dim: #999999;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .page-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(5, 5, 5, 0.8), rgba(5, 5, 5, 0.9)), url("style/back.jpg");
            background-size: cover;
            background-position: center;
            z-index: -2;
        }

        .ambient-glow {
            position: fixed;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, var(--crimson-glow) 0%, transparent 70%);
            bottom: -15vw;
            left: -10vw;
            z-index: -1;
            filter: blur(100px);
            opacity: 0.5;
        }

        .login-card {
            background: rgba(15, 15, 15, 0.6);
            backdrop-filter: blur(25px);
            padding: 60px 50px;
            border-radius: 32px;
            width: 95%;
            max-width: 450px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.8);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card h2 {
            font-family: 'Outfit';
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .login-card h2 span {
            color: var(--crimson);
        }

        .subtitle {
            color: var(--text-dim);
            margin-bottom: 40px;
            font-size: 0.95rem;
        }

        .form-group {
            text-align: left;
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #444;
        }

        input {
            width: 100%;
            padding: 18px 20px 18px 55px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: white;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--crimson);
            background: rgba(255, 255, 255, 0.07);
        }

        input:focus+i {
            color: var(--crimson);
        }

        .login-btn {
            width: 100%;
            padding: 20px;
            background: var(--crimson);
            border: none;
            border-radius: 16px;
            color: white;
            font-family: 'Outfit';
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.4s;
            box-shadow: 0 10px 30px var(--crimson-glow);
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 46, 46, 0.4);
        }

        .error-msg {
            background: rgba(255, 46, 46, 0.1);
            color: #ff4d4d;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 46, 46, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .options {
            margin-top: 35px;
            color: var(--text-dim);
            font-size: 0.9rem;
        }

        .options a {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            border-bottom: 1px solid var(--crimson);
        }

        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="page-background"></div>
    <div class="ambient-glow"></div>

    <div class="login-card">
        <h2>CRIMSON<span>GATE</span></h2>
        <p class="subtitle">Secure access to your premier tickets</p>

        <?php if ($error): ?>
            <div class="error-msg">
                <i class="fas fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="username" placeholder="Enter your email" required>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <button type="submit" name="login" class="login-btn">Sign In</button>
        </form>

        <div class="options">
            New to CrimsonGate? <a href="register.php">Create Account</a>
        </div>

        <a href="index.php" class="back-home">
            <i class="fas fa-arrow-left-long"></i> Return to Site
        </a>
    </div>
</body>

</html>