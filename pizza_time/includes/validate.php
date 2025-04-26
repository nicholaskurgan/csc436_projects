<?php
// validates that a email that user input 
// the function parameters take the following:
// $email - user's inputted email
// return true if it is a valid date
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
// validates that a text string is within a specific character length range 
// the function parameters take the following:
// $text - the string to validate
// $min - the minimum number of characters allowed
// $max - the maximum number of characters allowed
// returns true if valid, false otherwise
function validate_text_length($text, $min, $max) {
    $length = strlen($text);
    return $length >= $min && $length <= $max;
}

?>
