<?php

class Database {
    public function __construct(
        private string $host, 
        private string $name, 
        private string $user, 
        private string $password){

    }

    public function getConnection(): PDO {
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";
        return new PDO($dsn, $this->user, $this->password, [
                
                // to make sure it works
                PDO::ATTR_EMULATE_PREPARES => false,

                // if true all values are converted to string
                PDO::ATTR_STRINGIFY_FETCHES => false
            ]);
    }
}