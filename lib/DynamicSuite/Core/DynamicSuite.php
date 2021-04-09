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
 */

namespace DynamicSuite\Core;
use DynamicSuite\API\Request;
use DynamicSuite\API\Response;
use DynamicSuite\Database\Database;
use DynamicSuite\Package\Packages;
use Error;
use Exception;
use PDOException;
use function DynamicSuite\ds_log_exception;

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
     * @var View
     */
    public static View $view;

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
            self::$view = $cache['view'];
        } else {
            self::$cfg = new Config('dynamicsuite');
            //self::$view = new View();
            //self::$view->initTemplates();
            if (DS_CACHING) {
                $store = apcu_store($hash, [
                    'cfg' => self::$cfg,
                    'view' => self::$view
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
     * Call an API request.
     *
     * @param URL $request
     * @return Response
     */
    public static function callApi(Request $request): Response
    {
        $prefix = "[API] Package \"$request->package_id\" api \"$request->api_id\"";
        $api = Packages::$loaded[$request->package_id]->apis[$request->api_id] ?? null;
        $local = Packages::$loaded[$request->package_id]->local;
        $response = new Response();
        if (!$api) {
            error_log("$prefix not found");
            return $response;
        }
        foreach ($api->post as $key) {
            if (!array_key_exists($key, $request->data)) {
                error_log("$prefix missing required post key: $key");
                return $response;
            }
        }
        if (!$api->public && (!Session::checkPermissions($api->permissions))) {
            error_log("$prefix authentication required for user \"" . Session::$user_name . '"');
            return $response;
        }
        if (!defined('DS_PKG_DIR')) {
            define('DS_PKG_DIR', DS_ROOT_DIR . "/packages/{$request->package_id}");
        }
        spl_autoload_register(function (string $class) use ($local, $api) {
            if (class_exists($class)) {
                return;
            }
            $file = str_replace('\\', '/', $class) . '.php';
            foreach ($local['autoload'] as $dir) {
                $path = "$dir/$file";
                error_log($path);
                if (DS_CACHING && opcache_is_script_cached($path)) {
                    require_once $path;
                    break;
                } elseif (file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
            foreach ($api->autoload as $dir) {
                $path = "$dir/$file";
                if (DS_CACHING && opcache_is_script_cached($path)) {
                    require_once $path;
                    break;
                } elseif (file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
        });
        try {
            $return = (function () use ($local, $api, $request) {
                foreach ($local['init'] as $script) {
                    require_once $script;
                }
                foreach ($api->init as $script) {
                    require_once $script;
                }
                $_POST = $request->data;
                putenv("DS_API_ENTRY=$api->entry");
                if (DS_DEBUG_MODE) {
                    error_log("[API Request]");
                    error_log("  API: $request->package_id:$request->api_id");
                    error_log('  Script: ' . getenv('DS_API_ENTRY'));
                    foreach(preg_split('/((\r?\n)|(\r\n?))/', json_encode($_POST, JSON_PRETTY_PRINT)) as $i => $line) {
                        if ($i === 0) {
                            error_log('  Data: ' . $line);
                        } else {
                            error_log('  ' . $line);
                        }
                    }
                }
                unset($local, $api, $request);
                return (require_once getenv('DS_API_ENTRY'));
            })();
        } catch (Error | Exception | PDOException $exception) {
            ds_log_exception($exception);
            return new Response('SERVER_ERROR', 'A server error has occurred');
        }
        if ($return instanceof Response) {
            if (DS_DEBUG_MODE) {
                error_log('[API Response]');
                error_log("  API: $request->package_id:$request->api_id");
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
            trigger_error("$prefix bad output");
            return $response;
        }
    }

}