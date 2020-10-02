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

/** @noinspection PhpIncludeInspection */

namespace DynamicSuite;
use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Core\Request;
use DynamicSuite\API\Request as APIRequest;
use DynamicSuite\Core\Session;
use Error;
use Exception;
use PDOException;

ob_start();
require_once '../scripts/create_instance.php';
if (defined('STDIN')) {
    trigger_error('Web script cannot be called from CLI', E_USER_ERROR);
}
ob_clean();

// Views
if (DS_VIEW) {

    // Dynamic Suite does not work with IE, so check for it
    (function() {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return;
        }
        $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
        if (preg_match('~MSIE|Internet Explorer~i', $ua) || strpos($ua, 'Trident/7.0; rv:11.0') !== false) {
            die('Sorry, Internet Explorer is not supported.');
        }
    })();

    // Reset the view
    DynamicSuite::$view->reset();

    // Package view
    if (DynamicSuite::$view->setPackageView(
        Request::$url_string === ''
            ? DynamicSuite::$cfg->default_view
            : Request::$url_string
    )) {
        if (!is_readable(DynamicSuite::$view->structure->entry)) {
            trigger_error("Package view entry not readable: " . DynamicSuite::$view->structure->entry, E_USER_WARNING);
            DynamicSuite::$view->error404();
        } else {
            try {
                if (!DynamicSuite::$view->structure->public) {
                    if (!Session::checkPermissions(DynamicSuite::$view->structure->permissions)) {
                        Request::redirect(DynamicSuite::$cfg->login_view . '?ref=' . Request::$url_string);
                    }
                    DynamicSuite::$view->document->replace([
                        'data-ds-session="0"' => 'data-ds-session="1"'
                    ]);
                }
                define('DS_PKG_DIR', DS_ROOT_DIR . '/packages/' . DynamicSuite::$view->structure->package_id);
                spl_autoload_register(function (string $class) {
                    if (class_exists($class)) {
                        return;
                    }
                    $file = str_replace('\\', '/', $class) . '.php';
                    foreach (DynamicSuite::$view->structure->autoload as $dir) {
                        $path ="$dir/$file";
                        if (DS_CACHING && opcache_is_script_cached($path)) {
                            require_once $path;
                            break;
                        } elseif (file_exists($path)) {
                            require_once $path;
                            break;
                        }
                    }
                });
                ob_start();
                (function () {
                    foreach (DynamicSuite::$view->structure->init as $script) {
                        require_once $script;
                    }
                    if ((require_once DynamicSuite::$view->structure->entry) === false) {
                        DynamicSuite::$view->error500();
                    }
                })();
                DynamicSuite::$view->setViewResources();
                if (DynamicSuite::$view->structure->hide_nav) {
                    DynamicSuite::$view->document->replace([
                        '{{body}}' => ob_get_clean()
                    ]);
                } else {
                    DynamicSuite::$view->setNavigable();
                    DynamicSuite::$view->document->replace([
                        '{{view-body}}' => ob_get_clean()
                    ]);
                }
            } catch (Error | Exception | PDOException $exception) {
                ob_clean();
                ds_log_exception($exception);
                DynamicSuite::$view->error500();
            }
        }
    }

    // About view
    elseif (
        Request::urlIs('/dynamicsuite/about') ||
        (Request::$url_string === '' && DynamicSuite::$cfg->default_view === '/dynamicsuite/about')
    ) {
        DynamicSuite::$view->about();
    }

    // 404/Unknown view
    else {
        if (DynamicSuite::$cfg->error_404_log) {
            error_log("404 Encountered " . Request::$url_string . " from {$_SERVER['REMOTE_ADDR']}");
        }
        DynamicSuite::$view->error404();
    }
    die(DynamicSuite::$view->document->contents);
}

// Apis
elseif(DS_API) {
    header('Content-Type: application/json');
    if (count(Request::$url_array) !== 4) {
        error_log('[API] Malformed API request (' . Request::$url_string . ')');
    }
    $request = new APIRequest(
        Request::$url_array[2],
        Request::$url_array[3],
        json_decode(file_get_contents('php://input'), true)
    );
    $response = DynamicSuite::callApi($request);
    ob_clean();
    die(json_encode([
        'status' => $response->status,
        'message' => $response->message,
        'data' => $response->data
    ]));
}

// Unknown error
else {
    trigger_error('An unknown request was encountered', E_USER_ERROR);
}