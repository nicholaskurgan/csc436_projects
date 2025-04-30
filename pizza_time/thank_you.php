<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Thank You</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
</head>
<body class="bg-white text-dark d-flex align-items-center justify-content-center vh-100">

  <div class="card text-center border-0 shadow" style="max-width: 24rem;">
    <div class="card-body">
      <h1 class="card-title display-5 mb-3 text-dark">
        Thank you!
      </h1>
      <p class="card-text lead mb-4 text-dark">
        Your account has been successfully created.
      </p>
      <a href="login.php" class="btn btn-dark btn-lg">
        Back to Home
      </a>
    </div>
  </div>

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
