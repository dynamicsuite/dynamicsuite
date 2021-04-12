<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Database
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpUnused
 */

namespace DynamicSuite\Database;
use DynamicSuite\DynamicSuite;
use Exception;
use PDO;
use PDOException;

/**
 * Class Query.
 *
 * @package DynamicSuite\Database
 * @property Database|null $database
 * @property string $query
 * @property array $args
 * @property string|null $statement
 * @property bool $ignore
 * @property string|null $table
 * @property string[] $columns
 * @property array $duplicate_key_update
 * @property bool $distinct
 * @property array $joins
 * @property array $where
 * @property array $where_depth
 * @property array $group_by
 * @property array $order_by
 * @property int|null $limit
 * @property int|null $offset
 * @property string|null $table_alias
 * @property string|null $query_alias
 */
final class Query
{

    /**
     * Query string.
     *
     * @var string
     */
    public string $query = '';

    /**
     * Query arguments bound to the position of a question mark "?" in the query string.
     *
     * @var array
     */
    public array $args = [];

    /**
     * Query DML statement.
     *
     * @var string|null
     */
    public ?string $statement = null;

    /**
     * Ignore clause for DML INSERT.
     *
     * @var bool
     */
    public bool $ignore = false;

    /**
     * Main operating table.
     *
     * @var string|null
     */
    public ?string $table = null;

    /**
     * Statement columns.
     *
     * For INSERTs, this will be the column names bound to the values clause.
     *
     * @var string[]
     */
    public array $columns = [];

    /**
     * Row to update on duplicate key inserts.
     *
     * The key must be the column name, and the value is the value to set the column to.
     *
     * @var string[]
     */
    public array $duplicate_key_update = [];

    /**
     * DISTINCT clause for SELECTs.
     *
     * @var bool
     */
    public bool $distinct = false;

    /**
     * JOIN data for simple joins.
     *
     * @var array
     */
    public array $joins = [];

    /**
     * An array of WHERE clauses for queries.
     *
     * @var array
     */
    public array $where = [];

    /**
     * Where depth for nested WHERE clauses.
     *
     * @var array
     */
    public array $where_depth = [];

    /**
     * Group by data for grouping results.
     *
     * This is an array containing GROUP BY columns or expressions to be added to the query.
     *
     * @var string[]|Query[]
     */
    public array $group_by = [];

    /**
     * Order by data for ordering results.
     *
     * This is a 2-dimensional array, where each sub-array is an array containing a key "column" and "sort" where column
     * can be a string, expression, or sub-query and "sort" is the sort direction for the clause (ASC/DESC).
     *
     * @var array[]
     */
    public array $order_by = [];

    /**
     * Result limit (null will return unlimited).
     *
     * @var int|null
     */
    public ?int $limit = null;

    /**
     * Result limit offset (null will use no offset).
     *
     * @var int|null
     */
    public ?int $offset = null;

    /**
     * Table alias for deeper queries.
     *
     * @var string|null
     */
    public ?string $table_alias = null;

    /**
     * Alias for the while query. Only used for sub-queries.
     *
     * @var string|null
     */
    public ?string $query_alias = null;

    /**
     * Query constructor.
     *
     * @return void
     */
    public function __construct(?Database $database = null) {}

    /**
     * DML statement: INSERT.
     *
     * @param array $values
     * @return Query
     * @throws Exception
     */
    public function insert(array $values): Query
    {
        $this->statement = 'INSERT';
        $this->values($values);
        return $this;
    }

    /**
     * Ignore clause for INSERTs.
     *
     * @return Query
     */
    public function ignore(): Query
    {
        $this->ignore = true;
        return $this;
    }

    /**
     * Set the table for the INSERT,
     *
     * @param string $table
     * @return Query
     */
    public function into(string $table): Query
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Values clause for INSERTs.
     *
     * $values must be an array representing a row, where the key name is the column name, and the value is the value to
     * insert for that column.
     *
     * You can pass this method multiple times to insert multiple rows, or pass one 2-dimensional array containing
     * multiple rows.
     *
     * @param array $values
     * @return Query
     * @throws Exception
     */
    public function values(array $values): Query
    {
        $assign_value = function ($values) {
            foreach ($values as $column => $value) {
                if (!is_string($column)) {
                    throw new Exception('Query column must be a string matching the column name');
                }
                if (!in_array($column, $this->columns)) {
                    $this->columns[] = $column;
                }
                if (!is_scalar($value) && $value !== null) {
                    throw new Exception("Query non-scalar value. Column: $column, Value: " . json_encode($value));
                }
                $this->args[] = $value;
            }
        };
        if (count($values) === count($values, COUNT_RECURSIVE)) {
            $assign_value($values);
        } else {
            foreach ($values as $row) {
                if (!is_array($row)) {
                    throw new Exception('Query contains invalid values for INSERT clause');
                }
                $assign_value($row);
            }
        }
        return $this;
    }

    /**
     * On duplicate key update row for INSERTs.
     *
     * @param array $row
     * @return Query
     * @throws Exception
     */
    public function onDuplicateKeyUpdate(array $row): Query
    {
        foreach ($row as $column => $value) {
            if (!is_string($column)) {
                throw new Exception('Query column must be string for on duplicate key update');
            }
            if (!is_scalar($value) && $value !== null) {
                throw new Exception("Query non-scalar value. Column: $column, Value: " . json_encode($value));
            }
            $this->duplicate_key_update[$column] = $value;
        }
        return $this;
    }

    /**
     * DML statement: SELECT.
     *
     * @param array $columns
     * @return Query
     * @throws Exception
     */
    public function select(array $columns = ['*']): Query
    {
        $this->statement = 'SELECT';
        foreach ($columns as $column) {
            if (!is_string($column) && !$column instanceof Query) {
                throw new Exception('Query columns must be strings matching the database column names or sub-queries');
            }
        }
        $this->columns = $columns;
        return $this;
    }

    /**
     * Distinct clause.
     *
     * @return Query
     */
    public function distinct(): Query
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * From table for statements that use FROM for their table selection.
     *
     * @param string $table
     * @param string|null $table_alias
     * @return Query
     */
    public function from(string $table, ?string $table_alias = null): Query
    {
        $this->table = $table;
        $this->table_alias = $table_alias;
        return $this;
    }

    /**
     * Simple join statement.
     *
     * @param string $table
     * @param string $type
     * @return Query
     */
    public function join(string $table, string $type = 'LEFT'): Query
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'on' => []
        ];
        return $this;
    }

    /**
     * Join criteria.
     *
     * This method must be run immediately after the join method.
     *
     * @param string $column_1
     * @param string $operand
     * @param string $column_2
     * @return Query
     */
    public function on(string $column_1, string $operand, string $column_2): Query
    {
        $this->joins[array_key_last($this->joins)]['on'] = [
            'column_1' => $column_1,
            'operand' => $operand,
            'column_2' => $column_2
        ];
        return $this;
    }

    /**
     * DML statement: UPDATE.
     *
     * @param string $table
     * @return Query
     */
    public function update(string $table): Query
    {
        $this->statement = 'UPDATE';
        $this->table = $table;
        return $this;
    }

    /**
     * Verify and set columns for the query.
     *
     * @param array $columns
     * @return Query
     * @throws Exception
     */
    public function set(array $columns): Query
    {
        foreach ($columns as $column => $value) {
            if (!is_string($column)) {
                throw new Exception('Query column name must be a string for UPDATE SET');
            }
            if (!is_scalar($value) && $value !== null) {
                throw new Exception('Query value must be scalar or null for UPDATE SET');
            }
            $this->columns[$column] = $value;
        }
        return $this;
    }

    /**
     * DML statement: DELETE.
     *
     * @return Query
     */
    public function delete(): Query
    {
        $this->statement = 'DELETE';
        return $this;
    }

    /**
     * Add a where clause to the query.
     *
     * @param string|callable $term
     * @param string|null $operand
     * @param mixed $value
     * @param bool $literal
     * @param string $prefix
     * @return Query
     * @throws Exception
     */
    public function where(
        string | callable $term,
        ?string $operand = null,
        $value = null,
        bool $literal = false,
        $prefix = 'AND'
    ): Query {
        $where = &$this->where;
        foreach ($this->where_depth as $key) {
            $where = &$where[$key];
        }
        if ($value === null) {
            $value = 'NULL';
            $literal = true;
        }
        if (!count($where)) {
            $prefix = '';
        }
        if (is_string($term)) {
            if (!is_string($operand)) {
                throw new Exception('Query where operand must be a string');
            }
            if (!is_scalar($value) && $value !== null && $operand !== 'IN' && substr($operand, -3) !== 'ALL') {
                throw new Exception('Query where value must be scalar or null');
            }
            if ($operand === 'IN' && !is_array($value) && !$value instanceof Query) {
                throw new Exception('Query where value must be an array or sub-query when using IN');
            }
            if (substr($operand, -3) === 'ALL' && !$value instanceof Query) {
                throw new Exception('Query where value must be a sub-query when using ALL');
            }
            $where[] = [
                'column' => $term,
                'operand' => $operand,
                'value' => $value,
                'prefix' => $prefix,
                'literal' => $literal
            ];
        } elseif (is_callable($term)) {
            $where[] = [-1 => $prefix];
            end($where);
            $key = key($where);
            $parent_key = array_push($this->where_depth, $key) - 1;
            $term($this);
            unset($this->where_depth[$parent_key]);
        } else {
            throw new Exception('Query where term must be a column string or callable for nested where clauses');
        }
        return $this;
    }

    /**
     * Add a where clause to the query (Wrapper for OR prefix).
     *
     * @param string|callable $term
     * @param string|null $operand
     * @param mixed $value
     * @param bool $literal
     * @return Query
     * @throws Exception
     */
    public function orWhere(
        string | callable $term,
        ?string $operand = null,
        $value = null,
        bool $literal = false
    ): Query {
        return $this->where($term, $operand, $value, $literal, 'OR');
    }

    /**
     * Group by clause.
     *
     * @param string|Query
     * @return Query
     * @throws Exception
     */
    public function groupBy($expression): Query
    {
        if (!is_string($expression) && !$expression instanceof Query) {
            throw new Exception('Query group by expression must be a string or instance of Query for sub-queries');
        }
        $this->group_by[] = $expression;
        return $this;
    }

    /**
     * Clear the order by clause.
     *
     * @return Query
     */
    public function clearOrderBy(): Query
    {
        $this->order_by = [];
        return $this;
    }

    /**
     * Order by clause.
     *
     * @param string|Query $column
     * @param string $sort
     * @return Query
     * @throws Exception
     */
    public function orderBy(string | Query$column, string $sort = 'ASC'): Query
    {
        if (!is_string($column) && !$column instanceof Query) {
            throw new Exception('Query order by column must be a string or instance of Query for sub-queries');
        }
        if ($sort !== 'ASC' && $sort !== 'DESC') {
            throw new Exception('Query order by sort must be ASC or DSC, ' . json_encode($sort) . ' given');
        }
        $this->order_by[] = [
            'column' => $column,
            'sort' => $sort
        ];
        return $this;
    }

    /**
     * Limit clause.
     *
     * @param int $limit
     * @param int|null $offset
     * @return Query
     */
    public function limit(int $limit, ?int $offset = null): Query
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * Create a query alias for sub-queries.
     *
     * @param string $alias
     * @return Query
     */
    public function as(string $alias): Query
    {
        $this->query_alias = $alias;
        return $this;
    }

    /**
     * Build the query string.
     *
     * @return Query
     * @throws Exception
     */
    public function build(): Query
    {
        if (!isset($this->table)) {
            throw new Exception('Query missing table');
        }
        if ($this->statement === 'INSERT' && !$this->columns) {
            throw new Exception('Query has no columns to insert');
        } elseif (!$this->columns) {
            $this->columns = ['*'];
        }
        if ($this->statement === 'INSERT' && !$this->args) {
            throw new Exception('Query has no data to insert');
        }
        if ($this->statement === 'UPDATE' && empty($this->columns)) {
            throw new Exception('Query has no columns to update');
        }
        $this->query = '';
        switch ($this->statement) {
            case 'INSERT':
                $this->query .= 'INSERT ';
                if ($this->ignore) {
                    $this->query .= 'IGNORE ';
                }
                $this->query .= "INTO $this->table ";
                $columns = '';
                foreach ($this->columns as $column) {
                    $columns .= "$column, ";
                }
                $columns = rtrim($columns, ', ');
                $this->query .= "($columns) VALUES ";
                $values_count = count($this->args);
                $column_count = count($this->columns);
                if ($values_count % $column_count !== 0) {
                    throw new Exception('Query argument count does not match row count');
                }
                $values = '(';
                for ($i = 1; $i <= $values_count; $i++) {
                    $values .= '?, ';
                    if ($i % $column_count === 0) {
                        $values = rtrim($values, ', ') . '), (';
                    }
                }
                $this->query .= rtrim($values, ', (');
                if ($this->duplicate_key_update) {
                    $duplicate = ' ON DUPLICATE KEY UPDATE ';
                    foreach ($this->duplicate_key_update as $column => $value) {
                        $duplicate .= "$column = ?, ";
                        $this->args[] = $value;
                    }
                    $duplicate = rtrim($duplicate, ', ');
                    $this->query .= $duplicate;
                }
                break;
            case 'SELECT':
                $this->query .= 'SELECT ';
                if ($this->distinct) {
                    $this->query .= 'DISTINCT ';
                }
                $columns = '';
                foreach ($this->columns as $column) {
                    if (is_string($column)) {
                        $columns .= "$column, ";
                    } else {
                        /** @var Query $column */
                        $column->build();
                        $columns .= "($column->query)";
                        if ($column->query_alias) {
                            $columns .= " AS $column->query_alias, ";
                        } else {
                            $columns .= ', ';
                        }
                        $this->args = array_merge($this->args, $column->args);
                    }
                }
                $columns = rtrim($columns, ', ');
                $this->query .= "$columns ";
                $this->query .= "FROM $this->table";
                if ($this->table_alias) {
                    $this->query .= " AS $this->table_alias";
                }
                if ($this->joins) {
                    $joins = '';
                    foreach ($this->joins as $join) {
                        if (empty($join['on'])) {
                            throw new Exception('Query join missing "on" criteria');
                        }
                        $joins .= " {$join['type']} JOIN {$join['table']} ON ";
                        $joins .= "{$join['on']['column_1']} {$join['on']['operand']} {$join['on']['column_2']}";
                    }
                    $this->query .= $joins;
                }
                if ($this->where) {
                    $this->query .= ' WHERE ' . $this->buildWhere($this->where, $this->args);
                }
                if ($this->group_by) {
                    $group_by = ' GROUP BY ';
                    foreach ($this->group_by as $column) {
                        if ($column instanceof Query) {
                            $column->build();
                            $group_by .= "($column->query), ";
                            $this->args = array_merge($this->args, $column->args);
                        } elseif (is_string($column)) {
                            $group_by .= "$column, ";
                        }
                    }
                    $group_by = rtrim($group_by, ', ');
                    $this->query .= $group_by;
                }
                if ($this->order_by) {
                    $order_by = ' ORDER BY ';
                    foreach ($this->order_by as $order) {
                        $order_by .= "{$order['column']} {$order['sort']}, ";
                    }
                    $order_by = rtrim($order_by, ', ');
                    $this->query .= $order_by;
                }
                if ($this->limit !== null) {
                    $this->query .= " LIMIT $this->limit";
                }
                if ($this->offset !== null) {
                    $this->query .= " OFFSET $this->offset";
                }
                break;
            case 'UPDATE':
                $this->query .= "UPDATE $this->table";
                $columns = '';
                foreach ($this->columns as $column => $value) {
                    $columns .= "$column = ?, ";
                    $this->args[] = $value;
                }
                $columns = rtrim($columns, ', ');
                $this->query .= " SET $columns";
                if ($this->where) {
                    $this->query .= ' WHERE ' . $this->buildWhere($this->where, $this->args);
                }
                break;
            case 'DELETE':
                $this->query .= 'DELETE ';
                $this->query .= "FROM $this->table";
                if ($this->where) {
                    $this->query .= ' WHERE ' . $this->buildWhere($this->where, $this->args);
                }
                break;
            default:
                throw new Exception('Query missing statement type');
        }
        return $this;
    }

    /**
     * Execute the current query.
     *
     * If a database handle was not set when the query was created, the core Dynamic Suite handle will be used.
     *
     * Return value is varied, see: Database::query.
     *
     * @param bool $fetch_single
     * @param int $fetch_mode
     * @return array|int
     * @throws PDOException|Exception
     */
    public function execute(bool $fetch_single = false, int $fetch_mode = PDO::FETCH_ASSOC): array | int
    {
        if ($this->database) {
            return $this->database->query($this, [], $fetch_single, $fetch_mode);
        } else {
            return DynamicSuite::$db->query($this, [], $fetch_single, $fetch_mode);
        }
    }

    /**
     * Build and return the WHERE clause for the query as a string.
     *
     * @param array $where
     * @param array $args
     * @return string
     * @throws Exception
     */
    private function buildWhere(array $where, array &$args): string
    {
        $string = '';
        if (array_key_exists(-1, $where)) {
            $nested = true;
            $string .= " {$where[-1]} (";
            unset($where[-1]);
        } else {
            $nested = false;
        }
        $counter = 0;
        foreach ($where as $expression) {
            if (!isset($expression['column'])) {
                $string .= $this->buildWhere($expression, $args);
            } else {
                if ($counter) {
                    $string .= " {$expression['prefix']} ";
                }
                if ($expression['literal']) {
                    $string .= "{$expression['column']} {$expression['operand']} {$expression['value']}";
                } elseif ($expression['operand'] === 'IN' || $expression['operand'] === '= ALL') {
                    $string .= "{$expression['column']} {$expression['operand']} (";
                    if (is_array($expression['value'])) {
                        foreach ($expression['value'] as $in) {
                            $string .= '?, ';
                            $args[] = $in;
                        }
                        $string = rtrim($string, ', ') . ')';
                    } elseif ($expression['value'] instanceof Query) {
                        $expression['value']->build();
                        $string .= $expression['value']->query . ')';
                        $args = array_merge($args, $expression['value']->args);
                    }
                } else {
                    $string .= "{$expression['column']} {$expression['operand']} ?";
                    $args[] = $expression['value'];
                }
            }
            $counter++;
        }
        if ($nested) {
            $string .= ')';
        }
        return $string;
    }

    /**
     * Column trim expression.
     *
     * @param string $column
     * @param string|null $alias
     * @return string
     */
    public static function trim(string $column, ?string $alias = null): string
    {
        $expression = "TRIM($column)";
        if ($alias) {
            $expression .= " AS $alias";
        }
        return $expression;
    }

    /**
     * Column format expression.
     *
     * @param string $column
     * @param int $decimals
     * @param string|null $alias
     * @return string
     */
    public static function format(string $column, int $decimals = 0, ?string $alias = null): string
    {
        $expression = "FORMAT($column, $decimals)";
        if ($alias) {
            $expression .= " AS $alias";
        }
        return $expression;
    }

    /**
     * Concatenate values expression.
     *
     * If $separator is set, it will return a CONCAT_WS expression.
     *
     * @param string[] $values
     * @param string|null $separator
     * @param string|null $alias
     * @return string
     * @throws Exception
     */
    public static function concat(array $values, ?string $separator = null, ?string $alias = null): string
    {
        if ($separator) {
            $expression = "CONCAT_WS('$separator', ";
        } else {
            $expression = 'CONCAT(';
        }
        foreach ($values as $value) {
            if (!is_string($value)) {
                throw new Exception('Query concat values must be an array of string values to concatenate');
            }
            $expression .= "$value, ";
        }
        $expression = rtrim($expression, ', ') . ')';
        if ($alias) {
            $expression .= " AS $alias";
        }
        return $expression;
    }

}