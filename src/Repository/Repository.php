<?php

namespace App\Repository;

use App\Core\Database\MySQLConnection;

abstract class Repository
{
    public function __construct(
        protected MySQLConnection $connection = new MySQLConnection
    ) {
        $this->connection = $connection;
    }

    public function getConnection(): MySQLConnection
    {
        return $this->connection;
    }
}
