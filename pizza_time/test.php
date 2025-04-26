<?php
// order.php
require_once 'includes/database-connection.php';
require_once 'includes/validate.php';
session_start();

$phone    = '';
$orders   = [];
$error    = '';
$customer = null;
$success  = '';

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $orderID = $_POST['orderID'];
    $phone   = preg_replace('/[^0-9]/', '', $_POST['phone']);

    try {
        $order = pdo($pdo,
            "SELECT orders.orderID
             FROM orders
             JOIN customer
               ON orders.custID = customer.custID
             WHERE orders.orderID = ? AND customer.phone_number = ?",
            [$orderID, $phone]
        )->fetch();

        if ($order) {
            pdo($pdo,
                "DELETE FROM orders WHERE orderID = ?",
                [$orderID]
            );
            pdo($pdo,
                "DELETE FROM food   WHERE orderID = ?",
                [$orderID]
            );
            $success = "Order #$orderID has been cancelled";
        } else {
            $error = "Order not found or access denied";
        }
    } catch (PDOException $e) {
        $error = "Cancellation failed: " . $e->getMessage();
    }
}

// Remove a single line‐item
if (isset($_POST['remove_item'])) {
    $orderID = $_POST['orderID'];
    $foodID  = $_POST['foodID'];
    $phone   = preg_replace('/[^0-9]/','', $_POST['phone']);

    try {
        $check = pdo($pdo,
            "SELECT orders.orderID
             FROM orders
             JOIN customer
               ON orders.custID = customer.custID
             WHERE orders.orderID = ? AND customer.phone_number = ?",
            [$orderID, $phone]
        )->fetch();

        if ($check) {
            pdo($pdo, "DELETE FROM food WHERE foodID = ?", [$foodID]);
            $success = "Item removed from order #$orderID.";
        } else {
            $error = "Access denied.";
        }
    } catch (PDOException $e) {
        $error = "Remove failed: " . $e->getMessage();
    }
}

// Add a new line‐item
if (isset($_POST['add_item'])) {
    $orderID          = $_POST['orderID'];
    $menuID           = $_POST['menuID'];
    $amount           = $_POST['amount'];
    $customer_request = trim($_POST['customer_request']);
    $phone            = preg_replace('/[^0-9]/','', $_POST['phone']);

    try {
        $check = pdo($pdo,
            "SELECT orders.orderID
             FROM orders
             JOIN customer
               ON orders.custID = customer.custID
             WHERE orders.orderID = ? AND customer.phone_number = ?",
            [$orderID, $phone]
        )->fetch();

        if ($check) {
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

// Handle order lookup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lookup'])) {
    $phone       = trim($_POST['phone'] ?? '');
    $clean_phone = preg_replace('/[^0-9]/', '', $phone);

    if (!validate_phone($clean_phone)) {
        $error = "Invalid phone number format";
    } else {
        try {
            // Customer + address lookup
            $customer = pdo($pdo,
                "SELECT
                    customer.custID,
                    customer.fname,
                    customer.lname,
                    customer.phone_number,
                    customer_address.street_name,
                    customer_address.house_number,
                    customer_address.zip
                 FROM customer
                 LEFT JOIN customer_address
                   ON customer.streetID = customer_address.streetID
                 WHERE customer.phone_number = ?",
                [$clean_phone]
            )->fetch();

            if ($customer) {
                // Orders + method lookup
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
}

// Load menu items for "Add item" dropdown
$menuItems = pdo($pdo, "SELECT menuID, item_name FROM menu")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <link rel="stylesheet" href="CSS/based.css">
    <link rel="stylesheet" href="CSS/orders.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
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
                      ($customer['street_name'] ?? '') . ', ' .
                      ($customer['zip'] ?? '')
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
                                 default    => '❔ '.ucfirst($row['method_type'])
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

    <!-- New Order Placement Section -->
    <div class="new-order mt-5">
        <h3 class="mb-4">Place New Order</h3>
        <!-- Your existing “place order” form goes here -->
    </div>
</div>
</body>
</html>
