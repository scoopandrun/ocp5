<?php

namespace App\Core\Database;

use App\Core\Exceptions\Server\DB\DBConnectionException;

/**
 * Connection to MySQL database.
 */
class MySQL extends \PDO
{
    public function __construct()
    {
        $host = $_ENV["DB_HOST"];
        $port = $_ENV["DB_PORT"];
        $base = $_ENV["DB_BASE"];
        $user = $_ENV["DB_USER"];
        $pass = $_ENV["DB_PASS"];

        try {
            parent::__construct(
                "mysql:host=$host;port=$port;dbname=$base;charset=utf8mb4",
                $user,
                $pass,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    \PDO::MYSQL_ATTR_FOUND_ROWS => true,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            );
        } catch (\PDOException $pdo_exception) {
            throw new DBConnectionException(previous: $pdo_exception);
        }
    }

    /**
     * Vérifie si une entrée existe dans la base de données.
     * 
     * @param string $table Nom de la table.
     * @param int    $id    Identifiant de l'entrée.
     */
    public function exists(string $table, int $id)
    {
        $statement = "SELECT EXISTS (SELECT * FROM `$table` WHERE id = :id)";

        $requete = $this->prepare($statement);
        $requete->execute(["id" => $id]);

        $exists = (bool) $requete->fetch();

        return $exists;
    }
}
