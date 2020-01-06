<?php
/*
 * Dynamic Suite
 * Copyright (C) 2019 Dynamic Suite Team
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

namespace DynamicSuite;
use TypeError, RangeException, InvalidArgumentException;

/**
 * Class Query.
 *
 * @package DynamicSuite
 * @property string $type
 * @property bool $distinct
 * @property bool $ignore
 * @property array $columns
 * @property string $table
 * @property array $row_columns
 * @property int $row_count
 * @property array $set
 * @property array $where
 * @property array $order_by
 * @property array $group_by
 * @property int $limit
 * @property string $query
 * @property array $args
 */
class Query extends ProtectedObject
{

    /**
     * DML Query type.
     *
     * @var string
     */
    protected $type;

    /**
     * If the query is a SELECT DISTINCT statement.
     *
     * @var bool
     */
    protected $distinct;

    /**
     * Ignore state for INSERT IGNORE statmenets.
     *
     * @var bool
     */
    protected $ignore;

    /**
     * DML SELECT columns.
     *
     * @var array
     */
    protected $columns;

    /**
     * DML table name.
     *
     * @var string
     */
    protected $table;

    /**
     * Row columns for INSERT statements.
     *
     * @var array
     */
    protected $row_columns;

    /**
     * Row count for INSERT statements.
     *
     * @var int
     */
    protected $row_count;

    /**
     * Array of columns to update for UPDATE statements.
     *
     * @var array
     */
    protected $set;

    /**
     * An array of where clauses.
     *
     * @var array
     */
    protected $where;

    /**
     * An array of order by clauses.
     *
     * @var array
     */
    protected $order_by;

    /**
     * An array of group by clauses.
     *
     * @var array
     */
    protected $group_by;

    /**
     * Query limit.
     *
     * @var int
     */
    protected $limit;

    /**
     * Built query ready to execute.
     *
     * @var string
     */
    protected $query;

    /**
     * Query arguments.
     *
     * @var array
     */
    protected $args;

    /**
     * Set the DML query type.
     *
     * @param string $type
     * @return Query
     */
    protected function setType(string $type): Query
    {
        if ($this->type) trigger_error('Set type on a query that already has a type', E_USER_WARNING);
        $this->type = $type;
        return $this;
    }

    /**
     * Set the query to a SELECT query.
     *
     * @param null|array|string $columns
     * @return Query
     */
    public function select($columns = null): Query
    {
        if (is_string($columns)) {
            $this->columns([$columns]);
        } elseif ($columns) {
            $this->columns($columns);
        }
        return $this->setType('SELECT');
    }

    /**
     * Set the query to a distinct query.
     *
     * @return Query
     * @noinspection PhpUnused
     */
    public function distinct(): Query
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Set the query to an INSERT query.
     *
     * @param null|array $data
     * @return Query
     */
    public function insert(array $data = null): Query
    {
        if ($data) {
            if (count($data) < 1) {
                trigger_error('Invalid data for insert query', E_USER_WARNING);
            }
            if (gettype($data[key($data)]) === 'array') {
                $this->rows($data);
            } else {
                $this->row($data);
            }
        }
        return $this->setType('INSERT');
    }

    /**
     * Ignore flag for INSERT IGNORE queries.
     *
     * @return Query
     * @noinspection PhpUnused
     */
    public function ignore(): Query
    {
        $this->ignore = true;
        return $this;
    }

    /**
     * Set the query to an UPDATE query.
     *
     * @param string $table
     * @return Query
     */
    public function update(string $table): Query
    {
        $this->table = $table;
        return $this->setType('UPDATE');
    }

    /**
     * Set the query to a DELETE query.
     *
     * @return Query
     */
    public function delete(): Query
    {
        return $this->setType('DELETE');
    }

    /**
     * Set the columns to display for SELECT statements.
     *
     * @param array $columns
     * @return Query
     */
    public function columns(array $columns): Query
    {
        foreach ($columns as $key => $column) {
           if (!is_string($column)) {
               trigger_error("Query column is not a string at position $key", E_USER_WARNING);
               unset($columns[$key]);
           }
        }
        $this->columns = array_unique($columns);
        return $this;
    }

    /**
     * Set the table to perform operations on.
     *
     * @param string $table
     * @return Query
     */
    public function setTable(string $table): Query
    {
        if ($this->table) trigger_error('Set table on a query that already has a table', E_USER_WARNING);
        $this->table = $table;
        return $this;
    }

    /**
     * Set the table for SELECT and DELETE statements.
     *
     * @param string $table
     * @return Query
     */
    public function from(string $table): Query
    {
        return $this->setTable($table);
    }

    /**
     * Set the table for INSERT statements.
     *
     * @param string $table
     * @return Query
     */
    public function into(string $table): Query
    {
        return $this->setTable($table);
    }

    /**
     * Set the columns to insert and their associated data for INSERT statements.
     *
     * @param array $rows
     * @return Query
     * @throws TypeError
     */
    public function rows(array $rows): Query
    {
        $this->row_columns = [];
        $this->row_count = 0;
        $this->args = [];
        foreach ($rows as $key => $row) {
            if (!is_array($row)) {
                trigger_error("Query row must be an array at position $key", E_USER_WARNING);
                continue;
            }
            if ($this->row_count && count($row) !== count($this->row_columns)) {
                trigger_error("Query rows are not all the same length at position $key", E_USER_WARNING);
                continue;
            }
            foreach ($row as $column => $value) {
                if (!$this->row_count) {
                    if (!is_string($column)) throw new TypeError('First row columns must match SQL column names');
                    $this->row_columns[] = $column;
                }
                if (!is_scalar($value) && $value !== null) throw new TypeError('Row values must be scalar or null');
                $this->args[] = $value;
            }
            $this->row_count++;
        }
        return $this;
    }

    /**
     * Single row insert (wrapper for Query::rows()).
     *
     * @param array $row
     * @return Query
     * @throws TypeError
     */
    public function row(array $row): Query
    {
        return $this->rows([$row]);
    }

    /**
     * Set the columns to update and their associated data for UPDATE statements.
     *
     * @param array $set
     * @return Query
     * @throws RangeException
     */
    public function set(array $set): Query
    {
        if ($this->type !== 'UPDATE') throw new RangeException('Invalid query type');
        $this->args = [];
        $this->set = [];
        foreach ($set as $key => $value) {
            if (!is_scalar($value) && $value !== null) {
                trigger_error("Non-scalar query value at position $key", E_USER_WARNING);
                continue;
            }
            if (!is_string($key)) {
                trigger_error("Non-string key for query set at position $key", E_USER_WARNING);
                continue;
            }
            $this->set[] = $key;
            $this->args[] = $value;
        }
        return $this;
    }

    /**
     * Add a where clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param $value
     * @param string $type
     * @return Query
     * @throws TypeError
     */
    public function where(string $column, string $operator, $value, string $type = 'AND'): Query
    {
        if (!is_scalar($value) && $value !== null) throw new TypeError('Value must be scalar or null');
        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'type' => $type
        ];
        return $this;
    }

    /**
     * Order by clause.
     *
     * @param string|array $columns
     * @param string $order
     * @return Query
     * @throws InvalidArgumentException
     */
    public function orderBy($columns, string $order = 'ASC'): Query
    {
        if (is_string($columns)) {
            $this->order_by[] = [
                'columns' => $columns,
                'order' => $order
            ];
        } elseif (is_array($columns)) {
            $group = [];
            foreach ($columns as $key => $column) {
                if (!is_string($column)) {
                    trigger_error("Query::orderBy() columns values must be a string at position $key", E_USER_WARNING);
                    continue;
                }
                $group[] = $column;
            }
            $this->order_by[] = [
                'columns' => $group,
                'order' => $order
            ];
        } else {
            throw new InvalidArgumentException('Invalid column type');
        }
        return $this;
    }

    /**
     * Group by clause.
     *
     * @param string|array $columns
     * @return Query
     * @throws InvalidArgumentException
     */
    public function groupBy($columns): Query
    {
        if (is_string($columns)) {
            $this->group_by[] = $columns;
        } elseif (is_array($columns)) {
            $group = [];
            foreach ($columns as $key => $column) {
                if (!is_string($column)) {
                    trigger_error("Query::groupBy() columns values must be a string at position $key", E_USER_WARNING);
                    continue;
                }
                $group[] = $column;
            }
            $this->group_by[] = $group;
        } else {
            throw new InvalidArgumentException('Invalid column type');
        }
        return $this;
    }

    /**
     * Set the query limit.
     *
     * @param int $limit
     * @return Query
     */
    public function limit(int $limit): Query
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Build the query for execution.
     *
     * @return void
     * @throws RangeException
     */
    public function build()
    {
        $this->query = '';
        if (!$this->type) throw new RangeException('Query missing type');
        if (!$this->table) throw new RangeException('Query missing table');
        $this->query .= $this->type;
        switch ($this->type) {
            case 'SELECT':
                if ($this->distinct) $this->query .= ' DISTINCT';
                if ($this->columns) {
                    foreach ($this->columns as $column) {
                        $this->query .= " `$column`, ";
                    }
                    $this->query = rtrim($this->query, ', ');
                } else {
                    $this->query .= ' *';
                }
                $this->query .= " FROM `$this->table`";
                if ($this->where) {
                    $this->query .= ' WHERE ';
                    $clauses_added = 0;
                    foreach ($this->where as $condition) {
                        if ($clauses_added) $this->query .= " {$condition['type']} ";
                        $this->query .= "`{$condition['column']}` {$condition['operator']} ";
                        if ($condition['value'] === null) {
                            $this->query .= 'NULL';
                        } else {
                            $this->query .= '?';
                            $this->args[] = is_bool($condition['value'])
                                ? (int) $condition['value']
                                : $condition['value'];
                        }
                        $clauses_added++;
                    }
                }
                if ($this->order_by) {
                    $this->query .= ' ORDER BY ';
                    foreach ($this->order_by as $group) {
                        if (is_string($group['columns'])) {
                            $this->query .= "`{$group['columns']}`";
                        } else {
                            foreach ($group['columns'] as $value) {
                                $this->query .= "`$value`, ";
                            }
                            $this->query = rtrim($this->query, ', ');
                        }
                        $this->query .= " {$group['order']}, ";
                    }

                }
                if ($this->group_by) {
                    $this->query .= ' GROUP BY ';
                    foreach ($this->group_by as $group) {
                        if (is_string($group)) {
                            $this->query .= "`$group`";
                        } else {
                            foreach ($group as $value) {
                                $this->query .= "`$value`, ";
                            }
                            $this->query = rtrim($this->query, ', ');
                        }
                    }
                }
                break;
            case 'INSERT':
                if (!$this->row_count) throw new RangeException('No data to insert');
                if ($this->ignore) $this->query .= ' IGNORE';
                $this->query .= " INTO `$this->table` (";
                foreach ($this->row_columns as $column) {
                    $this->query .= "`$column`, ";
                }
                $this->query = rtrim($this->query, ', ') . ') VALUES ';
                $column_count = count($this->row_columns);
                for ($i = 0; $i < $this->row_count; $i++) {
                    $content = '';
                    for($c = 0; $c < $column_count; $c++) {
                        $content .= '?, ';
                    }
                    $content = rtrim($content, ', ');
                    $this->query .= "($content), ";
                }
                break;
            case 'UPDATE':
                $this->query .= " `$this->table`";
                if (!$this->set) throw new RangeException('Nothing to update');
                $this->query .= ' SET ';
                foreach ($this->set as $value) {
                    $this->query .= "`$value` = ?, ";
                }
                $this->query = rtrim($this->query, ', ');
                if ($this->where) {
                    $this->query .= ' WHERE ';
                    $clauses_added = 0;
                    foreach ($this->where as $condition) {
                        if ($clauses_added) $this->query .= " {$condition['type']} ";
                        $this->query .= "`{$condition['column']}` {$condition['operator']} ";
                        if ($condition['value'] === null) {
                            $this->query .= 'NULL';
                        } else {
                            $this->query .= '?';
                            $this->args[] = is_bool($condition['value'])
                                ? (int) $condition['value']
                                : $condition['value'];
                        }
                        $clauses_added++;
                    }
                }
                break;
            case 'DELETE':
                $this->query .= " FROM `$this->table`";
                if ($this->where) {
                    $this->query .= ' WHERE ';
                    $clauses_added = 0;
                    foreach ($this->where as $condition) {
                        if ($clauses_added) $this->query .= " {$condition['type']} ";
                        $this->query .= "`{$condition['column']}` {$condition['operator']} ";
                        if ($condition['value'] === null) {
                            $this->query .= 'NULL';
                        } else {
                            $this->query .= '?';
                            $this->args[] = is_bool($condition['value'])
                                ? (int) $condition['value']
                                : $condition['value'];
                        }
                        $clauses_added++;
                    }
                }
                break;
            default:
                throw new RangeException('Unknown query type');
        }
        $this->query = rtrim($this->query, ', ');
        if ($this->limit) $this->query .= " LIMIT $this->limit";
    }

}