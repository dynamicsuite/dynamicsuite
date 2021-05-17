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
 * @noinspection PhpUnused
 */

namespace DynamicSuite;
use PDO;

/**
 * Class Config.
 *
 * @package DynamicSuite
 * @property bool $debug_mode
 * @property array $packages
 * @property string $charset
 * @property string $language
 * @property string $meta_description
 * @property string $favicon
 * @property string $default_view
 * @property string $authentication_view
 * @property string|null $overlay_nav_footer_text
 * @property string $overlay_nav_footer_view
 * @property string $document_template
 * @property string $about_template
 * @property string $about_title
 * @property string $error_404_template
 * @property string $error_404_title
 * @property bool $error_404_logging
 * @property string $error_500_template
 * @property string $error_500_title
 * @property string $css_fontawesome
 * @property string $css_dynamicsuite
 * @property string $js_vue
 * @property string $js_dynamicsuite
 * @property string $db_dsn
 * @property string $db_user
 * @property string $db_pass
 * @property array $db_options
 */
final class Config extends JSONConfig
{

    /**
     * Config constructor.
     *
     * @return void
     */
    public function __construct(
        protected bool $debug_mode = false,
        protected array $packages = [],
        protected string $charset = 'UTF-8',
        protected string $language = 'en',
        protected string $meta_description = 'Dynamic Suite',
        protected string $favicon = '/dynamicsuite/public/img/favicon.svg',
        protected string $default_view = '/dynamicsuite/about',
        protected string $authentication_view = '/dynamicsuite/about',
        protected string|null $overlay_nav_footer_text = 'Dynamic Suite',
        protected string $overlay_nav_footer_view = '/dynamicsuite/about',
        protected string $document_template = '/client/templates/document.html',
        protected string $about_template = '/client/templates/about.html',
        protected string $about_title = 'About Dynamic Suite',
        protected string $error_404_template = '/client/templates/error_404.html',
        protected string $error_404_title = 'Page Not Found',
        protected bool $error_404_logging = false,
        protected string $error_500_template = '/client/templates/error_500.html',
        protected string $error_500_title = 'Internal Server Error',
        protected string $css_fontawesome = '/dynamicsuite/public/css/fontawesome.min.css',
        protected string $css_dynamicsuite = '/dynamicsuite/public/css/dynamicsuite.min.css',
        protected string $js_vue = '/dynamicsuite/public/js/vue.min.js',
        protected string $js_dynamicsuite = '/dynamicsuite/public/js/dynamicsuite.min.js',
        protected string $db_dsn = 'mysql:unix_socket=/tmp/mysql.sock;dbname=dynamicsuite;charset=utf8mb4',
        protected string $db_user = '',
        protected string $db_pass = '',
        protected array $db_options = [
            PDO::ATTR_TIMEOUT => 1,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    ) {
        parent::__construct('dynamicsuite');
    }

}