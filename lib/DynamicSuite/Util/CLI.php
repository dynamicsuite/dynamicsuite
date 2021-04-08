<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Util
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpUnused PhpPureAttributeCanBeAddedInspection
 */

namespace DynamicSuite\Util;

/**
 * Command line utility.
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
     * If $old is set to TRUE, skipping the input (enter) will use the $old value.
     *
     * @param string $prompt
     * @param string|null $old
     * @return string
     */
    public static function in(string $prompt, ?string $old = null): string
    {
        self::out($old ? "$prompt (Enter for '$old'): " : "$prompt: ", false);
        $input = trim(fgets(STDIN));
        if ($old !== null && $input === '') {
            $input = $old;
        }
        return $input;
    }

    /**
     * Display a Yes/No prompt to STDOUT and wait for user input on STDIN.
     *
     * $default can be used to define if Y (TRUE) or N (FALSE) should be used as the default.
     *
     * @param string $prompt
     * @param bool $default
     * @return bool
     */
    public static function yn(string $prompt, bool $default = false): bool
    {
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
     * If $fatal is TRUE, all execution stops after the message is output to STDERR.
     *
     * @param string $text
     * @param bool $fatal
     * @return void
     */
    public static function err(string $text, $fatal = true): void
    {
        fputs(STDERR, $text . PHP_EOL);
        if ($fatal) {
            exit;
        }
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
                    array_map('mb_strlen', array_column($data, $column))
                );
            } else {
                $map[$column] = mb_strlen($column);
            }
            if ($map[$column] < mb_strlen($column)) {
                $map[$column] = mb_strlen($column);
            }
            $break .= str_repeat('-', $map[$column] + 2) . '+';
            $header .= ' ' . str_pad($column, $map[$column]) . ' |';
        }
        $break .= PHP_EOL;
        $table = $break . $header . PHP_EOL . $break;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (array) $row;
                $table .= '|';
                foreach ($map as $key => $length) {
                    if ($row[$key] === null) {
                        $row[$key] = '';
                    }
                    if (is_bool($row[$key])) {
                        $row[$key] = $row[$key] ? 'Y' : 'N';
                    }
                    $table .= ' ' . str_pad($row[$key], $length) . ' |';
                }
                $table .= PHP_EOL;
            }
            return rtrim($table . $break, PHP_EOL);
        } else {
            return 'No Data';
        }
    }

    /**
     * Get a key's value from the given DSN.
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
    public static function readDSNKey(string $dsn, string $key): string
    {
        $value = substr($dsn, strpos($dsn, "$key=") + (mb_strlen($key) + 1));
        $pos = strpos($value, ';');
        if ($pos !== false) {
            $value = substr($value, 0, $pos);
        }
        return $value;
    }

}