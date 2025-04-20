<?php
require 'includes/session.php';

$form_error = '';
$email = '';
$password = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $form_error = 'Please fill out all fields.';
    } else {
        // Step 1: Validate user from 'users' table using email
        $sql = "SELECT * FROM users WHERE email = :email AND password = :password";
        $user = pdo($pdo, $sql, [
            'email' => $email,
            'password' => $password
        ])->fetch();

        if ($user) {
            $staffID = $user['staffID'];

            // Step 2: Get staff position
            $sql = "SELECT position FROM staff WHERE staffID = :staffID";
            $position = pdo($pdo, $sql, ['staffID' => $staffID])->fetchColumn();

            if ($position) {
                // Store email in session and log in
                login($email); // this function must store $_SESSION['email']

                // Step 3: Redirect based on staff position
                if ($position === 'Manager' || $position === 'Owner') {
                    header('Location: profile.php');
                } elseif ($position === 'Server') {
                    header('Location: index.php');
                } else {
                    $form_error = 'Access denied. Unknown position.';
                }
                exit;
            } else {
                $form_error = 'Staff position not found.';
            }
        } else {
            $form_error = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 480px;">
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white text-center">
            <h4>Login to Your Account</h4>
        </div>
        <div class="card-body">
            <?php if ($form_error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($form_error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" value="<?= htmlspecialchars($password) ?>">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-dark">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
