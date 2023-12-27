<?php

namespace App\Models;

use App\Core\Database\MySQL;

abstract class Model
{
    /**
     * Connection to MySQL database.
     */
    protected MySQL $mysql;

    public function __construct()
    {
        $this->mysql = new MySQL();
    }
}
