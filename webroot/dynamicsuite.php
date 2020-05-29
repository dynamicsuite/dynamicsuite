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
use DynamicSuite\API\APIEndpoint;
use DynamicSuite\API\APIResponse;
use DynamicSuite\Core\View;
use Error;

ob_start();
require_once '../scripts/create_instance.php';
if (defined('STDIN')) trigger_error('Web script cannot be called from CLI', E_USER_ERROR);
ob_clean();

/** @var $ds DynamicSuite */

// Views
if (defined('DS_VIEW')) {
    // Dynamic suite does not work with IE, so check for it
    (function() {
        $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
        if (
            preg_match('~MSIE|Internet Explorer~i', $ua) ||
            strpos($ua, 'Trident/7.0; rv:11.0') !== false ||
            strpos($ua, 'Edge/') !== false
        ) {
            die('Sorry, Internet Explorer and Edge is not supported. Support coming soon!');
        }
    })();
    $pos = strpos($_SERVER['REQUEST_URI'], '?');
    if ($pos !== false) {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $pos);
    }
    if ($_SERVER['REQUEST_URI'] !== '/') {
        $ds->request->initViewable();
    } else {
        $ds->request->initViewable($ds->cfg->default_view);
    }
    (function() {
        global $ds;
        if (DS_CACHING && apcu_exists(DS_ROOT_DIR . '_view')) {
            $ds->set('view', apcu_fetch(DS_ROOT_DIR . '_view'));
        } else {
            $ds->set('view', new View($ds));
            if (DS_CACHING) apcu_store(DS_ROOT_DIR . '_view', $ds->view);
        }
    })();
    if ($ds->view->setPackageView($ds->request->url_string)) {
        if (!is_readable($ds->view->package->entry)) {
            trigger_error("Package view entry not readable: {$ds->view->package->entry}", E_USER_WARNING);
            $ds->view->error404();
        } else {
            try {
                $ds->view->resetDocument();
                $ds->view->resetNav();
                if (!$ds->view->package->public) {
                    if (!$ds->session->checkPermissions($ds->view->package->permissions)) {
                        $ds->request->redirect("{$ds->cfg->login_view}?ref={$ds->request->url_string}");
                    }
                    $ds->view->document->replace(['data-ds-session="0"' => 'data-ds-session="1"']);
                }
                define('DS_PKG_DIR', DS_ROOT_DIR . "/packages/{$ds->view->package->package_id}");
                spl_autoload_register(function (string $class) {
                    if (class_exists($class)) return;
                    global $ds;
                    $file = str_replace('\\', '/', $class) . '.php';
                    foreach ($ds->view->package->resources->autoload as $dir) {
                        $path = DS_ROOT_DIR . "/$dir/$file";
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
                    global $ds;
                    foreach ($ds->view->package->resources->init as $script) {
                        require_once $script;
                    }
                    require_once $ds->view->package->entry;
                })();
                $ds->view->setViewResources();
                if ($ds->view->package->hide_nav) {
                    $ds->view->document->replace(['{{body}}' => ob_get_clean()]);
                } else {
                    $ds->view->setNavigable();
                    $ds->view->document->replace(['{{view-body}}' => ob_get_clean()]);
                }
            } catch (Error $exception) {
                ob_clean();
                error_log(
                    'Dynamic Suite package error!' . PHP_EOL .
                    '  Message: ' . $exception->getMessage() . PHP_EOL .
                    '  File:    ' . $exception->getFile() . PHP_EOL .
                    '  Line:    ' . $exception->getLine() . PHP_EOL .
                    '  Trace:   ' . PHP_EOL . $exception->getTraceAsString()
                );
                $ds->view->error500();
            }
        }
    } elseif ($ds->request->urlIs('/dynamicsuite/about')) {
        $ds->view->about();
    } else {
        if ($ds->cfg->error_404_log) {
            error_log("404 Encountered {$ds->request->url_string} from {$_SERVER['REMOTE_ADDR']}");
        }
        $ds->view->error404();
    }
    die($ds->view->document->contents);
}

// Apis
elseif(defined('DS_API')) {
    $ds->set('api', new APIEndpoint($ds));
    $request = $ds->api->buildExternalRequest();
    if (!$request) {
        $response = new APIResponse();
    } else {
        $response = $ds->api->call($request);
    }
    header('Content-Type: ' . APIEndpoint::CONTENT_TYPE);
    ob_clean();
    die(json_encode([
        'status' => $response->status,
        'message' => $response->message,
        'data' => $response->data
    ]));
}

// Unknown error
else {
    trigger_error('An unknown view was requested', E_USER_ERROR);
}