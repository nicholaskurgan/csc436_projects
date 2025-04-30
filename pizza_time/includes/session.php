<?php

	// include the database connection script
	require_once 'includes/database-connection.php';


	// start/renew session
	session_start();										 	

	// is user logged in?
	$logged_in = $_SESSION['logged_in'] ?? false; 		      


	// remember user passed login
	function login($email)
	{
		// update session id
    	session_regenerate_id(true);

    	// set logged_in key to true
	    $_SESSION['logged_in'] = true;

	    // set email key to one from form 
	    $_SESSION['email'] = $email;
	}

	// check if user logged in
	function require_login($logged_in)					
	{
		// If not logged in
	    if ($logged_in == false) {
	    	// send to login page 						
	        header('Location: login.php');

	        // stop rest of page running				
	        exit;    									
	    }
	}

	// terminate the session
	function logout()  
	{
		// clear contents of array
	    $_SESSION = [];

	    // get session cookie parameters
	    $params = session_get_cookie_params();

	    // delete session cookie			
	    setcookie('PHPSESSID', '', time() - 3600, $params['path'], $params['domain'],
	        $params['secure'], $params['httponly']);	

	    // delete session file
	    session_destroy();								
	}
	// authenticate user credentials by verifying email and password.
	function authenticate($pdo, $email, $password) {
		// sql query to select password_hash from the db for the provided email
		$sql = "SELECT password_hash FROM users WHERE email = :email";
		// execute query and fetch the user row as an associative array
		$user = pdo($pdo, $sql, ['email' => $email])->fetch();
		// returns true if password  matched
		return $user && password_verify($password, $user['password_hash']);
	}
