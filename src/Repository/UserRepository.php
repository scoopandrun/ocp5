<?php

namespace App\Repository;

use App\Core\Exceptions\Server\DB\DBException;
use App\Service\UserService;
use App\Entity\User;

class UserRepository extends Repository
{
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
                u.emailVerificationToken,
                u.emailVerified,
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
     * @return array<int, \App\Entity\User> 
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
                u.emailVerified,
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

    public function createUser(User $user): int
    {
        $db = $this->connection;

        $db->beginTransaction();

        $req = $db->prepare(
            "INSERT INTO users
            SET
                name = :name,
                email = :email,
                emailVerificationToken = :emailVerificationToken,
                password = :password"
        );

        $req->execute([
            "name" => $user->getName(),
            "email" => $user->getEmail(),
            "emailVerificationToken" => $user->getEmailVerificationToken(),
            "password" => $user->getPassword(),
        ]);

        $lastInsertId = $db->lastInsertId();

        $db->commit();

        return (int) $lastInsertId;
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
                emailVerified = :emailVerified,
                $passwordSql
                admin = :admin
            WHERE
                id = :id"
        );

        $data = [
            "id" => $user->getId(),
            "name" => $user->getName(),
            "email" => $user->getEmail(),
            "emailVerified" => (int) $user->getEmailVerified(),
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

    public function setEmailVerificationToken(string $email, string $token): bool
    {
        $db = $this->connection;

        $req = $db->prepare(
            "UPDATE users
            SET emailVerificationToken = :token
            WHERE email = :email"
        );

        $success = $req->execute(compact("email", "token"));

        return $success;
    }

    /**
     * Set an email as verified.
     * 
     * @param string $emailVerificationToken 
     * 
     * @return bool `true` if the token is valid, `false` if the token is invalid
     */
    public function verifyEmail(string $emailVerificationToken): bool
    {
        $db = $this->connection;

        $req = $db->prepare(
            "UPDATE users
            SET
                emailVerified = 1,
                emailVerificationToken = NULL
            WHERE emailVerificationToken = :token"
        );

        $req->execute(["token" => $emailVerificationToken]);

        $rowsAffected = $req->rowCount();

        return (bool) $rowsAffected;
    }

    /**
     * 
     * @param string $email 
     * @param string $token 
     * @return int|false `1` if a token has been set (valid e-mail),  
     *                   `0` if no token has been set (unknown e-mail),  
     *                   `false` in case of an error.
     */
    public function setPasswordResetToken(string $email, string $token): int|false
    {
        $db = $this->connection;

        $req = $db->prepare(
            "UPDATE users
            SET passwordResetToken = :token
            WHERE email = :email"
        );

        $success = $req->execute(compact("email", "token"));

        if (!$success) {
            return false;
        }

        $rowsAffected = $req->rowCount();

        return $rowsAffected;
    }

    /**
     * @param string $token Password verification token.
     * 
     * @return bool `true` if the token is valid, `false` otherwise.
     */
    public function checkIfPasswordResetTokenIsRegistered(string $token): bool
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT COUNT(*)
            FROM users
            WHERE passwordResetToken = :token"
        );

        $req->execute(compact("token"));

        $tokenIsValid = (bool) $req->fetch(\PDO::FETCH_COLUMN);

        return $tokenIsValid;
    }

    public function resetPassword(string $token, string $password): bool
    {
        $db = $this->connection;

        $req = $db->prepare(
            "UPDATE users
            SET
                password = :password,
                passwordResetToken = NULL
            WHERE passwordResetToken = :token"
        );

        $success = $req->execute(compact("password", "token"));

        if (!$success) {
            return false;
        }

        $rowsAffected = $req->rowCount();

        return (bool) $rowsAffected;
    }

    /**
     * Fetch an author (= light-weight user) based on its ID.
     * 
     * @param int $id ID of the author.
     */
    public function getAuthor(int $id): User | null
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT
                u.id,
                u.name,
                u.email
            FROM users u
            WHERE u.id = :id"
        );

        $req->execute(compact("id"));

        $authorRaw = $req->fetch();

        if (!$authorRaw) {
            return null;
        }

        $userService = new UserService();

        $author = $userService->makeUserObject($authorRaw);

        return $author;
    }
}
