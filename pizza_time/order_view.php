<?php
// order_view.php — used to retrieve orders made by a customer based on name input

// Include the session and database connection setup
require 'includes/session.php';

// Initialize an empty array to store order data
$orderData = [];

// Check if the request is a POST and the customer name input is not empty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cust_name'])) {

    // Sanitize and trim the customer name from the form
    $custName = trim($_POST['cust_name']);

    // Split the name into first and last names (assumes user inputs "First Last")
    $nameParts = explode(" ", $custName);
    $first = $nameParts[0] ?? ''; // Use first part as first name
    $last = $nameParts[1] ?? '';  // Use second part as last name (if present)

    // SQL query to fetch order details for the customer
    $sql = "SELECT 
                customer.first_name,        -- Customer's first name
                customer.last_name,         -- Customer's last name
                orders.order_id,            -- Order ID
                orders.order_time,          -- When the order was placed
                menu.item_name,             -- Name of the menu item ordered
                menu.item_price             -- Price of the item
            FROM orders
            JOIN customer ON orders.cust_id = customer.cust_id       -- Link orders to customers
            JOIN food ON orders.order_id = food.order_id             -- Link orders to food entries
            JOIN menu ON food.item_id = menu.item_id                 -- Link food items to menu details
            WHERE customer.first_name LIKE :first                   -- Match by first name
              AND customer.last_name LIKE :last                     -- Match by last name then 
            ORDER BY orders.order_time DESC";                       // Sort all newest to oldest

    try {
        // Prepare the SQL statement to safely bind variables
        $stmt = $pdo->prepare($sql);

        // Execute the query with bound parameters for first and last name
        $stmt->execute([
            'first' => "%$first%",   // Partial match for flexibility (e.g. "Jo" matches "John")
            'last' => "%$last%"      // Same for last name
        ]);

        // Fetch all matching records into an associative array
        $orderData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Display an error message and stop script if the query fails
        die("Database error: " . $e->getMessage());
    }
}
?>