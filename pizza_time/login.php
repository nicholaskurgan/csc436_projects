<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once 'includes/session.php';
require_once 'includes/database-connection.php';
require_once 'includes/validate.php';


$active_tab = $_GET['tab'] ?? 'login';
$message = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Login Form Submission
    if (isset($_POST['login_email'])) {
        $email = trim($_POST['login_email'] ?? '');
        $password = trim($_POST['login_password'] ?? '');

        if (empty($email) || empty($password)) {
            $message = "Please fill in all fields.";
            $active_tab = 'login';
          } else {
            try {
                // Fetch user with staff position
                $user = pdo($pdo, 
                    "SELECT users.*, staff.position 
                     FROM users 
                     LEFT JOIN staff ON users.staffID = staff.staffID 
                     WHERE email = :email", 
                    ['email' => $email]
                )->fetch();
        
                if ($user && password_verify($password, $user['password_hash'])) {
                    login($email); // Set session
                
                    // Get position or default to empty string
                    $position = $user['position'] ?? '';
                    
                    // Redirect based on position
                    if (in_array($position, ['Manager', 'Owner'])) {
                        header('Location: manage.php');
                        exit;
                    } elseif ($position === 'Server') {
                        header('Location: server_lookup.php');
                        exit;
                    } else {
                        // Handle invalid/unknown positions
                        $message = 'Access denied. Unknown position or non-staff account.';
                        $active_tab = 'login';
                    }
                } else {
                    $message = "Invalid email or password.";
                    $active_tab = 'login';
                }
        
            } catch (PDOException $e) {
                $message = "Login error: " . $e->getMessage();
                $active_tab = 'login';
            }
        }
    }
    
    // Handle Signup Form Submission
    elseif (isset($_POST['signup_email'])) {
        $form_data = [
            'email' => trim($_POST['signup_email'] ?? ''),
            'password' => trim($_POST['signup_password'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
        ];
    
        // Validation
        $valid = true;
        $staffID = null;
    
        // Check if username follows staff naming convention
        if (preg_match('/^[a-z]+_[a-z]+$/', $form_data['username'])) {
            // Split username into first/last names
            [$firstName, $lastName] = explode('_', $form_data['username']);
            
            try {
                // Check staff table for matching name
                $staff = pdo($pdo,
                    "SELECT staffID FROM staff 
                    WHERE LOWER(fname) = ? AND LOWER(lname) = ?",
                    [strtolower($firstName), strtolower($lastName)]
                )->fetch();
    
                if ($staff) {
                    $staffID = $staff['staffID'];
                    
                    // Check if staff already has an account
                    $existing = pdo($pdo,
                        "SELECT email FROM users WHERE staffID = ?",
                        [$staffID]
                    )->fetch();
                    
                    if ($existing) {
                        $message = "Staff account already exists: " . $existing['email'];
                        $valid = false;
                    }
                } else {
                    $message = "Staff member not found. Contact HR.";
                    $valid = false;
                }
            } catch (PDOException $e) {
                $message = "Staff validation error: " . $e->getMessage();
                $valid = false;
            }
        }
    
        // Validate other fields
        if (!validate_email($form_data['email'])) {
            $message = "Invalid email format.";
            $valid = false;
        }
        if (!validate_text_length($form_data['password'], 6, 20)) {
            $message = "Password must be 6-20 characters.";
            $valid = false;
        }
        if (!validate_text_length($form_data['username'], 3, 20)) {
            $message = "Username must be 3-20 characters.";
            $valid = false;
        }
    
        if ($valid) {
            try {
                $password_hash = password_hash($form_data['password'], PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (email, password_hash, username, staffID)
                        VALUES (:email, :password_hash, :username, :staffID)";
                
                pdo($pdo, $sql, [
                    'email' => $form_data['email'],
                    'password_hash' => $password_hash,
                    'username' => $form_data['username'],
                    'staffID' => $staffID // Will be NULL for non-staff
                ]);
                
                header("Location: thank_you.php");
                exit;
            } catch (PDOException $e) {
                $message = ($e->errorInfo[1] == 1062) 
                    ? "Email already registered." 
                    : "Registration error: " . $e->getMessage();
                $active_tab = 'register';
            }
        } else {
            $active_tab = 'register';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login / Sign Up</title>
    <link rel="stylesheet" href="CSS/login_signup.css">
    <link rel="stylesheet" href="CSS/based.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<body>
  <div class="left-side"></div>

  <div class="right-side">
  <section>
    <div class="form-wrapper">
      <div class="tabs">
        <a class="tab <?= $active_tab === 'login' ? 'active' : '' ?>" href="?tab=login">Log In</a>
        <a class="tab <?= $active_tab === 'register' ? 'active' : '' ?>" href="?tab=register">Sign Up</a>
      </div>

      <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <!-- Login Form -->
      <form class="form <?= $active_tab === 'login' ? 'active' : '' ?>" method="POST">
        <div class="mb-3">
          <label for="login_email" class="form-label">Email</label>
          <input type="email" name="login_email" class="form-control" value="<?= htmlspecialchars($_POST['login_email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label for="login_password" class="form-label">Password</label>
          <input type="password" name="login_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark w-100 fw-semibold shadow-sm">Login</button>
      </form>

      <!-- Register Form -->
      <form class="form <?= $active_tab === 'register' ? 'active' : '' ?>" method="POST">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label for="signup_email" class="form-label">Email</label>
            <input type="email" name="signup_email" class="form-control" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label for="signup_password" class="form-label">Password</label>
            <input type="password" name="signup_password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-dark w-100 fw-semibold shadow-sm">Sign Up</button>
      </form>
    </div>
  </section>
  </div>
  <br>
  <br>
</body>
</html>
