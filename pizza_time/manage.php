<?php
require 'includes/session.php';

// Must be logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Get staffID for this user based on email
$sql = "SELECT staffID FROM users WHERE email = :email";
$staffID = pdo($pdo, $sql, ['email' => $_SESSION['email']])->fetchColumn();

if (!$staffID) {
    die("Access denied. Staff ID not found.");
}

// Get position from staff table
$sql = "SELECT position FROM staff WHERE staffID = :staffID";
$position = pdo($pdo, $sql, ['staffID' => $staffID])->fetchColumn();

if ($position !== 'Manager' && $position !== 'Owner') {
    die("Access denied. This page is for Managers and Owners only.");
}

$sql = "SELECT fname FROM staff WHERE staffID = :staffID";
$fname = pdo($pdo, $sql, ['staffID' => $staffID])->fetchColumn();


//manager salary update query code
// Initialize variables for salary update
$update_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['targetStaffID'], $_POST['newSalary'])) {
    $targetStaffID = trim($_POST['targetStaffID']);
    $newSalary = trim($_POST['newSalary']);

    // Validate inputs
    if (!is_numeric($targetStaffID) || !is_numeric($newSalary)) {
        $update_message = 'Invalid input. Please enter valid numbers.';
    } else {
        // Update the salary in the database
        try {
            $sql = "UPDATE staff SET salary = :newSalary WHERE staffID = :targetStaffID";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'newSalary' => $newSalary,
                'targetStaffID' => $targetStaffID
            ]);
            $update_message = 'Salary updated successfully.';
        } catch (PDOException $e) {
            $update_message = 'Error updating salary: ' . $e->getMessage();
        }
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager page</title>
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
                        <a class="nav-link text-white fw-bold px-lg-5" href="manage.php">Managment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="#">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="server_lookup.php">Server_lookup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="#">not sure yet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="logout.php">Log_out</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>
	

    <div class="container mt-5">
        <h2 class="text-center mb-4">Update Staff Salary</h2>

        <?php if ($update_message): ?>
            <div class="alert alert-info text-center"><?= htmlspecialchars($update_message) ?></div>
        <?php endif; ?>

        <form method="POST" action="manage.php" class="mb-4">
            <div class="mb-3">
                <label for="targetStaffID" class="form-label">Staff ID</label>
                <input type="text" name="targetStaffID" id="targetStaffID" class="form-control" placeholder="Enter Staff ID" required>
            </div>
            
            <div class="mb-3">
                <label for="newSalary" class="form-label">New Salary</label>
                <input type="text" name="newSalary" id="newSalary" class="form-control" placeholder="Enter New Salary" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Update Salary</button>
        </form>
    </div>





	<footer class="site-footer bg-dark text-white text-center border-top border-dark py-4 mt-auto">
        &copy; <?= date('Y') ?> Chicken Jockey Pizzaria <br> (Why'd you have to go and make things so complicated?)
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
