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

namespace DynamicSuite;
use DynamicSuite\Core\Instance;
use DynamicSuite\Core\Session;
use DynamicSuite\API\APIEndpoint;
use DynamicSuite\API\APIResponse;
use Error;

ob_start();
require_once '../scripts/create_instance.php';
if (defined('STDIN')) trigger_error('Web script cannot be called from CLI', E_USER_ERROR);
ob_clean();

/** @var $ds Instance */
$ds->registerGlobal('session', new Session($ds));

// Views
if (defined('DS_VIEW')) {
    $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
    if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0; rv:11.0') !== false)) {
        die('Sorry, Internet Explorer is not supported.');
    }
    unset($ua);
    if ($_SERVER['REQUEST_URI'] !== '/') {
        $ds->request->initViewable();
    } else {
        $ds->request->initViewable($ds->cfg->default_view);
    }
    if ($ds->view->setPackageView()) {
        if (!is_readable($ds->view->package->entry)) {
            trigger_error("Package view entry point not readable: {$ds->view->package->entry}", E_USER_WARNING);
            $ds->view->error404();
        } else {
            try {
                if (!$ds->view->package->public) {
                    if (!$ds->session->checkPermissions($ds->view->package->permissions)) {
                        $ds->request->redirect("{$ds->cfg->login_view}?ref={$ds->request->url_string}");
                    }
                    $ds->view->document->replace(['data-ds-session="0"' => 'data-ds-session="1"']);
                }
                define('DS_PKG_DIR', DS_ROOT_DIR . "/packages/{$ds->view->package->package_id}");
                spl_autoload_register(function ($class) {
                    /** @var Instance $ds */
                    global $ds;
                    $file = str_replace('\\', '/', $class) . '.php';
                    foreach ($ds->view->package->resources->autoload as $dir) {
                        if (file_exists("$dir/$file")) {
                            include "$dir/$file";
                            break;
                        }
                    }
                });
                foreach ($ds->view->package->resources->init as $script) include $script;
                ob_start();
                require_once $ds->view->package->entry;
                $ds->view->setViewResources();
                if ($ds->view->package->hide_nav) {
                    $ds->view->document->replace(['{{body}}' => ob_get_clean()]);
                } else {
                    $ds->view->setNavigable();
                    $ds->view->document->replace(['{{view-body}}' => ob_get_clean()]);
                }
            } catch (Error $exception) {
                ob_clean();
                trigger_error($exception->getMessage(), E_USER_WARNING);
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