<?php

namespace App\Repository;

use App\Core\Database\MySQLConnection;
use App\Core\Exceptions\Server\DB\DBException;
use App\Service\UserService;
use App\Entity\User;

class UserRepository
{
    private MySQLConnection $connection;

    public function __construct(MySQLConnection $connection = new MySQLConnection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetch a single user based on its ID.
     * 
     * @param int $id ID of the user.
     */
    public function getUser(int $id): User | null
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT
                u.id,
                u.name,
                u.email,
                u.password,
                u.admin,
                u.createdAt
            FROM users u
            WHERE u.id = :id"
        );

        $req->execute(compact("id"));

        $userRaw = $req->fetch();

        if (!$userRaw) {
            return null;
        }

        $userService = new UserService();

        $user = $userService->makeUserObject($userRaw);

        return $user;
    }

    /**
     * Fetch the users.
     * 
     * @param int $pageNumber     Page number.
     * @param int $pageSize       Number of users to show on a page.
     * 
     * @return array<array-key, \App\Entity\User> 
     */
    public function getUsers(int $pageNumber, int $pageSize): array
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT
                u.id,
                u.name,
                u.email,
                u.admin,
                u.createdAt
            FROM users u
            ORDER BY u.createdAt DESC, id
            LIMIT :limit
            OFFSET :offset"
        );

        $req->bindValue(":limit", $pageSize, \PDO::PARAM_INT);
        $req->bindValue(":offset", $pageSize * ($pageNumber - 1), \PDO::PARAM_INT);

        $req->execute();

        $usersRaw = $req->fetchAll();

        if (!$usersRaw) {
            throw new DBException("Erreur lors de la récupération des utilisateurs.");
        }

        $userService = new UserService();

        $users = array_map(function ($userRaw) use ($userService) {
            $user = $userService->makeUserObject($userRaw);

            return $user;
        }, $usersRaw);

        $req->closeCursor();

        return $users;
    }

    /**
     * Fetch a user's credentials based on its ID.
     * 
     * @param int $id ID of the user.
     */
    public function getUserCredentials(string $email): User|null
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT
                u.id,
                u.password
            FROM users u
            WHERE u.email = :email"
        );

        $req->execute(compact("email"));

        $userRaw = $req->fetch();

        if (!$userRaw) {
            return null;
        }

        $userService = new UserService();

        $user = $userService->makeUserObject($userRaw);

        return $user;
    }

    /**
     * Check if an email address is already used in the database.
     * 
     * @param string $email Email address to be checked.
     * 
     * @return bool `false` is the email address is not used, `true` otherwise.
     */
    public function checkEmailTaken(string $email, ?int $id = null): bool
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT COUNT(*)
            FROM users
            WHERE email = :email
            AND NOT id <=> :id"
        );

        $req->execute([
            "email" => $email,
            "id" => $id,
        ]);

        $emailIsTaken = (bool) $req->fetch(\PDO::FETCH_COLUMN);

        return $emailIsTaken;
    }

    /**
     * Get the amount of users in the database.
     */
    public function getUserCount(): int
    {
        $db = $this->connection;

        $req = $db->query("SELECT COUNT(*) FROM users");

        $count = $req->fetch(\PDO::FETCH_COLUMN);

        return $count;
    }


    /**
     * @return bool `true` on success, `false` on failure.
     */
    public function editUser(User $user): bool
    {
        $db = $this->connection;

        $changePassword = (bool) $user->getPassword();

        $passwordSql = $changePassword ? "password = :password," : "";

        $req = $db->prepare(
            "UPDATE users
            SET
                name = :name,
                email = :email,
                $passwordSql
                admin = :admin
            WHERE
                id = :id"
        );

        $data = [
            "id" => $user->getId(),
            "name" => $user->getName(),
            "email" => $user->getEmail(),
            "admin" => (int) $user->getIsAdmin(),
        ];

        if ($changePassword) {
            $data["password"] = $user->getPassword();
        }

        $success = $req->execute($data);

        return $success;
    }

    public function deleteUser(int $id): bool
    {
        $db = $this->connection;

        $req = $db->prepare("DELETE FROM users WHERE id = :id");

        $success = $req->execute(["id" => $id]);

        return $success;
    }
}
