<?php
// DBConfig.php
//Options to improve security:  These settings make your database connection 
//more secure (PDO::ATTR_EMULATE_PREPARES => false) -- forces the use of real prepared statements
//Easier to debug (PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
//More convenient for fetching data (PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC)

//create a nested associative array that holds the settings for the database.  Options is nested
//inside database, which is nested inside the array that is returned.  

return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'supremeheir_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    ]
];
