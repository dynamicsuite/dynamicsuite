<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpIncludeInspection
 */

namespace DynamicSuite;
use DynamicSuite\API\Request;
use DynamicSuite\API\Response;
use DynamicSuite\Database\Database;
use Error;
use Exception;
use PDOException;

/**
 * Class DynamicSuite.
 *
 * @package DynamicSuite
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
     * Register included autoload libraries.
     *
     * @param array $libraries
     * @return void
     */
    public static function registerAutoload(array $libraries): void
    {
        spl_autoload_register(function(string $class) use ($libraries) {
            $file = strtr($class, '\\', '/') . '.php';
            foreach ($libraries as $dir) {
                if (@require_once "$dir/$file") {
                    break;
                }
            }
        });
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
        self::registerAutoload($api->autoload);
        try {
            $return = (function() use ($api, $request) {
                foreach ($api->init as $script) {
                    putenv("DS_API_INIT=$script");
                    (function() {
                        require_once getenv('DS_API_INIT');
                    })();
                }
                $_POST = $request->data;
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
                putenv("DS_API_ENTRY=$api->entry");
                return (function() {
                    return require_once getenv('DS_API_ENTRY');
                })();
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