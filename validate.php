<?php
function is_valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_password(string $password): bool {
    // Add custom password rules if needed
    return !empty($password);
}
?>

