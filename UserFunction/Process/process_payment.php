<?php
session_start();
include '../../db.php';

// --- PAYMONGO CONFIGURATION ---


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Data Collection & Validation
    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $seats = isset($_POST['final_seats']) ? $_POST['final_seats'] : [];
    $method = isset($_POST['method']) ? $_POST['method'] : 'card';

    // Get voucher info from the review page
    $applied_coupon = isset($_POST['coupon_used']) ? $_POST['coupon_used'] : "";
    $discount_amount = 0;

    $reference_no = 'CG-' . strtoupper(uniqid());

    if (!isset($_SESSION['email'])) {
        header("Location: login.php?error=session_expired");
        exit();
    }

    if ($event_id === 0 || empty($seats)) {
        header("Location: seatno.php?error=invalid_selection");
        exit();
    }

    // 2. Fetch User & Event Details
    $email = $_SESSION['email'];
    $user_query = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $user_query->bind_param("s", $email);
    $user_query->execute();
    $user_data = $user_query->get_result()->fetch_assoc();

    $event_stmt = $conn->prepare("SELECT title, price FROM events WHERE id = ?");
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event_res = $event_stmt->get_result()->fetch_assoc();

    if (!$user_data || !$event_res) {
        die("Error: Data synchronization failed. Please log in again.");
    }

    $user_id = $user_data['id'];
    $event_title = $event_res['title'];
    $reg_price = $event_res['price'] ?? 1000;

    // 3. Prepare Line Items for PayMongo
    $line_items = [];
    $total_base_price = 0;

    foreach ($seats as $seat) {
        $row = strtoupper(substr($seat, 0, 1));

        if (in_array($row, ['A', 'B']))
            $price = $reg_price * 3.0;
        elseif (in_array($row, ['C', 'D']))
            $price = $reg_price * 2.0;
        elseif (in_array($row, ['E', 'F']))
            $price = $reg_price * 1.5;
        else
            $price = $reg_price;

        $total_base_price += $price;

        $line_items[] = [
            'currency' => 'PHP',
            'amount' => (int) ($price * 100),
            'description' => "Seat $seat | $event_title",
            'name' => "CrimsonGate Ticket",
            'quantity' => 1
        ];
    }

    // --- APPLY VOUCHER REDUCTION ---
    if (!empty($applied_coupon)) {
        if ($applied_coupon === 'CRIMSON-NEWBIE') {
            $discount_amount = 50.00;
        } elseif ($applied_coupon === 'FAN-FAVE-10') {
            $discount_amount = $total_base_price * 0.10;
        }

        // Adjusting total for PayMongo
        // Note: PayMongo requires specific handling for discounts. 
        // We subtract the discount from the first item to ensure the final total is correct.
        if ($discount_amount > 0 && !empty($line_items)) {
            $discount_centavos = (int) ($discount_amount * 100);
            $line_items[0]['amount'] = max(0, $line_items[0]['amount'] - $discount_centavos);
            $line_items[0]['description'] .= " (Voucher Applied: $applied_coupon)";
        }
    }

    $final_calculated_total = max(0, $total_base_price - $discount_amount);
    $total_points = ($final_calculated_total * 0.10);

    // 4. Database: Save as Pending
    $conn->begin_transaction();
    try {
        // FIXED: Removed 'coupon_used' to solve 'Unknown Column' error
        $cleanup = $conn->prepare("DELETE FROM bookings WHERE event_id = ? AND seat_number = ? AND status = 'pending'");
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_id, seat_number, price, status, payment_method) VALUES (?, ?, ?, ?, 'pending', ?)");

        foreach ($seats as $index => $seat) {
            $cleanup->bind_param("is", $event_id, $seat);
            $cleanup->execute();

            $seat_base_price = ($line_items[$index]['amount'] / 100);

            // FIXED: 'iisis' string matches the 5 variables below
            $stmt->bind_param("iisis", $user_id, $event_id, $seat, $seat_base_price, $method);
            $stmt->execute();
        }

        // Logic to remove the voucher from the user's account
        if (!empty($applied_coupon)) {
            $del_stmt = $conn->prepare("DELETE FROM user_coupons WHERE user_id = ? AND coupon_name = ? LIMIT 1");
            $del_stmt->bind_param("is", $user_id, $applied_coupon);
            $del_stmt->execute();
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Database Error: " . $e->getMessage());
    }

    // 5. PayMongo Checkout Request
    $payload = json_encode([
        'data' => [
            'attributes' => [
                'send_email_receipt' => true,
                'show_description' => true,
                'show_line_items' => true,
                'payment_method_types' => ['card', 'gcash', 'paymaya'],
                'line_items' => $line_items,
                'success_url' => "http://localhost/phpSystem-master/UserFunction/Process/payment_success.php?status=success&points=$total_points&ref=$reference_no",
                'cancel_url' => "http://localhost/phpSystem-master/UserFunction/Process/payment.php?event_id=$event_id",
                'description' => "Ref: $reference_no | User: $email | Coupon: $applied_coupon"
            ]
        ]
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.paymongo.com/v1/checkout_sessions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($paymongo_secret_key . ":")
        ],
    ]);

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    if (isset($data['data']['attributes']['checkout_url'])) {
        header("Location: " . $data['data']['attributes']['checkout_url']);
        exit();
    } else {
        echo "<h3>Payment Error</h3>";
        print_r($data);
        echo "<a href='payment.php?event_id=$event_id'>Try Again</a>";
    }
}
?>