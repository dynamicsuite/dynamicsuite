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

/**
 * Start buffering and load the instance.
 */
ob_start();
require_once '../scripts/create_instance.php';
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
    if (isset(Packages::$views[URL::$as_string])) {
        $view = &Packages::$views[URL::$as_string];
        if (!is_readable($view->entry)) {
            error_log("View [$view->package_id:$view->view_id] entry not readable $view->entry");
            Render::error500();
        }
        if (!$view->public && !Session::checkPermissions($view->permissions)) {
            URL::redirect(DynamicSuite::$cfg->authentication_view . '?ref=' . URL::$as_string);
        }
        DynamicSuite::registerAutoload($view->autoload);
        Render::$document_template->replace([
            '{{meta_description}}' => Render::$meta_description,
            '{{title}}' => $view->title
        ]);
        if (!Render::$window_data['header_title']) {
            Render::$window_data['header_title'] = $view->title;
        }
        if (!Render::$window_data['nav_tree']) {
            Render::$window_data['nav_tree'] = Render::generateNavTree();
        }
        Render::$window_data['hide_ds'] = $view->hide_ds;
        ob_start();
        (function() use ($view) {
            foreach ($view->init as $script) {
                putenv("DS_VIEW_INIT=$script");
                (function() {
                    require_once getenv('DS_VIEW_INIT');
                })();
            }
            putenv("DS_VIEW_ENTRY=$view->entry");
            (function() {
                require_once getenv('DS_VIEW_ENTRY');
            })();
        })();
        Render::$document_template->replace([
            '{{body}}' => ob_get_clean()
        ]);

        /**
         * Set variable CSS.
         */
        $css_variable = [];
        foreach ($view->css as $path) {
            if (!in_array($path, $css_variable)) {
                $css_variable[] = $path;
            }
        }
        $css_template = '';
        foreach ($css_variable as $path) {
            $css_template .= "<link rel=\"stylesheet\" href=\"$path\">";
        }

        /**
         * Set variable JS.
         */
        $js_variable = [];
        foreach ($view->js as $path) {
            if (!in_array($path, $js_variable)) {
                $js_variable[] = $path;
            }
        }
        $js_template = '';
        foreach ($js_variable as $path) {
            $js_template .= "<script src=\"$path\"></script>";
        }

        /**
         * Set window data.
         */
        $window_data = '<script>window.ds_window_data=' . json_encode(Render::$window_data) . '</script>';

        /**
         * Update and render the template.
         */
        Render::$document_template->replace([
            '<!--{{window_data}}-->' => $window_data,
            '<!--{{css_variable}}-->' => $css_template,
            '<!--{{js_variable}}-->' => $js_template
        ]);
        echo Render::$document_template->contents;

    } else {
        Render::error404();
        if (DynamicSuite::$cfg->error_404_logging) {
            error_log('404: ' . URL::$as_string);
        }
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