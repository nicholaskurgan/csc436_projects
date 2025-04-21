<?php
    // Include session and DB connection
    require 'includes/session.php';

    // SQL to retrieve staff and their assigned tables
    $sql = "SELECT 
                s.fname, 
                s.lname, 
                s.position, 
                t.table_num, 
                t.datetime_seated
            FROM tables t
            JOIN staff s ON t.staffID = s.staffID
            ORDER BY t.datetime_seated DESC";

    // Execute the query using your pdo() helper
    $assignments = pdo($pdo, $sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Table Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.gstatic.com">

    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="CSS/style.css" as="style">

    <link rel = "stylesheet" href = "CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom-green w-100">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- change link if you had the chance-->
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav menu">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="https://jimmyzhang.rhody.dev/csc372_projects/index.html">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="https://jimmyzhang.rhody.dev/csc372_projects/guide.html">PC BUILD</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="https://jimmyzhang.rhody.dev/csc372_projects/shop.html">SHOP</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="https://jimmyzhang.rhody.dev/csc372_projects/about_us.php">ABOUT US</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="https://jimmyzhang.rhody.dev/csc372_projects/index.html">CONTACT US</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Staff Table Assignments</h2>

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
                            <td><?= htmlspecialchars($row['position']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['table_num']) ?></td>
                            <td><?= date('M d, Y H:i:s', strtotime($row['datetime_seated'])) ?></td>
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
