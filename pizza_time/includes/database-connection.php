<?php
$type     = 'mysql';                             // Type of database
$server   = '192.185.2.183';                    // Server the database is on
$db       = 'ryanfist_pizza';    // Name of the database
$port     = '3306';                           // Port is usually 3306 in Hostgator
$charset  = 'utf8mb4';                       // UTF-8 encoding using 4 bytes of data per char

$username = 'ryanfist_jimmyzhang';     // Enter YOUR cPanel username and user here
$password = 'Frostfl4me!!';           // Enter YOUR user password here                                    

$options  = [                        // Options for how PDO works
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];                                                                  // Set PDO options

// DO NOT CHANGE ANYTHING BENEATH THIS LINE
$dsn = "$type:host=$server;dbname=$db;port=$port;charset=$charset"; // Create DSN
try {                                                               // Try following code
    $pdo = new PDO($dsn, $username, $password, $options);           // Create PDO object
} catch (PDOException $e) {                                         // If exception thrown
    throw new PDOException($e->getMessage(), $e->getCode());        // Re-throw exception
}

function pdo(PDO $pdo, string $sql, array $arguments = null)
    {
        if (!$arguments) {                   // If no arguments
            return $pdo->query($sql);        // Run SQL and return PDOStatement object
        }
        $statement = $pdo->prepare($sql);    // If arguments prepare statement
        $statement->execute($arguments);     // Execute statement
        return $statement;                   // Return PDOStatement object
    }

?>
