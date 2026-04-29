<?php
session_start();
include '../db.php';

// 1. ADMIN ACCESS CHECK
// Ensures only logged-in Admins can see this page
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// 2. FETCH VOUCHERS 
// Using your confirmed working query with 'coupon_id'
$query = "SELECT * FROM rewards_coupon ORDER BY coupon_id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Management | CrimsonGate</title>
    
    <!-- External Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Outfit:wght@700;900&display=swap" rel="stylesheet">
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
            --success: #00ffa3;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--glass-border);
            padding: 2.5rem 1.5rem;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            color: var(--crimson);
            font-weight: 900;
            font-size: 1.5rem;
            margin-bottom: 3.5rem;
            text-align: center;
            letter-spacing: 1px;
        }

        .logo span { color: white; }

        .nav-links { list-style: none; flex-grow: 1; }

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

        .nav-links a:hover, .nav-links a.active {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
        }

        .nav-links a.active { border-left: 3px solid var(--crimson); }

        .logout-link {
            margin-top: auto;
            color: var(--crimson);
            text-decoration: none;
            padding: 14px;
            display: flex;
            align-items: center; gap: 15px;
            font-weight: 600;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 4rem;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 3rem;
        }

        .header-section h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
        }

        .btn-primary {
            background: var(--crimson);
            color: #fff;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
            font-family: 'Outfit';
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center; gap: 10px;
            transition: 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 46, 46, 0.4);
        }

        /* --- TABLE PANEL --- */
        .table-panel {
            background: var(--panel);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            padding: 15px 30px;
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 20px 30px;
            border-bottom: 1px solid var(--glass-border);
            font-size: 0.95rem;
        }

        .tier-badge {
            background: rgba(255, 46, 46, 0.1);
            color: var(--crimson);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .icon-btn {
            width: 36px; height: 36px;
            display: inline-flex;
            align-items: center; justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            color: var(--text-dim);
            text-decoration: none;
            transition: 0.2s;
        }

        .icon-btn:hover {
            background: var(--crimson);
            color: white;
            border-color: var(--crimson);
        }

        /* --- MODAL --- */
        .modal {
            display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
            background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index: 1000;
        }
        .modal-content {
            background: var(--panel); padding: 40px; border-radius: 24px; 
            width: 450px; border: 1px solid var(--glass-border);
        }
        .modal-content input, .modal-content textarea {
            width: 100%; padding: 12px; margin: 10px 0; background: #151515; 
            border: 1px solid var(--glass-border); color: white; border-radius: 8px;
            font-family: inherit;
        }

        .alert {
            padding: 15px; background: rgba(0, 255, 163, 0.1); color: var(--success);
            border: 1px solid var(--success); border-radius: 12px; margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">CRIMSON<span>ADMIN</span></div>
        <ul class="nav-links">
            <li><a href="admindash.php"><i class="fas fa-chart-pie"></i> Overview</a></li>
            <li><a href="event.php"><i class="fas fa-calendar-check"></i> Events</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="user.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_vouchers.php" class="active"><i class="fas fa-tags"></i> Vouchers</a></li>
        </ul>
        <a href="../index.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <!-- Success Alert -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert"><i class="fas fa-check-circle"></i> Voucher offer created successfully!</div>
        <?php endif; ?>

        <section class="header-section">
            <div>
                <h1>Voucher Management</h1>
                <p style="color: var(--text-dim);">Create and manage point-based reward offers.</p>
            </div>
            <button onclick="document.getElementById('addModal').style.display='flex'" class="btn-primary">
                <i class="fas fa-plus"></i> Create Offer
            </button>
        </section>

        <!-- DATA TABLE -->
        <div class="table-panel">
            <table>
                <thead>
                    <tr>
                        <th>Coupon Name</th>
                        <th>Description</th>
                        <th>Point Cost</th>
                        <th>Max Uses</th>
                        <th>Tier</th>
                        <th>Tools</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><div style="font-weight: 700; color: var(--crimson);"><?php echo htmlspecialchars($row['coupon_name']); ?></div></td>
                                <td style="color: var(--text-dim);"><?php echo htmlspecialchars($row['description']); ?></td>
                                <td style="font-weight: 700;"><?php echo number_format($row['point_cost']); ?> pts</td>
                                <td><?php echo htmlspecialchars($row['max_uses']); ?>x</td>
                                <td><span class="tier-badge"><?php echo htmlspecialchars($row['tier_label'] ?? 'General'); ?></span></td>
                                <td>
                                    <!-- Using coupon_id for deletion to match your DB -->
                                    <a href="delete_coupon.php?id=<?php echo $row['coupon_id']; ?>" class="icon-btn" 
                                       onclick="return confirm('Delete this voucher offer?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; color:var(--text-dim); padding: 50px;">No vouchers found in rewards_coupon.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- CREATE MODAL -->
    <div id="addModal" class="modal" onclick="if(event.target == this) this.style.display='none'">
        <div class="modal-content">
            <h2 style="font-family: 'Outfit'; margin-bottom: 20px;">New Voucher Offer</h2>
            <form action="Coupon.php" method="POST">
                <input type="text" name="coupon_name" placeholder="COUPON CODE (e.g. SAVE10)" required>
                <textarea name="description" placeholder="Voucher Description" rows="3" required></textarea>
                <input type="number" name="point_cost" placeholder="Points Required" required>
                <input type="number" name="max_uses" placeholder="Max Uses Per User" required>
                <input type="text" name="tier_label" placeholder="Tier Label (e.g. VIP ONLY)">
                
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn-primary" style="flex:1; justify-content: center;">Save Offer</button>
                    <button type="button" onclick="document.getElementById('addModal').style.display='none'" 
                            style="flex:1; background:#222; color:white; border:none; border-radius:12px; cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>