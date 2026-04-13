<?php
session_start();
include '../../db.php';

// 1. IDENTITY PROTECTION
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. DATA RETRIEVAL
$selected_seats = isset($_POST['seats']) ? $_POST['seats'] : [];
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 1;
$base_total = isset($_POST['total_amount']) ? (float) $_POST['total_amount'] : 0;

if (empty($selected_seats)) {
    header("Location: seatno.php?event_id=$event_id&error=noselection");
    exit();
}

// 3. FETCH AVAILABLE VOUCHERS FOR THIS SPECIFIC USER
$vouchers = [];
$v_query = $conn->prepare("SELECT DISTINCT coupon_name FROM user_coupons WHERE user_id = ?");
$v_query->bind_param("i", $user_id);
$v_query->execute();
$v_result = $v_query->get_result();

while ($row = $v_result->fetch_assoc()) {
    $vouchers[] = $row['coupon_name'];
}

// 4. APPLY REDEEMED VOUCHER LOGIC
$discount = 0;
$applied_coupon = isset($_POST['voucher_select']) ? $_POST['voucher_select'] : "";

if (!empty($applied_coupon)) {
    // Applying discounts based on the specific coupons in your table
    if ($applied_coupon === 'CRIMSON-NEWBIE') {
        $discount = 50.00;
    } elseif ($applied_coupon === 'FAN-FAVE-10') {
        $discount = $base_total * 0.10;
    }
}

$final_total = max(0, $base_total - $discount);

// 5. FETCH EVENT DETAILS
$stmt = $conn->prepare("SELECT title, artist, venue FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Review Payment | CrimsonGate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@700;900&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #050505;
            --card: #0e0e0e;
            --crimson: #ff2e2e;
            --border: rgba(255, 255, 255, 0.1);
            --text-dim: #888;
        }

        body {
            background: var(--bg);
            color: white;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            align-items: center;
        }

        .review-container {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border-radius: 28px;
            padding: 40px;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .header h1 {
            font-family: 'Outfit';
            font-weight: 900;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Dropdown Styling */
        .voucher-section {
            margin: 25px 0;
        }

        .voucher-label {
            font-size: 0.75rem;
            color: var(--crimson);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 10px;
        }

        .select-wrapper {
            position: relative;
            width: 100%;
        }

        select {
            width: 100%;
            padding: 15px;
            background: #151515;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: white;
            font-size: 0.9rem;
            appearance: none;
            cursor: pointer;
            outline: none;
        }

        select:focus {
            border-color: var(--crimson);
        }

        .total-section {
            background: rgba(255, 46, 46, 0.05);
            margin: 30px -40px;
            padding: 30px 40px;
            text-align: center;
        }

        .total-price {
            font-family: 'Outfit';
            font-size: 3rem;
            font-weight: 900;
            color: var(--crimson);
        }

        .btn-confirm {
            width: 100%;
            padding: 20px;
            background: var(--crimson);
            color: white;
            border: none;
            border-radius: 15px;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            transition: 0.3s;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 46, 46, 0.3);
        }
    </style>
</head>

<body>

    <div class="review-container">
        <div class="header">
            <h1>Review & Pay</h1>
        </div>

        <div
            style="font-size: 0.85rem; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px;">
            <p><span style="color: var(--text-dim);">Event:</span> <?php echo htmlspecialchars($event['title']); ?></p>
            <p><span style="color: var(--text-dim);">Seats:</span> <?php echo implode(', ', $selected_seats); ?></p>
        </div>

        <div class="voucher-section">
            <span class="voucher-label">Apply Voucher</span>
            <form method="POST" id="voucherForm">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <input type="hidden" name="total_amount" value="<?php echo $base_total; ?>">
                <?php foreach ($selected_seats as $seat): ?>
                    <input type="hidden" name="seats[]" value="<?php echo htmlspecialchars($seat); ?>">
                <?php endforeach; ?>

                <div class="select-wrapper">
                    <select name="voucher_select" onchange="this.form.submit()">
                        <option value="">-- Select an available voucher --</option>
                        <?php foreach ($vouchers as $v): ?>
                            <option value="<?php echo $v; ?>" <?php echo ($applied_coupon == $v) ? 'selected' : ''; ?>>
                                <?php echo $v; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <div class="total-section">
            <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 700;">FINAL AMOUNT</span>
            <?php if ($discount > 0): ?>
                <div style="color: #2ecc71; font-size: 0.8rem; margin: 5px 0;">
                    Voucher Applied: -₱<?php echo number_format($discount, 2); ?>
                </div>
            <?php endif; ?>
            <div class="total-price">₱<?php echo number_format($final_total, 2); ?></div>
        </div>

        <form action="process_payment.php" method="POST">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
            <input type="hidden" name="total_amount" value="<?php echo $final_total; ?>">
            <input type="hidden" name="coupon_used" value="<?php echo $applied_coupon; ?>">
            <?php foreach ($selected_seats as $seat): ?>
                <input type="hidden" name="final_seats[]" value="<?php echo htmlspecialchars($seat); ?>">
            <?php endforeach; ?>

            <button type="submit" class="btn-confirm">Complete Payment</button>
        </form>
    </div>

</body>

</html>