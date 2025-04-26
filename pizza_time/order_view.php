<?php
// order_view.php — used to retrieve orders made by a customer based on name input

// Include session and database connection file
require 'includes/session.php';

// Initialize an empty array to store order results
$orderData = [];

// Check if the form was submitted and the customer name is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cust_name'])) {

    // Get and sanitize the customer name input
    $custName = trim($_POST['cust_name']);

    // Split full name into first and last name parts
    $nameParts = explode(" ", $custName);
    $first = $nameParts[0] ?? ''; // Use first word as first name
    $last = $nameParts[1] ?? '';  // Use second word as last name if available

    // SQL query to find the orders linked to the provided customer name
    $sql = "SELECT 
                customer.first_name,        -- Customer's first name
                customer.last_name,         -- Customer's last name
                orders.order_id,            -- ID of the order
                orders.order_time,          -- When the order was placed
                menu.item_name,             -- Name of the item ordered
                menu.item_price             -- Price of the item
            FROM orders
            JOIN customer ON orders.cust_id = customer.cust_id
            JOIN food ON orders.order_id = food.order_id
            JOIN menu ON food.item_id = menu.item_id
            WHERE customer.first_name LIKE :first   -- Match first name (flexible)
              AND customer.last_name LIKE :last     -- Match last name (flexible)
            ORDER BY orders.order_time DESC";        -- Sort orders newest first

    try {
        // Prepare the SQL query to avoid SQL injection
        $stmt = $pdo->prepare($sql);

        // Bind user input to the query with wildcards for partial matching
        $stmt->execute([
            'first' => "%$first%",
            'last' => "%$last%"
        ]);

        // Fetch all matching order records
        $orderData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Display an error message if the database query fails
        die("Database error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Lookup</title>

    <!-- Link to external CSS styles -->
    <link rel="stylesheet" href="css/style.css"> <!-- adjust path if needed -->
</head>
<body>
    <div class="form-container">
        <!-- Form to search for customer orders -->
        <h2>Search Customer Orders</h2>
        <form method="POST" action="order_view.php">
            <label for="cust_name">Customer Name:</label>
            <input type="text" name="cust_name" id="cust_name" class="form-control" placeholder="e.g., Olivia Johnson" required>
            <br>
            <button type="submit" class="btn-green">Search</button>
        </form>
    </div>

    <div class="content">
        <?php if (!empty($orderData)): ?>
            <!-- Display order results if found -->
            <h3>Order Results:</h3>
            <table class="form-control" border="1" style="margin-top: 20px; width: 100%;">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Order ID</th>
                        <th>Order Time</th>
                        <th>Item</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderData as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['order_id']) ?></td>
                            <td><?= htmlspecialchars($row['order_time']) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td>$<?= number_format($row['item_price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
