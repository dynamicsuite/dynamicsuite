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

namespace DynamicSuite\Database;
use Exception;
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
final class Database
{

    /**
     * Database connection DSN.
     *
     * @var string|null
     */
    private string $dsn;

    /**
     * Username credential for connection to the database.
     *
     * @var string|null
     */
    private string $user;

    /**
     * Password credential for connection to the database.
     *
     * @var string|null
     */
    private string $pass;

    /**
     * Database connection options.
     *
     * @var array
     */
    private array $options;

    /**
     * Database connection.
     *
     * @var PDO|null
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
    public function __construct(string $dsn, string $user, string $pass, array $options = [])
    {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->pass = $pass;
        $this->options = $options;
    }

    /**
     * Parameter getter magic method.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->$property;
    }

    /**
     * Connect to the database.
     *
     * Returns the new connection on success.
     *
     * @return PDO
     * @throws PDOException
     */
    public function connect(): PDO
    {
        return $this->conn = new PDO($this->dsn, $this->user, $this->pass, $this->options);
    }

    /**
     * A basic prepared statement wrapper with binding of arguments.
     *
     * Arguments are bound to question marks in the query string (?) which must match data in $args as an exact key
     * position indexed from 0.
     *
     * Example:
     *
     * $query:
     *      'SELECT * FROM table WHERE col = ?'
     *
     * $args:
     *      ['col_1_val']
     *
     * Boolean, NULL, and Integers are bound as their respective types. All other arguments are bound as strings.
     *
     * $args must always be an array.
     *
     * If $fetch_single is TRUE, only the first row is returned as an associative array if data is present, or FALSE if
     * no data is present.
     *
     * If the query is a SELECT COUNT query, the count value is returned only as an integer.
     *
     * If the query is a standard select statement and $fetch_single is FALSE, an array is returned where each element
     * is an associative array of the row data. Note: May be empty which will evaluate to FALSE.
     *
     * If the query is an INSERT statement, the affected row count is returned as an integer.
     *
     * $fetch_mode sets the fetch mode for queries that return data. The default is an array containing the fetched
     * rows where each row is an associative array with the key name as the column name and the value as the column
     * value (PDO::FETCH_ASSOC).
     *
     * @param string|Query $query
     * @param array $args
     * @param bool $fetch_single
     * @param int $fetch_mode
     * @return array|int
     * @throws PDOException|Exception
     */
    public function query($query, array $args = [], bool $fetch_single = false, int $fetch_mode = PDO::FETCH_ASSOC)
    {
        if (!$this->conn instanceof PDO && !$this->connect()) {
            throw new PDOException('Database not initialized');
        }
        if ($query instanceof Query) {
            $query->build();
            $args = $query->args;
            $query = $query->query;
        }
        if (defined('DS_DEBUG_MODE') && DS_DEBUG_MODE) {
            error_log('Query Executed:');
            error_log("  Query: $query");
            error_log('  Data:  ' . json_encode($args));
        }
        $stmt = $this->conn->prepare($query);
        if ($args) {
            for ($i = 0, $count = count($args); $i < $count; $i++) {
                if (is_bool($args[$i])) {
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
        if (strcasecmp('SELECT COUNT', substr($query, 0, 12)) === 0) {
            return $stmt->fetch(PDO::FETCH_NUM)[0];
        } elseif (strcasecmp('SELECT', substr($query, 0, 6)) === 0) {
            return $fetch_single ? $stmt->fetch($fetch_mode) : $stmt->fetchAll($fetch_mode);
        } elseif (strcasecmp('INSERT', substr($query, 0, 6)) === 0) {
            return $this->conn->lastInsertId();
        } else {
            return $stmt->rowCount();
        }
    }

    /**
     * Attempt to start a new transaction.
     *
     * @throws PDOException
     */
    public function startTx(): void
    {
        if (!$this->conn instanceof PDO && !$this->connect()) {
            throw new PDOException('Database not initialized');
        }
        $this->conn->beginTransaction();
    }

    /**
     * Attempt to end the current transaction.
     *
     * @throws PDOException
     */
    public function endTx()
    {
        if (!$this->conn instanceof PDO && !$this->connect()) {
            throw new PDOException('Database not initialized');
        }
        try {
            $this->conn->commit();
        } catch (PDOException $exception) {
            $this->conn->rollBack();
        }
    }

}