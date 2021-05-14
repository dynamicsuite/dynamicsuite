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
use Error;
use Exception;

/**
 * Start buffering and load the instance.
 */
ob_start();
require_once __DIR__ . '/../include/create_instance.php';
if (defined('STDIN')) {
    die('Web script cannot be called from CLI');
}
ob_clean();

/**
 * Execute the view.
 */
if (DS_VIEW) {

    /**
     * Dynamic Suite does not work with IE, so check for it.
     */
    (function() {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return;
        }
        $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
        if (preg_match('~MSIE|Internet Explorer~i', $ua) || str_contains($ua, 'Trident/7.0; rv:11.0')) {
            die('Sorry, Internet Explorer is not supported.');
        }
    })();

    /**
     * Initialize the renderer.
     */
    Render::init();

    /**
     * Load the view.
     */
    if (Render::$server_error) {
        Render::error500();
    } elseif (str_starts_with(URL::$as_string, '/dynamicsuite/about')) {
        Render::about();
    } elseif (isset(Packages::$views[URL::$as_string])) {

        /**
         * Set reference to the view.
         */
        $view = &Packages::$views[URL::$as_string];

        /**
         * Check if the entry can be served.
         */
        if (!str_starts_with($view->entry, 'vue://') && !is_readable($view->entry)) {
            error_log("View [$view->package_id:$view->view_id] entry not readable $view->entry");
            Render::error500();
        }

        /**
         * Authentication check.
         */
        if (!$view->public && !Session::checkPermissions($view->permissions)) {
            URL::redirect(DynamicSuite::$cfg->authentication_view . '?ref=' . URL::$as_string);
        }

        /**
         * Autoload included libraries.
         */
        DynamicSuite::registerAutoload($view->autoload);

        /**
         * Run view specific initialization scripts.
         */
        try {
            foreach ($view->init as $script) {
                if (!is_readable($script)) {
                    error_log("View init script not found: '$script'");
                    Render::error500();
                }
                putenv("DS_VIEW_INIT=$script");
                (function () {
                    require_once getenv('DS_VIEW_INIT');
                })();
            }
        } catch (Exception | Error $exception) {
            error_log(
                $exception->getMessage() . ' at ' .
                $exception->getFile() . ':' .
                $exception->getLine()
            );
            Render::error500();
        }

        /**
         * Update the client data.
         */
        Render::$client_data['overlay_title'] ??= $view->title;
        Render::$client_data['default_view'] ??= DynamicSuite::$cfg->default_view;
        Render::$client_data['overlay_nav_tree'] ??= Render::generateNavTree();
        Render::$client_data['overlay_nav_footer_text'] ??= DynamicSuite::$cfg->overlay_nav_footer_text;
        Render::$client_data['overlay_nav_footer_view'] ??= DynamicSuite::$cfg->overlay_nav_footer_view;
        Render::$client_data['overlay_actions'] ??= Render::generateOverlayActions();
        Render::$client_data['hide_overlay'] = $view->hide_overlay;
        Render::$client_data['has_session'] = !$view->public;

        /**
         * Execute the view.
         */
        if (!str_starts_with($view->entry, 'vue://')) {
            putenv("DS_VIEW_ENTRY=$view->entry");
            ob_start();
            try {
                (function() {
                    require_once getenv('DS_VIEW_ENTRY');
                })();
            } catch (Exception | Error $exception) {
                error_log(
                    $exception->getMessage() . ' at ' .
                    $exception->getFile() . ':' .
                    $exception->getLine()
                );
                ob_clean();
                Render::error500();
            }
        } else {
            $component = str_replace('vue://', '', $view->entry);
            echo "<$component></$component>";
        }
        Render::$document_template->replace([
            '{{body}}' => ob_get_clean()
        ]);

        /**
         * Set variable resources.
         */
        $css_variable = $js_variable = '';
        foreach (['css', 'js'] as $type) {
            $template = $type . '_variable';
            foreach ($view->$type as $path) {
                if ($type === 'css') {
                    $$template .= "<link rel=\"stylesheet\" href=\"$path\">";
                } else {
                    $$template .= "<script src=\"$path\"></script>";
                }
            }
        }

        /**
         * Update and render the template.
         */
        Render::$document_template->replace([
            '{{meta_description}}' => Render::$meta_description,
            '{{title}}' => Render::$client_data['overlay_title'],
            '"<!--{{client_data}}-->"' => json_encode(Render::$client_data, JSON_HEX_TAG),
            '<!--{{css_variable}}-->' => $css_variable,
            '<!--{{js_variable}}-->' => $js_variable
        ]);
        // Clean up the template
        $replace = [
            '/\>[^\S ]+/s' => '>',
            '/[^\S ]+\</s' => '<',
            '/<!--(.|\s)*?-->/' => '',
        ];
        ob_start();
        echo preg_replace(array_keys($replace), array_values($replace), Render::$document_template->contents);
        if (!ob_get_length()) {
            error_log("Output empty, check that your view is valid HTML at: $view->entry");
        }
        exit;

    } else {
        Render::error404();
    }

}

/**
 * Execute the API request.
 */
elseif(DS_API) {
    header('Content-Type: application/json');
    if (count(URL::$as_array) !== 3) {
        error_log('Malformed API request (' . URL::$as_string . ')');
    }
    $request = new Request(
        URL::$as_array[2],
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

/**
 * Unknown request type.
 */
else {
    die('An unknown request was encountered');
}