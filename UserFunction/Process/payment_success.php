<?php
session_start();
include '../../db.php';

// 1. Get the data passed back from process_payment.php URL
$status = isset($_GET['status']) ? $_GET['status'] : '';
$points_earned = isset($_GET['points']) ? (float)$_GET['points'] : 0;
$email = $_SESSION['email'] ?? '';

// 2. Logic to finalize the booking in the database
if ($status === 'success' && !empty($email)) {

    // --- NEW: GENERATE 10 CHARACTER UNIQUE QR VARIABLE ---
    // This creates a randomized alphanumeric string (e.g., X8K2P9R1WQ)
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $qr_code_var = substr(str_shuffle($chars), 0, 10);

    // Update booking status AND store the unique QR variable
    // We target the 'pending' booking for the logged-in user
    $stmt = $conn->prepare("UPDATE bookings SET status = 'paid', qr_code_data = ? WHERE user_id = (SELECT id FROM user WHERE email = ?) AND status = 'pending'");
    $stmt->bind_param("ss", $qr_code_var, $email);
    $stmt->execute();

    // Award loyalty points to the user
    $stmt_points = $conn->prepare("UPDATE user SET points = points + ? WHERE email = ?");
    $stmt_points->bind_param("ds", $points_earned, $email);
    $stmt_points->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success | CrimsonGate</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #050505;
            color: white;
            font-family: 'Outfit', sans-serif;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            padding: 60px;
            background: #0c0c0c;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(0, 255, 170, 0.1);
            color: #00ffaa;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            margin: 0 auto 25px;
        }

        h1 { font-weight: 900; font-size: 2.2rem; margin-bottom: 10px; }
        p { color: #888; line-height: 1.6; margin-bottom: 25px; }

        .ticket-info {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .ref-label { font-size: 0.7rem; color: #555; text-transform: uppercase; letter-spacing: 1px; display: block; }
        .ref-value { font-family: monospace; font-size: 1.2rem; color: #fff; font-weight: 700; }

        .btn {
            background: #ff2e2e;
            color: white;
            padding: 16px 35px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 46, 46, 0.3);
            background: #ff4545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-circle">
            <i class="fas fa-check"></i>
        </div>
        <h1>Booking Secured!</h1>
        <p>Your payment was successful. Your digital pass has been generated and added to your vault.</p>
        
        <?php if(isset($qr_code_var)): ?>
            <div class="ticket-info">
                <span class="ref-label">Ticket Reference</span>
                <span class="ref-value"><?php echo $qr_code_var; ?></span>
            </div>
        <?php endif; ?>

        <a href="../dash.php" class="btn">Go Back to DashBoard</a>
    </div>
</body>
</html>