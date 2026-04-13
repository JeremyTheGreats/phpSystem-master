<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Fetch User Current Points
$u_query = mysqli_query($conn, "SELECT points FROM user WHERE id = '$user_id'");
$u_data = mysqli_fetch_assoc($u_query);
$current_points = $u_data['points'];

// 2. Handle Redemption Logic
if (isset($_POST['redeem'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['coupon_name']);
    $cost = intval($_POST['point_cost']);
    $limit = intval($_POST['max_uses']);

    // Check how many times this user has already used this coupon
    $check_usage = mysqli_query($conn, "SELECT id FROM user_coupons WHERE user_id = '$user_id' AND coupon_name = '$c_name'");
    $used_count = mysqli_num_rows($check_usage);

    if ($used_count >= $limit) {
        $message = "<div class='alert error'>Limit reached! You cannot redeem this again.</div>";
    } elseif ($current_points < $cost) {
        $message = "<div class='alert error'>Insufficient points. You need " . ($cost - $current_points) . " more.</div>";
    } else {
        // Process: Deduct Points + Log the use in user_coupons
        $new_bal = $current_points - $cost;
        mysqli_query($conn, "UPDATE user SET points = '$new_bal' WHERE id = '$user_id'");
        mysqli_query($conn, "INSERT INTO user_coupons (user_id, coupon_name) VALUES ('$user_id', '$c_name')");

        // Refresh to show updated points
        header("Location: voucher.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Voucher Rewards | CrimsonGate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --crimson: #ff2e2e;
            --bg: #050505;
            --card: #111;
        }

        body {
            background: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            display: flex;
            margin: 0;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            background: #000;
            padding: 40px 20px;
            border-right: 1px solid #222;
            position: fixed;
        }

        .main {
            margin-left: 260px;
            flex: 1;
            padding: 60px;
        }

        .points-header {
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #222;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .voucher-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .v-card {
            background: var(--card);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #222;
            position: relative;
            transition: 0.3s;
        }

        .v-card:hover {
            border-color: var(--crimson);
            transform: translateY(-5px);
        }

        .tag {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.6rem;
            font-weight: 800;
            color: var(--crimson);
            border: 1px solid var(--crimson);
            padding: 3px 8px;
            border-radius: 5px;
        }

        .btn-redeem {
            width: 100%;
            padding: 12px;
            background: var(--crimson);
            border: none;
            color: #fff;
            font-weight: 800;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-redeem:disabled {
            background: #333;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .error {
            background: rgba(255, 0, 0, 0.1);
            color: #ff4444;
        }

        .success {
            background: rgba(0, 255, 120, 0.1);
            color: #00ff78;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <h2 style="color:var(--crimson); font-family:'Outfit';">CRIMSONGATE</h2>
        <nav style="margin-top:50px;">
            <a href="dash.php" style="color:#888; text-decoration:none;"><i class="fas fa-th-large"></i> Dashboard</a>
        </nav>
    </aside>

    <main class="main">
        <div class="points-header">
            <div>
                <h1 style="margin:0;">Voucher Rewards</h1>
                <p style="color:#666;">Convert your points into exclusive event access.</p>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.7rem; color:var(--crimson); font-weight:bold;">YOUR BALANCE</div>
                <div style="font-size:2.5rem; font-weight:900;"><?php echo number_format((float)$current_points); ?> <small style="font-size:1rem;">PTS</small></div>
            </div>
        </div>

        <?php
        if (isset($_GET['success'])) echo "<div class='alert success'>Voucher Redeemed Successfully!</div>";
        echo $message;
        ?>

        <div class="voucher-grid">
            <?php
            // Fetch all coupons from the Shop table
            $offers = mysqli_query($conn, "SELECT * FROM coupon_offers");

            while ($row = mysqli_fetch_assoc($offers)):
                $c_name = $row['coupon_name'];
                $limit = $row['max_uses'];

                // Check usage for THIS specific user
                $usage = mysqli_query($conn, "SELECT id FROM user_coupons WHERE user_id = '$user_id' AND coupon_name = '$c_name'");
                $count = mysqli_num_rows($usage);
                $is_locked = ($count >= $limit);
            ?>
                <div class="v-card">
                    <span class="tag"><?php echo $row['tier_label']; ?></span>
                    <h3><?php echo $c_name; ?></h3>
                    <p style="color:#888; font-size:0.9rem; margin:15px 0;"><?php echo $row['description']; ?></p>
                    <div style="font-weight:bold; color:var(--crimson);"><?php echo number_format($row['point_cost']); ?> PTS</div>

                    <form method="POST">
                        <input type="hidden" name="coupon_name" value="<?php echo $c_name; ?>">
                        <input type="hidden" name="point_cost" value="<?php echo $row['point_cost']; ?>">
                        <input type="hidden" name="max_uses" value="<?php echo $limit; ?>">

                        <button type="submit" name="redeem" class="btn-redeem" <?php echo $is_locked ? 'disabled' : ''; ?>>
                            <?php echo $is_locked ? 'LIMIT REACHED' : 'REDEEM NOW'; ?>
                        </button>
                    </form>

                    <div style="margin-top:10px; font-size:0.7rem; color:#444;">
                        Used: <?php echo $count; ?> / <?php echo $limit; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

</body>

</html>