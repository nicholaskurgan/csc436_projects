<?php   										// Opening PHP tag
	
	// Include the database connection script
	require 'includes/database-connection.php';

	// Retrieve the value of the 'toynum' parameter from the URL query string
	//		i.e., ../toy.php?toynum=0001
	$toy_id = $_GET['toynum'];


	/*
	 * TO-DO: Define a function that retrieves ALL toy and manufacturer info from the database based on the toynum parameter from the URL query string.
	 		  - Write SQL query to retrieve ALL toy and manufacturer info based on toynum
	 		  - Execute the SQL query using the pdo function and fetch the result
	 		  - Return the toy info

	 		  Retrieve info about toy from the db using provided PDO connection
	 */


	 //REVIEW THIS FUNCTION AND GO OVER WHAT TF PDO IS ACTUALLY DOING
	 //DO THE SAME FOR THE SQL CALL AT THE BOTTOM

	 function get_toy_and_manufacturer(PDO $pdo, string $toy_id) {
        // SQL query to retrieve toy and manufacturer information
        $sql = "
            SELECT 
                toy.name AS toy_name,
                toy.description,
                toy.price,
                toy.agerange,
                toy.numinstock,
                toy.imgSrc,
                manuf.name AS manufacturer_name,
                manuf.Street,
				manuf.City,
				manuf.State,
				manuf.ZipCode,
                manuf.phone,
                manuf.contact
            FROM 
                toy
            INNER JOIN 
                manuf 
            ON 
                toy.manid = manuf.manid
            WHERE 
                toy.toynum = '$toy_id'
        ";

        
		
        // Execute the SQL query directly
		//did this instead of prepared statement as this is more straight forward, and i had issues
		//implementing a prepared statement
		$result = $pdo->query($sql);

		// Fetch the result as an associative array
		//kinda get why we need that kind of array but im not fully sure
		return $result->fetch(PDO::FETCH_ASSOC);
    	}


    // Retrieve the toy and manufacturer information
    $toy_info = get_toy_and_manufacturer($pdo, $toy_id);

	//debug tool hopefully
	//var_dump($toy_info);
	//exit;
	//so we shoudl end up with toy_info which is an array containing the info we need, i hope keyed by id 


	//god this better work my head hurts with this

// Closing PHP tag  ?> 

<!DOCTYPE>
<html>

	<head>
		<meta charset="UTF-8">
  		<meta name="viewport" content="width=device-width, initial-scale=1.0">
  		<title>Toys R URI</title>
  		<link rel="stylesheet" href="css/style.css">
  		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
	</head>

	<body>

		<header>
			<div class="header-left">
				<div class="logo">
					<img src="imgs/logo.png" alt="Toy R URI Logo">
      			</div>

	      		<nav>
	      			<ul>
	      				<li><a href="index.php">Toy Catalog</a></li>
	      				<li><a href="about.php">About</a></li>
			        </ul>
			    </nav>
		   	</div>

		    <div class="header-right">
		    	<ul>
		    		<li><a href="order.php">Check Order</a></li>
		    	</ul>
		    </div>
		</header>

		<main>
			<!-- 
			  -- TO DO: Fill in ALL the placeholders for this toy from the db
  			  -->
			
			<div class="toy-details-container">
				<div class="toy-image">
					<!-- Display image of toy with its name as alt text -->
					 <!-- so we use the key for the info within toy_info dynamically -->
					<img src="<?= $toy_info['imgSrc'] ?>" alt="<?= $toy_info['toy_name'] ?>">
				</div>

				<div class="toy-details">

					<!-- Display name of toy -->
			        <h1><?= $toy_info['toy_name'] ?></h1> <!-- ok just repeat these for the different keys -->

			        <hr />

			        <h3>Toy Information</h3>

			        <!-- Display description of toy -->
			        <p><strong>Description:</strong> <?= $toy_info['description'] ?></p>

			        <!-- Display price of toy -->
			        <p><strong>Price:</strong> $ <?= $toy_info['price'] ?></p>

			        <!-- Display age range of toy -->
			        <p><strong>Age Range:</strong> <?= $toy_info['agerange'] ?></p>

			        <!-- Display stock of toy -->
			        <p><strong>Number In Stock:</strong> <?= $toy_info['numinstock'] ?></p>

			        <br />

			        <h3>Manufacturer Information</h3>

			        <!-- Display name of manufacturer -->
			        <p><strong>Name:</strong> <?= $toy_info['manufacturer_name'] ?> </p>

			        <!-- Display address of manufacturer -->
					 <!-- this got me stuck for awhile -->
			        <p><strong>Address:</strong> <?= $toy_info['Street'] ?>, <?= $toy_info['City'] ?>, <?= $toy_info['State'] ?>, <?= $toy_info['ZipCode'] ?></p>

			        <!-- Display phone of manufacturer -->
			        <p><strong>Phone:</strong> <?= $toy_info['phone'] ?></p>

			        <!-- Display contact of manufacturer -->
			        <p><strong>Contact:</strong> <?= $toy_info['contact'] ?></p>
			    </div>
			</div>
		</main>

	</body>
</html>
