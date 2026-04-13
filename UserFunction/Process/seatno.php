<?php
session_start();
include '../../db.php';

// 1. Get Event ID
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 1;

// 2. Fetch occupied seats
$occupied_seats = [];
$query = "SELECT seat_number FROM bookings WHERE event_id = ? AND (status = 'paid' OR status = 'pending')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $occupied_seats[] = $row['seat_number'];
}

// 3. Fetch Event Info
$event_info = $conn->prepare("SELECT title, artist, venue, price FROM events WHERE id = ?");
$event_info->bind_param("i", $event_id);
$event_info->execute();
$event_res = $event_info->get_result()->fetch_assoc();

$display_name = $event_res['title'] ?? "Select Seats";
$venue_name = $event_res['venue'] ?? "Venue";
$regular_price = $event_res['price'] ?? 1000;

// 4. Fetch User Vouchers
$user_vouchers = [];
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $u_stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $u_stmt->bind_param("s", $email);
    $u_stmt->execute();
    $u_res = $u_stmt->get_result()->fetch_assoc();

    if ($u_res) {
        $uid = $u_res['id'];
        $v_stmt = $conn->prepare("SELECT id, coupon_name FROM user_coupons WHERE user_id = ?");
        $v_stmt->bind_param("i", $uid);
        $v_stmt->execute();
        $v_result = $v_stmt->get_result();
        while ($v_row = $v_result->fetch_assoc()) {
            $user_vouchers[] = $v_row;
        }
    }
}

function renderSeat($id, $reg_price, $rowLetter, $occupied)
{
    $isOcc = in_array($id, $occupied);

    if (in_array($rowLetter, ['A', 'B'])) {
        $tier = 'vip1';
        $price = $reg_price * 3.0;
    } elseif (in_array($rowLetter, ['C', 'D'])) {
        $tier = 'vip2';
        $price = $reg_price * 2.0;
    } elseif (in_array($rowLetter, ['E', 'F'])) {
        $tier = 'vip3';
        $price = $reg_price * 1.5;
    } else {
        $tier = 'regular';
        $price = $reg_price;
    }

    $class = "seat $tier " . ($isOcc ? 'occupied' : '');
    $dis = $isOcc ? 'disabled' : '';

    echo "
    <label class='seat-wrapper'>
        <input type='checkbox' name='seats[]' value='$id' data-price='$price' onclick='calc()' $dis>
        <span class='$class'>$id</span>
    </label>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking | <?php echo htmlspecialchars($display_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050505;
            --vip1: #ffd700;
            --vip2: #c0c0c0;
            --vip3: #cd7f32;
            --regular: #ff2e2e;
            --available: #1a1a1a;
        }

        body {
            background-color: var(--bg);
            color: white;
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding-bottom: 140px;
        }

        .header {
            text-align: center;
            padding: 30px 20px;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 2px;
        }

        .stage-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 50px;
        }

        .stage-box {
            width: 50%;
            max-width: 500px;
            height: 12px;
            background: linear-gradient(to bottom, #444, #111);
            border-radius: 0 0 50px 50px;
            border-bottom: 2px solid #555;
        }

        .stage-label {
            margin-top: 10px;
            font-size: 0.65rem;
            color: #444;
            letter-spacing: 10px;
            font-weight: 900;
        }

        .theatre-map {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding-bottom: 50px;
        }

        .seating-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 40px;
        }

        .row-id {
            width: 30px;
            text-align: center;
            color: #333;
            font-weight: 900;
            font-size: 0.8rem;
        }

        .seat-block {
            display: grid;
            gap: 6px;
        }

        .seat-wrapper {
            position: relative;
        }

        .seat-wrapper input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .seat {
            width: 34px;
            height: 34px;
            background: var(--available);
            border: 1px solid #222;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            font-weight: 700;
            color: #555;
            transition: 0.2s;
            cursor: pointer;
        }

        .seat.vip1 {
            border-color: var(--vip1);
            color: var(--vip1);
        }

        .seat.vip2 {
            border-color: var(--vip2);
            color: var(--vip2);
        }

        .seat.vip3 {
            border-color: var(--vip3);
            color: var(--vip3);
        }

        .seat.regular {
            border-color: var(--regular);
            color: var(--regular);
        }

        .seat-wrapper input:checked+.seat {
            background: #fff;
            color: #000;
            border-color: #fff;
            transform: scale(1.1);
        }

        .seat.occupied {
            opacity: 0.05;
            cursor: not-allowed;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #0c0c0c;
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #222;
            box-sizing: border-box;
            z-index: 1000;
        }

        .voucher-box {
            flex: 1;
            margin: 0 40px;
            max-width: 300px;
        }

        .voucher-select {
            width: 100%;
            padding: 12px;
            background: #111;
            border: 1px solid #333;
            color: #fff;
            border-radius: 8px;
            font-family: 'Outfit';
            cursor: pointer;
        }

        .btn-confirm {
            background: var(--regular);
            color: white;
            border: none;
            padding: 16px 45px;
            border-radius: 8px;
            font-weight: 900;
            cursor: pointer;
        }

        .btn-confirm:disabled {
            background: #222;
            color: #444;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <div class="header">
        <div style="color:#666; font-size: 0.8rem; letter-spacing: 2px;"><?php echo htmlspecialchars($venue_name); ?>
        </div>
        <h1 style="margin: 5px 0;"><?php echo htmlspecialchars($display_name); ?></h1>

        <div class="legend">
            <div class="legend-item">
                <div class="dot" style="background:var(--vip1)"></div> VIP 1
            </div>
            <div class="legend-item">
                <div class="dot" style="background:var(--vip2)"></div> VIP 2
            </div>
            <div class="legend-item">
                <div class="dot" style="background:var(--vip3)"></div> VIP 3
            </div>
            <div class="legend-item">
                <div class="dot" style="background:var(--regular)"></div> Regular
            </div>
        </div>
    </div>

    <div class="stage-container">
        <div class="stage-box"></div>
        <div class="stage-label">STAGE</div>
    </div>

    <form action="payment.php" method="POST">
        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
        <input type="hidden" name="total_amount" id="total_amount_input" value="0">

        <div class="theatre-map">
            <?php
            $rows = (strpos($venue_name, 'Philippine Arena') !== false) ? range('A', 'L') : range('A', 'H');
            foreach ($rows as $r) {
                echo '<div class="seating-row"><div class="row-id">' . $r . '</div>';
                if (strpos($venue_name, 'Philippine Arena') !== false) {
                    echo "<div class='seat-block' style='grid-template-columns: repeat(8, 1fr);'>";
                    for ($i = 1; $i <= 8; $i++)
                        renderSeat($r . $i, $regular_price, $r, $occupied_seats);
                    echo "</div><div class='seat-block' style='grid-template-columns: repeat(14, 1fr);'>";
                    for ($i = 9; $i <= 22; $i++)
                        renderSeat($r . $i, $regular_price, $r, $occupied_seats);
                    echo "</div><div class='seat-block' style='grid-template-columns: repeat(8, 1fr);'>";
                    for ($i = 23; $i <= 30; $i++)
                        renderSeat($r . $i, $regular_price, $r, $occupied_seats);
                    echo "</div>";
                } else {
                    echo "<div class='seat-block' style='grid-template-columns: repeat(15, 1fr);'>";
                    for ($i = 1; $i <= 15; $i++)
                        renderSeat($r . $i, $regular_price, $r, $occupied_seats);
                    echo "</div>";
                }
                echo '<div class="row-id">' . $r . '</div></div>';
            }
            ?>
        </div>

        <div class="footer">
            <div>
                <div style="font-size: 0.7rem; color:#444;">TOTAL PRICE</div>
                <div id="total" style="font-size: 2rem; font-weight: 900;">₱0</div>
            </div>

            <div class="voucher-box">
                <div
                    style="font-size: 0.6rem; color:var(--regular); font-weight:900; margin-bottom:5px; text-transform:uppercase;">
                    Apply Voucher</div>
                <select name="voucher_id" class="voucher-select">
                    <option value="">No Voucher Selected</option>
                    <?php foreach ($user_vouchers as $v): ?>
                        <option value="<?php echo $v['id']; ?>">🎟️ <?php echo htmlspecialchars($v['coupon_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" id="btn" class="btn-confirm" disabled>CONFIRM SEATS</button>
        </div>
    </form>

    <script>
        function calc() {
            let total = 0;
            let selected = document.querySelectorAll('input[name="seats[]"]:checked');

            selected.forEach(checkbox => {
                total += parseFloat(checkbox.dataset.price);
            });

            // Update visible total
            document.getElementById('total').innerText = '₱' + total.toLocaleString();

            // NEW: Update hidden input value for the POST request
            document.getElementById('total_amount_input').value = total;

            document.getElementById('btn').disabled = selected.length === 0;
        }
    </script>
</body>

</html>