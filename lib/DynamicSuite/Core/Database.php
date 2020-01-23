<?php
/*
 * Dynamic Suite
 * Copyright (C) 2020 Dynamic Suite Team
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
 */

/** @noinspection PhpUnused */

namespace DynamicSuite\Core;
use DynamicSuite\Base\ProtectedObject;
use DynamicSuite\Util\Query;
use PDO;
use PDOException;

/**
 * Class Database.
 *
 * @package DynamicSuite\Core
 * @property string $dsn
 * @property string $user
 * @property string $pass
 * @property array $options
 * @property PDO|null $conn
 */
final class Database extends ProtectedObject
{

    /**
     * Database DSN.
     *
     * @var string
     */
    protected string $dsn;

    /**
     * Database username.
     *
     * @var string
     */
    protected string $user;

    /**
     * Database password.
     *
     * @var string
     */
    protected string $pass;

    /**
     * Database (PDO) options.
     *
     * @var array
     */
    protected array $options;

    /**
     * Database connection.
     *
     * @var PDO
     */
    protected ?PDO $conn = null;

    /**
     * Database constructor.
     *
     * @param string $dsn
     * @param string $user
     * @param string $pass
     * @param array $options
     * @return void
     */
    public function __construct(string $dsn, string $user = '', string $pass = '', array $options = [])
    {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->pass = $pass;
        $this->options = $options;
    }

    /**
     * Sleep magic method.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['dsn', 'user', 'pass', 'options'];
    }

    /**
     * Connect to the database.
     *
     * @return bool
     */
    public function connect(): bool
    {
        try {
            $this->conn = new PDO($this->dsn, $this->user, $this->pass, $this->options);
            return true;
        } catch (PDOException $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * Set a single database option, overriding the current.
     *
     * @param mixed $key
     * @param mixed $value
     * @return Database
     */
    public function setOption($key, $value): Database
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * A basic prepared statement wrapper with binding of arguments.
     *
     * Arguments are held by a "?" in the query, and match to their value in $args based
     * on key position.
     *
     * Returns an associative array on select statements (which could be empty).
     * Returns the affected row count on all other statements.
     * Returns FALSE on failure and logs the warning.
     *
     * @param string|Query $query
     * @param array $args
     * @return array|int
     * @throws PDOException
     */
    public function query($query, array $args = [])
    {
        if (!$this->conn) {
            $this->connect();
        }
        if (!$this->conn instanceof PDO) {
            throw new PDOException('Tried to execute query on a null connection');
        }
        if ($query instanceof Query) {
            $query->build();
            $args = $query->args;
            $query = $query->query;
        }
        $stmt = $this->conn->prepare($query);
        if ($args) {
            for ($i = 0, $count = count($args); $i < $count; $i++) {
                if ($args[$i] === true || $args[$i] === false) {
                    $type = PDO::PARAM_BOOL;
                } elseif ($args[$i] === null) {
                    $type = PDO::PARAM_NULL;
                } elseif (is_int($args[$i])) {
                    $type = PDO::PARAM_INT;
                } else {
                    $type = PDO::PARAM_STR;
                }
                $stmt->bindValue($i + 1, $args[$i], $type);
            }
        }
        $stmt->execute();
        if (strcasecmp('SELECT', substr($query, 0, 6)) === 0) {
            return $stmt->fetchAll();
        } elseif (strcasecmp('INSERT', substr($query, 0, 6)) === 0) {
            return $this->conn->lastInsertId();
        } else {
            return $stmt->rowCount();
        }
    }

    /**
     * Attempt to start a new transaction.
     *
     * @return Database
     * @throws PDOException
     */
    public function startTx(): Database
    {
        if (!$this->conn instanceof PDO) {
            trigger_error('Tried to start transaction on a null connection', E_USER_WARNING);
            throw new PDOException('Tried to start transaction on a null connection');
        }
        $this->conn->beginTransaction();
        return $this;
    }

    /**
     * Attempt to end a current transaction.
     *
     * @return Database
     * @throws PDOException
     */
    public function endTx(): Database
    {
        if (!$this->conn instanceof PDO) {
            trigger_error('Tried to end transaction on a null connection', E_USER_WARNING);
            throw new PDOException('Tried to end transaction on a null connection');
        }
        try {
            $this->conn->commit();
        } catch (PDOException $exception) {
            $this->conn->rollBack();
        }
        return $this;
    }

}