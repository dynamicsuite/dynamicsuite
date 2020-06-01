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

namespace DynamicSuite\Storable;
use PDOException;

/**
 * Class Storable.
 *
 * @package DynamicSuite\Storable
 */
abstract class Storable
{

    /**
     * Storable constructor.
     *
     * @param array $array
     * @return void
     */
    public function __construct(array $array = [])
    {
        foreach ($array as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }
    }

    /**
     * Validate the storable item for storing in the database.
     *
     * Limits is an associative array, where the key is the column name and the value is the maximum length of that
     * column.
     *
     * This will only work if your column name you are validating is a member of your object.
     *
     * Only checks for errors for the following types:
     *      int
     *      float
     *      string
     *
     * If your column type does not match one of these types, do not include it in the $limits.
     *
     * @param array $limits
     * @return bool
     * @throws PDOException
     */
    public function validate(array $limits): bool
    {
        $errors = [];
        foreach (array_keys($limits) as $key) {
            if (!isset($this->$key)) {
                continue;
            }
            if (
                (is_int($this->$key) && $this->$key > $limits[$key]) ||
                (is_float($this->$key) && $this->$key > $limits[$key]) ||
                (is_string($this->$key) && mb_strlen($this->$key) > $limits[$key])
            ) {
                $errors[$key] = "{$this->$key} > {$limits[$key]}";
                continue;
            }
            if (is_string($this->$key) && mb_strlen($this->$key) === 0) {
                $this->$key = null;
            }
        }
        if ($errors) {
            $message = 'Database item has data that exceeds database limits' . PHP_EOL;
            foreach ($errors as $column) {
                $message .= "  $column" . PHP_EOL;
            }
            throw new PDOException($message);
        } else {
            return true;
        }
    }

    /**
     * Return the storable object as an array.
     *
     * @return array
     */
    public function asArray(): array
    {
        return (array) $this;
    }

}