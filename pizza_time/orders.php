<?php
// order.php
// This script handles order management, including placing new orders, looking up existing orders, and modifying orders.

require_once 'includes/database-connection.php'; // Include the database connection file
require_once 'includes/validate.php'; // Include validation functions
session_start(); // Start the session to manage user state

// Initialize variables

$phone = ''; // Stores the phone number for lookup
$orders = []; // Stores the list of orders for a customer
$error = ''; // Stores error messages
$customer = null; // Stores customer information
$success = ''; // Stores success messages


// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $orderID = $_POST['orderID']; // Get the order ID from the form
    $phone   = preg_replace('/[^0-9]/', '', $_POST['phone']); // Sanitize the phone number

    try {
        // Check if the order exists and belongs to the customer
        $order = pdo($pdo,
            "SELECT orders.orderID, orders.tableID
             FROM orders
             JOIN customer
               ON orders.custID = customer.custID
             WHERE orders.orderID = ? AND customer.phone_number = ?",
            [$orderID, $phone]
        )->fetch();

        if ($order) {
            $tableID = $order['tableID']; // Get the tableID associated with the order

            // Delete the order and its associated food items
            pdo($pdo, "DELETE FROM orders WHERE orderID = ?", [$orderID]);
            pdo($pdo, "DELETE FROM food   WHERE orderID = ?", [$orderID]);

            // If the order is dine-in, delete the corresponding table entry
            if (!empty($tableID)) {
                pdo($pdo, "DELETE FROM tables WHERE tableID = ?", [$tableID]);
            }

            $success = "Order #$orderID has been cancelled.";
        } else {
            $error = "Order not found or access denied.";
        }
    } catch (PDOException $e) {
        $error = "Cancellation failed: " . $e->getMessage();
    }
}

// Remove a single line-item from an order
if (isset($_POST['remove_item'])) {
    $orderID = $_POST['orderID']; // Get the order ID
    $foodID  = $_POST['foodID']; // Get the food ID
    $phone   = preg_replace('/[^0-9]/','', $_POST['phone']); // Sanitize the phone number

    try {
        // Check if the order exists and belongs to the customer
        $check = pdo($pdo,
            "SELECT orders.orderID
             FROM orders
             JOIN customer
               ON orders.custID = customer.custID
             WHERE orders.orderID = ? AND customer.phone_number = ?",
            [$orderID, $phone]
        )->fetch();

        if ($check) {
            // Remove the specific food item from the order
            pdo($pdo, "DELETE FROM food WHERE foodID = ?", [$foodID]);
            $success = "Item removed from order #$orderID.";
        } else {
            $error = "Access denied.";
        }
    } catch (PDOException $e) {
        $error = "Remove failed: " . $e->getMessage();
    }
}

// Add a new line-item to an order
if (isset($_POST['add_item'])) {
    $orderID = $_POST['orderID']; // Get the order ID
    $menuID = $_POST['menuID']; // Get the menu item ID
    $amount = $_POST['amount']; // Get the quantity
    $customer_request = trim($_POST['customer_request']); // Get any special request
    $phone = preg_replace('/[^0-9]/','', $_POST['phone']); // Sanitize the phone number


    try {
        // Check if the order exists and belongs to the customer
        $check = pdo($pdo,
            "SELECT orders.orderID
             FROM orders
             JOIN customer
               ON orders.custID = customer.custID
             WHERE orders.orderID = ? AND customer.phone_number = ?",
            [$orderID, $phone]
        )->fetch();

        if ($check) {
            // Add the new item to the order
            pdo($pdo,
                "INSERT INTO food (orderID, menuID, amount, customer_request)
                 VALUES (?,?,?,?)",
                [$orderID, $menuID, $amount, $customer_request]
            );
            $success = "Item added to order #$orderID.";
        } else {
            $error = "Access denied.";
        }
    } catch (PDOException $e) {
        $error = "Add failed: " . $e->getMessage();
    }
}

// Validate phone number format
function validate_phone($phone) {
    return preg_match('/^\d{10}$/', $phone); // Ensure the phone number is exactly 10 digits
}

// Handle order lookup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lookup'])) {
    $phone = trim($_POST['phone'] ?? ''); // Get the phone number from the form
    $clean_phone = preg_replace('/[^0-9]/', '', $phone); // Sanitize the phone number

    if (!validate_phone($clean_phone)) {
        $error = "Invalid phone number format";
    } else {
        try {
            // Lookup customer and address information
            $customer = pdo($pdo,
                "SELECT
                    customer.custID,
                    customer.fname,
                    customer.lname,
                    customer.phone_number,
                    customer_address.street_name,
                    customer_address.house_number
                 FROM customer
                 LEFT JOIN customer_address
                   ON customer.streetID = customer_address.streetID
                 WHERE customer.phone_number = ?",
                [$clean_phone]
            )->fetch();

            if ($customer) {
                // Lookup orders and associated items for the customer
                $orders = pdo($pdo,
                    "SELECT
                        orders.orderID,
                        order_method.method_type,
                        orders.datetime_placed,
                        food.foodID,
                        food.amount,
                        food.customer_request,
                        menu.item_name,
                        menu.item_price
                     FROM orders
                     JOIN order_method
                       ON orders.methodID = order_method.methodID
                     LEFT JOIN food
                       ON orders.orderID = food.orderID
                     LEFT JOIN menu
                       ON food.menuID = menu.menuID
                     WHERE orders.custID = ?
                     ORDER BY orders.datetime_placed DESC",
                    [$customer['custID']]
                )->fetchAll();

                if (empty($orders)) {
                    $error = "No orders found for this customer";
                }
            } else {
                $error = "No customer found with this phone number";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
    $methodOptions = pdo($pdo, "SELECT method_type FROM order_method")->fetchAll(PDO::FETCH_COLUMN);
}

// Handle new order placement
if (isset($_POST['place_order'])) {

    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? ''); // Sanitize the phone number
    $fname = trim($_POST['fname'] ?? ''); // Get the first name
    $lname = trim($_POST['lname'] ?? ''); // Get the last name
    $methodID = intval($_POST['methodID'] ?? 0); // Get the order method ID
    $items = $_POST['items'] ?? []; // Get the list of items
    $house_number = trim($_POST['house_number'] ?? ''); // Get the house number
    $street_name = trim($_POST['street_name']  ?? ''); // Get the street name

    $table_num = trim($_POST['table_num'] ?? ''); // Get the table number (if dine-in)

    try {
        // Basic validation
        if (strlen($phone) !== 10) {
            throw new Exception("Phone must be 10 digits.");
        }
        if ($methodID < 0) {
            throw new Exception("Please select a valid order method.");
        }
        if (count($items) === 0) {
            throw new Exception("Add at least one item.");
        }

        // Lookup or create customer
        $row = pdo($pdo,
            "SELECT custID FROM customer WHERE phone_number = ?",
            [$phone]
        )->fetch();
        if ($row) {
            $custID = $row['custID'];
        } else {
            if ($fname === '' || $lname === '') {
                throw new Exception("First & last name are required for new customers.");
            }
            pdo($pdo,
                "INSERT INTO customer (fname, lname, phone_number)
                 VALUES (?,?,?)",
                [$fname, $lname, $phone]
            );
            $custID = $pdo->lastInsertId();
        }

        // Create the order
        $method = pdo($pdo,
            "SELECT method_type FROM order_method WHERE methodID = ?",
            [$methodID]
        )->fetchColumn();

        if ($method === 'dinein') {
            // Require table number for dine-in orders
            if (empty($table_num)) {
                throw new Exception("Table number is required for dine-in.");
            }

            // Validate server
            $staffID = intval($_POST['staffID'] ?? 0);
            if ($staffID <= 0) {
                throw new Exception("Please select a valid server.");
            }

            // Check if the table exists with the same table_num and staffID
            $row = pdo($pdo,
                "SELECT tableID, datetime_seated FROM tables WHERE table_num = ? AND staffID = ?",
                [$table_num, $staffID]
            )->fetch();

            if (!$row) {
                // Insert a new table if it does not exist
                pdo($pdo,
                    "INSERT INTO tables (table_num, staffID, datetime_seated) VALUES (?, ?, NOW())",
                    [$table_num, $staffID]
                );
                $tableID = $pdo->lastInsertId(); // Get the newly inserted table ID
            } else {
                // Check if the datetime_seated is different
                $existingDatetimeSeated = $row['datetime_seated'];
                $currentDatetime = date('Y-m-d H:i:s'); // Get the current date and time

                if ($existingDatetimeSeated !== $currentDatetime) {
                    // Insert a new row with the current datetime_seated
                    pdo($pdo,
                        "INSERT INTO tables (table_num, staffID, datetime_seated) VALUES (?, ?, NOW())",
                        [$table_num, $staffID]
                    );
                    $tableID = $pdo->lastInsertId(); // Get the newly inserted table ID
                } else {
                    // Use the existing tableID
                    $tableID = $row['tableID'];
                }
            }

            // Insert the dine-in order into the orders table
            pdo($pdo,
                "INSERT INTO orders (custID, tableID, methodID, datetime_placed)
                 VALUES (?, ?, ?, NOW())",
                [$custID, $tableID, $methodID]
            );
        } else {
            // Non-dine-in orders
            pdo($pdo,
                "INSERT INTO orders (custID, methodID, datetime_placed)
                 VALUES (?, ?, NOW())",
                [$custID, $methodID]
            );
        }

        $orderID = $pdo->lastInsertId(); // Get the newly created order ID

        // Insert each line-item
        foreach ($items as $i) {
            $m = intval($i['menuID']);
            $q = intval($i['amount']);
            $r = trim($i['request'] ?? '');
            if ($m < 0 || $q < 1) {
                throw new Exception("Invalid item or quantity.");
            }
            pdo($pdo,
                "INSERT INTO food (orderID, menuID, amount, customer_request)
                 VALUES (?, ?, ?, ?)",
                [$orderID, $m, $q, $r]
            );
        }

        $success = "Order #{$orderID} placed successfully!";
    } catch (Exception $e) {
        $error = "Order failed: " . $e->getMessage();
    }
}

// Load menu items for "Add item" dropdown
$menuItems = pdo($pdo, "SELECT menuID, item_name FROM menu")->fetchAll();
// Load order-method IDs + types
$methodOptions = pdo($pdo,
    "SELECT methodID, method_type
       FROM order_method
      ORDER BY methodID",
    []
)->fetchAll(PDO::FETCH_ASSOC);
// Load servers for dine-in
$staffOptions = pdo($pdo,
    "SELECT staffID,
            CONCAT(fname,' ',lname) AS server_name
     FROM staff
     WHERE position = 'server'",
    []
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <link rel="stylesheet" href="css/based.css">
    <link rel="stylesheet" href="css/orders.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom-green w-100">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- change link if you had the chance-->
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav menu">
                <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="manage.php">Managment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="server_lookup.php">Server_lookup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="logout.php">Log_out</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>



<body>
<div class="container mt-5">
    <h2 class="mb-4">Order Management</h2>

    <!-- Order Lookup Form -->
    <form method="POST" class="mb-5 shadow-sm p-4 bg-light rounded">
        <div class="mb-3">
            <label for="phone" class="form-label">Enter your phone number:</label>
            <input type="tel"
                   name="phone"
                   id="phone"
                   class="form-control"
                   required
                   pattern="[0-9]{10}"
                   placeholder="1234567890"
                   value="<?= htmlspecialchars($phone) ?>">
            <div class="form-text">Format: 10 digits only</div>
        </div>
        <button type="submit" name="lookup" class="btn btn-primary">Find Orders</button>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($customer): ?>
        <div class="customer-info mb-4">
            <h4>Orders for <?= htmlspecialchars($customer['fname'] . ' ' . $customer['lname']) ?></h4>
            <p class="text-muted">
                <?= htmlspecialchars(
                      ($customer['house_number'] ?? '') . ' ' .
                      ($customer['street_name'] ?? '')
                   ) ?>
            </p>
        </div>

        <div class="order-list">
            <?php
            $currentOrder = null;
            $orderTotal   = 0;

            foreach ($orders as $row):
                // Start a new order card
                if ($currentOrder !== $row['orderID']):
                    // Close previous order
                    if ($currentOrder !== null):
                        echo '</tbody></table>';
                        echo '<div class="order-total">Total: $' . number_format($orderTotal,2) . '</div>';
                        // Add-item & Cancel forms
                        echo '<form method="POST" class="mt-3 d-flex gap-2 align-items-center">'
                           . '<input type="hidden" name="orderID" value="'.$currentOrder.'">'
                           . '<input type="hidden" name="phone"   value="'.htmlspecialchars($phone).'">'
                           . '<select name="menuID" class="form-select form-select-sm">';
                        foreach ($menuItems as $m) {
                            echo '<option value="'.$m['menuID'].'">'
                                 . htmlspecialchars($m['item_name'])
                                 . '</option>';
                        }
                        echo '</select>'
                           . '<input type="number" name="amount" class="form-control form-control-sm" min="1" value="1">'
                           . '<input type="text"   name="customer_request" class="form-control form-control-sm" placeholder="Request">'
                           . '<button name="add_item"     class="btn btn-success btn-sm">Add</button>'
                           . '<button name="cancel_order" class="btn btn-danger btn-sm ms-auto">Cancel Order</button>'
                           . '</form>'
                           . '</div>'; // .order-card
                    endif;

                    // Begin new order
                    $currentOrder = $row['orderID'];
                    $orderTotal   = 0;
            ?>
            <div class="order-card mb-4 shadow-sm">
                <div class="order-header p-3 bg-light d-flex justify-content-between">
                    <h5>Order #<?= $row['orderID'] ?></h5>
                    <div>
                        <span class="badge bg-primary">
                            <?= match(strtolower($row['method_type'])) {
                                 'delivery' => '🚚 Delivery',
                                 'pickup'   => '📦 Pickup',
                                 'dinein'   => '🍽️ Dine-in',
                                 default    => ''.ucfirst($row['method_type'])
                               } ?>
                        </span>
                        <span class="text-muted ms-2">
                            <?= date('M j, Y g:i A', strtotime($row['datetime_placed'])) ?>
                        </span>
                    </div>
                </div>
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Requests</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
            <?php endif; ?>

            <?php if ($row['item_name']): 
                $subtotal = $row['item_price'] * $row['amount'];
                $orderTotal += $subtotal;
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td>$<?= number_format($row['item_price'],2) ?></td>
                    <td><?= $row['amount'] ?></td>
                    <td><?= htmlspecialchars($row['customer_request'] ?? 'None') ?></td>
                    <td>$<?= number_format($subtotal,2) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="foodID"  value="<?= $row['foodID'] ?>">
                            <input type="hidden" name="orderID" value="<?= $row['orderID'] ?>">
                            <input type="hidden" name="phone"   value="<?= htmlspecialchars($phone) ?>">
                            <button name="remove_item" class="btn btn-sm btn-warning">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endif; ?>

            <?php endforeach; ?>

            <?php if ($currentOrder !== null): 
                // Close last order
                echo '</tbody></table>';
                echo '<div class="order-total">Total: $'.number_format($orderTotal,2).'</div>';
                echo '<form method="POST" class="mt-3 d-flex gap-2 align-items-center">'
                   . '<input type="hidden" name="orderID" value="'.$currentOrder.'">'
                   . '<input type="hidden" name="phone"   value="'.htmlspecialchars($phone).'">'
                   . '<select name="menuID" class="form-select form-select-sm">';
                foreach ($menuItems as $m) {
                    echo '<option value="'.$m['menuID'].'">'.htmlspecialchars($m['item_name']).'</option>';
                }
                echo '</select>'
                   . '<input type="number" name="amount" class="form-control form-control-sm" min="1" value="1">'
                   . '<input type="text"   name="customer_request" class="form-control form-control-sm" placeholder="Request">'
                   . '<button name="add_item"     class="btn btn-success btn-sm">Add</button>'
                   . '<button name="cancel_order" class="btn btn-danger btn-sm ms-auto">Cancel Order</button>'
                   . '</form>'
                   . '</div>';
            endif; ?>
        </div>
    <?php endif; ?>


    <div class="new-order mt-5">
    <h3>Place New Order</h3>
    <form method="POST" class="shadow-sm p-4 bg-light rounded">

        <!-- Phone -->
        <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control"
                required pattern="[0-9]{10}" placeholder="1234567890">
        </div>

        <!-- New customer names -->
        <div class="row g-3 mb-3">
        <div class="col">
            <input type="text" name="fname" class="form-control" placeholder="First name">
        </div>
        <div class="col">
            <input type="text" name="lname" class="form-control" placeholder="Last name">
        </div>
        </div>
        <small class="text-muted">Required only for new customers</small>

        <div class="row g-3 mb-3">
            <div class="col">
                <input type="text"
                    name="house_number"
                    class="form-control"
                    placeholder="House number"
                    value="<?= htmlspecialchars($_POST['house_number'] ?? '') ?>">
            </div>
            <div class="col">
                <input type="text"
                    name="street_name"
                    class="form-control"
                    placeholder="Street name"
                    value="<?= htmlspecialchars($_POST['street_name'] ?? '') ?>">
            </div>
        </div>
        <small class="text-muted">Required only for new customers</small>

        <!-- Method dropdown -->
        <div class="mb-3">
        <label class="form-label">Order Method</label>
        <select id="methodID" name="methodID" class="form-select" required>
          <?php foreach ($methodOptions as $m): 
            $t = strtolower($m['method_type']); ?>
            <option value="<?= $m['methodID'] ?>">
              <?= match($t) {
                   'delivery' => '🚚 Delivery',
                   'pickup'   => '📦 Pickup',
                   'dinein'   => '🍽️ Dine-in',
                   default    => ucfirst($t)
                 } ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3 dinein-field" style="display:none;">
        <label for="table_num" class="form-label">Table Number</label>
        <input type="text" id="table_num" name="table_num" class="form-control" placeholder="e.g. 5">
      </div>
      <div class="mb-3 dinein-field" style="display:none;">
        <label class="form-label">Server</label>
        <select name="staffID" class="form-select">
          <option value="">Select Server</option>
          <?php foreach ($staffOptions as $s): ?>
            <option value="<?= $s['staffID'] ?>">
              <?= htmlspecialchars($s['server_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

        <!-- Items repeater -->
        <div class="mb-4">
        <label class="form-label">Items</label>
        <div id="items">
            <div class="row g-2 align-items-center mb-2 item-row">
            <div class="col-5">
                <select name="items[0][menuID]" class="form-select">
                <?php foreach($menuItems as $it): ?>
                    <option value="<?= $it['menuID'] ?>">
                    <?= htmlspecialchars($it['item_name']) ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </div>
            <div class="col-2">
                <input type="number" name="items[0][amount]"
                    class="form-control" value="1" min="1">
            </div>
            <div class="col-4">
                <input type="text" name="items[0][request]"
                    class="form-control" placeholder="Request (opt)">
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-danger remove-item">×</button>
            </div>
            </div>
        </div>
        </div>

        <button type="submit" name="place_order" class="btn btn-primary">
        Place Order
        </button>
    </form>
    </div>
</div>
<script src = "JavaScript/toggle.js"></script>
<script src = "JavaScript/hide.js"></script>

</body>
</html>
