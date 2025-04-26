<?php
// start a new session or resume the existing one
session_start();
// retrieve the user's name from the 'user_name' cookie if it exists,
// otherwise default to the string 'user'
$user = $_COOKIE['user_name'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thank You</title>
    <link rel="stylesheet" href="CSS/form.css">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body class="bg-black text-white text-center">
    <div class="container mt-5">
        <h1>Thank you, <?= htmlspecialchars($user) ?>!</h1>
        <p>Your account has been successfully created.</p>
        <p><a href="login.php" class="btn btn-green mt-3">Back to Home</a></p>
    </div>
</body>
</html>