<?php
    // Include session and DB connection
    require 'includes/session.php';

    // Initialize assignments as an empty array
    $assignments = [];

    // Default query to retrieve all staff and their assigned tables
    $sql = "SELECT 
                staff.fname, 
                staff.lname, 
                staff.position, 
                tables.table_num, 
                tables.datetime_seated
            FROM tables
            JOIN staff ON tables.staffID = staff.staffID
            ORDER BY tables.datetime_seated DESC";

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['staffID'])) {
            // Sanitize and validate staffID
            $staffID = trim($_POST['staffID']);
            
            // Query to retrieve data for a specific staffID
            $sql = "SELECT 
                        staff.staffID,
                        staff.fname, 
                        staff.lname, 
                        staff.position,
                        tables.table_num, 
                        tables.datetime_seated
                    FROM tables
                    JOIN staff ON tables.staffID = staff.staffID
                    WHERE staff.staffID = :staffID";

            // Prepare and execute the query
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['staffID' => $staffID]);
            $assignments = $stmt->fetchAll();

            // Debugging: Check if results are empty
            if (empty($assignments)) {
                error_log("No results found for staffID: $staffID");
            }
        } else {
            // Execute the default query
            $stmt = $pdo->query($sql);
            $assignments = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        // Log any database errors
        error_log("Database error: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Table Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom-green w-100">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav menu">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="manage.php">Managment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="#">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="server_lookup.php">Server_lookup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="order_view.php">Order_view</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="logout.php">Log_out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Staff Table Assignments</h2>

        <!-- Form to input staffID -->
        <form method="POST" action="server_lookup.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="staffID" class="form-control" placeholder="Enter Staff ID (put 0 to see all servers)" required>
                <button type="submit" class="btn btn-dark">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Staff Name</th>
                        <th>Position</th>
                        <th>Table Number</th>
                        <th>Time Seated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
                            <td><?= htmlspecialchars($row['position'] ?? 'N/A') ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['table_num']) ?></td>
                            <td><?= isset($row['datetime_seated']) ? date('M d, Y H:i:s', strtotime($row['datetime_seated'])) : 'N/A' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($assignments)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No active assignments.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-center">
        <form action="logout.php" method="post" class="d-inline">
            <button type="submit" class="btn btn-dark">Log Out</button>
        </form>
    </div>
    <br>
    <div class="card-footer bg-dark text-white text-center border-top border-dark py-4">
        &copy; <?= date('Y') ?> Chicken Jockey Pizzaria <br>(Why'd you have to go and make things so complicated?)
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>