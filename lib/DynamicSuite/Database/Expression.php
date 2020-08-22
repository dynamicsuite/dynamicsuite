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

/**
 * Class Expression.
 *
 * @package DynamicSuite\Database
 * @property string $expression
 * @property array $args
 * @property string|null $type
 * @property string|null $condition
 * @property string|null $then
 * @property string|null $else
 * @property string|null $alias
 */
final class Expression
{

    /**
     * Query expression text.
     *
     * @var string
     */
    public string $expression = '';

    /**
     * Expression arguments.
     *
     * @var array
     */
    public array $args = [];

    /**
     * Expression type.
     *
     * @var string|null
     */
    private ?string $type = null;

    /**
     * Expression condition text.
     *
     * @var string|null
     */
    private ?string $condition = null;

    /**
     * Expression "value if true".
     *
     * @var string|null
     */
    private ?string $then = null;

    /**
     * Expression "value of false".
     *
     * @var string|null
     */
    private ?string $else = null;

    /**
     * Expression alias.
     *
     * @var string|null
     */
    private ?string $alias = null;

    /**
     * SQL IF expression builder.
     *
     * If $null is true, an IFNULL will be used instead of the standard IF.
     *
     * @param string|Query $condition
     * @param bool $null
     * @return Expression
     * @throws Exception
     */
    public function if($condition, bool $null = false): Expression
    {
        if (!is_string($condition) && !$condition instanceof Query) {
            throw new Exception('Expression IF condition must be a string or instance of Query');
        }
        if ($null) {
            $this->type = 'IFNULL';
        } else {
            $this->type = 'IF';
        }
        if (is_string($condition)) {
            $this->condition = "$condition";
        } else {
            /** @var Query $condition */
            $condition->build();
            $this->condition = "($condition->query)";
            $this->args = array_merge($this->args, $condition->args);
        }
        return $this;
    }

    /**
     * IFNULL wrapper function.
     *
     * Alias of Expression::if($condition, true).
     *
     * @param string|Query $condition
     * @return Expression
     * @throws Exception
     */
    public function ifNull($condition): Expression
    {
        return $this->if($condition, true);
    }

    /**
     * Set value if true for IF expressions.
     *
     * @param string|int|Query $value
     * @return Expression
     * @throws Exception
     */
    public function then($value): Expression
    {
        $this->setIfValue($value, true);
        return $this;
    }

    /**
     * Set value if not true for IF expressions.
     *
     * @param string|int|Query $value
     * @return Expression
     * @throws Exception
     */
    public function else($value): Expression
    {
        $this->setIfValue($value, false);
        return $this;
    }

    /**
     * Set an expression alias.
     *
     * @param string $alias
     * @return Expression
     */
    public function as(string $alias): Expression
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Build the expression.
     *
     * @return Expression
     * @throws Exception
     */
    public function build(): Expression
    {
        if (!$this->type) {
            throw new Exception('Expression missing type');
        }
        switch ($this->type) {
            case 'IF':
                $this->expression = "IF($this->condition, $this->then, $this->else)";
                break;
            case 'IFNULL':
                $this->expression = "IFNULL($this->condition, $this->else)";
                break;
            default:
                throw new Exception('Unknown expression type');
        }
        if ($this->alias) {
            $this->expression .= " AS $this->alias";
        }
        return $this;
    }

    /**
     * Set the if value (then or else).
     *
     * @param string|int|Query $value
     * @param bool $if_true
     * @throws Exception
     */
    private function setIfValue($value, bool $if_true = true): void
    {
        $property = $if_true ? 'then' : 'else';
        if (!is_string($value) && !is_int($value) && !$value instanceof Query) {
            throw new Exception("Expression $property value must be a string or instance of Query");
        }
        if (is_string($value)) {
            $this->$property = $value;
        } elseif (is_int($value)) {
            $this->$property = (string) $value;
        } else {
            /** @var Query $value */
            $value->build();
            $this->$property = "($value->query)";
            $this->args = array_merge($this->args, $value->args);
        }
    }

}