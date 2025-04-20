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
        <h2 class="text-center mb-4">Welcome, <?= htmlspecialchars($fname) ?>! <br>Here are your quick actions:</h2>
			<div class="text-center">
				<ul class="list-group list-group-flush border border-dark rounded">
					<li class="list-group-item"><a href="staff_assignment.php">View Staff Assignments</a></li>
					<li class="list-group-item"><a href="orders.php">View All Orders</a></li>
					<li class="list-group-item"><a href="report.php">Generate Reports</a></li>
				</ul>
				<br>
				<form action="logout.php" method="post" class="d-inline">
					<button type="submit" class="btn btn-dark">Log Out</button>
				</form>
        	</div>
	</div>
	<footer class="site-footer bg-dark text-white text-center border-top border-dark py-4 mt-auto">
        &copy; <?= date('Y') ?> Chicken Jockey Pizzaria <br> (Why'd you have to go and make things so complicated?)
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
