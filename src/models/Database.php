<?php
// Database.php
class Database
{
    private PDO $connection;
    private array $config;

    //the constructor establishes the connection from the data in the nested
    //associative array that is in $config (from the return in DBConfig)

    public function __construct(array $config)
    {
        $this->config = $config;
        try 
        {
            $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";

            $this->connection = new PDO(
                $dsn,
                $config['database']['username'],

                $config['database']['password'],
                $config['database']['options']);

        }
        catch (PDOException $e)
        {
              throw new Exception("Database connection failed");
        }  
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

 }
