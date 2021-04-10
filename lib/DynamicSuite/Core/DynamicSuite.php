<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Core
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpIncludeInspection
 */

namespace DynamicSuite\Core;
use DynamicSuite\API\Request;
use DynamicSuite\API\Response;
use DynamicSuite\Database\Database;
use Error;
use Exception;
use PDOException;

/**
 * Class DynamicSuite.
 *
 * @package DynamicSuite\Core
 */
final class DynamicSuite
{

    /**
     * Global configuration.
     *
     * @var Config
     */
    public static Config $cfg;

    /**
     * Database connection.
     *
     * @var Database
     */
    public static Database $db;

    /**
     * View interface.
     *
     * @var Render
     */
    public static Render $view;

    /**
     * Initialize Dynamic Suite.
     *
     * @return void
     * @throws PDOException
     */
    public static function init(): void
    {
        $hash = 'ds' . crc32(__FILE__);
        if (DS_CACHING && apcu_exists($hash) && $cache = apcu_fetch($hash)) {
            self::$cfg = $cache['cfg'];
        } else {
            self::$cfg = new Config('dynamicsuite');
            if (DS_CACHING) {
                $store = apcu_store($hash, [
                    'cfg' => self::$cfg
                ]);
                if (!$store) {
                    error_log('Error saving "DynamicSuite" in cache, check server config');
                }
            }
        }
        self::$db = new Database(
            self::$cfg->db_dsn,
            self::$cfg->db_user,
            self::$cfg->db_pass,
            self::$cfg->db_options
        );
        URL::init();
        Session::init();
        Packages::init();
        define('DS_DEBUG_MODE', self::$cfg->debug_mode);
    }

    /**
     * Execute an API request.
     *
     * @param Request $request
     * @return Response
     */
    public static function callApi(Request $request): Response
    {
        $api = Packages::$apis[$request->api_id] ?? null;
        if (!$api) {
            error_log("API [$request->api_id] not found");
            return new Response();
        }
        foreach ($api->post as $key) {
            if (!array_key_exists($key, $request->data)) {
                error_log("API [$request->api_id] missing required post key: $key");
                return new Response();
            }
        }
        if (!$api->public && !Session::checkPermissions($api->permissions)) {
            error_log("API [$request->api_id] authentication required");
            return new Response();
        }
        spl_autoload_register(function (string $class) use ($api) {
            if (class_exists($class)) {
                return;
            }
            $file = str_replace('\\', '/', $class) . '.php';
            foreach ($api->autoload as $dir) {
                $path = "$dir/$file";
                if ((DS_CACHING && opcache_is_script_cached($path)) || file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
        });
        try {
            $return = (function () use ($api, $request) {
                foreach ($api->init as $script) {
                    require_once $script;
                }
                $_POST = $request->data;
                putenv("DS_API_ENTRY=$api->entry");
                if (DS_DEBUG_MODE) {
                    error_log("[API Request]");
                    error_log("  API: $request->api_id");
                    error_log('  Script: ' . getenv('DS_API_ENTRY'));
                    foreach(preg_split('/((\r?\n)|(\r\n?))/', json_encode($_POST, JSON_PRETTY_PRINT)) as $i => $line) {
                        if ($i === 0) {
                            error_log('  Data: ' . $line);
                        } else {
                            error_log('  ' . $line);
                        }
                    }
                }
                unset($api, $request);
                return (require_once getenv('DS_API_ENTRY'));
            })();
        } catch (Error | Exception | PDOException $exception) {
            error_log($exception->getMessage());
            return new Response('SERVER_ERROR', 'A server error has occurred');
        }
        if ($return instanceof Response) {
            if (DS_DEBUG_MODE) {
                error_log('[API Response]');
                error_log("  API: :$request->api_id");
                error_log('  Script: ' . getenv('DS_API_ENTRY'));
                foreach(preg_split('/((\r?\n)|(\r\n?))/', json_encode([
                    'status' => $return->status,
                    'message' => $return->message,
                    'data' => $return->data
                ], JSON_PRETTY_PRINT)) as $i => $line) {
                    if ($i === 0) {
                        error_log('  Data: ' . $line);
                    } else {
                        error_log('  ' . $line);
                    }
                }
            }
            return $return;
        } else {
            error_log(
                "API [$request->api_id] entry must return an instance of Response (" . gettype($return) . ' returned)'
            );
            return new Response();
        }
    }

}