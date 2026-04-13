<?php
include "db.php";
$error = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $check = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'");

    if (mysqli_num_rows($check) > 0) {
        $error = "Email is already registered!";
    } else {
        // Updated to include 'pending' status by default
        $insert = mysqli_query($conn, "INSERT INTO user (name, lname, email, password, role, status) VALUES ('$name', '$lname', '$email', '$hash', 'user', 'pending')");
        if ($insert) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $error = "Registration Failed! Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join CrimsonGate | Experience the Sound</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Outfit:wght@700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            overflow-x: hidden;
        }

        /* Ambient Background Layers */
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
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, var(--crimson-glow) 0%, transparent 70%);
            top: -20vw;
            left: -10vw;
            z-index: -1;
            filter: blur(120px);
            opacity: 0.4;
        }

        /* Registration Card */
        .register-card {
            background: rgba(15, 15, 15, 0.6);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 50px;
            border-radius: 32px;
            width: 100%;
            max-width: 520px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.7);
            animation: slideUp 0.8s cubic-bezier(0.2, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-area {
            text-align: center;
            margin-bottom: 35px;
        }

        .header-area h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 8px;
            letter-spacing: -1px;
        }

        .header-area h2 span {
            color: var(--crimson);
        }

        .header-area p {
            color: var(--text-dim);
            font-size: 0.95rem;
        }

        /* Form Layout */
        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 22px;
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #444;
            transition: 0.3s;
        }

        input {
            width: 100%;
            padding: 15px 15px 15px 48px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            color: white;
            outline: none;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--crimson);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(255, 46, 46, 0.15);
        }

        input:focus+i {
            color: var(--crimson);
        }

        /* Custom Checkbox */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 25px 0;
            font-size: 0.85rem;
            color: var(--text-dim);
        }

        .checkbox-group input {
            width: auto;
            padding: 0;
            accent-color: var(--crimson);
            cursor: pointer;
        }

        .register-btn {
            width: 100%;
            padding: 18px;
            background: var(--crimson);
            border: none;
            border-radius: 14px;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.4s;
            box-shadow: 0 10px 30px var(--crimson-glow);
        }

        .register-btn:hover {
            background: #ff4d4d;
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 46, 46, 0.4);
        }

        .error-msg {
            background: rgba(255, 46, 46, 0.1);
            color: #ff4d4d;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 46, 46, 0.2);
            text-align: center;
        }

        .footer-links {
            text-align: center;
            margin-top: 35px;
            color: var(--text-dim);
            font-size: 0.9rem;
        }

        .footer-links a {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            border-bottom: 1px solid var(--crimson);
        }

        .footer-links a:hover {
            color: var(--crimson);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.8rem;
        }

        @media (max-width: 480px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .register-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="page-background"></div>
    <div class="ambient-glow"></div>

    <div class="register-card">
        <div class="header-area">
            <h2>JOIN <span>CRIMSON</span></h2>
            <p>Access the Philippines' most exclusive events.</p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="#" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="fname">First Name</label>
                    <div class="input-wrapper">
                        <input type="text" id="fname" name="name" placeholder="John" required>
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <div class="input-wrapper">
                        <input type="text" id="lname" name="lname" placeholder="Doe" required>
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" placeholder="name@email.com" required>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Security Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="terms" required>
                <label for="terms">I accept the <a href="#">Service Agreement</a></label>
            </div>

            <button type="submit" class="register-btn" name="register">Create Account</button>
        </form>

        <div class="footer-links">
            Already a member? <a href="login.php">Sign In</a>
        </div>

        <a href="index.php" class="back-link"><i class="fas fa-arrow-left-long"></i> Return to Site</a>
    </div>

</body>

</html>