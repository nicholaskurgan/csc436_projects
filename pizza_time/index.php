<?php   										// Opening PHP tag
	
	// Include the database connection script
	require 'includes/database-connection.php';

	/*
	 * Retrieve toy information from the database based on the toy ID.
	 * 
	 * @param PDO $pdo       An instance of the PDO class.
	 * @param string $id     The ID of the toy to retrieve.
	 * @return array|null    An associative array containing the toy information, or null if no toy is found.
	 */
	function get_toy(PDO $pdo, string $id) {

		// SQL query to retrieve toy information based on the toy ID
		$sql = "SELECT * 
			FROM toy
			WHERE toynum= :id;";	// :id is a placeholder for value provided later 
		                               // It's a parameterized query that helps prevent SQL injection attacks and ensures safer interaction with the database.


		// Execute the SQL query using the pdo function and fetch the result
		$toy = pdo($pdo, $sql, ['id' => $id])->fetch();		// Associative array where 'id' is the key and $id is the value. Used to bind the value of $id to the placeholder :id in  SQL query.

		// Return the toy information (associative array)
		return $toy;
	}


	//THIS IS THE FIRST HW BIT I THINK

	// Retrieve info about toy with ID '0001' from the db using provided PDO connection
	$toy1 = get_toy($pdo, '0001');
	

	/*
	 * TO-DO: Retrieve info for ALL remaining toys from the db
	 * IDK HOW TO AUTOMATE THIS SO MANUAL IT IS
	 */

	 $toy2 = get_toy($pdo, '0002');
	 $toy3 = get_toy($pdo, '0003');
	 $toy4 = get_toy($pdo, '0004');
	 $toy5 = get_toy($pdo, '0005');
	 $toy6 = get_toy($pdo, '0006');
	 $toy7 = get_toy($pdo, '0007');
	 $toy8 = get_toy($pdo, '0008');
	 $toy9 = get_toy($pdo, '0009');
	 $toy10 = get_toy($pdo, '0010');


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
  			<section class="toy-catalog">

  				<div class="toy-card">
  					<!-- Create a hyperlink to toy.php page with toy number as parameter -->
  					<a href="toy.php?toynum=<?= $toy1['toynum'] ?>">

  						<!-- Display image of toy with its name as alt text -->
  						<img src="<?= $toy1['imgSrc'] ?>" alt="<?= $toy1['name'] ?>">
  					</a>

  					<!-- Display name of toy -->
  					<h2><?= $toy1['name'] ?></h2>

  					<!-- Display price of toy -->
  					<p>$<?= $toy1['price'] ?></p>
  				</div>


  				<!-- 
				  -- TO DO: Fill in the rest of the cards for ALL remaining toys from the db
  				  -->

				<!-- COPY PASTE FIRST IMPLEMENTATION AND TWEAK IT -->

				<div class="toy-card">
  					<!-- Create a hyperlink to toy.php page with toy number as parameter -->
  					<a href="toy.php?toynum=<?= $toy2['toynum'] ?>">

  						<!-- Display image of toy with its name as alt text -->
  						<img src="<?= $toy2['imgSrc'] ?>" alt="<?= $toy2['name'] ?>">
  					</a>

  					<!-- Display name of toy -->
  					<h2><?= $toy2['name'] ?></h2>

  					<!-- Display price of toy -->
  					<p>$<?= $toy2['price'] ?></p>
  				</div>




  				<div class="toy-card">
      <!-- Create a hyperlink to toy.php page with toy number as parameter -->
      <a href="toy.php?toynum=<?= $toy3['toynum'] ?>">

          <!-- Display image of toy with its name as alt text -->
          <img src="<?= $toy3['imgSrc'] ?>" alt="<?= $toy3['name'] ?>">
      </a>

      <!-- Display name of toy -->
      <h2><?= $toy3['name'] ?></h2>

      <!-- Display price of toy -->
      <p>$<?= $toy3['price'] ?></p>
</div>

<div class="toy-card">
      <a href="toy.php?toynum=<?= $toy4['toynum'] ?>">
          <img src="<?= $toy4['imgSrc'] ?>" alt="<?= $toy4['name'] ?>">
      </a>
      <h2><?= $toy4['name'] ?></h2>
      <p>$<?= $toy4['price'] ?></p>
</div>

<div class="toy-card">
      <a href="toy.php?toynum=<?= $toy5['toynum'] ?>">
          <img src="<?= $toy5['imgSrc'] ?>" alt="<?= $toy5['name'] ?>">
      </a>
      <h2><?= $toy5['name'] ?></h2>
      <p>$<?= $toy5['price'] ?></p>
</div>

<div class="toy-card">
      <a href="toy.php?toynum=<?= $toy6['toynum'] ?>">
          <img src="<?= $toy6['imgSrc'] ?>" alt="<?= $toy6['name'] ?>">
      </a>
      <h2><?= $toy6['name'] ?></h2>
      <p>$<?= $toy6['price'] ?></p>
</div>

<div class="toy-card">
      <a href="toy.php?toynum=<?= $toy7['toynum'] ?>">
          <img src="<?= $toy7['imgSrc'] ?>" alt="<?= $toy7['name'] ?>">
      </a>
      <h2><?= $toy7['name'] ?></h2>
      <p>$<?= $toy7['price'] ?></p>
</div>

<div class="toy-card">
      <a href="toy.php?toynum=<?= $toy8['toynum'] ?>">
          <img src="<?= $toy8['imgSrc'] ?>" alt="<?= $toy8['name'] ?>">
      </a>
      <h2><?= $toy8['name'] ?></h2>
      <p>$<?= $toy8['price'] ?></p>
</div>

<div class="toy-card">
      <a href="toy.php?toynum=<?= $toy9['toynum'] ?>">
          <img src="<?= $toy9['imgSrc'] ?>" alt="<?= $toy9['name'] ?>">
      </a>
      <h2><?= $toy9['name'] ?></h2>
      <p>$<?= $toy9['price'] ?></p>
</div>

<div class="toy-card">
      <a href="toy.php?toynum=<?= $toy10['toynum'] ?>">
          <img src="<?= $toy10['imgSrc'] ?>" alt="<?= $toy10['name'] ?>">
      </a>
      <h2><?= $toy10['name'] ?></h2>
      <p>$<?= $toy10['price'] ?></p>
</div>

  			</section>
  		</main>

	</body>
</html>



