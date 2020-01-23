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

namespace DynamicSuite\Util;

/**
 * Class CLI.
 *
 * @package DynamicSuite\Util
 */
final class CLI
{

    /**
     * Output text to the console (STDIN).
     *
     * If $newline is set to FALSE, a line break is not added after output.
     *
     * @param string $text
     * @param bool $newline
     * @return void
     */
    public static function out(string $text, bool $newline = true): void
    {
        fputs(STDOUT, $newline ? $text . PHP_EOL : $text);
    }

    /**
     * Read input from the console (STDIN).
     *
     * $prompt is the string of text displayed before the input area such as "enter a value".
     *
     * The input may display an old/previous value to enter through quickly.
     *
     * @param string $prompt
     * @param string|null $old
     * @return string
     */
    public static function in(string $prompt, ?string $old = null): string
    {
        if (defined('CLI_FORCE') && CLI_FORCE) return (string) $old;
        $prompt = $old ? "$prompt (Enter for `$old`): " : "$prompt: ";
        self::out($prompt, false);
        $input = trim(fgets(STDIN));
        if ($old !== null && $input === '') $input = $old;
        return $input;
    }

    /**
     * Display a Yes/No prompt to STDOUT and wait for user input on STDIN.
     *
     * The $default value is a boolean that should map to a yes/no value.
     *
     * If the -f flag is used, will always return true.
     *
     * @param string $prompt
     * @param bool $default
     * @return bool
     */
    public static function yn(string $prompt, bool $default = false): bool
    {
        if (CLI_FORCE) return true;
        $suffix = $default ? '[Y/n]' : '[y/N]';
        self::out("$prompt $suffix: ", false);
        $input = trim(fgets(STDIN));
        if ($input === '' && $suffix === '[Y/n]') {
            return true;
        } elseif ($input == 'y') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Output text to the console (STDERR).
     *
     * If $fatal is TRUE, all execution stops.
     *
     * @param string $text
     * @param bool $fatal
     * @return void
     */
    public static function err(string $text, $fatal = true): void
    {
        fputs(STDERR, $text . PHP_EOL);
        if ($fatal) exit;
    }

    /**
     * Build a formatted table to display data.
     *
     * @param array $columns
     * @param mixed $data
     * @return string
     */
    public static function table(array $columns, array $data): string
    {
        $header = '|';
        $break = '+';
        $map = [];
        foreach ($columns as $column) {
            if (!empty($data)) {
                $map[$column] = max(
                    array_map('strlen', array_column($data, $column))
                );
            } else {
                $map[$column] = strlen($column);
            }
            if ($map[$column] < strlen($column)) {
                $map[$column] = strlen($column);
            }
            $break .= str_repeat('-', $map[$column] + 2) . '+';
            $header .= ' ' . str_pad($column, $map[$column], ' ') . ' |';
        }
        $break .= PHP_EOL;
        $table = $break . $header . PHP_EOL . $break;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (array) $row;
                $table .= '|';
                foreach ($map as $key => $length) {
                    if ($row[$key] === null) $row[$key] = '(NULL)';
                    $table .= ' ' . str_pad($row[$key], $length, ' ') . ' |';
                }
                $table .= PHP_EOL;
            }
            return rtrim($table . $break, PHP_EOL);
        } else {
            return 'No Data';
        }
    }

    /**
     * A method for splitting a value out of a DSN.
     *
     * Example:
     *
     * $dsn = 'mysql:host=localhost;dbname=my_db;charset=utf8';
     * CLI::splitDSN($dsn, 'host'); // localhost
     * CLI::splitDSN($dsn, 'dbname'); // my_db
     *
     * @param string $dsn
     * @param string $key
     * @return string
     */
    public static function splitDSN(string $dsn, string $key): string
    {
        $value = substr($dsn, strpos($dsn, "$key=") + (strlen($key) + 1));
        $pos = strpos($value, ';');
        if ($pos !== false) $value = substr($value, 0, $pos);
        return $value;
    }

    /**
     * Used to parse script option arguments.
     *
     * For example:
     *
     * If you pass --help or -h to your script, you can verify this by
     * calling:
     *
     * CLI::actionIs('h', optargs('h', ['help'])); // TRUE
     *
     * @param string|array $keys
     * @param array $options
     * @return bool
     */
    public static function actionIs($keys, array $options): bool
    {
        if (is_string($keys)) {
            return array_key_exists($keys, $options);
        } elseif (is_array($keys)) {
            $exists = false;
            foreach($keys as $value) {
                if (!$exists) $exists = array_key_exists($value, $options);
            }
            return $exists;
        } else {
            CLI::err('Error validating script arguments', false);
            return false;
        }
    }

}