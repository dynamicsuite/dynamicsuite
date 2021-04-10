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
 */

namespace DynamicSuite;
use DynamicSuite\API\Request;
use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Core\URL;

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
 * Execute view or API.
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

    var_dump('in view');

} elseif(DS_API) {
    header('Content-Type: application/json');
    if (count(URL::$as_array) !== 3) {
        error_log('[API] Malformed API request (' . URL::$as_string . ')');
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
} else {
    die('An unknown request was encountered');
}