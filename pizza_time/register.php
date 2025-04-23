<?php
session_start();
include 'validate.php';

// Initial form state
$form_values = [
    'email' => '',
    'password' => ''
];

$form_errors = [
    'email' => '',
    'password' => ''
];

$form_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_values['email'] = trim($_POST['email'] ?? '');
    $form_values['password'] = trim($_POST['password'] ?? '');

    // Validate inputs
    if (empty($form_values['email'])) {
        $form_errors['email'] = 'Email is required.';
    } elseif (!filter_var($form_values['email'], FILTER_VALIDATE_EMAIL)) {
        $form_errors['email'] = 'Invalid email format.';
    }

    if (empty($form_values['password'])) {
        $form_errors['password'] = 'Password is required.';
    }

    if (empty($form_errors['email']) && empty($form_errors['password'])) {
        header("Location: login.php");
        exit;
    } else {
        $form_message = 'Please fix the errors below.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Registration</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="CSS/style.css">
        <link rel="stylesheet" href="CSS/form.css">
</head>
<body class="bg-black text-white">

<div class="form-container">
        <h4 class="text-center mb-4">CREATE ACCOUNT</h4>

        <?php if ($form_message): ?>
            <p class="text-center text-danger fw-bold"><?= htmlspecialchars($form_message) ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm">

            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($form_values['email']) ?>">
            <small class="text-danger"><?= $form_errors['email'] ?></small>


            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control">
            <small class="text-danger"><?= $form_errors['password'] ?></small>



            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="termsCheckbox" onchange="toggleSubmit()">
                <label class="form-check-label">
                    I accept the <a href="#" class="text-neon">Terms of Service</a> and <a href="#" class="text-neon">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn w-100 mt-3" id="submitBtn" disabled>ACCEPT AND CREATE</button>
        </form>
    </div>

</body>
</html>
